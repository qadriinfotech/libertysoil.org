<?php
/* File contain the functions which run & execute the auto install */

set_time_limit(0);
global  $wpdb,$pagenow;

/* Show notifications As per plug-ins activation */
if((!is_plugin_active('Tevolution/templatic.php') || !is_plugin_active('Tevolution-Directory/directory.php')) && is_admin() && 'themes.php' == $pagenow ){
	add_action("admin_notices", "activate_eco_plugin"); // action show notification when tevolution not activated.
}else{
	if(function_exists('is_active_addons')){
		if(!is_active_addons('custom_taxonomy') || !is_active_addons('custom_fields_templates')){
			add_action("admin_notices", "activate_eco_addons"); // action show notification when custom field module not available.
		}else{
			// Action to admin_notices for auto install
			if( false == get_option( 'hide_ajax_notification' ) ) {
				add_action("admin_notices", "tmpl_autoinstall");  // action show notification when custom field module not available.
			}
		}
	}
}

/*
Name: activate_eco_plugin
Desc: Return notifications to admin - to activate tevolution and related plug-in 
*/
function activate_eco_plugin(){
	global $pagenow;
	$url = home_url().'/wp-admin/plugins.php';
	add_css_to_admin();
	$current_system = '';
	if(!is_plugin_active('Tevolution/templatic.php') && is_admin() ){
		$current_system = "<a id='templatic_plugin' href=".$url." style='color:#21759B'>".__('Tevolution',ADMINDOMAIN)."</a>";
	}	
	if(!is_plugin_active('Tevolution-Directory/directory.php') && is_admin() ){
		if($current_system != '')
			$current_system .= __(' and ', ADMINDOMAIN);
		$current_system .= '<a id="booking_plugin" href="'.$url.'" style="color:#21759B">'.__('Tevolution - Directory',ADMINDOMAIN).'</a>';
	}
	if(!is_plugin_active('Tevolution-Directory/directory.php') || !is_plugin_active('Tevolution/templatic.php')):
?>
<div class="error" style="padding:10px 0 10px 10px;font-weight:bold;"> <span>
  <?php echo sprintf(__('Thanks for choosing templatic themes, the base system of templatic is not installed at your side, Please download and activate %s addons to get started with %s website.',ADMINDOMAIN),$current_system,'<span style="color:#000">'. @wp_get_theme().'</span>');?>
  </span> </div>
<?php 	
	endif;
}

/* css to hide notification */
add_action('admin_notices','add_css_to_admin');
function add_css_to_admin(){
	echo '<style type="text/css">
		#message1{
			display:none;
		}
	</style>';
}
/*
Name: activate_eco_addons
Desc: return message to activate plugin if user activate only directory theme
*/
function activate_eco_addons(){
	$url_custom_field = home_url()."/wp-admin/admin.php?page=templatic_system_menu&activated=custom_fields_templates&true=1";
	$url_custom_post_type = home_url()."/wp-admin/admin.php?page=templatic_system_menu&activated=custom_taxonomy&true=1";
	add_css_to_admin();
	?>
	<div class="error" style="padding:10px 0 10px 10px;font-weight:bold;"> <span>
	  <?php echo sprintf(__('Thanks for choosing templatic themes,  the base system of templatic is not installed at your side Now, Please activate both <a id="templatic_plugin" href="%s" style="color:#21759B">Templatic - Custom Post Types Manager</a> and <a  href="%s" style="color:#21759B">Templatic - Custom Fields</a> addons to get started with %s website.',ADMINDOMAIN),$url_custom_post_type,$url_custom_field,'<span style="color:#000">'. @wp_get_theme().'</span>');?>
	  </span> </div>
	<?php 
	}

	/* Templatic Add-On Required messages End */
	
/* Activate add on when run the auto install */
function tmpl_autoinstall()
{
	global $wpdb;
	$wp_user_roles_arr = get_option($wpdb->prefix.'user_roles');
	global $wpdb;
	if((strstr($_SERVER['REQUEST_URI'],'themes.php') && !isset($_REQUEST['page'])) && @$_REQUEST['template']=='' || (isset($_REQUEST['page']) && $_REQUEST['page']=="templatic_system_menu") ){
	
		$post_counts = $wpdb->get_var("select count(post_id) from $wpdb->postmeta where (meta_key='pt_dummy_content' || meta_key='tl_dummy_content') and meta_value=1");
		if($post_counts>0){
			$theme_name = get_option('stylesheet');
			$nav_menu = get_option('theme_mods_'.strtolower($theme_name));
			if(!isset($nav_menu['nav_menu_locations']['secondary']) && @$nav_menu['nav_menu_locations']['secondary'] == 0){
				$menu_msg = "<p><b>".__('Navigation Menu',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/nav-menus.php")."'><b>Setup your Menu here</b></a>  | <b>".__('Customize',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/customize.php")."'><b>".__('Customize your Theme Options.',ADMINDOMAIN)."</b></a><br/> <b>".__('Manage Locations',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=location_settings&location_tabs=location_manage_locations&locations_subtabs=city_manage_locations")."'> <b>".__('Start adding new cities',ADMINDOMAIN)."</b></a> | <b>".__('General Settings',ADMINDOMAIN).":</b><a href='".site_url("wp-admin/admin.php?page=templatic_settings")."'> <b>".__('Setup a few common options to get started',ADMINDOMAIN)."</b></a><br/><b>".__('Manage Custom fields',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=custom_fields")."'> <b>".__('Start adding new fields for your submision form',ADMINDOMAIN)."</b></a> | <b>".__('Manage Listings',ADMINDOMAIN).":</b><a href='".site_url("/wp-admin/edit.php?post_type=listing")."'> <b>".__('Add/Edit/Delete your listings',ADMINDOMAIN)."</b></a><br/><b>".__('Help',ADMINDOMAIN).":</b> <a href='http://templatic.com/docs/directory-guides/'> <b>".__('Theme Documentation Guide',ADMINDOMAIN)."</b></a> | <b>".__('Support',ADMINDOMAIN).":</b><a href='http://templatic.com/forums/viewforum.php?f=119'> <b>".__('Community Forum',ADMINDOMAIN)."</b></a><br/></p>";
			}else{
				$menu_msg="<p><b>".__('Navigation Menu',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/nav-menus.php")."'><b>Setup your Menu here</b></a>  | <b>".__('Customize',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/customize.php")."'><b>".__('Customize your Theme Options.',ADMINDOMAIN)."</b></a><br/> <b>".__('Manage Locations',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=location_settings&location_tabs=location_manage_locations&locations_subtabs=city_manage_locations")."'> <b>".__('Start adding new cities',ADMINDOMAIN)."</b></a> | <b>".__('General Settings',ADMINDOMAIN).":</b><a href='".site_url("wp-admin/admin.php?page=templatic_settings")."'> <b>".__('Setup a few common options to get started',ADMINDOMAIN)."</b></a><br/><b>".__('Manage Custom fields',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=custom_fields")."'> <b>".__('Start adding new fields for your submision form',ADMINDOMAIN)."</b></a> | <b>".__('Manage Listings',ADMINDOMAIN).":</b><a href='".site_url("/wp-admin/edit.php?post_type=listing")."'> <b>".__('Add/Edit/Delete your listings',ADMINDOMAIN)."</b></a><br/><b>".__('Help',ADMINDOMAIN).":</b> <a href='http://templatic.com/docs/directory-guides/'> <b>".__('Theme Documentation Guide',ADMINDOMAIN)."</b></a> | <b>".__('Support',ADMINDOMAIN).":</b><a href='http://templatic.com/forums/viewforum.php?f=119'> <b>".__('Community Forum',ADMINDOMAIN)."</b></a><br/></p>";
			}			
			$dummy_data_msg = '<p><a class="button_delete button-primary" href="'.home_url().'/wp-admin/themes.php?dummy=del">'.__("Delete sample data.",ADMINDOMAIN).'</a> </p>';
			$import_data = __('Sample data has been populated on your website.',ADMINDOMAIN);
			
			
			$dummy_data_msg .= "<p class='hidden-on-test'>".$import_data."</p>".$menu_msg;
		}else{
			$theme_name = get_option('stylesheet');
			$nav_menu = get_option('theme_mods_'.strtolower($theme_name));
			if( @$nav_menu['nav_menu_locations']['secondary'] == 0 ){
				$menu_msg1 = "<p><b>".__('Navigation Menu',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/nav-menus.php")."'><b>Setup your Menu here</b></a>  | <b>".__('Customize',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/customize.php")."'><b>".__('Customize your Theme Options.',ADMINDOMAIN)."</b></a><br/> <b>".__('Manage Locations',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=location_settings&location_tabs=location_manage_locations&locations_subtabs=city_manage_locations")."'> <b>".__('Start adding new cities',ADMINDOMAIN)."</b></a> | <b>".__('General Settings',ADMINDOMAIN).":</b><a href='".site_url("wp-admin/admin.php?page=templatic_settings")."'> <b>".__('Setup a few common options to get started',ADMINDOMAIN)."</b></a><br/><b>".__('Manage Custom fields',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=custom_fields")."'> <b>".__('Start adding new fields for your submision form',ADMINDOMAIN)."</b></a> | <b>".__('Manage Listings',ADMINDOMAIN).":</b><a href='".site_url("/wp-admin/edit.php?post_type=listing")."'> <b>".__('Add/Edit/Delete your listings',ADMINDOMAIN)."</b></a><br/><b>".__('Help',ADMINDOMAIN).":</b> <a href='http://templatic.com/docs/directory-guides/'> <b>".__('Theme Documentation Guide',ADMINDOMAIN)."</b></a> | <b>".__('Support',ADMINDOMAIN).":</b><a href='http://templatic.com/forums/viewforum.php?f=119'> <b>".__('Community Forum',ADMINDOMAIN)."</b></a><br/></p>";
			}else{
				$menu_msg1="<p><b>".__('Navigation Menu',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/nav-menus.php")."'><b>Setup your Menu here</b></a>  | <b>".__('Customize',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/customize.php")."'><b>".__('Customize your Theme Options.',ADMINDOMAIN)."</b></a><br/> <b>".__('Manage Locations',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=location_settings&location_tabs=location_manage_locations&locations_subtabs=city_manage_locations")."'> <b>".__('Start adding new cities',ADMINDOMAIN)."</b></a> | <b>".__('General Settings',ADMINDOMAIN).":</b><a href='".site_url("wp-admin/admin.php?page=templatic_settings")."'> <b>".__('Setup a few common options to get started',ADMINDOMAIN)."</b></a><br/><b>".__('Manage Custom fields',ADMINDOMAIN).":</b> <a href='".site_url("/wp-admin/admin.php?page=custom_fields")."'> <b>".__('Start adding new fields for your submision form',ADMINDOMAIN)."</b></a> | <b>".__('Manage Listings',ADMINDOMAIN).":</b><a href='".site_url("/wp-admin/edit.php?post_type=listing")."'> <b>".__('Add/Edit/Delete your listings',ADMINDOMAIN)."</b></a><br/><b>".__('Help',ADMINDOMAIN).":</b> <a href='http://templatic.com/docs/directory-guides/'> <b>".__('Theme Documentation Guide',ADMINDOMAIN)."</b></a> | <b>".__('Support',ADMINDOMAIN).":</b><a href='http://templatic.com/forums/viewforum.php?f=119'> <b>".__('Community Forum',ADMINDOMAIN)."</b></a><br/></p>";
			}
			
			$dummy_data_msg='';
			$dummy_data_msg ='<p>'.__('1 click-install',ADMINDOMAIN).' <a class="button_insert button-primary" href="'.home_url().'/wp-admin/themes.php?dummy_insert=1">'.__('Install sample data',ADMINDOMAIN).'</a></p>';
			$dummy_data_msg .='<p>'.__('1 click-install allows you to quickly populate your site with sample content such as page, posts, listings etc. and automatic widget settings.',ADMINDOMAIN).'</p>'.$menu_msg1;
		}
		
		if(isset($_REQUEST['dummy_insert']) && $_REQUEST['dummy_insert']){
			require_once (get_template_directory().'/library/functions/auto_install/auto_install_data.php');
			
			$args = array(
						'post_type' => 'page',
						'meta_key' => '_wp_page_template',
						'meta_value' => 'page-templates/front-page.php'
						);
			$page_query = new WP_Query($args);
			$front_page_id = $page_query->post->ID;
			update_option('page_on_front',$front_page_id);
			wp_redirect(admin_url().'themes.php?x=y');
		}
		if(isset($_REQUEST['dummy']) && $_REQUEST['dummy']=='del'){
			tmpl_delete_dummy_data();
			wp_redirect(admin_url().'themes.php');
		}
		
		define('THEME_ACTIVE_MESSAGE','<div id="ajax-notification" class="updated templatic_autoinstall">'.$dummy_data_msg.'<span id="ajax-notification-nonce" class="hidden">' . wp_create_nonce( 'ajax-notification-nonce' ) . '</span><a href="javascript:;" id="dismiss-ajax-notification" class="templatic-dismiss" style="float:right">Dismiss</a></div>');
		echo THEME_ACTIVE_MESSAGE;
	}
}

/*
 To delete dummy data
*/
function tmpl_delete_dummy_data()
{
	global $wpdb;
	delete_option('sidebars_widgets'); //delete widgets
	$productArray = array();
	$pids_sql = "select p.ID from $wpdb->posts p join $wpdb->postmeta pm on pm.post_id=p.ID where (meta_key='pt_dummy_content' || meta_key='tl_dummy_content' || meta_key='auto_install') and (meta_value=1 || meta_value='auto_install')";
	$pids_info = $wpdb->get_results($pids_sql);
	foreach($pids_info as $pids_info_obj)
	{
		wp_delete_post($pids_info_obj->ID,true);
	}
	$widget_array = array(
		'widget_social_media',
		'widget_googlemap_homepage',
		'widget_templatic_text',
		'widget_supreme_subscriber_widget',
		'widget_hybrid-categories',
		'widget_widget_directory_featured_category_list',
		'widget_directory_featured_homepage_listing',
		'widget_directory_search_location',
		'widget_flicker_widget',
		'widget_hybrid-pages',
		'widget_templatic_browse_by_categories',
		'widget_templatic_aboust_us',
		'widget_supreme_facebook',
		'widget_directory_mile_range_widget',
		'widget_directory_neighborhood',
		'widget_templatic_popular_post_technews',
		'widget_templatic_twiter',
		'widget_text',
		'widget_templatic_google_map',
		'widget_supreme_facebook',
	);
	foreach($widget_array as $widget_array){
		delete_option($widget_array); //delete widgets
	}
}
/* Setting For dismiss auto install notification message from themes.php START */
register_activation_hook( __FILE__, 'activate'  );
register_deactivation_hook( __FILE__, 'deactivate'  );
add_action( 'admin_enqueue_scripts', 'register_admin_scripts'  );
add_action( 'wp_ajax_hide_admin_notification', 'hide_admin_notification' );
function activate() {
	add_option( 'hide_ajax_notification', false );
}
function deactivate() {
	delete_option( 'hide_ajax_notification' );
}
function register_admin_scripts() {
	wp_register_script( 'ajax-notification-admin', get_template_directory_uri().'/js/_admin-install.js'  );
	wp_enqueue_script( 'ajax-notification-admin' );
}
function hide_admin_notification() {
	if( wp_verify_nonce( $_REQUEST['nonce'], 'ajax-notification-nonce' ) ) {
		if( update_option( 'hide_ajax_notification', true ) ) {
			die( '1' );
		} else {
			die( '0' );
		}
	}
}
/* Setting For dismiss auto install notification message from themes.php END */
/*
Name : set_page_info_autorun
Description : update pages in autorun
*/
function set_page_info_autorun($pages_array,$page_info_arr)
{
	global $wpdb,$current_user;
	for($i=0;$i<count($page_info_arr);$i++)
	{ 
		$post_title = $page_info_arr[$i]['post_title'];
		$post_count = $wpdb->get_var("SELECT count(ID) FROM $wpdb->posts where post_title like \"$post_title\" and post_type='page' and post_status in ('publish','draft')");
		if(!$post_count)
		{
			$post_info_arr = array();
			$catids_arr = array();
			$my_post = array();
			$post_info_arr = $page_info_arr[$i];
			$my_post['post_title'] = $post_info_arr['post_title'];
			$my_post['post_content'] = $post_info_arr['post_content'];
			$my_post['post_type'] = 'page';
			if(isset($post_info_arr['post_author']) && $post_info_arr['post_author'])
			{
				$my_post['post_author'] = $post_info_arr['post_author'];
			}else
			{
				$my_post['post_author'] = 1;
			}
			$my_post['post_status'] = 'publish';
	
			$last_postid = wp_insert_post( $my_post );
			$post_meta = $post_info_arr['post_meta'];
			if($post_meta)
			{
				foreach($post_meta as $mkey=>$mval)
				{
					update_post_meta($last_postid, $mkey, $mval);
				}
			}
			
			$post_image = (isset($post_info_arr['post_image']))?$post_info_arr['post_image']:'';
			if($post_image)
			{
				for($m=0;$m<count($post_image);$m++)
				{
					$menu_order = $m+1;
					$image_name_arr = explode('/',$post_image[$m]);
					$img_name = $image_name_arr[count($image_name_arr)-1];
					$img_name_arr = explode('.',$img_name);
					$post_img = array();
					$post_img['post_title'] = $img_name_arr[0];
					$post_img['post_status'] = 'attachment';
					$post_img['post_parent'] = $last_postid;
					$post_img['post_type'] = 'attachment';
					$post_img['post_mime_type'] = 'image/jpeg';
					$post_img['menu_order'] = $menu_order;
					$last_postimage_id = wp_insert_post( $post_img );
					update_post_meta($last_postimage_id, '_wp_attached_file', $post_image[$m]);					
					$post_attach_arr = array(
										"width"	=>	580,
										"height" =>	480,
										"hwstring_small"=> "height='150' width='150'",
										"file"	=> $post_image[$m],
										//"sizes"=> $sizes_info_array,
										);
					wp_update_attachment_metadata( $last_postimage_id, $post_attach_arr );
				}
			}
		}
	}
}
?>