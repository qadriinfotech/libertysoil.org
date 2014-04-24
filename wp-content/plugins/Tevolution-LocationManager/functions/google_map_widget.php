<?php
add_action('widgets_init','directory_googlemap_widgets_init');
function directory_googlemap_widgets_init(){
	register_widget('widget_googlemap_homepage');
}
/* BOF - Home page Google map widget - FOr multicity */
class widget_googlemap_homepage extends WP_Widget {
	function widget_googlemap_homepage() {	
		$widget_ops = array('classname' => 'widget Google Map in Home page', 'description' => __('Display a Google map with custom icons and marker clusters while operating multiple cities. Widget works best inside the Homepage Slider or Homepage - Main Content area.',LMADMINDOMAIN) );		
		$this->WP_Widget('googlemap_homepage', __('T &rarr; Homepage Map - multi city',LMADMINDOMAIN), $widget_ops);
	}
	function widget($arg, $instance) {
		global $current_cityinfo;
		$height = empty($instance['height']) ? '425' : apply_filters('widget_height', $instance['height']);
		$clustering = empty($instance['clustering']) ? '' : apply_filters('widget_clustering', $instance['clustering']);
		$city_category_id=($current_cityinfo['categories']!='')? explode(',',$current_cityinfo['categories']) :array();
		if(!empty($city_category_id)){
			$post_info=(strstr($current_cityinfo['post_type'],','))? explode(',',$current_cityinfo['post_type']):array($current_cityinfo['post_type']) ;		
		}else{
			$post_info=array();
		}		
		$city_category_id=($current_cityinfo['categories']!='')? explode(',',$current_cityinfo['categories']) :array();		
		
		$tmpdata = get_option('templatic_settings');			
		$maptype=($current_cityinfo['map_type'] != '')? $current_cityinfo['map_type']: 'ROADMAP';		
		$latitude    = $current_cityinfo['lat'];
		$longitude   = $current_cityinfo['lng'];
		$map_type    = ($current_cityinfo['map_type']) ? $current_cityinfo['map_type'] : 'ROADMAP';
		$map_display = $current_cityinfo['is_zoom_home'];
		$zoom_level  = ($current_cityinfo['scall_factor']) ? $current_cityinfo['scall_factor'] : 3;
		
		wp_print_scripts( 'google-maps-apiscript' );		
		wp_print_scripts( 'google-clusterig' );		
		wp_print_scripts( 'widget-googlemap-js' );
		
		$google_map_customizer=get_option('google_map_customizer');// store google map customizer required formate.
		?>		
        <script type="text/javascript">
		var map_latitude= '<?php echo $latitude?>';
		var map_longitude= '<?php echo $longitude?>';
		var map_zomming_fact= <?php echo $zoom_level;?>;		
		<?php if($map_display == 1) { ?>
		var multimarkerdata = new Array();
		<?php }?>
		var zoom_option = '<?php echo $map_display; ?>';
		var markers = '';		
		var markerArray = [];
		var ClustererMarkers=[];
		var m_counter=0;
		var map = null;
		var mgr = null;
		var mc = null;		
		var mClusterer = null;
		var showMarketManager = false;
		var PIN_POINT_ICON_HEIGHT = 32;
		var PIN_POINT_ICON_WIDTH = 20;
		var clustering = '<?php echo $clustering; ?>';
		var infobox;
		var infoBubble;
		function initialize(){		
			var isDraggable = jQuery(document).width() > 480 ? true : false;
			var myOptions = {
				scrollwheel: false,
				draggable: isDraggable,
				zoom: map_zomming_fact,
				center: new google.maps.LatLng(map_latitude, map_longitude),
				mapTypeId: google.maps.MapTypeId.<?php echo $map_type;?>
			}
			map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
			
			var styles = [<?php echo substr($google_map_customizer,0,-1);?>];			
			map.setOptions({styles: styles});
			// Initialize Fluster and give it a existing map		 
			mgr = new MarkerManager( map );
			
		}
		
		
		google.maps.event.addDomListener(window, 'load', initialize);
		google.maps.event.addDomListener(window, 'load', google_map_initialize_onload);
		</script>         
          <div id="map_sidebar" class="map_sidebar">
          <div class="top_banner_section_in clearfix">
               <div class="TopLeft"><span id="triggermap"></span></div>
               <div class="TopRight"></div>
               <div class="iprelative">
               	<div id="map_canvas" style="width: 100%; height:<?php echo $height;?>px" class="map_canvas"></div>               
                    <div id="map_loading_div" style="width: 100%; height:<?php echo $height;?>px; display: none;"></div>                     
                    <div id="map_marker_nofound"><?php _e('<p>Your selected category do not have any records yet at your current location.</p>',LDOMAIN); ?></div>     
               </div>             
              
               <form id="ajaxform" name="slider_search" class="pe_advsearch_form" action="javascript:void(0);"  onsubmit="return(new_googlemap_ajaxSearch());">
                	<div class="paf_search"><input  type="text" class="" id="search_string" name="search_string" value="" placeholder="<?php _e('Title or Keyword',LDOMAIN);?>" onclick="this.placeholder=''" onmouseover="this.placeholder='<?php _e('Title or Keyword',LDOMAIN);?>'"/></div>
               
               <?php if($post_info):$tevolution_post=get_option('templatic_custom_post');	?>
               	<div class="paf_row map_post_type" id="toggle_postID" style="display:block; max-height:<?php echo $height-105;?>px;">
                    <?php for($c=0;$c<count($post_info);$c++):
							if($post_info[$c])
							{	
							$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_info[$c],'public'   => true, '_builtin' => true ));	
							?>
					<div class="mw_cat_title">
                    <label><input type="checkbox" data-category="<?php echo str_replace("&",'&amp;',$post_info[$c]).'categories';?>" onclick="newgooglemap_initialize(this,'');"  value="<?php echo str_replace("&",'&amp;',$post_info[$c]);?>"  <?php if(!empty($_POST['posttype']) && !in_array(str_replace("&",'&amp;',$post_info[$c]) ,$_POST['posttype'])):?> <?php else:?> checked="checked" <?php endif;?> class="<?php echo str_replace("&",'&amp;',$post_info[$c]).'custom_categories';?>" id="<?php echo str_replace("&",'&amp;',$post_info[$c]).'custom_categories';?>" name="posttype[]"> <?php echo ($tevolution_post[$post_info[$c]]['label'])? $tevolution_post[$post_info[$c]]['label']: ucfirst($post_info[$c]);?></label><span id='<?php echo $post_info[$c].'_toggle';?>' class="toggle_post_type toggleon" onclick="custom_post_type_taxonomy('<?php echo $post_info[$c].'categories';?>',this)"></span></div>
                        
                         <div class="custom_categories <?php echo str_replace("&",'&amp;',$post_info[$c]).'custom_categories';?>" id="<?php echo str_replace("&",'&amp;',$post_info[$c]).'categories';?>" >
                         	 <?php homepage_map_wp_terms_checklist(0, array( 'taxonomy' =>$taxonomies[0],'post_type'=>$post_info[$c],'selected_cats'=>$city_category_id) );?>
                         </div>
                         
                    <?php }
						endfor;?>
                    </div>
                    <div id="toggle_post_type" class="paf_row toggleon" onclick="toggle_post_type();"></div>
                    <?php endif;?>
               </form>     
               
          </div>
          </div>
          <script>
		var maxMap = document.getElementById( 'triggermap' );		
		google.maps.event.addDomListener(maxMap, 'click', showFullscreen);
		function showFullscreen() {
			  // window.alert('DIV clicked');
			    jQuery('#map_sidebar').toggleClass('map-fullscreen');
				jQuery('#map_canvas').toggleClass('map-fullscreen');
				jQuery('.map_category').toggleClass('map_category_fullscreen');
				jQuery('.map_post_type').toggleClass('map_category_fullscreen');
				jQuery('#toggle_post_type').toggleClass('map_category_fullscreen');
				jQuery('#trigger').toggleClass('map_category_fullscreen');
				jQuery('body').toggleClass('body_fullscreen');
				jQuery('#loading_div').toggleClass('loading_div_fullscreen');
				jQuery('#advmap_nofound').toggleClass('nofound_fullscreen');
				jQuery('#triggermap').toggleClass('triggermap_fullscreen');
				
				jQuery('.TopLeft').toggleClass('TopLeft_fullscreen');		
					 //map.setCenter(darwin);
					 window.setTimeout(function() { 
					var center = map.getCenter(); 
					google.maps.event.trigger(map, 'resize'); 
					map.setCenter(center); 
			   		}, 100);			 }
		</script>
          <?php
          
          
	}
	/*Widget update function */
	function update($new_instance, $old_instance) {
		//save the widget
		return $new_instance;
	}
	
	/*Widget admin form display function */
	function form($instance) {
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array(  'height' => 500,'clustering'=> '') );		
		$height = strip_tags($instance['height']);
		$clustering = strip_tags($instance['clustering']);
		?>
	
		<p>
		 <label for="<?php echo $this->get_field_id('height'); ?>"><?php echo __('Map Height: <small>(Default is 500px. To change enter a numeric value.)</small>',LMADMINDOMAIN);?>
		 <input class="widefat" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr($height); ?>" />
		 </label>
	    </p>
		<p>
		<?php if($clustering) { $checked = "checked=checked"; }else{ $checked =''; } ?>
		 <label for="<?php echo $this->get_field_id('clustering'); ?>">
		 <input id="<?php echo $this->get_field_id('clustering'); ?>" name="<?php echo $this->get_field_name('clustering'); ?>" type="checkbox" value="1" <?php echo $checked; ?>/>
		 <?php echo __('Disable Clustering',LMADMINDOMAIN); ?></label>
	    </p>
	    <?php
	}
}
/* EOF - Home page Google map widget - FOr multicity */
/*Name:utf_special_char
Desc: special character replace */
function utf_special_char($s)
{
 $out = "";

 for ($i=0; $i<strlen($s); $i++) 
 {
  $c1 = substr ($s, $i, 1);
  $byte1 = ord ($c1);
  if ($byte1>>5 == 6) // 110x xxxx, 110 prefix for 2 bytes unicode
  {
   $i++;
   $c2 = substr ($s, $i, 1);
   $byte2 = ord ($c2);
   $byte1 &= 31; // remove the 3 bit two bytes prefix
   $byte2 &= 63; // remove the 2 bit trailing byte prefix
   $byte2 |= (($byte1 & 3) << 6); // last 2 bits of c1 become first 2 of c2
   $byte1 >>= 2; // c1 shifts 2 to the right

   $word = ($byte1<<8) + $byte2;
   if ($word==1025) $out .= chr(168);                   
   elseif ($word==1105) $out .= chr(184);               
   elseif ($word>=0x0410 && $word<=0x044F) $out .= chr($word-848); 
   else
   {  
     $a = dechex($byte1);
     $a = str_pad($a, 2, "0", STR_PAD_LEFT);
     $b = dechex($byte2);
     $b = str_pad($b, 2, "0", STR_PAD_LEFT);
     $out .= "&#x".$a.$b.";";
   }
  }
  else 
  {
   $out .= $c1;
  }
 }

 return $out;
}

/*
 * Function Name: googlemap_initialize
 * Return: send the google map marker popup info in jason
 */
add_action('wp_ajax_nopriv_googlemap_initialize','googlemap_initialize');
add_action('wp_ajax_googlemap_initialize','googlemap_initialize');
function googlemap_initialize(){
	global $wpdb,$current_cityinfo;
	if(isset($_REQUEST['lang']) && $_REQUEST['lang']!=""){
		$_COOKIE['_icl_current_language']=$_REQUEST['lang'];
	}
	$j=0;
	$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
	$pids=array("");
	$srcharr = array('"');
	$replarr = array('\"');
	$title_srcharr = array('"');
	$title_replarr = array('\"');
	$post_type =(explode(',',substr($_REQUEST['posttype'],0,-1)));
	$categoryname =(explode(',',substr($_REQUEST['categoryname'],0,-1)));
	$templatic_settings=get_option('templatic_settings');	
	for($i=0;$i<count($post_type);$i++){
		
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type[$i],'public'   => true, '_builtin' => true ));	
		$cat_args = array(
					'taxonomy'=>$taxonomies[0],
					'orderby' => 'name', 				
					'hierarchical' => 'true',
					'title_li'=>''
				);		
		$r = wp_parse_args( $cat_args);
		
		if ( false === ( $catname_arr = get_transient( '_tevolution_query_googlemapwidget'.$post_type[$i].$cur_lang_code ))  && get_option('tevolution_cache_disable')==1 ) {
			$catname_arr=get_categories( $r );
			set_transient( '_tevolution_query_googlemapwidget'.$post_type[$i].$cur_lang_code, $catname_arr, 12 * HOUR_IN_SECONDS );				
		}elseif(get_option('tevolution_cache_disable')==''){
			$catname_arr=get_categories( $r );
		}	
		$cat_ID='';
		foreach($catname_arr as $cat){
			if(!in_array($cat->term_id,$categoryname))
				continue;
				
			$cat_ID.= $cat->term_id.',';
		}
		$args3=array('post_type'      => trim($post_type[$i]),
				   'posts_per_page' => 400,
				   'post_status'    => 'publish',
				   'tax_query'      => array(
									  array(
										 'taxonomy' => $taxonomies[0],
										 'field'    => 'id',
										 'terms'    => explode(',',$cat_ID),
										 'operator' => 'IN'
									  )
								   ),
				   );			
		add_filter( 'posts_where', 'google_search_posts_where', 10, 2 );
		$post_details= new WP_Query($args3);		
		remove_filter( 'posts_where', 'google_search_posts_where', 10, 2 );
		$content_data='';	

		if ($post_details->have_posts()) :			
			while ( $post_details->have_posts() ) : $post_details->the_post();
					global $post;
					$post_categories = get_the_terms( get_the_ID() ,$taxonomies[0]);
					foreach($post_categories as $post_category){
						if($post_category->term_icon){
							$term_icon=$post_category->term_icon;
							break;
						}else{
							$term_icon=TEVOLUTION_LOCATION_URL.'images/pin.png';
						}
					}
					$ID =get_the_ID();				
					$title = get_the_title($ID);
					$plink = get_permalink($ID);
					$lat = get_post_meta($ID,'geo_latitude',true);
					$lng = get_post_meta($ID,'geo_longitude',true);					
					$address = str_replace($srcharr,$replarr,(get_post_meta($ID,'address',true)));
					$contact = str_replace($srcharr,$replarr,(get_post_meta($ID,'phone',true)));
					$website = get_post_meta($ID,'website',true);
					if(!strstr($website,'http') && $website)
						$website = 'http://'.$website;
					/*Fetch the image for display in map */
					if ( has_post_thumbnail()){
						$post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail');						
						$post_images=$post_img[0];
					}else{
						$post_img = bdw_get_images_plugin($ID,'thumbnail');					
						$post_images = $post_img[0]['file'];
					}
					$imageclass='';
					if($post_images){
						$post_image='<div class=map-item-img><img src='.$post_images.' width=150 height=150/></div>';
						
					}else{
						$post_image='';
						$imageclass='no_map_image';
					}
					
					$image_class=($post_image)?'map-image' :'';					
						
					$comment_count= count(get_comments(array('post_id' => $ID)));
					$review=($comment_count <=1 )? __('review',LDOMAIN):__('reviews',LDOMAIN);	
					
					if(($lat && $lng )&& !in_array($ID,$pids))
					{ 	
						$retstr ='{';
						$retstr .= '"name":"'.utf_special_char(str_replace($title_srcharr,$title_replarr,$post->post_title)).'",';
						$retstr .= '"location": ['.$lat.','.$lng.'],';
						$retstr .= '"message":"<div class=\"google-map-info '.$image_class.'\"><div class=map-inner-wrapper><div class=\"map-item-info '.$imageclass.'\">'.$post_image;
						$retstr .= '<h6><a href='.$plink.' class=ptitle><span>'.utf_special_char($title).'</span></a></h6>';							
						if($address){$retstr .= '<p class=address>'.utf_special_char(trim($address)).'</p>';}
						if($contact){$retstr .= '<p class=contact>'.$contact.'</p>';}
						if($website){$retstr .= '<p class=website><a href= '.$website.'>'.$website.'</a></p>';}
						if($templatic_settings['templatin_rating']=='yes'){
							$rating=draw_rating_star_plugin(get_post_average_rating(get_the_ID()));
							$retstr .= '<div class=map_rating>'.str_replace('"','',$rating).' <span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
						}elseif(is_plugin_active('Templatic-MultiRating/multiple_rating.php') && function_exists('single_average_rating')){
							$rating=get_single_average_rating(get_the_ID());
							$retstr .= '<div class=map_rating>'.stripcslashes(str_replace('"','',$rating)).'<span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
						}
						$retstr .= '</div></div></div>';
						$retstr .= '",';
						$retstr .= '"icons":"'.$term_icon.'",';
						$retstr .= '"pid":"'.$ID.'"';
						$retstr .= '}';
						$content_data[] = $retstr;
						$j++;
					}
					$pids[]=$ID;					
			endwhile;	
			wp_reset_query();
			wp_reset_postdata();
		endif;
		if($content_data)	
			$cat_content_info[]= implode(',',$content_data);
	}
	//
	if($cat_content_info)
	{
		echo '[{"totalcount":"'.$j.'",'.substr(implode(',',$cat_content_info),1).']';
	}else
	{
		echo '[{"totalcount":"0"}]';
	}
	exit;
}
/*
 * Function name: google_search_posts_where
 * Return : pass the search post title
 */
function google_search_posts_where( $where, &$wp_query){
	global $wpdb;
	
	if(isset($_SESSION['post_city_id']) && $_SESSION['post_city_id']!=''){
		$where .= " AND $wpdb->posts.ID in (select pm.post_id from $wpdb->postmeta pm where pm.meta_key ='post_city_id' and FIND_IN_SET( ".$_SESSION['post_city_id'].", pm.meta_value ))";
	}
	
	if(isset($_REQUEST['search_string']) && $_REQUEST['search_string']!=''){
		$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $_REQUEST['search_string']) ) . '%\'';
		
		$where .= " OR  ($wpdb->posts.ID in (select p.ID from $wpdb->terms c,$wpdb->term_taxonomy tt,$wpdb->term_relationships tr,$wpdb->posts p where c.name like '".esc_sql( like_escape( $_REQUEST['search_string']) )."' and c.term_id=tt.term_id and tt.term_taxonomy_id=tr.term_taxonomy_id and tr.object_id=p.ID and p.post_status = 'publish' group by  p.ID))";
	}	
	return $where;	
}
add_action('wp_head','location_google_map_responsive');
function location_google_map_responsive(){
	$city_googlemap_setting = get_option('city_googlemap_setting'); 		
	if(strtolower(@$city_googlemap_setting['google_map_hide']) == strtolower('yes')){ ?>
		<style type='text/css'>
			@media only screen and (max-width: 719px){
				.map_sidebar{ display:none; }
			}
		</style>
	<?php }	
}

/*Name:google_map_initialize_onload
Desc: Returnthe markers on map( on page load ) */
add_action('wp_ajax_nopriv_google_map_initialize_onload','google_map_initialize_onload');
add_action('wp_ajax_google_map_initialize_onload','google_map_initialize_onload');
function google_map_initialize_onload(){
	global $wpdb,$current_cityinfo;	
	if(isset($_REQUEST['lang']) && $_REQUEST['lang']!=""){
		$_COOKIE['_icl_current_language']=$_REQUEST['lang'];
	}
	$j=0;
	$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
	$pids=array("");
	$srcharr = array('"');
	$replarr = array('\"');
	$title_srcharr = array('"');
	$title_replarr = array('\"');
	$post_type =(explode(',',substr($_REQUEST['posttype'],0,-1)));
	$categoryname =(explode(',',substr($_REQUEST['categoryname'],0,-1)));
	$templatic_settings=get_option('templatic_settings');
	$multicity_table = $wpdb->prefix . "multicity";	
	if(isset($_SESSION['post_city_id']) && $_SESSION['post_city_id']!=''){
		$sql= "select city_slug from $multicity_table where city_id=".$_SESSION['post_city_id'];
		$result=$wpdb->get_row($sql);
	     $current_cityinfo['city_slug']=$result->city_slug;		
	}

	$json_option = $current_cityinfo['city_slug']."_".rtrim($_REQUEST['posttype'],",")."_".'map_data';
	$map_json_data = get_transient($json_option);
//	if($map_json_data)
	$json_total_data=json_decode($map_json_data, true);	

	{
		for($i=0;$i<count($post_type);$i++){
			
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type[$i],'public'   => true, '_builtin' => true ));	
			$cat_args = array(
						'taxonomy'=>$taxonomies[0],
						'orderby' => 'name', 				
						'hierarchical' => 'true',
						'title_li'=>''
					);		
			$r = wp_parse_args( $cat_args);
			
			if ( false === ( $catname_arr = get_transient( '_tevolution_query_googlemapwidget'.$post_type[$i].$cur_lang_code ))  && get_option('tevolution_cache_disable')==1 ) {
				$catname_arr=get_categories( $r );
				set_transient( '_tevolution_query_googlemapwidget'.$post_type[$i].$cur_lang_code, $catname_arr, 12 * HOUR_IN_SECONDS );				
			}elseif(get_option('tevolution_cache_disable')==''){
				$catname_arr=get_categories( $r );
			}	
			$cat_ID='';
			foreach($catname_arr as $cat){
				if(!in_array($cat->term_id,$categoryname))
					continue;
					
				$cat_ID.= $cat->term_id.',';
			}
			$args3=array('post_type'      => trim($post_type[$i]),
					   'posts_per_page' => 400,
					   'post_status'    => 'publish',
					   'tax_query'      => array(
										  array(
											 'taxonomy' => $taxonomies[0],
											 'field'    => 'id',
											 'terms'    => explode(',',$cat_ID),
											 'operator' => 'IN'
										  )
									   ),
					   );			
			add_filter( 'posts_where', 'google_search_posts_where', 10, 2 );
			$post_details= new WP_Query($args3);	
			remove_filter( 'posts_where', 'google_search_posts_where', 10, 2 );
			$content_data='';					
			if ($post_details->have_posts()) :			
			while ( $post_details->have_posts() ) : $post_details->the_post();
					global $post;
					$post_categories = get_the_terms( get_the_ID() ,$taxonomies[0]);
					foreach($post_categories as $post_category){
						if($post_category->term_icon){
							$term_icon=$post_category->term_icon;
							break;
						}else{
							$term_icon=TEVOLUTION_LOCATION_URL.'images/pin.png';
						}
					}
					$ID =get_the_ID();				
					$title = get_the_title($ID);
					$plink = get_permalink($ID);
					$lat = get_post_meta($ID,'geo_latitude',true);
					$lng = get_post_meta($ID,'geo_longitude',true);					
					$address = str_replace($srcharr,$replarr,(get_post_meta($ID,'address',true)));
					$contact = str_replace($srcharr,$replarr,(get_post_meta($ID,'phone',true)));
					$website = get_post_meta($ID,'website',true);
					if(!strstr($website,'http') && $website)
						$website = 'http://'.$website;
					/*Fetch the image for display in map */
					if ( has_post_thumbnail()){
						$post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail');						
						$post_images=$post_img[0];
					}else{
						$post_img = bdw_get_images_plugin($ID,'thumbnail');					
						$post_images = $post_img[0]['file'];
					}
					$imageclass='';
					if($post_images){
						$post_image='<div class=map-item-img><img src='.$post_images.' width=150 height=150/></div>';
						
					}else{
						$post_image='';
						$imageclass='no_map_image';
					}
					
					$image_class=($post_image)?'map-image' :'';					
						
					$comment_count= count(get_comments(array('post_id' => $ID)));
					$review=($comment_count <=1 )? __('review',LDOMAIN):__('reviews',LDOMAIN);	
					
					if(($lat && $lng )&& !in_array($ID,$pids))
					{ 	
						$retstr ='{';
						$retstr .= '"name":"'.(str_replace($title_srcharr,$title_replarr,$post->post_title)).'",';
						$retstr .= '"location": ['.$lat.','.$lng.'],';
						$retstr .= '"message":"<div class=\"google-map-info '.$image_class.'\"><div class=map-inner-wrapper><div class=\"map-item-info '.$imageclass.'\">'.$post_image;
						$retstr .= '<h6><a href='.$plink.' class=ptitle><span>'.utf_special_char($title).'</span></a></h6>';							
						if($address){$retstr .= '<p class=address>'.utf_special_char(trim($address)).'</p>';}
						if($contact){$retstr .= '<p class=contact>'.$contact.'</p>';}
						if($website){$retstr .= '<p class=website><a href= '.$website.'>'.$website.'</a></p>';}
						if($templatic_settings['templatin_rating']=='yes'){
							$rating=draw_rating_star_plugin(get_post_average_rating(get_the_ID()));
							$retstr .= '<div class=map_rating>'.str_replace('"','',$rating).' <span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
						}elseif(is_plugin_active('Templatic-MultiRating/multiple_rating.php') && function_exists('single_average_rating')){
							$rating=get_single_average_rating(get_the_ID());
							$retstr .= '<div class=map_rating>'.stripcslashes(str_replace('"','',$rating)).'<span><a href='.$plink.'#comments>'.$comment_count.' '.$review.'</a></span></div>';
						}
						$retstr .= '</div></div></div>';
						$retstr .= '",';
						$retstr .= '"icons":"'.$term_icon.'",';
						$retstr .= '"pid":"'.$ID.'"';
						$retstr .= '}';
						$content_data[] = $retstr;
						$j++;
					}
					$pids[]=$ID;					
			endwhile;	
			wp_reset_query();
			wp_reset_postdata();
		endif;
		if($content_data)	
			$cat_content_info[]= implode(',',$content_data);
		}
		//
		
		if($cat_content_info)
		{
			$json= '[{"totalcount":"'.$j.'",'.substr(implode(',',$cat_content_info),1).']';
		}else
		{
			$json=  '[{"totalcount":"0"}]';
		}
		echo $json;
		set_transient( $json_option,$json, 12 * HOUR_IN_SECONDS );		
		
	}
	exit;
}



/**
 * Output an unordered list of checkbox <input> elements labelled
 * with term names. Taxonomy independent version of wp_category_checklist().
 *
 * @since 3.0.0
 *
 * @param int $post_id
 * @param array $args
 */
function homepage_map_wp_terms_checklist($post_id = 0, $args = array()) {
 	$defaults = array(		
		'selected_cats' => false,
		'walker' => null,
		'taxonomy' => 'category',
		'checked_ontop' => true,		
		'post_type' =>'post',
	);
	$args = apply_filters( 'wp_terms_checklist_args', $args, $post_id );
	
	extract( wp_parse_args($args, $defaults), EXTR_SKIP );

	if ( empty($walker) || !is_a($walker, 'Walker') )
		$walker = new Homepage_map_Walker_Category;

	$descendants_and_self = (int) $descendants_and_self;

	$args = array('taxonomy' => $taxonomy);

	$tax = get_taxonomy($taxonomy);
	$args['disabled'] = !current_user_can($tax->cap->assign_terms);

	if($post_type!=""){
		$args['post_type'] = $post_type;	
	}
	if ( is_array( $selected_cats ) )
		$args['selected_cats'] = $selected_cats;	
	else
		$args['selected_cats'] = array();
		
	
	
	$categories = (array) get_terms($taxonomy, array('get' => 'all'));	
	// Then the rest of them	
	echo call_user_func_array(array(&$walker, 'walk'), array($categories, 0, $args));
}



/**
 * Create HTML list of categories.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class Homepage_map_Walker_Category extends Walker {	
	var $tree_type = 'category';
	
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');
	
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "\n";
	}
	
	function end_lvl( &$output, $depth = 0, $args = array() ) {		
		$output .= "\n";
	}
	
	function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		extract($args);

		$cat_name = esc_attr( $category->name );
		if(@$category->term_icon)
			$term_icon=$category->term_icon;
		else
			$term_icon=TEVOLUTION_LOCATION_URL.'images/pin.png';
			
		$depth_parent=0;
		if($depth!=0){
			$depth_parent=3*5;	
		}
		
		$category_image='<img height="14" width="8" alt="" src="'.$term_icon.'">';
			
		if($args['post_type'])
			$post_type=str_replace("&",'&amp;',$args['post_type']);
		else
			$post_type='post';
			
		$onclick='onclick="newgooglemap_initialize(this,&quot;'.$post_type.'&quot;)"';		
		
		if(in_array($category->term_id,$args['selected_cats']) || in_array('all',$args['selected_cats'])){
			$output .= "\n" . '<label for="in-'.$taxonomy.'-' . $category->term_id . '" style="margin-left:'.$depth_parent.'px"><input type="checkbox" name="categoryname[]" value="' . $category->term_id . '"   id="in-'.$taxonomy.'-' . $category->term_id . '" checked="checked"  onclick="newgooglemap_initialize(this,&quot;'.$post_type.'&quot;)" /> '.$category_image.'' . esc_html(  $category->name ).'</label>';	
		}
		
	
	}
	
	function end_el( &$output, $page, $depth = 0, $args = array() ) {		
		$output .= "\n";
	}
}
?>
