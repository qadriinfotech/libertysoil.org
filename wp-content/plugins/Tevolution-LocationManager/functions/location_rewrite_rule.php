<?php 
/* add  city wise permalink*/
if(get_option('tev_lm_new_city_permalink') == 1){
	add_action('init', 'templatic_add_rewrite_city_rules',10);
}else{
	add_action('init', 'templatic_add_rewrite_rules',10);
}

/*
Name:templatic_add_rewrite_city_rules
Desc : permalink rule - return permalink with city slug
*/
function templatic_add_rewrite_city_rules() {
	global $wp_rewrite,$wpdb;
	$city_slug=get_option('location_multicity_slug');	
	$multi_city=($city_slug)? $city_slug : 'city';
	
	$wp_rewrite->add_rewrite_tag('%city%', '([^/]+)', $multi_city.'=');
	$pid = get_option('default_comments_page');
	if(!get_option('permalink_autoupdate')){
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name like '%s'",'%_tevolution_query_%' ));		
		update_option('permalink_autoupdate',1);
	}
	if($pid =='last'){ $pid ='1'; }else{ $pid ='1';}
	$location_post_type=get_option('location_post_type');
	$tevolution_taxonomies=get_option('templatic_custom_taxonomy');	
	if($location_post_type!='' ||!empty($location_post_type)){
		foreach($location_post_type as $post_type){
			$posttype=explode(',',$post_type);
			
			$wp_rewrite->add_rewrite_tag('%'.$posttype[0].'%', '([^/]+)', $posttype[0].'=');
			$wp_rewrite->add_permastruct($posttype[0], '/'.$multi_city.'/%city%/'.$posttype[0].'/%'.$posttype[0].'%', false);
					
			$category_slug=@$tevolution_taxonomies[$posttype[1]]['rewrite']['slug'];
			if($posttype[0]=='post'){
				$category_slug='category';
			}
			if($category_slug==""){
				$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $posttype[0],'public'   => true, '_builtin' => true ));
				$category_slug=$taxonomies[0];	
			}
			$wp_rewrite->add_permastruct($posttype[1], '/'.$multi_city.'/%city%/'.$category_slug.'/%'.$posttype[1].'%', false);
			
			//$wp_rewrite->add_permastruct($posttype[2], '/'.$multi_city.'/%city%/%'.$posttype[2].'%/%'.$posttype[0].'%', false);
		}		
		$wp_rewrite->flush_rules();
		if(function_exists('flush_rewrite_rules')){
			//
			$ver = filemtime( __FILE__ ); // Get the file time for this file as the version number
			$defaults = array( 'version' => 0, 'time' => time() );
			$r = wp_parse_args( get_option( __CLASS__ . '_flush', array() ), $defaults );
			if ( $r['version'] != $ver || $r['time'] + 86400 < time() ) { // Flush if ver changes or if 48hrs has passed.
				flush_rewrite_rules(true);  
				// trace( 'flushed' );
				$args = array( 'version' => $ver, 'time' => time() );
				if ( ! update_option( __CLASS__ . '_flush', $args ) )
					add_option( __CLASS__ . '_flush', $args );
			}
			//
		}
	}
}
/*
Name:templatic_add_rewrite_rules
Desc : permalink rule - return permalink without city slug
*/
function templatic_add_rewrite_rules() {
	global $wp_rewrite;
	$city_slug=get_option('location_multicity_slug');	
	$multi_city=($city_slug)? $city_slug : 'city';
	
	$wp_rewrite->add_rewrite_tag('%city%', '([^/]+)', 'city=');
	$pid = get_option('default_comments_page');
	if($pid =='last'){ $pid ='1'; }else{ $pid ='1';}
	$location_post_type=get_option('location_post_type');	
	if($location_post_type!='' ||!empty($location_post_type)){
		foreach($location_post_type as $post_type){
			$posttype=explode(',',$post_type);	
			$wp_rewrite->add_rewrite_tag('%'.$posttype[0].'%', '([^/]+)', $posttype[0].'=');
			//$wp_rewrite->add_rewrite_tag('%event%', '([^/]+)', 'event=');
			$wp_rewrite->add_permastruct($posttype[0], '/'.$multi_city.'/%city%/'.$posttype[0].'/%'.$posttype[0].'%', false);
			//$wp_rewrite->add_permastruct('event', '/city/%city%/event/%event%', false);
		}
		$wp_rewrite->flush_rules();
		if(function_exists('flush_rewrite_rules')){
			//
			$ver = filemtime( __FILE__ ); // Get the file time for this file as the version number
			$defaults = array( 'version' => 0, 'time' => time() );
			$r = wp_parse_args( get_option( __CLASS__ . '_flush', array() ), $defaults );
			if ( $r['version'] != $ver || $r['time'] + 86400 < time() ) { // Flush if ver changes or if 48hrs has passed.
				flush_rewrite_rules(true);  
				// trace( 'flushed' );
				$args = array( 'version' => $ver, 'time' => time() );
				if ( ! update_option( __CLASS__ . '_flush', $args ) )
					add_option( __CLASS__ . '_flush', $args );
			}
			//
		}
	}
}

/*
 * Function Name: location_archive_filter_rewrite_rules
 * Return: set custom post type archive page as per location wise rewrite rules
 */
add_filter('rewrite_rules_array','location_archive_filter_rewrite_rules');	
function location_archive_filter_rewrite_rules($rewrite_rules){
	global $current_cityinfo,$wpdb;
	$multicity_table = $wpdb->prefix . "multicity";
	$location_post_type=get_option('location_post_type');	
	if($location_post_type!='' ||!empty($location_post_type)){
		$city_slug=get_option('location_multicity_slug');	
		$multi_city=($city_slug)? $city_slug : 'city';
		$sql=$wpdb->prepare("SELECT * FROM $multicity_table where is_default=%d",1);
		$default_city = $wpdb->get_results($sql);
		$city=(isset($current_cityinfo['city_slug']) && $current_cityinfo['city_slug']!='')? $current_cityinfo['city_slug'] :$default_city[0]->city_slug;
		foreach($location_post_type as $post_type){
			$posttype=explode(',',$post_type);				
			$new_archive_rules[$multi_city.'/([^/]+)/'.$posttype[0].'/?$'] = 'index.php?city=$matches[1]&post_type='.$posttype[0];
			$new_archive_rules[$multi_city.'/([^/]+)/'.$posttype[0].'/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?city=$matches[1]&post_type='.$posttype[0].'&feed=$matches[2]';
			$new_archive_rules[$multi_city.'/([^/]+)/'.$posttype[0].'/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?city=$matches[1]&post_type='.$posttype[0].'&feed=$matches[2]';
			$new_archive_rules[$multi_city.'/([^/]+)/'.$posttype[0].'/page/([0-9]{1,})/?$'] = 'index.php?city=$matches[1]&post_type='.$posttype[0].'&paged=$matches[2]';
			$rewrite_rules =  $new_archive_rules + $rewrite_rules;	
		}
		
	}
	
	return $rewrite_rules;
}

/*
 * Function Name: templatic_create_archive_permalinks
 * Return: add locaton city slug on archive page
 */
add_filter('post_type_archive_link','templatic_create_archive_permalinks',10,2);
function templatic_create_archive_permalinks( $link, $post_type){
	global $current_cityinfo,$wpdb;
	$multicity_table = $wpdb->prefix . "multicity";
	$location_post_type=implode(',',get_option('location_post_type'));
	if (strpos($location_post_type,','.$post_type) !== false) {		$city_slug=get_option('location_multicity_slug');	
		$multi_city=($city_slug)? $city_slug : 'city';
		$sql=$wpdb->prepare("SELECT * FROM $multicity_table where is_default=%d",1);
		$default_city = $wpdb->get_results($sql);
		$city=(isset($current_cityinfo['city_slug']) && $current_cityinfo['city_slug']!='')? $current_cityinfo['city_slug'] :$default_city[0]->city_slug;
		$link=get_bloginfo('url')."/".$multi_city."/".$city."/".$post_type;	
	}
	return $link;
}

/* Set the city permalink for category listing page */
add_filter('category_link','templatic_create_category_permalinks',10,3);
function templatic_create_category_permalinks($termlink, $term){
	global $current_cityinfo;
	
	if(isset($current_cityinfo['city_slug']) && $current_cityinfo['city_slug']!=''){
		$city=$current_cityinfo['city_slug'];
	}else{
		$city='na';
	}
	$termlink = str_replace(array('%city%'), array($city), $termlink);
	return $termlink;
}

/* Set the city permalink for taxonomies listing page */
add_filter('term_link','templatic_create_term_permalinks',10,3);
function templatic_create_term_permalinks($termlink, $term, $taxonomy){
	global $current_cityinfo;
	
	if(isset($current_cityinfo['city_slug']) && $current_cityinfo['city_slug']!=''){
		$city=$current_cityinfo['city_slug'];
	}else{
		$city='na';
	}
	$termlink = str_replace(array('%city%'), array($city), $termlink);
	return $termlink;
}
/*
 * Function Name: templatic_create_permalinks
 * Return : post_city_id is available , add city name slug in permalink
 */
add_filter('post_type_link', 'templatic_create_permalinks', 10, 3);
function templatic_create_permalinks($permalink, $post, $leavename) {	
	global $current_cityinfo;
	$no_data = 'no-data';
	$post_id = $post->ID;
	$pcity_id = apply_filters('city_permalink_slug',get_post_meta($post->ID,'post_city_id',true));	
	if(($post->post_type != '' && $pcity_id!='') && ( empty($permalink) || in_array($post->post_status, array('draft', 'pending', 'auto-draft'))))
		return $permalink;
	
	//echo 
	$pcity_id = get_post_meta($post_id, 'post_city_id', true);
	
	global $wpdb,$city_info;
	$multicity_db_table_name = $wpdb->prefix . "multicity"; // DATABASE TABLE  MULTY CITY
	$pcity_id = get_post_meta($post->ID,'post_city_id',true);
	if(strstr($pcity_id,',')){
		$pcity_id_ = explode(',',$pcity_id);
		$pcity_id = $pcity_id_[0];
	}
	if(!is_admin() && !empty($pcity_id_) && is_array($pcity_id_) && in_array($current_cityinfo['city_id'],$pcity_id_)){
		$pcity_id=$current_cityinfo['city_id'];
	}
	if($pcity_id!=''){
		if(is_admin() || is_singular() || is_search() || (isset($_REQUEST['page']) && $_REQUEST['page']=='success'))
			$city = strtolower($wpdb->get_var("SELECT city_slug FROM $multicity_db_table_name WHERE city_id =\"$pcity_id\""));	
		else
			$city = ($current_cityinfo['city_slug']!='' && !is_author())? $current_cityinfo['city_slug']:strtolower($wpdb->get_var("SELECT city_slug FROM $multicity_db_table_name WHERE city_id =\"$pcity_id\""));				
	}else{
		$city = 'na';
	}  
	$permalink = str_replace('%city%', $city, $permalink);	
	return $permalink;
}
/* Commnet post redirect link with location manager*/
add_filter('comment_post_redirect', 'redirect_after_comment');

/*
Name: redirect_after_comment
Desciption: Redirect on same listing page afetr post the comment ( With location manager city permalink)
*/
function redirect_after_comment($location)
{
	global $wpdb;
		$pid = get_option('default_comments_page');
	if($pid =='last'){ $pid ='1'; }else{ $pid ='2';}
	return $_SERVER["HTTP_REFERER"]."/#comment-".$wpdb->insert_id;
}
function directory_myfeed_request($qv) {
	if (isset($qv['feed']))
		$qv['post_type'] = get_post_types();
	return $qv;
}
add_filter('request', 'directory_myfeed_request');
?>
