<?php

/**
 * Slider.
 *
 * @link    https://pluginsware.com
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Public_Slider class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Public_Slider {

	/**
	 * Get things started.
	 *
	 * @since 1.6.4
	 */
	public function __construct() {		
		// Register shortcode(s)
		add_shortcode( "acadp_banner_rotator", array( $this, "shortcode_banner_rotator" ) );
		add_shortcode( "acadp_carousel_slider", array( $this, "shortcode_carousel_slider" ) );
	}

	/**
	 * Process the shortcode [acadp_banner_rotator].
	 *
	 * @since 1.6.4
	 * @param array $atts An associative array of attributes.
	 */
	public function shortcode_banner_rotator( $atts ) {
		// Dependencies
		wp_enqueue_style( ACADP_PLUGIN_NAME.'-slick' );
		wp_enqueue_style( ACADP_PLUGIN_NAME );		
		wp_enqueue_style( ACADP_PLUGIN_NAME . '-premium-public-slider' );

		wp_enqueue_script( ACADP_PLUGIN_NAME.'-slick' );
		wp_enqueue_script( ACADP_PLUGIN_NAME );	
		wp_enqueue_script( ACADP_PLUGIN_NAME . '-premium-public-slider' );
			
		// WP Query
		$atts = acadp_premium_slider_atts( $atts, 'banner_rotator' );		
		$acadp_query = new WP_Query( $atts['query'] );
			
		// Start the Loop
		global $post;
			
		// Process output
		if ( $acadp_query->have_posts() ) {			
			ob_start();
			include( ACADP_PLUGIN_DIR . "premium/public/templates/banner-rotator.php" );
			return ob_get_clean();			
		}		
	}

	/**
	 * Process the shortcode [acadp_carousel_slider].
	 *
	 * @since 1.6.4
	 * @param array $atts An associative array of attributes.
	 */
	public function shortcode_carousel_slider( $atts ) {			
		// Dependencies
		wp_enqueue_style( ACADP_PLUGIN_NAME.'-slick' );
		wp_enqueue_style( ACADP_PLUGIN_NAME );		
		wp_enqueue_style( ACADP_PLUGIN_NAME . '-premium-public-slider' );
			
		wp_enqueue_script( ACADP_PLUGIN_NAME.'-slick' );
		wp_enqueue_script( ACADP_PLUGIN_NAME );	
		wp_enqueue_script( ACADP_PLUGIN_NAME . '-premium-public-slider' );
			
		// WP Query
		$atts = acadp_premium_slider_atts( $atts, 'carousel_slider' );		
		$acadp_query = new WP_Query( $atts['query'] );
			
		// Start the Loop
		global $post;
			
		// Process Output
		if ( $acadp_query->have_posts() ) {			
			ob_start();
			include( ACADP_PLUGIN_DIR . "premium/public/templates/carousel-slider.php" );
			return ob_get_clean();			
		}
	}

}
