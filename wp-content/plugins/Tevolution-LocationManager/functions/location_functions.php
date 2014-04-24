<?php
add_action('tevolution_before_bulk_upload','location_before_bulk_upload');
function location_before_bulk_upload(){
	global $wpdb,$country_table,$zones_table,$multicity_table;
	if(isset($_REQUEST['import_city_csv']) && $_REQUEST['import_city_csv']==1){
	 	$csvfilepath = TEVOLUTION_LOCATION_URL."functions/csv/multi_city.csv";
		wp_redirect($csvfilepath);		
	}
	
	if(isset($_POST['import_city_csv']) && $_POST['import_city_csv']=='Import csv')
	{		
		if($_FILES['bulk_upload_city']['name']!='' && $_FILES['bulk_upload_city']['error']=='0'){
			$filename = $_FILES['bulk_upload_city']['name'];
			$filenamearr = explode('.',$filename);		
			$extensionarr = array('csv','CSV');
			$image_folder_name = '/bulk/';
			$dirinfo = wp_upload_dir();			
			$path = $dirinfo['path'];
			$url = $dirinfo['url'];
			$subdir = $dirinfo['subdir'];
			$basedir = $dirinfo['basedir'];
			$baseurl = $dirinfo['baseurl'];			
			$tmppath = "/csv/";			
			
			if(in_array($filenamearr[count($filenamearr)-1],$extensionarr))
			{
				$destination_path = $basedir . $tmppath;
				if (!file_exists($destination_path))
				{
					mkdir($destination_path, 0777);				
				}
				$target_path = $destination_path . $filename;
				$csv_target_path = $target_path;
				if(move_uploaded_file($_FILES['bulk_upload_city']['tmp_name'], $target_path)) 
				{
					$fd = fopen($target_path, "rt");
				
					$rowcount1 = 0;
					$customKeyarray = array();
					while (!feof ($fd))
					{
						$buffer = fgetcsv($fd, 4096);						
						if($rowcount1 == 0)
						{
							for($k=0;$k<count($buffer);$k++)
							{
								$customKeyarray[$k] = $buffer[$k];
							}
							
							if($customKeyarray[0]=='')
							{
								$url = home_url('/wp-admin/admin.php');
								?>
                                        <div  id="message" class="error" style="padding:10px;width:960px;margin:0 0 10px;">
									<?php 
                                                  $download = "<a href='".get_bloginfo("url")."/wp-admin/admin.php?page=bulk_upload&ptype=csvdl' style='color:#21759B'>download</a>";
                                                  _e("csv file structure doesn't match. Please $download sample csv file to see required structure.",LMADMINDOMAIN);
                                             ?>
                                        </div>	
                                        <?php
							}
						}else{
							$city_id = trim($buffer[0]);
							$country_id = trim($buffer[1]);
							$zone_id = trim($buffer[2]);							
							$cityname = convert_chars(trim($buffer[3]));
							$city_slug=str_replace(' ','-',trim($buffer[4]));
							$city_slug=str_replace('.','',trim($city_slug));
							$city_slug=str_replace(',','-',trim($city_slug));
							if(@$city_slug==''){
								$city_slug=str_replace(' ','-',$cityname);
								$city_slug=str_replace('.','',$city_slug);
								$city_slug=str_replace(',','-',$city_slug);
							}
							$lat = trim($buffer[5]);
							$lng = addslashes($buffer[6]);
							if($lat=='' && $lng==''){
								$http=(is_ssl())?"https://":"http://";
								$v = str_replace(' ','+',convert_chars($cityname));
								$geocode = file_get_contents($http.'maps.google.com/maps/api/geocode/json?address='.$v.'&sensor=false');
								$output= json_decode($geocode);								
								$lat = $output->results[0]->geometry->location->lat;
								$lng = $output->results[0]->geometry->location->lng;
								
							}
							$scall_factor = trim($buffer[7]);							
							$is_zoom_home = addslashes($buffer[8]);	
							$map_type = 	trim($buffer[9]);
							$city_post_type = 	trim($buffer[10]);	
							$category_id = 	trim($buffer[11]);								
							$is_default = ($buffer[12]=="")? 0: addslashes($buffer[12]);
							$message = addslashes($buffer[13]);
							$color = addslashes($buffer[14]);
							$image = addslashes($buffer[15]);
							$header_color = addslashes($buffer[16]);
							$header_image = addslashes($buffer[17]);
							$image_url=$image;
							$header_image_url=$header_image;
							$upload_img_path=$basedir.$image_folder_name._wp_relative_upload_path( $image );
							if($image!='' && file_exists($upload_img_path)){								
								$upload_target_path=$path.'/'.$image;								
								copy($upload_img_path,$upload_target_path );
								
								$image_url=$url.'/'.$image;
								$wp_filetype = wp_check_filetype(basename($image), null );
								$attachment = array(
									 'guid' => $baseurl.$image_folder_name._wp_relative_upload_path( $image ), 
									 'post_mime_type' => $wp_filetype['type'],
									 'post_title' => preg_replace('/\.[^.]+$/', '', basename($image)),
									 'post_content' => '',
									 'post_status' => 'inherit'
								  );		
									
								  $img_attachment=substr($image_folder_name.$image,1);				  
								  $attach_id = wp_insert_attachment( $attachment, $img_attachment, '');
								  require_once(ABSPATH . 'wp-admin/includes/image.php');								 
								  
								  $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_target_path );					  			 
								  wp_update_attachment_metadata( $attach_id, $attach_data );
							
							}	
							
							$upload_img_path=$basedir.$image_folder_name._wp_relative_upload_path( $header_image );
							if($header_image!='' && file_exists($upload_img_path)){
								
								$upload_target_path=$path.'/'.$header_image;								
								copy($upload_img_path,$upload_target_path );
								
								$header_image_url=$url.'/'.$header_image;
								$wp_filetype = wp_check_filetype(basename($header_image), null );
								$attachment = array(
									 'guid' => $baseurl.$image_folder_name._wp_relative_upload_path( $header_image ), 
									 'post_mime_type' => $wp_filetype['type'],
									 'post_title' => preg_replace('/\.[^.]+$/', '', basename($header_image)),
									 'post_content' => '',
									 'post_status' => 'inherit'
								  );		
									
								  $img_attachment=substr($image_folder_name.$header_image,1);				  
								  $attach_id = wp_insert_attachment( $attachment, $img_attachment, '');
								  require_once(ABSPATH . 'wp-admin/includes/image.php');								 
								  
								  $attach_data = wp_generate_attachment_metadata( $attach_id, $upload_target_path );					  			 
								  wp_update_attachment_metadata( $attach_id, $attach_data );
							
							}		
							if($city_id !=""){
								$sql="INSERT INTO ".$multicity_table." (city_id,country_id,zones_id,cityname,city_slug,lat,lng,scall_factor,is_zoom_home,map_type,post_type,categories,is_default,message,color,images,header_color,header_image) VALUES(".$city_id.",".$country_id.",".$zone_id.",'".$cityname."','".$city_slug."','".$lat."','".$lng."',".$scall_factor.",'".$is_zoom_home."','".$map_type."','".$city_post_type."','".$category_id."',".$is_default.",'".$message."','".$color."','".$image_url."','".$header_color."','".$header_image_url."')";
								$wpdb->query($sql);
							}
							
						}
						
						$rowcount1 ++;
						
					}// finish while loop
					
				@unlink($csv_target_path);
				?>
                    <div class="updated fade" style="padding:10px;width:960px;margin:0 0 10px;">
					<?php echo __('Imported your MultiCity Csv file successfully.',LMADMINDOMAIN);?>
                    </div>
                    <?php
				}//finish if condition for move upload file
			}
			
		}// finish if condition for bulk upload city
		
	}// finish if condition for check export city csv
	
	
	if(isset($_REQUEST['dropcities']) && $_REQUEST['dropcities']=='true' && !isset($_POST['import_city_csv'])){
		$wpdb->query("DROP TABLE $multicity_table");
		if($wpdb->get_var("SHOW TABLES LIKE \"$multicity_table\"") != $multicity_table) {
			$create_multicity = "CREATE TABLE IF NOT EXISTS $multicity_table (
			city_id int(8) NOT NULL AUTO_INCREMENT,
			country_id int(8) NOT NULL,		
			zones_id int(8) NOT NULL,
			cityname varchar(255) NOT NULL,
			city_slug varchar(255) NOT NULL,
			lat varchar(255) NOT NULL,
			lng varchar(255) NOT NULL,
			scall_factor int(100) NOT NULL,
			is_zoom_home varchar(100) NOT NULL,
			map_type varchar(1000) NOT NULL,
			post_type text NOT NULL,		
			categories varchar(255) NOT NULL,		
			is_default tinyint(4) NOT NULL DEFAULT '0',
			message text NOT NULL,
			color varchar(255) NOT NULL DEFAULT '',
			images varchar(255) NOT NULL DEFAULT '',
			header_color varchar(255) NOT NULL DEFAULT '',
			header_image varchar(255) NOT NULL DEFAULT '',
			PRIMARY KEY (city_id))DEFAULT CHARSET=utf8";
			$wpdb->query($create_multicity);	
		}
		$wpdb->query("ALTER TABLE $multicity_table AUTO_INCREMENT =1");
		?>
          <div class="updated fade" style="padding:10px;width:960px;margin:0 0 10px;">
			<?php echo __('Drop MultiCity entries successfully.',LMADMINDOMAIN);?>
		</div>
          <?php
	}
	
	
	
	
}
add_action('tevolution_after_bulk_upload','location_after_bulk_upload');
function location_after_bulk_upload(){
	global $wpdb,$wp_query
	?>
     <div class="tevo_sub_title"><?php echo __('CSV City Import',LMADMINDOMAIN);?></div>
     <table class="form-table" >
     <form action="" method="post" enctype="multipart/form-data">
     	<tr><p class="tevolution_desc"><?php echo __('Directly upload all the content of your cities here and check under wp-admin -&gt; Tevolution -&gt; Manage Locations -&gt; Cities. This will help you reduce the manual work of adding each city individually.<br><strong>Tip:</strong> You can directly add/delete your cities their in csv file itself and then upload here, it will save your time.',LMADMINDOMAIN)?></p>
          
          <p><?php echo __('If you want to import cities from GeoPlaces4 to Tevolution-LocationManager plugin with same City ID then',LMADMINDOMAIN); ?>
			<a href="<?php echo home_url()."/wp-admin/admin.php?page=bulk_upload&dropcities=true"; ?>">
				<?php echo __('Click Here',LMADMINDOMAIN);?>
			</a>
			<?php echo __('to drop the current city entries and then import the city CSV exported from your GeoPlaces site here. ',LMADMINDOMAIN); ?></p>
          	<th><?php echo __('Upload your CSV file',LMADMINDOMAIN);?></th>
               <td>
               <input type="file" name="bulk_upload_city" id="bulk_upload_city">
               <br/><br/>
               <input id="submit" class="button button-primary" type="submit" value="<?php echo __('Import csv',LMADMINDOMAIN);?>" name="import_city_csv">
			<?php $sample_download = get_bloginfo("url")."/wp-admin/admin.php?page=bulk_upload&import_city_csv=1";?>
               <p class="description"><?php echo sprintf(__('Download the <a href="%s">sample CSV file</a> to see the correct structure of the Multicity.csv file. To use bulk upload with custom fields simply add them as new columns inside your .csv file at last(at the end). ',LMADMINDOMAIN),$sample_download);?></p>
               
               </td>
          </tr>
     </form>
     </table>
     
     <div class="tevo_sub_title"><?php echo __('CSV City Export',LMADMINDOMAIN);?></div>
     <table class="form-table" >
     <form action="<?php echo TEVOLUTION_LOCATION_URL;?>functions/location_cities_export_csv.php" method="post" enctype="multipart/form-data">
     	<tr><p class="tevolution_desc"><?php echo __('Directly download all the city from here and save it on your hard drive.',LMADMINDOMAIN);?></p>
          	<th><?php echo __('Export City CSV file',LMADMINDOMAIN);?></th>
               <td>
                    <input type="hidden" name="export_city" id="export_city" value="1">               
                    <input id="submit" class="button button-primary" type="submit" value="<?php echo __('Export TO csv',LMADMINDOMAIN);?>" name="export_city_csv">			
               </td>
          </tr>
     </form>
     </table>
     <?php	
}

/*
 * Function Name: directory_after_related_post
 * Return: display the related post type field
 */
add_action('after_related_post','directory_after_related_post');
function directory_after_related_post(){
	$tmpdata = get_option('templatic_settings');
	$distance_factor=$tmpdata['related_radius'];
	?>
     <tr>
          <th><label><?php echo __('Enable distance-aware related posts for',LMADMINDOMAIN);?></label></th>
          <td>
           <?php $templatic_custom_post = get_option('templatic_custom_post');
		 	if(!empty($templatic_custom_post) && $templatic_custom_post!=''){
			foreach ($templatic_custom_post as $key => $val):							
			?>                            
			<div class="element">
				<label for="location_<?php echo $key; ?>"><input type="checkbox" name="related_post_type[]" id="location_<?php echo $key; ?>" value="<?php echo $key; ?>" <?php if(@$tmpdata['related_post_type'] && in_array($key,$tmpdata['related_post_type'])) { echo "checked=checked";  } ?>>&nbsp;<?php echo $val['label']; ?></label>
			</div>
			<?php endforeach;
			}else{
				echo sprintf(__(' No custom post type has been created at your site yet. Please <a href="?page=%s"> create it </a> to list it here.',LMADMINDOMAIN),'custom_taxonomy');	
			}
			
			?>
			<p class="description"><?php echo __('When this option is enabled, related posts will only show if within the distance you set below. The distance is relative to the post currently visited.',LMADMINDOMAIN);?></p>
          </td>
     </tr> 
	  <tr>
          <th><label><?php echo __('Set the distance',LMADMINDOMAIN);?></label></th>
          <td>
               <select id="related_radius" name="related_radius">
                    <option value="1" <?php if(esc_attr($distance_factor)=='1'){ echo 'selected="selected"';} ?>><?php echo __('1 mile',LMADMINDOMAIN); ?></option>
                    <option value="5" <?php if(esc_attr($distance_factor)=='5'){ echo 'selected="selected"';} ?>><?php echo __('5 miles',LMADMINDOMAIN); ?></option>
                    <option value="10" <?php if(esc_attr($distance_factor)=='10'){ echo 'selected="selected"';} ?>><?php echo __('10 miles',LMADMINDOMAIN); ?></option>
                    <option value="100" <?php if(esc_attr($distance_factor)=='100'){ echo 'selected="selected"';} ?>><?php echo __('100 miles',LMADMINDOMAIN); ?></option>
                    <option value="1000" <?php if(esc_attr($distance_factor)=='1000'){ echo 'selected="selected"';} ?>><?php echo __('1000 miles',LMADMINDOMAIN); ?></option>
                    <option value="5000" <?php if(esc_attr($distance_factor)=='5000'){ echo 'selected="selected"';} ?>><?php echo __('5000 miles',LMADMINDOMAIN); ?></option>      
               </select>
			<p class="description"><?php echo __('Specify the distance from which related posts will be pulled. This option is functional only if you enabled distance-aware functionality for your post type.',LMADMINDOMAIN);?></p>
          </td>
     </tr> 
     <?php	
}
/*
 * Function Name: location_columns_manage
 * Return: display custom post type column and column value
 */
add_action( 'admin_init', 'location_columns_manage' );
function location_columns_manage(){
	global $post,$wpdb;	
	$custom_post = get_option('templatic_custom_post');	
	foreach($custom_post as $key=>$val){
		if((isset($_REQUEST['action']) && $_REQUEST['action']=='inline-save') && (isset($_REQUEST['post_type']) && $_REQUEST['post_type']==$key)){
			add_filter('manage_edit-'.$key.'_columns', 'templatic_edit_location_columns',14 );		
		}
		
		add_filter('tevolution_manage_edit-'.$key.'_columns', 'templatic_edit_location_columns',14 );	
		if(isset($_REQUEST['post_type']) && $_REQUEST['post_type']==$key){				
			add_action( 'manage_'.$key.'_posts_custom_column', 'templatic_manage_location_columns', 10, 2 );
		}
	}
	
}
/*
 * Function Name: templatic_edit_location_columns
 * Return: custom post type column name
 */
function templatic_edit_location_columns($columns)
{
	global $wpdb,$typenow, $wp_query,$country_table,$zones_table,$multicity_table;
	
	$location_post_type=get_option('location_post_type');	
	
	if($location_post_type!='' ||!empty($location_post_type)){
		foreach($location_post_type as $post_type){
			$posttype=explode(',',$post_type);			
			 if ($typenow==$posttype[0]) :
				$columns['location']=__('Location',LMADMINDOMAIN);
			endif;			
			if(isset($_REQUEST) && $_REQUEST['action']=='inline-save' && $_REQUEST['post_type']==$posttype[0]){
				$columns['location']=__('Location',LMADMINDOMAIN);
			}
		}
	}
	return $columns;
}
/*
 * Function Name: templatic_manage_location_columns
 * Return: display the custom post type column value as per location post type wise
 */
function templatic_manage_location_columns($column, $post_id ){	
	global $post,$wpdb,$country_table,$zones_table,$multicity_table,$city_log_table;	
	switch($column){
		case 'location' :
			
			if($post->post_parent)
				$post_id = $post->post_parent;
			else
				$post_id = $post->ID;
			$city_id=get_post_meta($post_id,'post_city_id',true);
			if($city_id !=''){
				$sql="SELECT GROUP_CONCAT(cityname) as cityname FROM $multicity_table where city_id in(".$city_id.")";
				$cityname=$wpdb->get_var($sql);
			}else{
				$cityname='-';	
			}
			
			echo ($cityname)?'<p>City: '.$cityname.'</p>':'';
			$address=get_post_meta($post_id,'address',true);
			echo ($address)?'<p>Address: '.$address.'</p>':'';
			break;
	}
}
/*
 * Function Name: directory_posts_custom_column
 * Return: return the city name
 */
add_filter('tevolution_posts_custom_column','location_posts_custom_column',10,2);
function location_posts_custom_column($value,$column){
	global $wpdb,$country_table,$zones_table,$multicity_table,$city_log_table;
	if($column=='post_city_id'){
		$sql="SELECT GROUP_CONCAT(cityname) as cityname FROM $multicity_table where city_id in(".$value.")";
		$cityname=$wpdb->get_var($sql);
		$value=$cityname;
	}
	return $value;
}
add_filter('tevolution_export_csv','location_export_csv',10,2);
add_filter('tevolution_field_value','location_export_field_value',10,2);
function location_export_csv($custom_fields,$post_type){
	
	$location_post_type=get_option('location_post_type');	
	if($location_post_type!='' ||!empty($location_post_type)){
		foreach($location_post_type as $location_post){
			$posttype=explode(',',$location_post);	
			if($posttype[0]==$post_type){
				$custom_fields['country_id']=array('name'=>'country_id','ctype'=>'text');
				$custom_fields['zones_id']=array('name'=>'zones_id','ctype'=>'text');
			}
		}
	}
	return $custom_fields;
}
function location_export_field_value($post_value,$key){
	if($key=='country_id')
	{
		return $post_value;
	}
	//product price
	if($key=='zones_id')
	{
		return $post_value;
	}
	return $post_value;	
}
/*
 * Function Name: location_post_by_multicity
 * Return: this function only display on particular location post type if its allowed on post type for filtering from backend
 */
function location_post_by_multicity(){
	global $wpdb,$typenow, $wp_query,$country_table,$zones_table,$multicity_table;
	
	$location_post_type=get_option('location_post_type');	
	$countryinfo = $wpdb->get_results("SELECT  distinct  c.country_id,c.country_name,GROUP_CONCAT(mc.cityname) as cityname, GROUP_CONCAT(mc.city_id) as city_id   FROM $country_table c,$multicity_table mc where c.`country_id`=mc.`country_id`  AND c.is_enable=1 group by country_name order by country_name ASC");	
	
	if($location_post_type!='' ||!empty($location_post_type)){
		foreach($location_post_type as $post_type){
			$posttype=explode(',',$post_type);	
			 if ($typenow==$posttype[0]) :
				?>
                    <select name='multi_city_id' id='multi_city_id'>
                    	<option value=""><?php _e( 'Select a city', LMADMINDOMAIN )?></option>
                    <?php foreach($countryinfo as $country):?>
                    	<optgroup label="<?php echo $country->country_name?>">
                         <?php
					   $city_name=explode(',',$country->cityname);
					   $city_id=explode(',',$country->city_id);					   
					   for($i=0;$i<count($city_name);$i++){
						   $selected=(isset($_REQUEST['multi_city_id']) && $_REQUEST['multi_city_id']==$city_id[$i])? 'selected="selected"':'';
							echo '<option value="'.$city_id[$i].'"  '.$selected.'>'.$city_name[$i].'</option>';
					   } 
					   ?>
                            </optgroup>
                    <?php endforeach;?>
                    </select>
                    <?php	
    			 endif;
		}
	}
   
}
add_action('restrict_manage_posts','location_post_by_multicity');
add_action('pre_get_posts','location_backend_pre_get_posts');
function location_backend_pre_get_posts($query){
	global $wpdb,$typenow, $wp_query,$country_table,$zones_table,$multicity_table;	
	$location_post_type=get_option('location_post_type');
	if(is_admin() && isset($_REQUEST['multi_city_id']) && $_REQUEST['multi_city_id']!=''){
		
		if($location_post_type!='' ||!empty($location_post_type)){
			foreach($location_post_type as $post_type){
				$posttype=explode(',',$post_type);	
				 if ($typenow==$posttype[0]) :
					add_filter('posts_where','location_filter_multicity_backend');
				 endif;
			}
		}
	}
}

/*
 * Function Name: location_citywise_metadescription
 * Return: display meta description as per on city wise home page url
 */
add_action('wp_head','location_citywise_metadescription',0);
function location_citywise_metadescription(){
	global $current_cityinfo,$wpdb,$multicity_table;
	$city_slug=get_option('location_multicity_slug');	
	$multi_city=($city_slug)? $city_slug : 'city';
	if(strstr($_SERVER['REQUEST_URI'],'/'.$multi_city.'/') && (is_home() || is_front_page())){
		$current_city = explode('/'.$multi_city.'/',$_SERVER['REQUEST_URI']);	
		if(strstr($current_city[1],'/')){
			$current_city = explode('/',$current_city[1]);
			$current_city = str_replace('/','',$current_city[0]);
		}else{
			$current_city = str_replace('/','',$current_city[1]);			
		}
		$sql=$wpdb->prepare("SELECT * FROM $multicity_table where city_slug=%s",$current_city);
		$default_city = $wpdb->get_results($sql);		
		if ($default_city[0]->message!=''){
			if(function_exists('supreme_meta_description')){
				remove_action( 'wp_head', 'supreme_meta_description', 1 );
			}
			echo $description = "\n".'<meta name="description" content="' . str_replace( array( "\r", "\n", "\t" ), '', esc_attr( strip_tags( $default_city[0]->message ) ) ) . '" />' . "\n";
		}
	}
}

/* 
 * Function Name: location_filter_multicity_backend
 * Return: return post where for filter city wise post on backend
 */
function location_filter_multicity_backend($where){
	
	global $wpdb,$country_table,$zones_table,$multicity_table,$city_log_table,$current_cityinfo;	
	if(isset($_REQUEST['multi_city_id']) && $_REQUEST['multi_city_id']!=''){
		$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='post_city_id' and FIND_IN_SET( ". $_REQUEST['multi_city_id'].", pm.meta_value ))";
	} 
	return $where;
}
add_filter('tevolution_submited_email','location_multicity_email',10,2);
function location_multicity_email($value,$html_vars){
	global $wpdb,$country_table,$zones_table,$multicity_table,$city_log_table,$current_cityinfo;
	if($html_vars=='post_city_id'){
		$sql = $wpdb->get_results("SELECT * FROM $multicity_table where city_id in($value) order by cityname ASC");
		$value=$sql[0]->cityname;
	}
	return $value;
}
add_action('tevolution_category_query','location_category_query');
function location_category_query(){
	add_filter( 'terms_clauses','locationwise_change_category_query',10,3 );	
}
/*
 * Function Name: locationwise_change_category_query
 * Return: display category count as per city wise
 */
function locationwise_change_category_query($pieces , $taxonomies, $args){
	global $wpdb,$current_cityinfo;
	
	$location_post_type=explode(',',implode(',',get_option('location_post_type')));
	$location_taxonomies=implode(',',$taxonomies);	
	
	if (in_array($location_taxonomies,$location_post_type) && !empty($current_cityinfo) && $current_cityinfo['city_id']!='') {	
		$pieces['fields']= " distinct t1.term_id,t1.*, tt1.term_taxonomy_id ,tt1.term_id,tt1.taxonomy,tt1.description,tt1.parent, count( DISTINCT p.ID) as count from $wpdb->posts p, $wpdb->postmeta m, $wpdb->terms t1, $wpdb->term_taxonomy tt1, $wpdb->term_relationships tr where p.post_status='publish' AND p.ID=m.post_id and m.meta_key='post_city_id' AND FIND_IN_SET( ".$current_cityinfo['city_id'].", m.meta_value ) and t1.term_id = tt1.term_id and tt1.taxonomy IN ('".$location_taxonomies."') and p.ID=tr.object_id AND tr.term_taxonomy_id=tt1.term_taxonomy_id and t1.term_id in(select t.term_id";
		$pieces['order'].=" ) group by t1.term_id ORDER BY t1.name ASC";
	}	
	return $pieces;
}


add_action('show_map_shortcode_content','show_map_shortcode_content');
function show_map_shortcode_content()
{
	global $wpdb,$post;
	?>
 	<div class="listing-title">
		 <?php do_action('templ_post_title');                /* do action for display the single post title */?>
          <?php
        if($tmpdata['templatin_rating']=='yes'):?>
            <div class="listing_rating">
                <div class="directory_rating_row"><span class="single_rating"> <?php echo draw_rating_star_plugin(get_post_average_rating($post->ID));?> </span></div>
            </div>
        <?php elseif(is_plugin_active('Templatic-MultiRating/multiple_rating.php') && function_exists('single_average_rating')):?>
            <div class="listing_rating">
                <div class="directory_rating_row"><span class="single_rating"> <?php echo get_single_average_rating_image($post->ID);?> </span></div>
             </div>	
        <?php endif;
        $address=get_post_meta($post->ID,'address',true);
        $phone=get_post_meta($post->ID,'phone',true);
        echo ($phone)? '<p class="phone">'.$phone.'</p>' : '';
        echo ($address)? '<p class="address" >'.$address.'</p>' : '';		
        ?>
     </div>
<?php
}



add_filter('tevolution_taxonomies_rules_data','location_multicity_rules_data');
function location_multicity_rules_data($tevolution_taxonomies_rules_data){
	if(isset($_POST['tevolution_location_multicity'])){
		$tevolution_taxonomies_rules_data['tevolution_location_multicity']=$_POST['tevolution_location_multicity'];
		$multi_city_slug=($_POST['tevolution_location_multicity']!='')? $_POST['tevolution_location_multicity'] : 'city';
		update_option('location_multicity_slug',$multi_city_slug);
	}	
	return $tevolution_taxonomies_rules_data;
}

add_action('tevolution_bottom_taxonimies_permalink','location_multicity_permalink');
function location_multicity_permalink($tevolution_taxonomies_data){
	?>
     <div class="tevolution-section">
          
          <table class="form-table tevolution-inputs-taxonomies">                   
               <tr valign="top">
                    <th scope="row" style="width:18%;"><?php echo __('Change City Base', ADMINDOMAIN); ?></th>
                    <td >                        
                         <div class="clearfix">
                              <input type="text" name="tevolution_location_multicity" value="<?php echo $tevolution_taxonomies_data['tevolution_location_multicity']; ?>" />
                              <p><code><?php echo get_bloginfo('url');?>/<em><?php echo __('(city-base)', ADMINDOMAIN);?></em>/new-york</code></p>
                         </div>
                    </td>                         
               </tr>               
          </table>
     </div>
     <?php
}

/*
Save the json data on option table
*/
add_action('save_post','tmpl_save_map_json_data',15);

function tmpl_save_map_json_data($post_id){
	if(isset($_REQUEST['page']) && $_REQUEST['page']=='templatic_system_menu'){
		return '';	
	}
	global $wpdb;
	$multicity_table = $wpdb->prefix . "multicity";	
	$post_city_id = $_SESSION['post_city_id'];

	$post_data = get_post($post_id);

	if(isset($_REQUEST['post_type'])){
		$post_type = $_REQUEST['post_type'];
	}else{
		$post_type = $post_data->post_type;
	}
	
	if($post_city_id)
		$cityinfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM $multicity_table where city_id=%d",$post_city_id));
		
	delete_transient($cityinfo[0]->city_slug."_".$post_type.'_map_data');
}

?>