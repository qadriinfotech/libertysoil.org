<form id="listing_searchform" action="<?php echo home_url(); ?>" method="get" role="search">
     <div>
          <label class="screen-reader-text" for="s"><?php _e('Search for',DIR_DOMAIN)?>:</label>
          <input id="s" type="text" name="s" value="">
          <input type="hidden" name="post_type" value="listing" />
          <input id="searchsubmit" type="submit" value="<?php _e('Search',DIR_DOMAIN);?>">
     </div>
</form> 