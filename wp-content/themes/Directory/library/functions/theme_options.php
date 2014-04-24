<?php
/* Templatic theme options page template */

if(isset($_POST['theme_options_nonce']) && $_POST['theme_options_nonce'] !=''){
	if ( wp_verify_nonce( @$_POST['theme_options_nonce'], basename(__FILE__) ) ){
		if(function_exists('supreme_prefix')){
			$pref = supreme_prefix();
		}else{
			$pref = sanitize_key( apply_filters( 'hybrid_prefix', get_template() ) );
		}
		$theme_options = get_option($pref.'_theme_settings');
		foreach($_POST as $key => $value){
			if( $key!="theme_options_nonce" && $key !="Submit" && $key != 'hide_ajax_notification' ){
				$theme_options[$key] = $value;
			}
		}
		$theme_options['supreme_global_layout'] = ($_POST['supreme_global_layout']) ? $_POST['supreme_global_layout'] : '';
		$theme_options['customcss'] = ($_POST['customcss']) ? $_POST['customcss'] : '';
		$theme_options['enable_sticky_header_menu'] = ($_POST['enable_sticky_header_menu']) ? $_POST['enable_sticky_header_menu'] : '';
		$theme_options['supreme_author_bio_posts'] = ($_POST['supreme_author_bio_posts']) ? $_POST['supreme_author_bio_posts'] : '';
		$theme_options['supreme_author_bio_pages'] = ($_POST['supreme_author_bio_pages']) ? $_POST['supreme_author_bio_pages'] : '';
		$theme_options['supreme_show_breadcrumb'] = ($_POST['supreme_show_breadcrumb']) ? $_POST['supreme_show_breadcrumb'] : '';
		$theme_options['supreme_global_contactus_captcha'] = ($_POST['supreme_global_contactus_captcha']) ? $_POST['supreme_global_contactus_captcha'] : '';
		$theme_options['enable_inquiry_form'] = ($_POST['enable_inquiry_form']) ? $_POST['enable_inquiry_form'] : '';
		$theme_options['post_type_label'] = ($_POST['post_type_label']) ? $_POST['post_type_label'] : '';
		$theme_options['supreme_gogle_analytics_code'] = ($_POST['supreme_gogle_analytics_code']) ? $_POST['supreme_gogle_analytics_code'] : '';
		$theme_options['supreme_display_image'] = ($_POST['supreme_display_image']) ? $_POST['supreme_display_image'] : '';
		$theme_options['supreme_display_noimage'] = ($_POST['supreme_display_noimage']) ? $_POST['supreme_display_noimage'] : '';
		$theme_options['display_author_name'] = ($_POST['display_author_name']) ? $_POST['display_author_name'] : '';
		$theme_options['display_publish_date'] = ($_POST['display_publish_date']) ? $_POST['display_publish_date'] : '';
		$theme_options['display_post_terms'] = ($_POST['display_post_terms']) ? $_POST['display_post_terms'] : '';
		$theme_options['display_post_response'] = ($_POST['display_post_response']) ? $_POST['display_post_response'] : '';
		$theme_options['supreme_archive_display_excerpt'] = ($_POST['supreme_archive_display_excerpt']) ? $_POST['supreme_archive_display_excerpt'] : '';
		$theme_options['templatic_excerpt_length'] = ($_POST['templatic_excerpt_length']) ? $_POST['templatic_excerpt_length'] : '';
		$theme_options['templatic_excerpt_link'] = ($_POST['templatic_excerpt_link']) ? $_POST['templatic_excerpt_link'] : '';
		$theme_options['enable_comments_on_page'] = ($_POST['enable_comments_on_page']) ? $_POST['enable_comments_on_page'] : '';
		$theme_options['enable_comments_on_post'] = ($_POST['enable_comments_on_post']) ? $_POST['enable_comments_on_post'] : '';
		
		update_option('hide_ajax_notification',$_POST['hide_ajax_notification']);
		update_option($pref.'_theme_settings',$theme_options);
		wp_safe_redirect(admin_url('themes.php?page=theme-settings-page&updated=1'));
	}else{
		wp_die("You do not have permission to edit theme settings.");
	}
}
/*
Function Name: theme_settings_page_callback
Purpose		 : To display theme setting options 
*/
if(!function_exists('theme_settings_page_callback')){
	function theme_settings_page_callback() {
		if(function_exists('supreme_prefix')){
			$pref = supreme_prefix();
		}else{
			$pref = sanitize_key( apply_filters( 'hybrid_prefix', get_template() ) );
		}
		$theme_settings = get_option($pref.'_theme_settings');
?>
<div class="wrap">
  <form name="theme_options_settings" id="theme_options_settings" method="post" enctype="multipart/form-data">
    <input type="hidden" name="theme_options_nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>" />
    <div class="icon32 icon32-posts-post" id="icon-edit"><br>
    </div>
    <h2>
      <?php echo __("Theme Settings",ADMINDOMAIN);?>
    </h2>    
    <ul class="subsubsub">
      <li class="general_settings"> <a href="#general_settings">
        <?php echo __("General Settings",ADMINDOMAIN);?>
        </a> | </li>
      <li class="listing_settings"> <a href="#listing_settings">
        <?php echo __("Category/Tag Archive Page Settings",ADMINDOMAIN);?>
        </a> | </li>
      <li class="detail_settings"> <a href="#detail_settings">
        <?php echo __("Comments Settings",ADMINDOMAIN);?>
        </a> </li>
    </ul>
    <?php if(isset($_REQUEST['updated']) && $_REQUEST['updated']=''){?>
    <div class="updated" id="message" style="clear:both">
      <p>
        <?php echo __("Theme Settings",ADMINDOMAIN);?>
        <strong>
        <?php echo __("saved",ADMINDOMAIN);?>
        </strong>.</p>
    </div>
    <?php }?>
    <table class="form-table">
      <tbody>
        <!-- General Settings -->
        <tr id="general_settings">
          <td colspan="2">
          	<div class="theme_sub_title" style="margin-top:0;"><?php echo __("General Settings",ADMINDOMAIN);?></div>
            </td>
        </tr>
        <tr>
          <th><label for="supreme_global_layout">
              <?php echo __('Global layout',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <select style="vertical-align:top;width:200px;" name="supreme_global_layout" id="supreme_global_layout">
                <option value="layout_default" <?php echo ($theme_settings['supreme_global_layout']=='layout_default') ? 'selected' : ''?>>
                <?php echo __("Default Layout",ADMINDOMAIN);?>
                </option>
                <option value="layout_1c" <?php echo ($theme_settings['supreme_global_layout']=='layout_1c') ? 'selected' : ''?>>
                <?php echo __("One Column",ADMINDOMAIN);?>
                </option>
                <option value="layout_2c_l" <?php echo ($theme_settings['supreme_global_layout']=='layout_2c_l') ? 'selected' : ''?>>
                <?php echo __("Two Columns, Left",ADMINDOMAIN);?>
                </option>
                <option value="layout_2c_r" <?php echo ($theme_settings['supreme_global_layout']=='layout_2c_r') ? 'selected' : ''?>>
                <?php echo __("Two Columns, Right",ADMINDOMAIN);?>
                </option>
              </select>
            </div>
            <p class="description">
              <?php echo __("This setting can be overwritten by layout settings within individual posts/pages.",ADMINDOMAIN);?>
            </p></td>
        </tr>
        <tr>
          <th><label for="hide_ajax_notification">
              <?php echo __('Show the "Insert Sample Data" button',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox" value="1" <?php echo (get_option('hide_ajax_notification')==1) ? 'checked' : ''?> id="hide_ajax_notification" name="hide_ajax_notification">
              <label for="hide_ajax_notification"> <?php echo __('Disable',ADMINDOMAIN);?></label>
            </div>
            <p class="description">
              <?php echo __("Disabling this will hide the entire yellow box that appears above the active theme inside Appearance &rsaquo;&rsaquo; Themes section.",ADMINDOMAIN);?>
            </p>
            </td>
        </tr>
        <tr>
          <th><label for="customcss">
              <?php echo __("Use custom.css",ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox"  value="1" <?php echo (@$theme_settings['customcss']==1) ? 'checked' : ''?> id="customcss" name="customcss">
              <label for="customcss">
                <?php echo __('Enable',ADMINDOMAIN);?>
              </label>
            </div>
            <p class="description">
              <?php echo sprintf(__('Custom.css is used for quick design tweaks. You can modify it from the %s. For more details on custom.css read %s.',ADMINDOMAIN),'<a href="'.site_url().'/wp-admin/themes.php?page=templatic_custom_css_editor">Templatic custom CSS editor</a>','<a href="http://templatic.com/docs/using-custom-css-for-theme-customizations/">this article</a>');?>
            </p></td>
        </tr>
        <tr>
          <th><label for="enable_sticky_header_menu">
              <?php echo __('Show sticky header',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox"  value="1" <?php echo (@$theme_settings['enable_sticky_header_menu']==1) ? 'checked' : ''?> id="enable_sticky_header_menu" name="enable_sticky_header_menu">
              <label for="enable_sticky_header_menu">
                <?php echo __('Enable',ADMINDOMAIN);?>
              </label>
            </div>
            <p class="description">
              <?php echo __('Sticky header is a persistent navigation bar that continues to show even when you scroll down the page.',ADMINDOMAIN);?>
            </p></td>
        </tr>
        <tr>
          <th><label for="supreme_author_bio_posts">
               <?php echo __('Show author bio on WordPress posts',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox" value="1" <?php echo (@$theme_settings['supreme_author_bio_posts']==1) ? 'checked' : ''?>  id="supreme_author_bio_posts" name="supreme_author_bio_posts">
              <label for="supreme_author_bio_posts">
                <?php echo __('Enable',ADMINDOMAIN);?>
              </label>
            </div>
            <p class="description">
              <?php echo __('If enabled, a small box with the authors name, avatar and description will be shown below regular WordPress pages.',ADMINDOMAIN);?>
            </p></td>
        </tr>
        <tr>
          <th><label for="supreme_author_bio_pages">
               <?php echo __('Show author bio on WordPress pages',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox" value="1" <?php echo (@$theme_settings['supreme_author_bio_pages']==1) ? 'checked' : ''?>  id="supreme_author_bio_pages" name="supreme_author_bio_pages">
              <label for="supreme_author_bio_pages">
                <?php echo __('Enable',ADMINDOMAIN);?>
              </label>
            </div>
            <p class="description">
              <?php echo __('If enabled, a small box with the authors name, avatar and description will be shown below regular WordPress posts.',ADMINDOMAIN);?>
            </p></td>
        </tr>
        <tr>
          <th><label for="supreme_show_breadcrumb">
              <?php echo __('Show breadcrumbs',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox" value="1"  <?php echo (@$theme_settings['supreme_show_breadcrumb']==1) ? 'checked' : ''?> id="supreme_show_breadcrumb" name="supreme_show_breadcrumb">
              <label for="supreme_show_breadcrumb">
                <?php echo __('Enable',ADMINDOMAIN);?>
              </label>
            </div></td>
        </tr>
        <tr>
          <th><label for="enable_inquiry_form">
              <?php echo __('Contact page options',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox" value="1" <?php echo (@$theme_settings['enable_inquiry_form']==1) ? 'checked' : ''?> id="enable_inquiry_form" name="enable_inquiry_form">
              <label for="enable_inquiry_form">
                <?php echo __('Enable the inquiry form on the contact page',ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['supreme_global_contactus_captcha']==1) ? 'checked' : ''?>  id="supreme_global_contactus_captcha" name="supreme_global_contactus_captcha">
              <label for="supreme_global_contactus_captcha">
                <?php echo __('Enable captcha on the contact page',ADMINDOMAIN);?>
              </label>
            </div>
            <p class="description">
              <?php echo __('Use the "Contact Us" page template to create a contact page. For captcha to work you must install the  <a href="http://wordpress.org/plugins/wp-recaptcha/">WP-reCAPTCHA plugin</a>.',ADMINDOMAIN);?>
            </p></td>
        </tr>
        <tr>
          <th><label for="post_type_label">
              <?php echo __('Categories for the 404 page',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <?php 
										$post_types=get_post_types();
										$PostTypeName = '';
										foreach($post_types as $post_type):		
											if($post_type!='page' && $post_type!="attachment" && $post_type!="revision" && $post_type!="nav_menu_item" && $post_type!="admanager"):
												$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));	
												$archive_query = new WP_Query('showposts=60&post_type='.$post_type);
												if( count(@$archive_query->posts) > 0 ){
													$PostTypeName .= $post_type.', ';
												}
											endif;
										endforeach;
										$all_post_types = rtrim($PostTypeName,', ');
									?>
              <input type="text" value="<?php echo $theme_settings['post_type_label'];?>" id="post_type_label" name="post_type_label">
            </div>
            <p class="description">
              <?php echo __('Enter comma separated post type slugs that you want displayed.',ADMINDOMAIN);?>
              <br/>
              <?php echo __(' Available slugs: ',ADMINDOMAIN); echo $all_post_types;?>
            </p></td>
        </tr>
        <tr>
          <th><label for="supreme_gogle_analytics_code">
              <?php echo __('Google Analytics tracking code',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <textarea name="supreme_gogle_analytics_code" id="supreme_gogle_analytics_code" rows="6" cols="60"><?php echo stripslashes($theme_settings['supreme_gogle_analytics_code']);?></textarea>
            </div>
            <p class="description">
              <?php echo __("Enter the analytics code you received from GA or some other analytics software. e.g. <a href='https://www.google.co.in/analytics/'>Google Analytics</a>",ADMINDOMAIN);?>
            </p></td>
        </tr>
        <!-- Listing Page Settings -->
        <tr id="listing_settings">
          <td colspan="2"><div class="theme_sub_title">
              <?php echo __('Category page settings',ADMINDOMAIN);?>
            </div>
            </td>
        </tr>
        <tr>
          <th><label for="supreme_display_image">
              <?php echo __('Category page display options',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox" value="1" <?php echo ($theme_settings['supreme_display_image']==1) ? 'checked' : ''?>  id="supreme_display_image" name="supreme_display_image">
              <label for="supreme_display_image">
                <?php echo __("Show thumbnail on archive pages",ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['supreme_display_noimage']==1) ? 'checked' : ''?> id="supreme_display_noimage" name="supreme_display_noimage">
              <label for="supreme_display_noimage">
                <?php echo __("Show <em>no-image-available</em> thumbnail when there is no image uploaded in a particular post",ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['display_author_name']==1) ? 'checked' : ''?> id="display_author_name" name="display_author_name">
              <label for="display_author_name">
                <?php echo __("Show author name with a link to his profile for all posts",ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['display_publish_date']==1) ? 'checked' : ''?> id="display_publish_date" name="display_publish_date">
              <label for="display_publish_date">
                <?php echo __("Show published date of all posts",ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['display_post_terms']==1) ? 'checked' : ''?> id="display_post_terms" name="display_post_terms">
              <label for="display_post_terms">
                <?php echo __("Show selected categories and tags of individual posts",ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['display_post_response']==1) ? 'checked' : ''?> id="display_post_response" name="display_post_response">
              <label for="display_post_response">
                <?php echo __("Show number of comments for all posts with a link to comments section on post detail page",ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['supreme_archive_display_excerpt']==1) ? 'checked' : ''?> id="supreme_archive_display_excerpt" name="supreme_archive_display_excerpt">
              <label for="supreme_archive_display_excerpt">
                <?php echo __("Show post <a href='http://codex.wordpress.org/Excerpt'>excerpt</a> instead of full text",ADMINDOMAIN);?>
              </label>
              <br/>
            </div></td>
        </tr>
        <tr>
          <th><label for="templatic_excerpt_length">
              <?php echo __('Excerpt length',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="text" value="<?php echo $theme_settings['templatic_excerpt_length'];?>" id="templatic_excerpt_length" name="templatic_excerpt_length">
              <br/>
            </div>
            <p class="description">
              <?php echo __('Enter the number of characters that should be displayed from your post description. This option can be overwritten by entering the actual excerpt for the post.',ADMINDOMAIN);?>
            </p></td>
        </tr>
        <tr>
          <th><label for="templatic_excerpt_link">
              <?php echo __('Read more link name',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="text" value="<?php echo stripslashes($theme_settings['templatic_excerpt_link']);?>" id="templatic_excerpt_link" name="templatic_excerpt_link">
            </div>
            <p class="description">
              <?php echo __('Default link name is "Read More".',ADMINDOMAIN);?>
            </p></td>
        </tr>
        <!-- Detail Page Settings -->
        <tr id="detail_settings">
          <td colspan="2"><div class="theme_sub_title">
              <?php echo __('Comments settings',ADMINDOMAIN);?>
            </div>
            </td>
        </tr>
        <tr>
          <th><label for="enable_comments_on_page">
              <?php echo __('Comment display options',ADMINDOMAIN);?>
            </label></th>
          <td><div class="element">
              <input type="checkbox" value="1" <?php echo ($theme_settings['enable_comments_on_page']==1) ? 'checked' : ''?>  id="enable_comments_on_page" name="enable_comments_on_page">
              <label for="enable_comments_on_page">
                <?php echo __("Show comments on WordPress pages",ADMINDOMAIN);?>
              </label>
              <br/>
              <input type="checkbox" value="1" <?php echo ($theme_settings['enable_comments_on_post']==1) ? 'checked' : ''?>  id="enable_comments_on_post" name="enable_comments_on_post">
              <label for="enable_comments_on_post">
                <?php echo __('Show comments on posts (includes custom post types that you created)',ADMINDOMAIN);?>
              </label>
            </div></td>
        </tr>
        <tr>
          <td colspan="2"><p style="clear: both;" class="submit">
              <input type="submit" value="<?php echo __('Save All Settings',ADMINDOMAIN); ?>" class="button-primary" name="Submit">
            </p></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
<?php
	}
}
?>