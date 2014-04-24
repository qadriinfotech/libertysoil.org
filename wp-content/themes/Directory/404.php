<?php
/**
 * 404 Template
 *
 * The 404 template is used when a reader visits an invalid URL on your site. By default, the template will 
 * display a generic message.
 *
 * @package supreme
 * @subpackage Template
 * @link http://codex.wordpress.org/Creating_an_Error_404_Page
 */
@header( 'HTTP/1.1 404 Not found', true, 404 );
add_filter('body_class','directory_404_page_class');
function directory_404_page_class($class){
	$class[]='layout-1c';
	return $class;
}
get_header(); // Loads the header.php template. 
global $post;
$single_post = $post;
if ( current_theme_supports( 'breadcrumb-trail' ) && supreme_default_theme_settings('supreme_show_breadcrumb')) breadcrumb_trail( array( 'separator' => '&raquo;' ) ); ?>
<section id="content" class="error_404">
	<?php do_action( 'open_content' ); // supreme_open_content ?>  
	<div class="hfeed">
		<div id="post-0" >
			<div class="wrap404 clearfix">
				<p class="display404"><img src="<?php echo get_template_directory_uri()?>/library/images/404.jpg" /></p>
				<h4><?php _e("Sorry, The page you're looking for cannot be found!",THEME_DOMAIN); ?></h4>
		          <p><?php _e("I can help you find the page you want to see, just help me with a few clicks please.",THEME_DOMAIN); ?></p>
				<p><?php  _e('I recommend you ',THEME_DOMAIN);echo '<a href="'.home_url().'" title="Home">';_e('go to home',THEME_DOMAIN); echo'</a>';_e(' page or simply search what you want to see below',THEME_DOMAIN); ?></p>
		     </div>
			<div class="entry-content">
				<div class="search404"><?php get_search_form(); // Loads the searchform.php template. ?></div>
			</div>
				<?php 
                    $Supreme_Theme_Settings_Options =get_option(supreme_prefix().'_theme_settings');;
                    $Get_All_Post_Types = explode(',',@$Supreme_Theme_Settings_Options['post_type_label']);
                    foreach($Get_All_Post_Types as $post_type):
					if($post_type!='page' && $post_type!="attachment" && $post_type!="revision" && $post_type!="nav_menu_item"):
						$taxonomies = get_object_taxonomies( (object) array( 'post_type' => $post_type,'public'   => true, '_builtin' => true ));	
						$archive_query = new WP_Query('showposts=60&post_type='.$post_type);
						if( count(@$archive_query->posts) > 0 ){
							$PostTypeObject = get_post_type_object($post_type);
							$PostTypeName = @$PostTypeObject->labels->name;
						}							
						$WPListCustomCategories = wp_list_categories('title_li=&hierarchical=0&show_count=0&echo=0&taxonomy='.$taxonomies[0]);
						if(($WPListCustomCategories) && $WPListCustomCategories!="No categories" && $WPListCustomCategories!="<li>No categories</li>"){
						?> 
						<div class="arclist">
                                   <div class="title-container">
                                        <h2 class="title_green"><span><?php echo ucfirst($PostTypeName); _e(' Categories',THEME_DOMAIN);?></span></h2>                                       
                                   </div>
                                   <ul>
                                   <?php echo $WPListCustomCategories;?>
                                   </ul>
						</div>
						<?php } 
					endif;
				endforeach;?>      
		</div>
     <!-- .hentry -->
	</div>
	<!-- .hfeed -->
	<?php $post = $single_post;?>
</section>
<!-- #content -->
<?php get_footer(); // Loads the footer.php template. ?>