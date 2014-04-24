<?php
/*
Name:tevolution_post_upgrade_template
desc: return the upgrade package
*/

function tevolution_post_upgrade_template(){
	ob_start();
	remove_filter( 'the_content', 'wpautop' , 12);
	/* if You have successfully activated monetization then this function will be included for listing prices */
	if(is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
			global $sitepress;
			if(isset($_REQUEST['lang'])){
				$url = site_url().'/?page=payment&lang='.$_REQUEST['lang'];
			}elseif($sitepress->get_current_language()){
				
				if($sitepress->get_default_language() != $sitepress->get_current_language()){
					$url = site_url().'/'.$sitepress->get_current_language().'/?page=payment';
				}else{
					$url = site_url().'/?page=payment';
				}
			}else{
				$url = site_url().'/?page=payment';
			}
	}else{
			$url = site_url().'/?page=payment';
	}
	echo '<form name="submit_form" id="submit_form" class="form_front_style" action="'.$url.'" method="post" enctype="multipart/form-data">';
	if(is_active_addons('monetization'))
	{
			global $post,$monetization;
			$upgrade_id = @$_REQUEST['pid'];
			$edit_id = @$_REQUEST['pid'];
			$all_cat_id='';
			$upgrade_url = get_permalink($post->ID)."?pid=".@$_REQUEST['pid']."&_wpnonce=".@$_REQUEST['_wpnonce'];
			$post_details = get_post($upgrade_id);
			$post_type = $post_details->post_type;
			$taxonomies = get_object_taxonomies( (object) array( 'post_type' => @$post_type,'public'   => true, '_builtin' => true ));
			$taxonomy = @$taxonomies[0];
		
		if(get_post_meta($post->ID,'submit_post_type',true)=="" || $post_type!=get_post_meta($post->ID,'submit_post_type',true)){
			update_post_meta($post->ID,'submit_post_type',$post_type);	
		}
		if(get_post_meta($post->ID,'is_tevolution_upgrade_form',true)=="" || '1'!=get_post_meta($post->ID,'is_tevolution_upgrade_form',true)){
			update_post_meta($post->ID,'is_tevolution_upgrade_form',1);	
		}
		if(isset($_SESSION['category']) && class_exists('monetization'))
			{	
				global $cat_array;
				$cat_array = $monetization->templ_get_selected_category_id($_SESSION['category']);
				$cat_array_price = $monetization->templ_fetch_category_price($_SESSION['category']);				
			}
						
						$default_custom_metaboxes = array('post_categories'=>array('type'=>'post_categories','name'=>'category','label'=>'','htmlvar_name'=>'category','htmlvar_name'=>'category','is_require'=>1));
						
						display_custom_category_field_plugin($default_custom_metaboxes,'custom_fields','post',$post_type);//displaty  post category html.
						
						if(class_exists('monetization')){			
							global $current_user;
							$user_have_pkg = $monetization->templ_get_packagetype($current_user->ID,$post_type); /* User selected package type*/
							$user_last_postid = $monetization->templ_get_packagetype_last_postid($current_user->ID,$post_type); /* User last post id*/
							$user_have_days = $monetization->templ_days_for_packagetype($current_user->ID,$post_type); /* return alive days(numbers) of last selected package  */
							$is_user_have_alivedays = $monetization->is_user_have_alivedays($current_user->ID,$post_type); /* return user have an alive days or not true/false */
							if($current_user->ID)// check user wise post per  Subscription limit number post post 
							{
								$package_id=get_user_meta($current_user->ID,'package_selected',true);// get the user selected price package id
								if(!$package_id)
									$package_id=get_user_meta($current_user->ID,$post_type.'_package_select',true);// get the user selected price package id
								$user_limit_post=get_user_meta($current_user->ID,'total_list_of_post',true); //get the user wise limit post count on price package select
								if(!$user_limit_post)	
									$user_limit_post=get_user_meta($current_user->ID,$post_type.'_list_of_post',true); //get the user wise limit post count on price package select
								$package_limit_post=get_post_meta($package_id,'limit_no_post',true);// get the price package limit number of post
								$user_have_pkg = get_post_meta($package_id,'package_type',true); 
								$post_types = explode(',',get_post_meta($package_id,'package_post_type',true)); 
								if(in_array($post_type,$post_types)): $is_posttype_inpkg=1; else: $is_posttype_inpkg=0; endif; // check is this taxonomy included in package or not
							}
							

								if(isset($upgrade_id) && $upgrade_id !=''){
								$pkg_id = get_post_meta($edit_id,'package_select',true); /* user comes to edit fetch selected package */
								}else{ $pkg_id =''; }
								
								$monetization->fetch_monetization_packages_front_end($pkg_id,'all_packages',$post_type,$taxonomy,''); /* call this function to fetch price packages which have to show even no categories selected */
								if(!isset($all_cat_id)){ $all_cat_id ==0;}elseif(isset($_REQUEST['backandedit'])){ if(!empty($cat_array)){ $all_cat_id = implode(',',$cat_array); } }else if(isset($edit_id) && $edit_id !=''){ $all_cat_id = @$all_cat_id; }
								echo '<span class="message_error2" id="all_packages_error"></span>';
								$monetization->fetch_monetization_packages_front_end($pkg_id,'packages_checkbox',$post_type,@$taxonomy,@$all_cat_id); /* call this function to fetch price packages */
								if(!isset($_REQUEST['action']) && isset($_REQUEST['action']) !='edit'){
										$monetization->fetch_package_feature_details($edit_id,$pkg_id,@$all_cat_id); /* call this function to display fetured packages */
										if($user_have_pkg == 2 && $user_have_days > 0){
											echo "<div class='form_row clearfix act_success'>".sprintf(SUBMIT_LISTING_DAYS_TEXT,$user_have_days)."</div>";
										}	
								}
								$coupons = get_posts(array('post_type'=>'coupon_code','post_status'=>'publish')); // show only if coupon available
								if($coupons)
								{
									if(!isset($_REQUEST['action']) && isset($_REQUEST['action']) !='edit'){
										$coupon_code = '';
										if(@$_REQUEST['backandedit']) { $coupon_code = $_SESSION['upgrade_post']['add_coupon']; }else if(isset($edit_id) && $edit_id !=''){ 
										if(!isset($_REQUEST['upgpkg']) && $_REQUEST['upgpkg'] != 1){
											$coupon_code = get_post_meta($edit_id,'add_coupon',true);
										}
										}else{ $coupon_code = ''; } /* coupon code when click ok GBE*/
											templ_get_coupon_fields($coupon_code); /* fetch coupon code */
									}
								}
				
							}
		//for getting the alive days	
		if($current_user->ID){if(function_exists('templ_days_for_user_packagetype'))$alive_days= $monetization->templ_days_for_user_packagetype($current_user->ID, $post_type);} ?>
		
		 <span class="message_error2" id="common_error"></span>
         <input type="hidden" name="cur_post_type" id="cur_post_type" value="<?php echo $post_type; ?>"  />
         <input type="hidden" name="cur_post_taxonomy" id="cur_post_taxonomy" value="<?php echo $taxonomy; ?>"  />
         <input type="hidden" name="upgrade_url" id="upgrade_url" value="<?php echo $upgrade_url; ?>"  />
         <input type="hidden" name="cur_post_id" value="<?php echo $post->ID; ?>"  />
         <?php if(isset($upgrade_id) && $upgrade_id !=''): ?>
                <input type="hidden" name="pid" id="pid" value="<?php echo $upgrade_id; ?>"  />
         <?php endif; ?>
		 <input type="submit" name="upgrade" value="<?php  _e('Next Step',DOMAIN);?>" class="normal_button main_btn" <?php echo @$submit_button; ?>/>    
		 <input type="hidden" value="<?php echo @$alive_days;?>" id="alive_days" name="alive_days" >
         <?php
	 /* monetization end */
	global $post,$wpdb,$validation_info;
	$tmpdata = get_option('templatic_settings');
	$form_fields = array();
	if(!isset($_REQUEST['category']) && count(@$_REQUEST['category']) <= 0 && !isset($_REQUEST['fields']) && @$_REQUEST['fields'] =='' && @$_REQUEST['action'] != 'edit')
	{
		$form_fields['category'] = array(
							   'name' 	      => $taxonomy,
							   'espan'	      => 'category_span',
							   'type'	           => $tmpdata['templatic-category_type'],
							   'text'	           => __('Please select Category',DOMAIN),
							   'validation_type' => 'require'
							   );
	}
	$validation_info = array();
	foreach($form_fields as $key=>$val)
	{
		$str = ''; $fval = '';
		$field_val = $key.'_val';
		$val['title']=(isset($val['title']))? $val['title'] :'';		
		$validation_info[] = array(
							'title'	       => $val['title'],
							'name'	       => $key,
							'espan'	       => $key.'_error',
							'type'	       => $val['type'],
							'text'	       => $val['text'],
							'is_require'	  => @$val['is_require'],
							'validation_type'=> $val['validation_type']
					);
	}
		
		include_once(TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/submition_validation.php');
	}
	echo "</form>";
	return ob_get_clean();
}
//include_once(TEMPL_MONETIZE_FOLDER_PATH.'templatic-custom_fields/submition_validation.php');
?>