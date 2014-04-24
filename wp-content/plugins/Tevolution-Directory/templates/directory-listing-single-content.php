<?php global $post;
$tmpdata = get_option('templatic_settings');	
$googlemap_setting=get_option('city_googlemap_setting');
$special_offer=get_post_meta(get_the_ID(),'proprty_feature',true);
$video=get_post_meta(get_the_ID(),'video',true);
$facebook=get_post_meta(get_the_ID(),'facebook',true);
$google_plus=get_post_meta(get_the_ID(),'google_plus',true);
$twitter=get_post_meta(get_the_ID(),'twitter',true);
$listing_address=get_post_meta(get_the_ID(),'address',true);
if(function_exists('bdw_get_images_plugin'))
{
	$post_img = bdw_get_images_plugin(get_the_ID(),'directory-single-image');
	$postimg_thumbnail = bdw_get_images_plugin(get_the_ID(),'thumbnail');
	$more_listing_img = bdw_get_images_plugin(get_the_ID(),'directory-single-thumb');
	$thumb_img = @$post_img[0]['file'];
	$attachment_id = @$post_img[0]['id'];
	$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large');
	$attach_data = get_post($attachment_id);
	$img_title = $attach_data->post_title;
	$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
}
?>
<script type="text/javascript">
jQuery(function() {
	jQuery('.listing-image a.listing_img').lightBox();
});
</script>
<div class="claim-post-wraper">
	<?php echo '<div style="display: none; opacity: 0.5;" id="lean_overlay"></div>';?>
	<ul>
		<?php tevolution_dir_popupfrms($post); // show sent to friend and send inquiry form popup ?>
	</ul>
</div>
 
<?php
if(isset($post)){
	$post_img = bdw_get_images_plugin($post->ID,'thumb');
	$post_images = @$post_img[0]['file'];
	$title=urlencode($post->post_title);
	$url=urlencode(get_permalink($post->ID));
	$summary=urlencode(htmlspecialchars($post->post_content));
	$image=$post_images;
}
?>

<!--Directory Share Link Coding Start -->
<?php tevolution_socialpost_link($post); // to show the link of current post in social media ?>
<!--Directory Share Link Coding End -->


<?php
global $htmlvar_name;
?>
<div id="tabs">
	 <ul>
     <?php do_action('dir_before_tabs');
	 do_action('dir_start_tabs');
		if((@$googlemap_setting['direction_map']=='yes' && $listing_address )|| $special_offer!="" || $video!=""):?>	
    
          <li><a href="#listing_description"><?php _e('Overview',DIR_DOMAIN);?></a></li>
                  
		<?php if(@$googlemap_setting['direction_map']=='yes' && $listing_address):?>
          <li><a href="#listing_map"><?php _e('Map',DIR_DOMAIN);?></a></li>
          <?php endif;?>
          
		<?php if($special_offer!="" && $htmlvar_name['basic_inf']['proprty_feature']): ?>
          <li><a href="#special_offer"><?php echo $htmlvar_name['basic_inf']['proprty_feature']['label'];?></a></li>
          <?php endif;?>
          
		<?php if($video!="" && $htmlvar_name['basic_inf']['video']):?>
          <li><a href="#listing_video"><?php echo $htmlvar_name['basic_inf']['video']['label'];?></a></li>
          <?php endif;?>
		<?php
			$event_for_listing = get_post_meta($post->ID,'event_for_listing',true);		
			/* Recurring Event  */
			if(!empty($event_for_listing))
			{
			  $event_for_list = explode(',',$event_for_listing);
			  $args = array( 'posts_per_page'   => -1,
					'offset'           => 0,
					'post_status'         => array('publish','private'),
					'orderby'          => 'meta_value',
					'order'            => 'ASC',
					'include'          => $event_for_list,
					'exclude'          => '',
					'post_type'        => 'event',
					'post_mime_type'   => '',
					'post_parent'      => '',
					'post_status'      => 'publish',
					'meta_query'      => array('relation' => 'OR',
										array('key'     => 'st_date',
										'value'   => date('Y-m-d'),
										'compare' => '>='),
										array('key'     => 'end_date',
										'value'   => date('Y-m-d'),
										'compare' => '>=')),
					'suppress_filters' => true );
					
				$events_list = get_posts($args); 
	
				if(!empty($events_list)){
				?><li><a href="#listing_event"><?php _e('Events',DIR_DOMAIN);?></a></li><?php
				}
			}
		  ?>
     <?php endif;
			do_action('dir_end_tabs');
	 ?>
			
     </ul>
	 <?php do_action('dir_after_tabs');
	 	add_action('wp_footer','add_single_slider_script');
		function add_single_slider_script()
		{
			$slider_more_listing_img = bdw_get_images_plugin(get_the_ID(),'directory-single-thumb');
			?>
			<script type="text/javascript">
				jQuery(window).load(function()
				{
					jQuery('#silde_gallery').flexslider({
						animation: "slide",
						<?php if(!empty($slider_more_listing_img) && count($slider_more_listing_img)>4):?>
						controlNav: true,
						directionNav: true,
						prevText: '<i class="fa fa-chevron-left"></i>',
						nextText: '<i class="fa fa-chevron-right"></i>',
						<?php 
						else: ?>
						controlNav: false,
						directionNav: false,
						<?php endif; ?>
						animationLoop: false,
						slideshow: false,
						itemWidth: 50,
						itemMargin: 5,
						asNavFor: '#slider'
					  });
					jQuery('#slider').flexslider(
					{
						animation: 'slide',
						slideshow: false,
						direction: "horizontal",
						slideshowSpeed: 7000,
						animationLoop: true,
						startAt: 0,
						smoothHeight: true,
						easing: "swing",
						pauseOnHover: true,
						video: true,
						controlNav: false,
						directionNav: false,
						prevText: '<i class="fa fa-chevron-left"></i>',
						nextText: '<i class="fa fa-chevron-right"></i>',
						start: function(slider)
						{
							jQuery('body').removeClass('loading');
						}
					});
				});
				//FlexSlider: Default Settings
			</script>
			<?php
		}
	 ?>
     <!--Overview Section Start -->
     <div id="listing_description">
     	<div class="<?php if(!$thumb_img):?>content_listing<?php else:?>listing_content <?php endif;?>">
          
     	<?php
			do_action('dir_before_post_content'); 
					the_content();
			do_action('dir_after_post_content'); 
		?>
          
        </div>
        <?php if($thumb_img):?>
        <div id="directory_detail_img" class="entry-header-image">
		    
			<?php do_action('directory_before_post_image');?>
               
               <div id="slider" class="listing-image flexslider">    
					
					<ul class="slides">
					<?php
						foreach($post_img as $key=>$value):
							$attachment_id = $value['id'];
							$attach_data = get_post($attachment_id);
							$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large'); // returns an array							
							$img_title = $attach_data->post_title;
							$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
							
                    ?>
							<li>
                              	<a href="<?php echo $image_attributes['0'];?>" title="<?php echo $img_title; ?>" class="listing_img" >		
		                            <img src="<?php echo $value['file'];?>" alt="<?php echo $img_title; ?>"/>
                                </a>
                            </li>
									
					  <?php endforeach;?>
					</ul>
				
               </div>
               
			<?php if(!empty($more_listing_img) && count($more_listing_img)>1):?>
                    <div id="silde_gallery" class="flexslider<?php if(!empty($more_listing_img) && count($more_listing_img)>4) {echo ' slider_padding_class'; }?>">
						<ul class="more_photos slides">
                        <?php foreach($more_listing_img as $key=>$value):
							$attachment_id = $value['id'];
							$attach_data = get_post($attachment_id);
							$image_attributes = wp_get_attachment_image_src( $attachment_id ,'large'); // returns an array							
							$img_title = $attach_data->post_title;
							$img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
						?>
                        	<li>
                              	<a href="<?php echo $image_attributes['0'];?>" title="<?php echo $img_title; ?>" >		
		                            <img src="<?php echo $value['file'];?>"alt="<?php echo $img_title; ?>"  />
                                </a>
                            </li>
                               
                         <?php endforeach;?>
                         </ul>
                    </div>
               <?php endif;?>
               
               <?php do_action('directory_after_post_image');?>
			   
          </div><!-- .entry-header-image -->
          <?php endif;?>
     </div>
     <!--Overview Section End -->
      
     <?php if($googlemap_setting['direction_map']=='yes' && $listing_address):?>
          <!--Map Section Start -->
          <div id="listing_map">
               <?php do_action('directory_single_page_map') ?>
          </div>
          <!--Map Section End -->
     <?php endif; ?>
     
     <?php if($special_offer!="" && $htmlvar_name['basic_inf']['proprty_feature']):?>
          <!--Special Offer Start -->
          <div id="special_offer">
               <?php
			    $special_offer = apply_filters( 'the_content', $special_offer );
				$special_offer = str_replace( ']]>', ']]&gt;', $special_offer );
				echo $special_offer;
			   ?>
          </div>
          <!--Special Offer End -->
	<?php endif;?>
     
     
     <?php if($video!=""):?>
          <!--Video Code Start -->
          <div id="listing_video">
               <?php echo stripslashes($video);?>
          </div>
          <!--Video code End -->
	<?php endif;?>
	
	<?php
	if(is_single() && get_post_type()=='listing'){
	$event_for_listing = get_post_meta($post->ID,'event_for_listing',true);		
		/* Recurring Event  */
		if($event_for_listing !='')
		{
			if(!empty($events_list)){
		?>
          <!--Video Code Start -->
          <div id="listing_event">
               <?php 
				foreach($events_list as $event_detail) { 
				if($event_detail->post_status =='publish' ){
				?>
			<div class="listed_events clearfix"> 
				<?php 
				  if ( has_post_thumbnail($event_detail->ID)){
					$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($event_detail->ID), 'tevolution_thumbnail' );
					$post_image=($thumb[0])? $thumb[0] :TEVOLUTION_DIRECTORY_URL.'images/noimage-150x150.jpg';
					
				  }else{
						$post_image = bdw_get_images_plugin($event_detail->ID,'tevolution_thumbnail');
						$post_image=($post_image[0]['file'])? $post_image[0]['file'] :TEVOLUTION_DIRECTORY_URL.'images/noimage-150x150.jpg';
				  } 
				
				$e_id = $event_detail->ID;
				$e_title = $event_detail->post_title;
				if(get_post_meta($e_id,'st_date',true) !='' && get_post_meta($e_id,'end_date',true) !='' ){
					$date = "<strong>".__('From',DIR_DOMAIN)."</strong>".' '.get_post_meta($e_id,'st_date',true)." ".get_post_meta($e_id,'st_time',true)." <strong>".__('To',DIR_DOMAIN)."</strong> ".get_post_meta($e_id,'end_date',true)." ".get_post_meta($e_id,'end_time',true);
				}elseif(get_post_meta($e_id,'st_date',true) !='' && get_post_meta($e_id,'end_date',true) =='' ){ 
					$date = "<strong>".__('From',DIR_DOMAIN)."</strong>".' '.get_post_meta($e_id,'st_date',true)." ".get_post_meta($e_id,'st_time',true);
				} ?>
				<a class="event_img" href="<?php echo get_permalink($e_id); ?>"><img src="<?php echo $post_image; ?>" width="60" height="60" alt="<?php echo $e_title; ?>"/></a>
				<div class="event_detail">
					<a class="event_title" href="<?php echo get_permalink($e_id); ?>"><strong><?php echo $e_title; ?></strong></a><br/>
					<?php $address=get_post_meta($e_id,'address',true);
					$phone=get_post_meta($e_id,'phone',true);	
					$date_formate=get_option('date_format');
					$time_formate=get_option('time_format');
					$st_date=date($date_formate,strtotime(get_post_meta($e_id,'st_date',true)));
					$end_date=date($date_formate,strtotime(get_post_meta($e_id,'end_date',true)));
					
					$date=$st_date.' '. __('To',DIR_DOMAIN).' '.$end_date;
					
					$st_time=date($time_formate,strtotime(get_post_meta($e_id,'st_time',true)));
					$end_time=date($time_formate,strtotime(get_post_meta($e_id,'end_time',true)));	
					if($address){
						echo '<p class="address" >'.$address.'</p>';
					}
					if($date)
					{
						echo '<p class="event_date"><strong>'.__('Date:',DIR_DOMAIN).'&nbsp;</strong><span>'.$date.'</span></p>';
					}
					if($st_time || $end_time)
					{
						echo '<p class="time"><strong>'.__('Timing:',DIR_DOMAIN).'&nbsp;</strong><span>'.$st_time.' '.__('To',DIR_DOMAIN).' '.$end_time.'</span></p>';
					}?>
				</div>
			</div>  
			<?php } 
			} /* end froeach */
					?>
          </div>
          <!--Video code End -->
			
	<?php }
	} }?>
	
	
</div>
<div class="post-meta">    
	<?php 
		if( @$htmlvar_name['basic_inf']['category'] )
		{
			echo get_the_directory_taxonomies();
		}
	?>                          
     <?php echo get_the_directory_tag();?>
</div>

<?php
global $htmlvar_name,$heading_title;
$j=0;
if(!empty($htmlvar_name)){
	echo '<div class="listing_custom_field">';
	foreach($htmlvar_name as $key=>$value){
		$i=0;
		foreach($value as $k=>$val){
			$key = ($key=='basic_inf')? __('Listing Information',DIR_DOMAIN): @$heading_title[$key];
			if($k!='post_title' && $k!='post_content' && $k!='post_excerpt' && $k!='post_images' && $k!='listing_timing' && $k!='address' && $k!='listing_logo' && $k!='video' && $k!='post_tags' && $k!='map_view' && $k!='proprty_feature' && $k!='phone' && $k!='email' && $k!='website' && $k!='twitter' && $k!='facebook' && $k!='google_plus' && $k!='contact_info')
			{
				
				
				$field= get_post_meta(get_the_ID(),$k,true);				
				if($val['type'] == 'multicheckbox' && $field!=""):
				$checkbox_value = '';
				if($i==0){echo '<h2 class="custom_field_headding">'.$key.'</h2>';$i++;}					
					$option_values = explode(",",$val['option_values']);				
					$option_titles = explode(",",$val['option_title']);
					for($i=0;$i<count($option_values);$i++){
						if(in_array($option_values[$i],$field)){
							if($option_titles[$i]!=""){
								$checkbox_value .= $option_titles[$i].',';
							}else{
								$checkbox_value .= $option_values[$i].',';
							}
						}
					}	
				?>
     	<p class='<?php echo $k;?>'><label><?php echo $val['label']; ?>:&nbsp; </label> <?php echo substr($checkbox_value,0,-1);?></p>
     <?php 
				elseif($val['type']=='radio'):
					$option_values = explode(",",$val['option_values']);				
					$option_titles = explode(",",$val['option_title']);
					for($i=0;$i<count($option_values);$i++){
						if($field == $option_values[$i]){
							if($option_titles[$i]!=""){
								$rado_value = $option_titles[$i];
							}else{
								$rado_value = $option_values[$i];
							}							
							?>
       <p class='<?php echo $k;?>'><label><?php echo $val['label']; ?>:&nbsp; </label><?php echo $rado_value;?></p>
       <?php
						}
					}	
				endif;
				if($val['type']  == 'upload')
				{
					 $upload_file=strtolower(substr(strrchr($_SESSION['upload_file'][$name],'.'),1));					 
					 if($upload_file=='jpg' || $upload_file=='jpeg' || $upload_file=='gif' || $upload_file=='png' || $upload_file=='jpg' ):
				?>
                    	<p class="<?php echo $val['style_class'];?>"><img src="<?php echo $field; ?>" /></p>
                    	<?php else:?>
                    	<p class="<?php echo $val['style_class'];?>"><label><?php echo $val['label']; ?>: </label><a href="<?php echo $field; ?>" target="_blank"><?php echo basename($field); ?></a></p>
                         <?php endif;
				}
				if($val['type'] != 'multicheckbox' && $val['type'] != 'radio' && $val['type']  != 'upload' &&$field!=''):
				if($i==0){echo '<h2 class="custom_field_headding">'.$key.'</h2>';$i++;}?>                              
				<p class='<?php echo $val['style_class'];?>'><label><?php echo $val['label']; ?>:&nbsp;</label><?php echo $field;?></p>
				<?php
				endif;
			}// End If condition
			
			$j++;
		}// End second foreach
	}// END First foreach
	echo '</div>';
}
?>

<!--Directory Social Media Coding Start -->
<?php if(function_exists('tevolution_socialmedia_sharelink')) 
		   tevolution_socialmedia_sharelink($post); ?>
<!--Directory Social Media Coding End -->

<?php
if(isset($tmpdata['templatic_view_counter']) && $tmpdata['templatic_view_counter']=='Yes')
{
	if(function_exists('view_counter_single_post')){
		view_counter_single_post(get_the_ID());
	}
	
	$post_visit_count=(get_post_meta(get_the_ID(),'viewed_count',true))? get_post_meta(get_the_ID(),'viewed_count',true): '0';
	$post_visit_daily_count=(get_post_meta(get_the_ID(),'viewed_count_daily',true))? get_post_meta(get_the_ID(),'viewed_count_daily',true): '0';
	$custom_content='';
	echo "<div class='view_counter'>";
	$custom_content.="<p>".__('Visited',DIR_DOMAIN)." ".$post_visit_count." ".__('times',DIR_DOMAIN);
	$custom_content.= ', '.$post_visit_daily_count." ".__("Visits today",DIR_DOMAIN)."</p>";
	echo $custom_content;
	echo '</div>';	
}
?>