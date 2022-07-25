<?php
/**
 * Template for displaying search forms in Theme
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	
		<span class="screen-reader-text"><?php  esc_html_e( 'Search','mywiki' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search', 'mywiki' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	
	<button type="submit" class="search-submit fa fa-search"><span class="screen-reader-text"></span></button>
</form>
