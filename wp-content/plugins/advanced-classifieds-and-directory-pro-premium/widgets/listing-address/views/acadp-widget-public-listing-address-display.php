<?php

/**
 * This template displays the public-facing aspects of the widget.
 *
 * @link    https://pluginsware.com
 * @since   1.5.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-widget-listing-address">
    <!-- Listing Map -->
    <?php if ( $can_show_map ) : ?>
    	<div class="embed-responsive embed-responsive-16by9 acadp-margin-bottom" data-type="single-listing">
    		<div class="acadp-map embed-responsive-item">
				<div class="marker" data-latitude="<?php echo esc_attr( $post_meta['latitude'][0] ); ?>" data-longitude="<?php echo esc_attr( $post_meta['longitude'][0] ); ?>"></div> 
       		</div>
        </div>
	<?php endif; ?>
    
    <!-- Listing Address -->
	<?php the_acadp_address( $post_meta, $location->term_id ); ?>
</div>