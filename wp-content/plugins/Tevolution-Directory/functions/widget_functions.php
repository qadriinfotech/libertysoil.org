<?php
/* Widgets - widget_functions.php */
/*
 * Home page display google map as per current city related post type data on map
 */
add_action('widgets_init','directory_plugin_widgets_init');
function directory_plugin_widgets_init()
{	
	register_widget('directory_neighborhood');
	register_widget('directory_search_location');
	register_widget('directory_featured_homepage_listing');
	register_widget('directory_featured_category_list');
	register_widget('directory_mile_range_widget');
}
/*   
	Name : directory_neighborhood
	Desc: neighborhood posts Widget (particular category) 
*/
class directory_neighborhood extends WP_Widget {
	function directory_neighborhood() {
	//Constructor
		$widget_ops = array('classname' => 'widget In the neighborhood', 'description' => __('Display posts that are in the vicinity of the post that is currently displayed. Use in detail page sidebar areas.',DIR_DOMAIN) );
		$this->WP_Widget('directory_neighborhood', __('T &rarr; In The Neighbourhood',DIR_DOMAIN), $widget_ops);
	}
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		global $miles,$wpdb,$post,$single_post,$wp_query,$current_cityinfo;
		global $current_post,$post_number;
 		$current_post = $post->ID;	
		$title = empty($instance['title']) ? __("Nearest Listing",DIR_DOMAIN) : apply_filters('widget_title', $instance['title']);
		$post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
		$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);
		$radius = empty($instance['radius']) ? '0' : apply_filters('widget_radius', $instance['radius']);
		$closer_factor = empty($instance['closer_factor']) ? 0 : apply_filters('widget_closer_factor', $instance['closer_factor']);
		$show_list = empty($instance['show_list']) ? '0' : apply_filters('widget_show_list', $instance['show_list']);
		$radius_measure= empty($instance['radius_measure']) ? '0' : apply_filters('widget_radius_measure', $instance['radius_measure']);		
		
		
		//get the current post details
		$current_post_details=get_post($post->ID);
		echo $before_widget;
		?>          
		<div class="neighborhood_widget">
		<?php
          echo '<h3 class="widget-title">'.$title.'</h3>';
		if($show_list){
			$miles=(strtolower($radius_measure) == strtolower('Kilometer'))? $radius * 0.621: $radius;
				
			add_filter('posts_where','directory_nearby_filter');
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				add_filter('posts_where', 'wpml_listing_milewise_search_language');
			}
			$args = array(
				'post__not_in'        => array($current_post) ,
				'post_status'         => 'publish',
				'post_type'           => $post_type,
				'posts_per_page'      => $post_number,				
				'ignore_sticky_posts' => 1,
				'orderby'             => 'rand'
			);
			if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
			{
				add_filter('posts_where', 'location_multicity_where');
			}
			$wp_query_near = new WP_Query($args);			
			if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
			{
				remove_filter('posts_where', 'location_multicity_where');
			}
			if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
				remove_filter('posts_where', 'wpml_listing_milewise_search_language');
			}
			if($wp_query_near->have_posts()):
				echo '<ul class="nearby_distance">';
				while($wp_query_near->have_posts())
				{
					$wp_query_near->the_post();
					echo '<li class="nearby clearfix">';
					
					if ( has_post_thumbnail()){
						$post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'directory-neighbourhood-thumb');						
						$post_images=$post_img[0];
					}else{
						$post_img = bdw_get_images_plugin(get_the_ID(),'directory-neighbourhood-thumb');					
						$post_images = $post_img[0]['file'];
					}
					$image=($post_images)?$post_images : TEVOLUTION_DIRECTORY_URL.'images/no-image.png';
					?>
                         <div class='nearby_image'>
                         <a href="<?php echo get_permalink($post->post_id); ?>">
                         	<img src="<?php echo $image?>" alt="<?php echo get_the_title($post->post_id); ?>" title="<?php echo get_the_title($post->post_id); ?>" class="thumb <?php echo (!$post_images)?'no_image':''?>" />
                         </a>
                         </div>
                         <div class='nearby_content'>
                         	<h4><a href="<?php echo get_permalink($post->post_id); ?>"><?php the_title(); ?></a></h4>
						<p class="address"><?php $address = get_post_meta(get_the_ID(),'address',true); echo $address; ?></p>
                         </div>
					<?php
					echo '</li>';
                         
				}
				echo '</ul>';
			else:
          		_e('Sorry! There is no near by results found',DIR_DOMAIN);
			endif;
			remove_filter('posts_where','nearby_filter'); 
			wp_reset_query();
			
		}else{
			
			$geo_latitude = (get_post_meta($post->ID,'geo_latitude',true))?get_post_meta($post->ID,'geo_latitude',true): $_SESSION['custom_fields']['geo_latitude'];
			if($geo_latitude)
			{
				$geo_latitude_arr = explode('.',$geo_latitude);
				$geo_latitude=($geo_latitude_arr[1])? $geo_latitude_arr[0].'.'.substr($geo_latitude_arr[1],0,$closer_factor): $geo_latitude_arr[0];
			}
			$geo_longitude = (get_post_meta($post->ID,'geo_longitude',true))?get_post_meta($post->ID,'geo_longitude',true) : $_SESSION['custom_fields']['geo_longitude'];
			if($geo_longitude)
			{
				$geo_latitude_arr = explode('.',$geo_longitude);
				$geo_longitude=($geo_latitude_arr[1])?$geo_latitude_arr[0].'.'.substr($geo_latitude_arr[1],0,$closer_factor) :$geo_latitude_arr[0];			
			}
			if($current_cityinfo['city_id'])
			{ 
				global $wpdb, $pagenow, $wp_taxonomies,$ljoin;
				$language_where='';
				$join = '';
				if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
					$language = ICL_LANGUAGE_CODE;
					$join .= " {$ljoin} JOIN {$wpdb->prefix}icl_translations t ON m.post_id = t.element_id			
						AND t.element_type IN ('post_".$post_type."') JOIN {$wpdb->prefix}icl_languages l ON t.language_code=l.code AND l.active=1 AND t.language_code='".$language."'";
				}
				$post_city_id = $current_cityinfo['city_id'];				
				$post_lat = $wpdb->get_col("select m.post_id from $wpdb->posts p,$wpdb->postmeta m $join where p.ID=m.post_id AND p.post_type='".$post_type."' AND  m.meta_key like \"geo_latitude\" and (m.meta_value like\"$geo_latitude%\") and m.post_id!=\"$current_post\" and m.post_id in (select post_id from $wpdb->postmeta where meta_key='post_city_id' and ($wpdb->postmeta.meta_value = \"$post_city_id\" or $wpdb->postmeta.meta_value='' or $wpdb->postmeta.meta_value='0'))");
				
				$post_lng = $wpdb->get_col("select m.post_id from $wpdb->posts p, $wpdb->postmeta m where p.ID=m.post_id AND p.post_type='".$post_type."' AND m.meta_key like \"geo_longitude\" and (m.meta_value like\"$geo_longitude%\") and m.post_id!=\"$current_post\" and m.post_id in (select post_id from $wpdb->postmeta where meta_key='post_city_id' and ($wpdb->postmeta.meta_value = \"$post_city_id\" or $wpdb->postmeta.meta_value='' or $wpdb->postmeta.meta_value='0'))");				
			}else
			{
				$post_lat = $wpdb->get_col("select m.post_id from $wpdb->posts p, $wpdb->postmeta m $join where p.ID=m.post_id AND p.post_type='".$post_type."' AND m.meta_key like \"geo_latitude\" and (m.meta_value like\"$geo_latitude%\") and m.post_id!=\"$current_post\"");
				$post_lng = $wpdb->get_col("select m.post_id from $wpdb->posts p, $wpdb->postmeta m where p.ID=m.post_id AND p.post_type='".$post_type."' AND m.meta_key like \"geo_longitude\" and (m.meta_value like\"$geo_longitude%\") and m.post_id!=\"$current_post\"");
			}			
			
			if(1)
			{
				$post_id_arr = array();
				if($post_lat && $post_lng)
				{
					$post_id_arr = array_intersect($post_lat,$post_lng);
				}
				$post_id_arr = array_slice($post_id_arr,0,$post_number);
				$post_ids = implode(',',$post_id_arr);
			}			
			
			if($post_ids)
			{	
				$args = array(
					'post__in'            => explode(',',$post_ids) ,
					'post_type'           => $post->post_type,
					'posts_per_page'      => $post_number,
					'ignore_sticky_posts' => 1,
					'orderby'             => 'rand'
				);
				if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
					add_filter('posts_where', 'wpml_listing_milewise_search_language');
				}
				$latest_menus = new WP_Query($args);
				if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
					remove_filter('posts_where', 'wpml_listing_milewise_search_language');
				}				
			}			
			if(isset($latest_menus)):
				echo '<ul class="nearby_distance">';
				while($latest_menus->have_posts())
				{
					$latest_menus->the_post();
					echo '<li class="nearby clearfix">';
					
					if ( has_post_thumbnail()){
						$post_img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'directory-neighbourhood-thumb');						
						$post_images= @$post_img[0];
					}else{
						$post_img = bdw_get_images_plugin(get_the_ID(),'directory-neighbourhood-thumb');					
						$post_images = @$post_img[0]['file'];
					}
					$image=($post_images)?$post_images : TEVOLUTION_DIRECTORY_URL.'images/no-image.png';
					?>
                         <div class='nearby_image'>
                               <a href="<?php echo get_permalink($post->post_id); ?>">
                                   <img src="<?php echo $image; ?>" alt="<?php echo get_the_title($post->post_id); ?>" title="<?php echo get_the_title($post->post_id); ?>" class="thumb <?php echo (!$post_images)?'no_image':''?>" />
                               </a>
                         </div>
                         <div class='nearby_content'>
                        		<h4><a href="<?php echo get_permalink($post->post_id); ?>"><?php the_title(); ?></a></h4>
						<p class="address"><?php $address = get_post_meta(get_the_ID(),'address',true); echo $address; ?></p>
                         </div>
					<?php
					echo '</li>';
                         
				}
				echo '</ul>';
			else:
				echo _e('Sorry! No near by results found.',DIR_DOMAIN);
			endif;
		}		
		
		?>         
          </div>
		<?php
		echo $after_widget;
	}
	function update($new_instance, $old_instance) {
		//save the widget		
		return $new_instance;
	}
	function form($instance) {
		//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => __("Nearest Listing",DIR_DOMAIN), 'post_type' => 'listing', 'post_number' => 5, 'closer_factor'=>2 ) );
			$title = strip_tags($instance['title']);
			$post_type = strip_tags($instance['post_type']);
			$post_number = strip_tags($instance['post_number']);
			$post_link = strip_tags($instance['post_link']);
			$closer_factor = strip_tags($instance['closer_factor']);
			$show_list = strip_tags($instance['show_list']);
			$distance_factor = strip_tags($instance['radius']);
			$radius_measure=strip_tags($instance['radius_measure']);
		?>
          <script type="text/javascript">										
			function select_show_list(id,div_def,div_custom)
			{
				var checked=id.checked;
				jQuery('#'+div_def).slideToggle('slow');
				jQuery('#'+div_custom).slideToggle('slow');
			}			
		</script>
          <p>
               <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>
               <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
               </label>
          </p>
          <p>
               <label for="<?php echo $this->get_field_id('post_type');?>" ><?php echo __('Select Post:',DIR_DOMAIN);?>     </label>	
               <select  id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" class="widefat">        	
				<?php
                    $all_post_types = get_option("templatic_custom_post");
                    foreach($all_post_types as $key=>$post_types){
					?>
					<option value="<?php echo $key;?>" <?php if($key== $post_type)echo "selected";?>><?php echo esc_attr($post_types['label']);?></option>
					<?php
                    }
                    ?>	
               </select>
          </p>
          
          <p>
               <label for="<?php echo $this->get_field_id('post_number'); ?>"><?php echo __('Number of posts',DIR_DOMAIN);?>
               <input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
               </label>
          </p>
          
          <p>
               <label for="<?php echo $this->get_field_id('show_list'); ?>">
               <input id="<?php echo $this->get_field_id('show_list'); ?>" name="<?php echo $this->get_field_name('show_list'); ?>" type="checkbox" value="1" <?php if($show_list =='1'){ ?>checked=checked<?php } ?>style="width:10px;" onclick="select_show_list(this,'<?php echo $this->get_field_id('show_list_normal'); ?>','<?php echo $this->get_field_id('show_list_distance'); ?>');" />
               <?php echo __('<b>Show list by distance?</b>',DIR_DOMAIN);?>              
               </label>
          
          </p>          
          
		<p id="<?php echo $this->get_field_id('show_list_normal'); ?>" style="<?php if($show_list =='1'){ ?>display:none;<?php }else{?>display:block;<?php }?>">
               <label for="<?php echo $this->get_field_id('closer_factor'); ?>"><?php echo __('Show Listings From',DIR_DOMAIN);?>
               <select id="<?php echo $this->get_field_id('closer_factor'); ?>" name="<?php echo $this->get_field_name('closer_factor'); ?>">
                    <option value="0" <?php if(esc_attr($closer_factor)=='0'){ echo 'selected="selected"';} ?>><?php echo __('So Far Away',DIR_DOMAIN);?></option>
                    <option value="1" <?php if(esc_attr($closer_factor)=='1'){ echo 'selected="selected"';} ?>><?php echo __('Far Away',DIR_DOMAIN);?></option>
                    <option value="2" <?php if(esc_attr($closer_factor)=='2'){ echo 'selected="selected"';} ?>><?php echo __('At Some Distance',DIR_DOMAIN);?></option>
                    <option value="3" <?php if(esc_attr($closer_factor)=='3'){ echo 'selected="selected"';} ?>><?php echo __('Nearer',DIR_DOMAIN);?></option>
                    <option value="4" <?php if(esc_attr($closer_factor)=='4'){ echo 'selected="selected"';} ?>><?php echo __('Very Near',DIR_DOMAIN);?></option>
               </select>
               </label>
		</p> 
          
          <div id="<?php echo $this->get_field_id('show_list_distance'); ?>" style="<?php if($show_list =='1'){ ?>display:block;<?php }else{?>display:none;<?php }?>"> 
          <p>            
               <label for="<?php echo $this->get_field_id('radius'); ?>"><?php echo __('Select Distance',DIR_DOMAIN);?>
               <select id="<?php echo $this->get_field_id('radius'); ?>" name="<?php echo $this->get_field_name('radius'); ?>">
                    <option value="1" <?php if(esc_attr($distance_factor)=='1'){ echo 'selected="selected"';} ?>><?php echo __('1',DIR_DOMAIN); ?></option>
                    <option value="5" <?php if(esc_attr($distance_factor)=='5'){ echo 'selected="selected"';} ?>><?php echo __('5',DIR_DOMAIN); ?></option>
                    <option value="10" <?php if(esc_attr($distance_factor)=='10'){ echo 'selected="selected"';} ?>><?php echo __('10',DIR_DOMAIN); ?></option>
                    <option value="100" <?php if(esc_attr($distance_factor)=='100'){ echo 'selected="selected"';} ?>><?php echo __('100',DIR_DOMAIN); ?></option>
                    <option value="1000" <?php if(esc_attr($distance_factor)=='1000'){ echo 'selected="selected"';} ?>><?php echo __('1000',DIR_DOMAIN); ?></option>
                    <option value="5000" <?php if(esc_attr($distance_factor)=='5000'){ echo 'selected="selected"';} ?>><?php echo __('5000',DIR_DOMAIN); ?></option>      
               </select>
               </label>             
		</p> 
          <p>            
               <label for="<?php echo $this->get_field_id('radius_measure'); ?>"><?php echo __('Display By',DIR_DOMAIN);?>
               <select id="<?php echo $this->get_field_id('radius_measure'); ?>" name="<?php echo $this->get_field_name('radius_measure'); ?>">
                    <option value="kilometer" <?php if(esc_attr($radius_measure)=='kilometer'){ echo 'selected="selected"';} ?>><?php echo __('Kilometers',DIR_DOMAIN); ?></option>
                    <option value="miles" <?php if(esc_attr($radius_measure)=='miles'){ echo 'selected="selected"';} ?>><?php echo __('Miles',DIR_DOMAIN); ?></option>                    
               </select>
               </label>             
		</p> 
          </div>
		<?php
	}
}
/* End of directory_neighborhood*/
/*
	Name : directory_search_location
    Desc : location wise search widget
*/
class directory_search_location extends WP_Widget {
	function directory_search_location() {
		//Constructor
		$widget_ops = array('classname' => 'search_location', 'description' => __('Enter an address to get a list of nearby posts. Use in header and sidebar widget areas.','templatic-admin') );
		$this->WP_Widget('directory_search_location', __('T &rarr; Search by Address','templatic-admin'), $widget_ops);
	}
	function widget($args, $instance) {
		// prints the widget
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? 'Search Near By Location' : apply_filters('widget_title', $instance['title']);
		$post_type = empty($instance['post_type']) ? array('listing') : apply_filters('widget_post_type', $instance['post_type']);
		$miles_search = empty($instance['miles_search']) ? '' : apply_filters('widget_miles_search', $instance['miles_search']);
		$radius_measure= empty($instance['radius_measure']) ? 'miles' : apply_filters('widget_radius_measure', $instance['radius_measure']);		
		$radius_type=($radius_measure=='miles')? __('Miles',DIR_DOMAIN) : __('Kilometers',DIR_DOMAIN);
		echo $before_widget;
		$search_txt= __('What?',DIR_DOMAIN);
		if($miles_search==1){
			$class=' search_by_mile_active';
		}
		$search_id= rand();
		$distance_factor = @$_REQUEST['radius'];
		if(isset($_REQUEST['location'])) { $location= @$_REQUEST['location']; }else{$location='';  }
		if(isset($_REQUEST['s'])) { $what= @$_REQUEST['s']; }else{$what='';  }
		echo '<div class="search_nearby_widget'.$class.'">';
		if($instance['title']){echo '<h3 class="widget-title">'.$title.'</h3>';}
		?>
		<form method="get" id="searchform" action="<?php echo home_url(); ?>/">
          	<?php 
			if($post_type !=''){
				if(!is_array($post_type)){ $post_type = array($post_type); }
			foreach($post_type as $val):?>
               <input type="hidden" name="post_type[]" value="<?php echo $val;?>" />
            <?php endforeach; } ?>
          	<input type="hidden" name="nearby" value="search" />
               <input type="text" value="<?php echo $what; ?>" name="s" id="search_near-<?php echo $search_id;?>" class="searchpost" placeholder="<?php if(isset($_REQUEST['s']) && trim($_REQUEST['s']) == '') { echo $search_txt;} else { echo $search_txt; }?>"/>
               
               <input type="text" name="location" id="location" class="location" value="<?php echo $location; ?>"  placeholder="<?php _e('Where?',DIR_DOMAIN);?>"/>
               <?php if($miles_search==1):?>
                <select id="radius" name="radius">
                    <option value=''><?php _e('Within?',DIR_DOMAIN); ?></option>
                    <option value="1" <?php if(isset($distance_factor) && esc_attr($distance_factor)=='1'){ echo 'selected="selected"';} ?>>1 <?php echo ($radius_measure=='miles')? __('Mile',DIR_DOMAIN) : __('Kilometer',DIR_DOMAIN);; ?></option>
                    <option value="5" <?php if(isset($distance_factor) && esc_attr($distance_factor)=='5'){ echo 'selected="selected"';} ?>>5 <?php echo $radius_type; ?></option>
                    <option value="10" <?php if(isset($distance_factor) && esc_attr($distance_factor)=='10'){ echo 'selected="selected"';} ?>>10 <?php echo $radius_type; ?></option>
                    <option value="100" <?php if(isset($distance_factor) && esc_attr($distance_factor)=='100'){ echo 'selected="selected"';} ?>>100 <?php echo $radius_type; ?></option>
                    <option value="1000" <?php if(isset($distance_factor) && esc_attr($distance_factor)=='1000'){ echo 'selected="selected"';} ?>>1000 <?php echo $radius_type; ?></option>
                    <option value="5000" <?php if(isset($distance_factor) && esc_attr($distance_factor)=='5000'){ echo 'selected="selected"';} ?>> 5000 <?php echo $radius_type; ?></option>      
               </select>               
               <?php endif;?>
               <input type="hidden" name="radius_type" value="<?php echo $radius_measure?>" />
               <input type="submit" class="sgo" onclick="find_click(<?php echo $search_id;?>);" value="<?php _e('Search',DIR_DOMAIN);?>" />
          </form>
		<script type="text/javascript">
			jQuery('[placeholder]').focus(function() {
				var input = jQuery(this);
					if (input.val() == input.attr('placeholder')) {
						input.val('');
						input.removeClass('placeholder');
					}
				}).blur(function() {
					var input = jQuery(this);
					if (input.val() == '' || input.val() == input.attr('placeholder')) {
						input.addClass('placeholder');
						input.val(input.attr('placeholder'));
					}
				}).blur().parents('form').submit(function() {
					jQuery(this).find('[placeholder]').each(function() {
					var input = jQuery(this);
					if (input.val() == input.attr('placeholder')) {
						input.val('');
					}
				})
			});
			function find_click(search_id)
			{
				if(jQuery('#search_near-'+search_id).val() == '<?php  echo $search_txt; ?>')
				{
					jQuery('#search_near-'+search_id).val(' ');
				}
				if(jQuery('#location').val() == '<?php _e('Address',DIR_DOMAIN); ?>')
				{
					jQuery('#location').val('');
				}
			}
			
          </script>
		<?php
		echo '</div>';
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		return $new_instance;
	}
	function form($instance) {
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Search Near By Location' ,'post_type' => 'post' ) );		
		$title = strip_tags($instance['title']);
		$post_type = $instance['post_type'];
		$miles_search=strip_tags($instance['miles_search']);
		$radius_measure=strip_tags($instance['radius_measure']);	
		if(!is_array($post_type))		
			$post_type = array($post_type);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('post_type');?>" ><?php echo __('Select Post:','templatic-admin');?>     </label>	
			<select  id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>[]" multiple="multiple" class="widefat">        	
				<?php
                    $all_post_types = get_option("templatic_custom_post");
                    foreach($all_post_types as $key=>$post_types){
					?>
					<option value="<?php echo $key;?>" <?php if($key !='' && in_array($key,$post_type))echo "selected";?>><?php echo esc_attr($post_types['label']);?></option>
					<?php
                    }
                    ?>	
			</select>
		</p>
           <p>
               <label for="<?php echo $this->get_field_id('miles_search'); ?>">
               <input id="<?php echo $this->get_field_id('miles_search'); ?>" name="<?php echo $this->get_field_name('miles_search'); ?>" type="checkbox" value="1" <?php if($miles_search =='1'){ ?>checked=checked<?php } ?>style="width:10px;"  />
               <?php echo __('<b>Search By Distance?</b>','templatic-admin');?>              
               </label>
          </p>   
            <p>            
               <label for="<?php echo $this->get_field_id('radius_measure'); ?>"><?php echo __('Search By','templatic-admin');?>
               <select id="<?php echo $this->get_field_id('radius_measure'); ?>" name="<?php echo $this->get_field_name('radius_measure'); ?>">
                    <option value="kilometer" <?php if(esc_attr($radius_measure)=='kilometer'){ echo 'selected="selected"';} ?>><?php echo __('Kilometers','templatic-admin'); ?></option>
                    <option value="miles" <?php if(esc_attr($radius_measure)=='miles'){ echo 'selected="selected"';} ?>><?php echo __('Miles','templatic-admin'); ?></option>                    
               </select>
               </label>             
		</p> 
		<?php			
	}
}
/* End of location wise search widget */
/*
	Class: directory_featured_homepage_listing
	Desc: Widget of show the featured listing on home page
*/
class directory_featured_homepage_listing extends WP_Widget {
	
	function directory_featured_homepage_listing() {
		//Constructor
		global $thumb_url;
		$widget_ops = array('classname' => 'widget special', 'description' =>__('Showcase posts from any post type, including those created by you. Featured posts are displayed at the top. Works best in the Homepage - Main Content area.','templatic-admin')) ;
		$this->WP_Widget('directory_featured_homepage_listing',__('T &rarr; Homepage Display Posts','templatic-admin'), $widget_ops);
	}
	
	function widget($args, $instance) {
		// prints the widget
		global $current_cityinfo,$htmlvar_name;
		extract($args, EXTR_SKIP);		
		//echo $before_widget;
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$category = empty($instance['category']) ? '' : apply_filters('widget_category', $instance['category']);
		$number = empty($instance['number']) ? '5' : apply_filters('widget_number', $instance['number']);
		$my_post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
		$link = empty($instance['link']) ? '#' : apply_filters('widget_link', $instance['link']);
		$text = empty($instance['text']) ? '' : apply_filters('widget_text', $instance['text']);
		$view = empty($instance['view']) ? 'list' : apply_filters('widget_view', $instance['view']);
		$read_more = empty($instance['read_more']) ? '' : apply_filters('widget_read_more', $instance['read_more']);
		
		global $post,$wpdb;
		$post_widget_count = 1;
		
		$cus_post_type = empty($instance['post_type']) ? 'listing' : $instance['post_type'];
	
		$heading_type = directory_fetch_heading_post_type($cus_post_type);
		
		if(count($heading_type) > 0)
		{
			foreach($heading_type as $key=> $heading)
			{	
				$htmlvar_name[$key] = get_directory_listing_customfields($cus_post_type,$heading,$key);//custom fields for custom post type..
			}
		}
		remove_filter('pre_get_posts', 'home_page_feature_listing');
		$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $my_post_type,'public'   => true, '_builtin' => true ));
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			if(@$category!=""){
				$category_ID =  get_term_by( 'slug',$category,  $taxonomies[0] );	
				$category=$category_ID->slug;
				$title=ucfirst($category_ID->name);
			}
		}
		
		if($category!=""){
			$args=array(
					'post_type' => $my_post_type,
					'posts_per_page' => $number,				
					'post_status' => 'publish',				
					'tax_query' => array(
								array(
									'taxonomy' => $taxonomies[0],
									'field' => 'slug',
									'terms' => explode(",",$category),								
								)
						),			
			);
		}else{
			if(is_active_addons('custom_taxonomy')){
				$args=array(
					'post_type' => $my_post_type,
					'post_status' => 'publish',				
					'posts_per_page' => $number,
					);
			}
		}
		$my_query = null;
		
		remove_filter('posts_orderby', 'home_page_feature_listing_orderby');
		add_filter('posts_orderby', 'directory_feature_listing_orderby');
		
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			$flg=0;
			$location_post_type=implode(',',get_option('location_post_type'));
			if(isset($my_post_type) && $my_post_type!=''){
				if (strpos($location_post_type,','.$my_post_type) !== false) {
				   $flg=1;
				}
			}
			if($flg==1){
				add_filter('posts_where', 'location_multicity_where');
			}
		}
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			add_filter('posts_where', 'wpml_listing_milewise_search_language');
		}
		$my_query = new WP_Query($args);	
	
		if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
		{
			add_filter('posts_where', 'location_multicity_where');
		}
		if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			remove_filter('posts_where', 'wpml_listing_milewise_search_language');
		}
		global $htmlvar_name;
		$heading_type = directory_fetch_heading_post_type($my_post_type);
		if(count($heading_type) > 0)
		{
			foreach($heading_type as $key=>$heading)
			{	
				$htmlvar_name[$key] = get_directory_listing_customfields($my_post_type,$heading,$key);//custom fields for custom post type..
			}
		}
		
		?>
        <div id="widget_loop_<?php echo $my_post_type?>" class="widget widget_loop_taxonomy widget_loop_<?php echo $my_post_type?> <?php echo $view?>">			
          <?php if( $my_query->have_posts()): ?>
			<?php if($title){?><h3 class="widget-title"><span><?php echo $title;?></span><?php if($link){?><a href="<?php echo $link;?>" class="more" ><?php echo $text; ?></a><?php }?></h3> <?php }?>
			<!-- widget_loop_taxonomy_wrap START -->
			<div class="widget_loop_taxonomy_wrap">
          	<?php while($my_query->have_posts()) : $my_query->the_post();?> 
				<!-- inside loop div start -->
               	<div id="<?php echo $my_post_type.'_'.get_the_ID(); ?>" <?php if((get_post_meta($post->ID,'featured_h',true) == 'h')){ post_class('post featured_post');} else { post_class('post');}?>>
               	 
				<?php   $post_id=get_the_ID();
						do_action('directory_featured_widget_listing_image',$post_id,$my_post_type);
						?>
							  <!-- End fp_image-->
                              <!-- start fp_entry -->
                              <div class="fp_entry">
							<?php do_action('home_featured_before_title');
                                   $post_type= $post->post_type; 							
                                   do_action('supreme_before-title_'.$post_type);
                                   do_action('show_directory_featured_homepage_listing');
								  
                                   do_action('directory_featured_widget_listing_postinfo');
                                   do_action('supreme_after-title_'.$post_type,$instance);	
                                   
                                   do_action('home_featured_after_title',$instance);
                                   do_action('home_featured_before_content');
                                   do_action('home_featured_after_content');
                                   do_action('templ_the_taxonomies');
                                   
                                   echo "<div class='rev_pin'><ul>";								
                                   if(current_theme_supports('tevolution_my_favourites') && function_exists('tevolution_favourite_html')){
								echo '<li>';
								tevolution_favourite_html();	
								echo '</li>';
                                   }
                                   echo '<li>';
                                   do_action('directory_the_comment');   
                                   echo '</li></ul></div>'; ?>
                              </div> <!-- End fp_entry -->
				</div> <!-- inside loop div end -->     
            <?php endwhile; wp_reset_query();?>
			</div>
			<!-- widget_loop_taxonomy_wrap eND -->
			<?php endif; ?>
			</div> <!-- widget_loop_taxonomy -->
          <?php
		
	 	//echo $after_widget;
	}
	
	
	function update($new_instance, $old_instance) {
		//save the widget
		return $new_instance;
	}
	function form($instance) {
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'title' => __("Featured Listing",'templatic-admin'), 'category' => '', 'number' => 5 , 'post_type' => 'listing' , 'link' => '#', 'text' => __("View All",'templatic-admin'), 'view' => 'list','read_more' => '' ) );
		$title = strip_tags($instance['title']);
		$category = strip_tags($instance['category']);
		$number = strip_tags($instance['number']);
		$my_post_type = strip_tags($instance['post_type']);
		$link = strip_tags($instance['link']);
		$text = strip_tags($instance['text']);
		$view = strip_tags($instance['view']);
		$read_more = strip_tags($instance['read_more']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>: 
               	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
               </label>
          </p>
		<p>
          	<label for="<?php echo $this->get_field_id('text'); ?>"><?php echo __('View All Text','templatic-admin');?>: 
              		<input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo esc_attr($text); ?>" />
               </label>
          </p>
		<p>
          	<label for="<?php echo $this->get_field_id('link'); ?>"><?php echo __('View All Link URL: (ex.http://templatic.com/events)','templatic-admin');?> 
               	<input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr($link); ?>" />
               </label>
		</p>
		<p>
               <label for="<?php echo $this->get_field_id('number'); ?>"><?php echo __('Number of posts','templatic-admin');?>:
               	<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr($number); ?>" />
               </label>
		</p>
          <p>
               <label for="<?php echo $this->get_field_id('view'); ?>"><?php echo __('View','templatic-admin')?>:
               <select id="<?php echo $this->get_field_id('view'); ?>" name="<?php echo $this->get_field_name('view'); ?>">
                         <option value="list" <?php if($view == 'list'){ echo 'selected="selected"';}?>><?php echo __('List view','templatic-admin');?></option>
                         <option value="grid" <?php if($view == 'grid'){ echo 'selected="selected"';}?>><?php echo __('Grid view','templatic-admin');?></option>
               </select>
               </label>
          </p>
		<p>
               <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php echo __('Post Type','templatic-admin')?>:
               <select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
				 <?php
					$all_post_types = get_option("templatic_custom_post");
					foreach($all_post_types as $key=>$post_type){?>
						<option value="<?php echo $key;?>" <?php if($key== $my_post_type)echo "selected";?>><?php echo esc_attr($post_type['label']);?></option>
				<?php }?>	
               </select>
               </label>
		</p>
           <p>
             <label for="<?php echo $this->get_field_id('content_limit'); ?>"><?php echo __('Limit content to', 'templatic-admin'); ?>: </label> <input type="text" id="<?php echo $this->get_field_id('image_alignment'); ?>" name="<?php echo $this->get_field_name('content_limit'); ?>" value="<?php echo esc_attr(intval($instance['content_limit'])); ?>" size="3" /> <?php echo __('characters', 'templatic-admin'); ?>
          </p>
		  <p>
          	<label for="<?php echo $this->get_field_id('read_more'); ?>"><?php echo __('Read More Text','templatic-admin');?>: 
              		<input class="widefat" id="<?php echo $this->get_field_id('read_more'); ?>" name="<?php echo $this->get_field_name('read_more'); ?>" type="text" value="<?php echo esc_attr($read_more); ?>" />
               </label>
          </p>
		<p>
               <label for="<?php echo $this->get_field_id('category'); ?>"><?php echo __('Categories: (<code>SLUGs</code> separated by commas)','templatic-admin');?>
                    <input class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo esc_attr($category); ?>" />
               </label>
		</p>
		<?php
	}
}
/* End directory_featured_homepage_listing widget */
if(!function_exists('directory_content_limit')){
	function directory_content_limit($max_char, $more_link_text = '', $stripteaser = true, $more_file = '') {	
		global $post;	
		
		$content = get_the_content();
		$content = strip_tags($content);
		$content = substr($content, 0, $max_char);
		$content = substr($content, 0, strrpos($content, " "));
		$more_link_text='<a href="'.get_permalink().'">'.$more_link_text.'</a>';
		$content = $content." ".$more_link_text;
		echo $content;	
	}
}
/*
 * Class Name: directory_featured_category_list
 * Return: display all the category list on home page
 */
class directory_featured_category_list extends WP_Widget {
		function directory_featured_category_list() {
		//Constructor
			$widget_ops = array('classname' => 'widget all_category_list_widget', 'description' => __('Shows a list of all categories and their sub-categories. Works best in main content and subsidiary areas.','templatic-admin') );		
			$this->WP_Widget('directory_featured_category_list', __('T &rarr; All Categories List','templatic-admin'), $widget_ops);
		}
		function widget($args, $instance) 
		{
		// prints the widget
			global $current_cityinfo;
			extract($args, EXTR_SKIP);
			$cur_lang_code=(is_plugin_active('sitepress-multilingual-cms/sitepress.php'))? ICL_LANGUAGE_CODE :'';
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			$post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
			$category_level = empty($instance['category_level']) ? '1' : apply_filters('widget_category_level', $instance['category_level']);
			$number_of_category = ($instance['number_of_category'] =='') ? '6' : apply_filters('widget_number_of_category', $instance['number_of_category']);
			$hide_empty_cat = ($instance['hide_empty_cat'] =='') ? '0' : apply_filters('widget_hide_empty_cat', $instance['hide_empty_cat']);
			
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));
			
			$args5=array(
					'orderby'    => 'name',
					'taxonomy'   => $taxonomies[0],
					'order'      => 'ASC',
					'parent'     => '0',
					'show_count' => 0,
					'hide_empty' => 0,
					'pad_counts' => true,					
				);
			
			echo $before_widget;
			
			/* set wp_categories on transient */
			if ( false === ( $categories = get_transient( '_tevolution_query_catwidget'.$post_type.$cur_lang_code) ) && get_option('tevolution_cache_disable')==1 ) {
				$categories=get_categories($args5);
				set_transient( '_tevolution_query_catwidget'.$post_type.$cur_lang_code, $categories, 12 * HOUR_IN_SECONDS );
			}elseif(get_option('tevolution_cache_disable')==''){
				$categories=get_categories($args5);
			}
			 
			if($title){echo '<h3 class="widget-title">'.$title.'</h3>'; } ?>
			<div class="category_list_wrap">
            <?php 
			if(!isset($categories['errors'])){
				foreach($categories as $category) 
				{	
					/* set child wp_categories on transient */
					
					$transient_name=(!empty($current_cityinfo))? $current_cityinfo['city_slug']: '';					
					if ( false === ( $featured_catlist_list = get_transient( '_tevolution_query_catwidget'.$category->term_id.$post_type.$transient_name.$cur_lang_code) ) && get_option('tevolution_cache_disable')==1 ) { 
						do_action('tevolution_category_query');						
						$featured_catlist_list =  wp_list_categories('title_li=&child_of=' . $category->term_id .'&echo=0&depth='.$category_level.'&number='.$number_of_category.'&taxonomy='.$taxonomies[0].'&show_count=1&hide_empty='.$hide_empty_cat.'&pad_counts=0&show_option_none=');
						set_transient( '_tevolution_query_catwidget'.$category->term_id.$post_type.$transient_name.$cur_lang_code, $featured_catlist_list, 12 * HOUR_IN_SECONDS );				
					}elseif(get_option('tevolution_cache_disable')==''){
						do_action('tevolution_category_query');
						$featured_catlist_list =  wp_list_categories('title_li=&child_of=' . $category->term_id .'&echo=0&depth='.$category_level.'&number='.$number_of_category.'&taxonomy='.$taxonomies[0].'&show_count=1&hide_empty='.$hide_empty_cat.'&pad_counts=0&show_option_none=');
					}
					if(is_plugin_active('Tevolution-LocationManager/location-manager.php'))
					{
						remove_filter( 'terms_clauses','locationwise_change_category_query',10,3 );	
					}
					$parent = get_term( $category->term_id, $taxonomies[0] );
					if($hide_empty_cat ==1 ){
					if($parent->count !=0 || $featured_catlist_list != ""){
					?>	
                        <div class="category_list">
							<?php 
							if($parent){
									$parents = '<a href="' . get_term_link( $parent, $taxonomies[0] ) . '" title="' . esc_attr( $parent->name ) . '">' . $parent->name . '</a>';
									if($hide_empty_cat == 1){
										if($parent->count !=0){ ?>
										<h3><?php do_action('show_categoty_map_icon',$parent->term_icon); echo $parents; ?></h3>                         
									<?php } 
									}else{ ?>
										<h3><?php do_action('show_categoty_map_icon',$parent->term_icon); echo $parents; ?></h3>                         
									<?php }

								if( @$featured_catlist_list != "" ){
									if($number_of_category !=0){ ?>
										<ul>
											<?php echo $featured_catlist_list; ?>
											<li class="view">
												<a href="<?php echo get_term_link($parent, $taxonomies[0]);?>">
													<?php _e('View all &raquo;',DIR_DOMAIN)?>
												</a> 
											</li>                                        
										</ul>
						<?php 	
									}
								}
								}
						?>
                         </div>   
					<?php }
				 }else{ ?>
					<div class="category_list">
							<?php 
							if($parent){
									$parents = '<a href="' . get_term_link( $parent, $taxonomies[0] ) . '" title="' . esc_attr( $parent->name ) . '">' . $parent->name . '</a>';
									?>
										<h3><?php do_action('show_categoty_map_icon',$parent->term_icon); echo $parents; ?></h3>                         
								<?php

								if( @$featured_catlist_list != "" ){
									if($number_of_category !=0){ ?>
										<ul>
											<?php echo $featured_catlist_list; ?>
											<li class="view">
												<a href="<?php echo get_term_link($parent, $taxonomies[0]);?>">
													<?php _e('View all &raquo;',DIR_DOMAIN)?>
												</a> 
											</li>                                        
										</ul>
						<?php 	
									}
								}
								}
						?>
                    </div>   
				<?php }
				}
			}else{
				echo '<p>'. __('Invalid Category.',DIR_DOMAIN).'</p>';
			} ?>
             </div>
             <?php echo $after_widget;
		}
		function update($new_instance, $old_instance) {
			//save the widget	
			global $wpdb;
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name like '%s'",'%_tevolution_query_catwidget%' ));
			return $new_instance;
		}
		function form($instance) {
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'category_level' => '1','number_of_category' => '5') );		
			$title = strip_tags($instance['title']);
			$my_post_type = ($instance['post_type']) ? $instance['post_type'] : 'listing';
			$category_level = ($instance['category_level']);
			$number_of_category = ($instance['number_of_category']);
			$hide_empty_cat = ($instance['hide_empty_cat']);
			?>
               <p>
                    <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:','templatic-admin');?> 
                         <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
                    </label>
               </p>
				<p>
               	<label for="<?php echo $this->get_field_id('post_type'); ?>"><?php echo __('Post Type:','templatic-admin')?>
                    <select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
                          <?php
						$all_post_types = get_option("templatic_custom_post");
						foreach($all_post_types as $key=>$post_type){?>
							<option value="<?php echo $key;?>" <?php if($key== $my_post_type)echo "selected";?>><?php echo esc_attr($post_type['label']);?></option>
					<?php }?>	
                    </select>
                    </label>
               </p> 
				<p>
                    <label for="<?php  echo $this->get_field_id('category_level'); ?>"><?php echo __('Category Level','templatic-admin');?>: 
                         <select id="<?php  echo $this->get_field_id('category_level'); ?>" name="<?php echo $this->get_field_name('category_level'); ?>">
                         <?php
                         for($i=1;$i<=10;$i++)
                         {?>
                         	<option value="<?php echo $i;?>" <?php if(esc_attr($category_level)==$i){?> selected="selected" <?php } ?>><?php echo $i;?></option>
                         <?php
                         }?>
                         </select>
                    </label>
               </p> 
			 <p>
               	<label for="<?php  echo $this->get_field_id('number_of_category'); ?>"><?php echo __('Number of child categories','templatic-admin');?>: <input class="widefat" id="<?php  echo $this->get_field_id('number_of_category'); ?>" name="<?php echo $this->get_field_name('number_of_category'); ?>" type="text" value="<?php echo esc_attr($number_of_category); ?>" />
                    </label>
               </p> 
               <?php if(!is_plugin_active('Tevolution-LocationManager/location-manager.php')){ ?>   
			   	<p>
               		<label for="<?php  echo $this->get_field_id('hide_empty_cat'); ?>"><input class="widefat" id="<?php  echo $this->get_field_id('hide_empty_cat'); ?>" name="<?php echo $this->get_field_name('hide_empty_cat'); ?>" type="checkbox" value="1" <?php if(@$hide_empty_cat ==1){ echo "checked=checked"; }?>/>
                	<?php echo __('Hide empty categories','templatic-admin');?></label>
                </p>
               <?php } ?>
		
		<?php
		}
}
/*
directory_mile_range_widget : Miles wise searching widget 
*/
class directory_mile_range_widget extends WP_Widget {
	function directory_mile_range_widget() {
		//Constructor
		$widget_ops = array('classname' => 'search_miles_range', 'description' => __('Search through nearby posts by setting a range. Use in category page sidebar areas.','templatic-admin') );
		$this->WP_Widget('directory_mile_range_widget', __('T &rarr; Search by Miles Range','templatic-admin'), $widget_ops);
	}
	function widget($args, $instance) {
		// prints the widget
		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? 'Search Near By Miles Range' : apply_filters('widget_title', $instance['title']);		
		//$post_type = empty($instance['post_type']) ? 'listing' : apply_filters('widget_post_type', $instance['post_type']);
		$post_type= get_post_type();
		$miles_search = empty($instance['miles_search']) ? '' : apply_filters('widget_miles_search', $instance['miles_search']);
		$max_range = empty($instance['max_range']) ? '' : apply_filters('widget_max_range', $instance['max_range']);
		echo $before_widget;
		$search_txt=sprintf(__('Find a %s',DIR_DOMAIN),$post_type);
		echo '<div class="search_nearby_widget">';
		if($title){echo '<h3 class="widget-title">'.$title.'</h3>';}
		global $wpdb,$wp_query;
		wp_enqueue_script('directory-search-script', TEVOLUTION_DIRECTORY_URL.'js/search_map_script.js',array( 'jquery' ),'',false);
		
		if(is_tax()){
			$list_id='loop_'.$post_type.'_taxonomy';
			$page_type='taxonomy';
		}else{
			$list_id='loop_'.$post_type.'_archive';
			$page_type='archive';
		}
		
		
		$queried_object = get_queried_object();  
		$term_id = $queried_object->term_id;  
		$query_string='&term_id='.$term_id;
		
		?>
		<form method="get" id="searchform" action="<?php echo home_url(); ?>/">
          	<input type="hidden" name="post_type" value="<?php echo $post_type;?>" />          	
               <?php
			wp_enqueue_script("jquery-ui-slider");			
			?>               
               <div class="search_range">
                  <label><?php _e('Mile range:',DIR_DOMAIN); ?></label>
                  <input type="text" name="radius" id="radius_range" value="<?php echo $max_range; ?>" style="border:0; font-weight:bold;"  readonly="readonly"/>
              </div>              
              <div id="radius-range"></div>
              <script type="text/javascript">		    
				jQuery('#radius-range').bind('slidestop', function(event, ui) {				
				var miles_range=jQuery('#radius_range').val();
				var list_id='<?php echo $list_id?>';	
				jQuery('.'+list_id+'_process').remove();
				jQuery('#'+list_id ).before( "<p class='<?php echo $list_id.'_process';?>' style='text-align:center';><img src='<?php echo TEVOLUTION_DIRECTORY_URL.'images/process.gif';?>'  alt='Processing..'/></p>" );
				<?php
				if(isset($_SERVER['QUERY_STRING'])){
					$query_string.='&'.$_SERVER['QUERY_STRING'];
				}
				?>				
				jQuery.ajax({
					url:ajaxUrl,
					type:'POST',			
					data:'action=<?php echo $post_type."_search";?>&posttype=<?php echo $post_type;?>&miles_range='+miles_range+'&page_type=<?php echo $page_type.$query_string;?>',
					success:function(results){
						jQuery('.'+list_id+'_process').remove();
						jQuery('#'+list_id).html(results);
						jQuery('#listpagi').remove();
					}
				});
				
				jQuery.ajax({
					url:ajaxUrl,
					type:'POST',			
					data:'action=<?php echo $post_type."_search_map";?>&posttype=<?php echo $post_type;?>&miles_range='+miles_range+'&page_type=<?php echo $page_type.$query_string;?>',
					success:function(results){						
						miles_googlemap(results);
					}
				});	
			});
			jQuery(function(){jQuery("#radius-range").slider({range:true,min:1,max:<?php echo $max_range; ?>,values:[1,<?php echo $max_range; ?>],slide:function(e,t){jQuery("#radius_range").val(t.values[0]+" - "+t.values[1])}});jQuery("#radius_range").val(jQuery("#radius-range").slider("values",0)+" - "+jQuery("#radius-range").slider("values",1))})
		    </script>
            
          </form>		
		<?php
		echo '</div>';
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		return $new_instance;
	}
	function form($instance) {
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Search Nearby Miles Range', 'max_range' => 500, 'post_type' => 'listing' ) );		
		$title = strip_tags(@$instance['title']);
		$post_type = strip_tags(@$instance['post_type']);
		$max_range = strip_tags(@$instance['max_range']);
		$miles_search=strip_tags(@$instance['miles_search']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('max_range'); ?>"><?php echo __('Max Range','templatic-admin');?>:
			<input class="widefat" id="<?php echo $this->get_field_id('max_range'); ?>" name="<?php echo $this->get_field_name('max_range'); ?>" type="text" value="<?php echo esc_attr($max_range); ?>" />
			</label>
		</p>		 
		<?php			
	}
}
/* End directory_mile_range_widget */
/*
	Name : slider_search_option	
	Desc : Add the JS Of sliding search(miles wise searching) in footer
*/
function slider_search_option(){	
	?><script type="text/javascript">	  
		jQuery(function(){jQuery("#radius-range").slider({range:true,min:1,max:500,values:[1,500],slide:function(e,t){jQuery("#radius_range").val(t.values[0]+" - "+t.values[1])}});jQuery("#radius_range").val(jQuery("#radius-range").slider("values",0)+" - "+jQuery("#radius-range").slider("values",1))})
	   </script>
     <?php	
}
?>
