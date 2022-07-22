<?php

/**
 * Banner Rotator.
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
 * ACADP_Widget_Banner_Rotator Class.
 *
 * @since 1.6.4
 */
class ACADP_Widget_Banner_Rotator extends WP_Widget {
	
	/**
	 * Get things going.
	 *
	 * @since 1.6.4
	 */
	public function __construct() {	
		parent::__construct(
			'acadp-slider-banner-rotator',
			__( 'ACADP - Banner Rotator', 'advanced-classifieds-and-directory-pro' ),
			array(
				'classname'   => 'acadp-slider-banner-rotator-class',
				'description' => __( 'Banner Rotator for Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' )
			)
		);		
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @since 1.6.4
	 * @param array	$args	  The array of form elements.
	 * @param array $instance The current instance of the widget.
	 */
	public function widget( $args, $instance ) {
		// WP Query
		$atts = acadp_premium_slider_atts( $instance, 'banner_rotator' );		
		$acadp_query = new WP_Query( $atts['query'] );
		
		// Start the Loop
		global $post;
		
		// Process output
		if ( $acadp_query->have_posts() ) {
			$this->enqueue_styles_scripts();
			
			echo $args['before_widget'];
		
			if ( ! empty( $atts['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $atts['title'] ) . $args['after_title'];
			}
		
			include( ACADP_PLUGIN_DIR . "premium/public/templates/banner-rotator.php" );
			
			echo $args['after_widget'];			
		}
	}
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @since 1.6.4
	 * @param array	$new_instance The new instance of values to be generated via the update.
	 * @param array $old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['title']                = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['location']             = isset( $new_instance['location'] ) ? (int) $new_instance['location'] : 0;
		$instance['category']             = isset( $new_instance['category'] ) ? (int) $new_instance['category'] : 0;
		$instance['follow_locations']     = isset( $new_instance['follow_locations'] ) ? (int) $new_instance['follow_locations'] : 0;
		$instance['follow_categories']    = isset( $new_instance['follow_categories'] ) ? (int) $new_instance['follow_categories'] : 0;
		$instance['featured']             = isset( $new_instance['featured'] ) ? (int) $new_instance['featured'] : 0;
		$instance['limit']                = isset( $new_instance['limit'] ) ? (int) $new_instance['limit'] : 0;
		$instance['orderby']              = isset( $new_instance['orderby'] ) ? sanitize_text_field( $new_instance['orderby'] ) : 'title';
		$instance['order']                = isset( $new_instance['order'] ) ? sanitize_text_field( $new_instance['order'] ) : 'asc';
		$instance['images_ratio']         = isset( $new_instance['images_ratio'] ) ? floatval( $new_instance['images_ratio'] ) : 0.5625;
		$instance['images_scale_type']    = isset( $new_instance['images_scale_type'] ) ? sanitize_text_field( $new_instance['images_scale_type'] ) : 'uniform';
		$instance['show_content']         = isset( $new_instance['show_content'] ) ? (int) $new_instance['show_content'] : 0;
		$instance['dots']                 = isset( $new_instance['dots'] ) ? (int) $new_instance['dots'] : 0;
		$instance['dots_bg_color']        = isset( $new_instance['dots_bg_color'] ) ? sanitize_text_field( $new_instance['dots_bg_color'] ) : '';
		$instance['dots_active_bg_color'] = isset( $new_instance['dots_active_bg_color'] ) ? sanitize_text_field( $new_instance['dots_active_bg_color'] ) : '';
		$instance['arrows']               = isset( $new_instance['arrows'] ) ? (int) $new_instance['arrows'] : 0;
		$instance['arrows_bg_color']      = isset( $new_instance['arrows_bg_color'] ) ? sanitize_text_field( $new_instance['arrows_bg_color'] ) : '';
		$instance['arrows_icon_color']    = isset( $new_instance['arrows_icon_color'] ) ? sanitize_text_field( $new_instance['arrows_icon_color'] ) : '';
		$instance['arrows_top_offset']    = isset( $new_instance['arrows_top_offset'] ) ? sanitize_text_field( $new_instance['arrows_top_offset'] ) : '';
		$instance['arrows_left_offset']   = isset( $new_instance['arrows_left_offset'] ) ? sanitize_text_field( $new_instance['arrows_left_offset'] ) : '';
		$instance['arrows_right_offset']  = isset( $new_instance['arrows_right_offset'] ) ? sanitize_text_field( $new_instance['arrows_right_offset'] ) : '';
		$instance['arrows_padding']       = isset( $new_instance['arrows_padding'] ) ? sanitize_text_field( $new_instance['arrows_padding'] ) : '';
		$instance['arrows_icon_size']     = isset( $new_instance['arrows_icon_size'] ) ? sanitize_text_field( $new_instance['arrows_icon_size'] ) : '';
		$instance['arrows_border_radius'] = isset( $new_instance['arrows_border_radius'] ) ? sanitize_text_field( $new_instance['arrows_border_radius'] ) : '';
		$instance['autoplay']             = isset( $new_instance['autoplay'] ) ? (int) $new_instance['autoplay'] : 0;
		$instance['autoplay_interval']    = isset( $new_instance['autoplay_interval'] ) ? (int) $new_instance['autoplay_interval'] : 0;
		$instance['speed']                = isset( $new_instance['speed'] ) ? (int) $new_instance['speed'] : 0;
		$instance['fade']                 = isset( $new_instance['fade'] ) ? (int) $new_instance['fade'] : 0;
		
		return $instance;
	}
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @since 1.6.4
	 * @param array $instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$instance = acadp_premium_slider_atts( $instance, 'banner_rotator', 1 );
		
		// Display the admin form
		include( ACADP_PLUGIN_DIR . 'premium/widgets/forms/banner-rotator.php' );
	}
	
	/**
	 * Registers and enqueues widget-specific styles and scripts.
	 *
	 * @since 1.6.4
	 */
	public function enqueue_styles_scripts() {
		wp_enqueue_style( ACADP_PLUGIN_NAME.'-slick' );	
		wp_enqueue_style( ACADP_PLUGIN_NAME );	
		wp_enqueue_style( ACADP_PLUGIN_NAME . '-premium-public-slider' );
			
		wp_enqueue_script( ACADP_PLUGIN_NAME.'-slick' );
		wp_enqueue_script( ACADP_PLUGIN_NAME );	
		wp_enqueue_script( ACADP_PLUGIN_NAME . '-premium-public-slider' );
	}
	
}