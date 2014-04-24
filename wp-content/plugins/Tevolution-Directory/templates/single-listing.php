<?php
/**
 * Tevolution single custom post type template
 *
**/
get_header(); //Header Portion
$tmpdata = get_option('templatic_settings');
do_action('directory_before_container_breadcrumb'); /*do action for display the breadcrumb in between header and container. */

?>
<!-- start content part-->
<div id="content" role="main">	
	<?php do_action('directory_inside_container_breadcrumb'); /*do action for display the breadcrumb  inside the container. */ ?>
  <?php  
  	if(function_exists('supreme_sidebar_before_content'))
	  	apply_filters('tmpl_before-content',supreme_sidebar_before_content() ); // Loads the sidebar-before-content.?>
	<?php while ( have_posts() ) : the_post(); ?>
	     <?php do_action('directory_before_post_loop');?>
     	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>  
          	<!--start post type title -->
     		<?php do_action('directory_before_post_title');         /* do action for before the post title.*/ ?>
               
             	<header class="entry-header">
               	<?php $listing_logo=get_post_meta(get_the_ID(),'listing_logo',true); ?>
               	 <?php if($listing_logo!="" && $htmlvar_name['basic_inf']['listing_logo']):?>
                    <div class="entry-header-logo">
                    	<img src="<?php echo $listing_logo?>" alt="<?php echo $htmlvar_name['basic_inf']['listing_logo']['label']; ?>" />
                    </div>
                    <?php endif;?>
                    <div class="entry-header-title">
                         <h1 itemprop="name" class="entry-title"><?php the_title(); ?></h1>
                         <?php
					if($tmpdata['templatin_rating']=='yes'):
						$total=get_post_total_rating(get_the_ID());
						$total=($total=='')? 0: $total;
						$review_text=($total==1 || $total==0)? '<a href="#comments">'.__('Review',DIR_DOMAIN).'</a>': '<a href="#comments">'.__('Reviews',DIR_DOMAIN).'</a>';
					?>
                         	<div class="listing_rating">
							<div class="directory_rating_row"><span class="single_rating"> <?php echo draw_rating_star_plugin(get_post_average_rating(get_the_ID()));?> <span><?php echo $total.' '.$review_text?></span></span></div>
                              </div>
					<?php endif;
					do_action('directory_display_rating',get_the_ID());
					
					?>					
							<div class="entry-header-custom-wrap">
								<div class="entry-header-custom-left">
                                   <?php 
									global $htmlvar_name;
									$address=get_post_meta(get_the_ID(),'address',true);
									$website=get_post_meta(get_the_ID(),'website',true);
									$phone=get_post_meta(get_the_ID(),'phone',true);									
									$listing_timing=get_post_meta(get_the_ID(),'listing_timing',true);
									$email=get_post_meta(get_the_ID(),'email',true);
									if($address!="" && $htmlvar_name['basic_inf']['address']):?>
									   <p class="<?php echo $htmlvar_name['basic_inf']['address']['style_class'];?>"><span class="listing_custom"><?php echo get_post_meta(get_the_ID(),'address',true);?></span></p>
									   <?php endif;?>
									   <?php if($website!="" && $htmlvar_name['contact_info']['website']):
												if(!strstr($website,'http'))
													$website = 'http://'.$website;?>
									   <p class="<?php echo $htmlvar_name['contact_info']['website']['style_class']; ?>"><a href="<?php echo $website;?>"><?php echo $htmlvar_name['contact_info']['website']['label']; ?></a></p>
									   <?php endif;?>
								</div>
                            <div class="entry-header-custom-right">
							
                                   <?php 
							
								   if($phone!="" && $htmlvar_name['contact_info']['phone']):?>
                                   <p class="phone <?php echo $htmlvar_name['contact_info']['phone']['style_class']; ?>"><label><?php echo $htmlvar_name['contact_info']['phone']['label']; ?>: </label><span class="listing_custom"><?php echo $phone;?></span></p>
                                   <?php endif;?>
                                   <?php if($listing_timing!="" && $htmlvar_name['basic_inf']['listing_timing']):?>
                                   <p class="time <?php echo $htmlvar_name['basic_inf']['listing_timing']['style_class']; ?>"><label><?php echo $htmlvar_name['basic_inf']['listing_timing']['label']; ?>: </label><span class="listing_custom"><?php echo $listing_timing;?></span></p>
                                   <?php endif;?>
                                  	<?php if( @$email!="" && @$htmlvar_name['contact_info']['email']):?>
                                   <p class="email <?php echo $htmlvar_name['contact_info']['email']['style_class']; ?>"><label><?php echo $htmlvar_name['contact_info']['email']['label']; ?>: </label><span class="listing_custom"><?php echo antispambot($email);?></span></p>
                                   <?php endif;?>
                                  
								</div>
			
							</div>
							
						</div>
                   
               </header>
               
               <?php do_action('directory_after_post_title');          /* do action for after the post title.*/?>
     		<!--end post type title -->               
			
            
            <!--Code start for single captcha -->   
            <?php $display = (isset($tmpdata['user_verification_page']))?$tmpdata['user_verification_page']:array();
			  $captcha_set = array();
			  $captcha_dis = '';
			  if(count($display) > 0 && !empty($display) )
			   {
				  foreach($display as $_display)
				   {
					  if($_display == 'claim' || $_display == 'emaitofrd')
					   { 
						 $captcha_set[] = $_display;
						 $captcha_dis = $_display;
					   }
				   }
			   }
			   $recaptcha = get_option("recaptcha_options");
			   global $current_user;
			 ?>
               
            <div id="myrecap" style="display:none;"><?php if($recaptcha['show_in_comments']!= 1 || $current_user->ID != ''){ templ_captcha_integrate($captcha_dis); }?></div> 
            <input type="hidden" id="owner_frm" name="owner_frm" value=""  />
            <div id="claim_ship"></div>
            <script type="text/javascript">
			var RECAPTCHA_COMMENT = '';
				<?php
				
				if($recaptcha['show_in_comments']!= 1 || $current_user->ID != ''){ ?>
					jQuery('#owner_frm').val(jQuery('#myrecap').html());
			<?php 	} else{ ?> RECAPTCHA_COMMENT = <?php echo $recaptcha['show_in_comments']; ?>; <?php } ?>
            </script>
               
           	<!--Code end for single captcha -->
               
               <!-- listing content-->
               <div class="entry-content">
               <?php do_action('directory_before_post_content');?>
               
               <?php get_template_part( 'directory-listing','single-content' ); ?>
               
               <?php do_action('directory_after_post_content');?>
               </div>
               <!--Finish the listing Content -->
     			
     		<!--Custom field collection do action -->
     		<?php do_action('directory_custom_fields_collection');  ?>
               
               <?php do_action('directory_extra_single_content');?>               
                   
     		</div>
               <?php do_action('directory_after_post_loop');?>
               
               <?php do_action('directory_edit_link');?>
	<?php endwhile; // end of the loop. ?>
    
	<?php wp_reset_query(); // reset the wp query?>
     
     <?php do_action('tmpl_single_post_pagination'); /* add action for display the next previous pagination */ ?>
    
     <?php do_action('tmpl_before_comments'); /* add action for display before the post comments. */ ?>
     
	<?php do_action( 'after_entry' ); ?>	
	  
     <?php do_action( 'for_comments' );?>
     
     <?php do_action('tmpl_after_comments'); /*Add action for display after the post comments. */?>
     
	<?php 
	 global $post;
	$tmpdata = get_option('templatic_settings');
	
	do_action('tmpl_related_post'); /*add action for display the related post list. */
	?>
    <?php 
    	if(function_exists('supreme_sidebar_after_content'))	
		apply_filters('tmpl_after-content',supreme_sidebar_after_content()); // after-content-sidebar use remove filter to don't display it ?>
</div><!-- #content -->

<!--single post type sidebar -->
<?php if ( is_active_sidebar( get_post_type().'_detail_sidebar' ) ) : ?>
	<div id="sidebar-primary" class="sidebar">
		<?php 
		do_action('above_'.get_post_type().'_detail_sidebar');
		dynamic_sidebar( get_post_type().'_detail_sidebar' );
		do_action('below_'.get_post_type().'_detail_sidebar');
		?>		
	</div>
	<?php
elseif ( is_active_sidebar( 'primary-sidebar') ) :
	do_action('above_'.get_post_type().'_detail_sidebar');
	?>
	<div id="sidebar-primary" class="sidebar">
		<?php dynamic_sidebar('primary-sidebar'); ?>
	</div>
<?php
	do_action('below_'.get_post_type().'_detail_sidebar');
 endif; ?>
<!--end single post type sidebar -->
<!-- end  content part-->
<?php get_footer(); ?>