<?php

/**
 * Blocks
 *
 * @link    https://pluginsware.com
 * @since   1.6.1
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Blocks class.
 *
 * @since 1.6.1
 */
class ACADP_Blocks {

	/**
	 * Register our custom block category.
	 *
	 * @since  1.6.1
	 * @param  array $categories Default Gutenberg block categories.
	 * @return array             Modified Gutenberg block categories.
	 */
	public function block_categories( $categories ) {		
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'advanced-classifieds-and-directory-pro',
					'title' => __( 'Advanced Classifieds and Directory Pro', 'advanced-classifieds-and-directory-pro' ),
				),
			)
		);		
	}

	/**
	 * Enqueue block assets for backend editor.
	 *
	 * @since 1.6.1
	 */
	public function enqueue_block_editor_assets() {
		$general_settings   = get_option( 'acadp_general_settings' );		
		$map_settings       = get_option( 'acadp_map_settings' );
		$recaptcha_settings = get_option( 'acadp_recaptcha_settings' );
		$listings_settings  = get_option( 'acadp_listings_settings' );		

		if ( ! empty( $recaptcha_settings['site_key'] ) && ! empty( $recaptcha_settings['forms'] ) ) {
			$recaptcha_site_key     = $recaptcha_settings['site_key'];
			$recaptcha_registration = in_array( 'registration', $recaptcha_settings['forms'] ) ? 1 : 0;
			$recaptcha_listing      = in_array( 'listing', $recaptcha_settings['forms'] ) ? 1 : 0;
			$recaptcha_contact      = ! empty( $general_settings['has_contact_form'] ) && in_array( 'contact', $recaptcha_settings['forms'] ) ? 1 : 0;
			$recaptcha_report_abuse = ! empty( $general_settings['has_report_abuse'] ) && in_array( 'report_abuse', $recaptcha_settings['forms'] ) ? 1 : 0;
		} else {
			$recaptcha_site_key     = '';
			$recaptcha_registration = 0;
			$recaptcha_listing      = 0;
			$recaptcha_contact      = 0;
			$recaptcha_report_abuse = 0;
		}

		// Styles
		wp_enqueue_style( 
			ACADP_PLUGIN_NAME . '-bootstrap', 
			ACADP_PLUGIN_URL . 'vendor/bootstrap/bootstrap.css', 
			array(), 
			'3.3.5', 
			'all' 
		);

		wp_enqueue_style( 
			ACADP_PLUGIN_NAME . '-public', 
			ACADP_PLUGIN_URL . 'public/css/public.css', 
			array(), 
			ACADP_VERSION_NUM, 
			'all' 
		);
		
		if ( 'osm' == $map_settings['service'] ) {
			wp_enqueue_style( 
				ACADP_PLUGIN_NAME . '-map', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/leaflet.css', 
				array(), 
				'1.7.1', 
				'all' 
			);

			wp_enqueue_style( 
				ACADP_PLUGIN_NAME . '-markerclusterer-core', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/MarkerCluster.css', 
				array( ACADP_PLUGIN_NAME . '-map' ), 
				'1.4.1', 
				'all' 
			);

			wp_enqueue_style( 
				ACADP_PLUGIN_NAME . '-markerclusterer', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/MarkerCluster.Default.css', 
				array( ACADP_PLUGIN_NAME . '-markerclusterer-core' ), 
				'1.4.1', 
				'all' 
			);
		}		

		// Scripts
		if ( 'osm' == $map_settings['service'] ) {
			wp_enqueue_script( 
				ACADP_PLUGIN_NAME . '-map', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/leaflet.js', 
				array( ACADP_PLUGIN_NAME ), 
				'1.7.1', 
				true 
			);

			wp_enqueue_script( 
				ACADP_PLUGIN_NAME . '-markerclusterer', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/leaflet.markercluster.js', 
				array( ACADP_PLUGIN_NAME . '-map' ), 
				'1.4.1', 
				true 
			);
		} else {
			$map_api_key = ! empty( $map_settings['api_key'] ) ? '&key=' . $map_settings['api_key'] : '';

			wp_enqueue_script( 
				ACADP_PLUGIN_NAME . '-map', 
				'https://maps.googleapis.com/maps/api/js?v=3.exp' . $map_api_key, 
				array( ACADP_PLUGIN_NAME ), 
				'', 
				true 
			);
			
			wp_enqueue_script( 
				ACADP_PLUGIN_NAME . '-markerclusterer', 
				ACADP_PLUGIN_URL . 'vendor/markerclusterer/markerclusterer.js', 
				array( ACADP_PLUGIN_NAME . '-map' ), 
				'1.0.0', 
				true 
			);		
		}
		
		if ( ! empty( $recaptcha_site_key ) && ( $recaptcha_registration > 0 || $recaptcha_listing > 0 ) ) {
			wp_enqueue_script( 
				ACADP_PLUGIN_NAME . '-recaptcha', 
				'https://www.google.com/recaptcha/api.js?onload=acadp_on_recaptcha_load&render=explicit', 
				array( ACADP_PLUGIN_NAME ), 
				'', 
				true 
			);
		}
		
		wp_enqueue_script(
			'acadp-blocks-js',
			plugins_url( '/blocks/dist/blocks.build.js', dirname( __FILE__ ) ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			filemtime( plugin_dir_path( __DIR__ ) . 'blocks/dist/blocks.build.js' ),
			true
		);

		wp_localize_script( 
			'acadp-blocks-js', 
			'acadp', 
			array(
				'is_rtl'                       => is_rtl(),
				'plugin_url'                   => ACADP_PLUGIN_URL,
				'ajax_url'                     => admin_url( 'admin-ajax.php' ),
				'maximum_images_per_listing'   => $general_settings['maximum_images_per_listing'],
				'map_service'                  => $map_settings['service'],
				'zoom_level'                   => $map_settings['zoom_level'],
				'recaptcha_registration'       => $recaptcha_registration,
				'recaptcha_site_key'           => $recaptcha_site_key,				
				'recaptcha_listing'            => $recaptcha_listing,
				'recaptcha_contact'            => $recaptcha_contact,
				'recaptcha_report_abuse'       => $recaptcha_report_abuse,
				'recaptchas'                   => array( 'listing' => 0, 'contact' => 0, 'report_abuse' => 0 ),
				'recaptcha_invalid_message'    => __( "You can't leave Captcha Code empty", 'advanced-classifieds-and-directory-pro' ),
				'user_login_alert_message'     => __( 'Sorry, you need to login first.', 'advanced-classifieds-and-directory-pro' ),				
				'upload_limit_alert_message'   => __( 'Sorry, you have only %d images pending.', 'advanced-classifieds-and-directory-pro' ),
				'delete_label'                 => __( 'Delete Permanently', 'advanced-classifieds-and-directory-pro' ),
				'proceed_to_payment_btn_label' => __( 'Proceed to payment', 'advanced-classifieds-and-directory-pro' ),
				'finish_submission_btn_label'  => __( 'Finish submission', 'advanced-classifieds-and-directory-pro' ),
				'base_location'                => max( 0, $general_settings['base_location'] ),
				'listings'                     => array(
					'view'              => $listings_settings['default_view'],
					'category'          => 0,
					'location'          => 0,					
					'filterby'          => '',
					'orderby'           => $listings_settings['orderby'],
					'order'             => $listings_settings['order'],
					'columns'           => $listings_settings['columns'],
					'listings_per_page' => ! empty( $listings_settings['listings_per_page'] ) ? $listings_settings['listings_per_page'] : -1,
					'featured'          => true,
					'header'            => true,
					'pagination'        => true,
				),
				'search_form'                 => array(
					'style'             => 'inline',
					'location'          => empty( $general_settings['has_location'] ) ? 0 : 1,
					'category'          => 1,
					'custom_fields'     => 1,
					'price'             => empty( $general_settings['has_price'] ) ? 0 : 1  
				)
			)
		);

		wp_enqueue_script( 
			ACADP_PLUGIN_NAME . '-public', 
			ACADP_PLUGIN_URL . 'public/js/public.js', 
			array( 'jquery' ), 
			ACADP_VERSION_NUM, 
			true 
		);				
	}	

	/**
	 * Register our custom blocks.
	 * 
	 * @since 1.6.1
	 */
	public function register_block_types() {
		// Hook the post rendering to the block
		if ( function_exists( 'register_block_type' ) ) {
			// Hook a render function to the locations block
			register_block_type( 'acadp/locations', array(
				'render_callback' => array( $this, 'render_locations_block' ),
			) );

			// Hook a render function to the categories block
			register_block_type( 'acadp/categories', array(
				'render_callback' => array( $this, 'render_categories_block' ),
			) );
			
			// Hook a render function to the listings block
			register_block_type( 'acadp/listings', array(
				'attributes' => array(
					'view' => array(
						'type' => 'string'
					),
					'category' => array(
						'type' => 'number'
					),
					'location' => array(
						'type' => 'number'
					),					
					'filterby' => array(
						'type' => 'string'
					),
					'orderby' => array(
						'type' => 'string'
					),
					'order' => array(
						'type' => 'string'
					),
					'columns' => array(
						'type' => 'number'
					),
					'listings_per_page' => array(
						'type' => 'number'
					),
					'featured' => array(
						'type' => 'boolean'
					),
					'header' => array(
						'type' => 'boolean'
					),
					'pagination' => array(
						'type' => 'boolean'
					),					
				),
				'render_callback' => array( $this, 'render_listings_block' ),
			) );

			// Hook a render function to the search form block
			register_block_type( 'acadp/search-form', array(				
				'attributes' => array(
					'style' => array(
						'type' => 'string'
					),
					'location' => array(
						'type' => 'boolean'
					),
					'category' => array(
						'type' => 'boolean'
					),
					'custom_fields' => array(
						'type' => 'boolean'
					),
					'price' => array(
						'type' => 'boolean'
					),
				),
				'render_callback' => array( $this, 'render_search_form_block' ),
			) );

			// Hook a render function to the listing form block
			register_block_type( 'acadp/listing-form', array(				
				'render_callback' => array( $this, 'render_listing_form_block' ),
			) );
		}
	}

	/**
	 * Render the locations block frontend.
	 *
	 * @since  1.6.1
	 * @return string Locations block output.
	 */
	public function render_locations_block() {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return;
		}

		return do_shortcode( '[acadp_locations]' );
	}

	/**
	 * Render the categories block frontend.
	 *
	 * @since  1.6.1
	 * @return string Categories block output.
	 */
	public function render_categories_block() {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return;
		}

		return do_shortcode( '[acadp_categories]' );
	}

	/**
	 * Render the listings block frontend.
	 *
	 * @since  1.6.1
	 * @param  array  $atts An associative array of attributes.
	 * @return string       Listings block output.
	 */
	public function render_listings_block( $atts ) {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return;
		}

		return do_shortcode( '[acadp_listings ' . $this->build_shortcode_attributes( $atts ) . ']' );
	}

	/**
	 * Render the search form block frontend.
	 *
	 * @since  1.6.1
	 * @param  array  $atts An associative array of attributes.
	 * @return string       Search form block output.
	 */
	public function render_search_form_block( $atts ) {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return;
		}

		return do_shortcode( '[acadp_search_form ' . $this->build_shortcode_attributes( $atts ) . ']' );
	}

	/**
	 * Render the listing form block frontend.
	 *
	 * @since  1.6.1
	 * @return string Search form block output.
	 */
	public function render_listing_form_block() {
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return;
		}

		return do_shortcode( '[acadp_listing_form]' );		
	}

	/**
	 * Build shortcode attributes string.
	 * 
	 * @since  1.6.1
	 * @access private
	 * @param  array   $atts Array of attributes.
	 * @return string        Shortcode attributes string.
	 */
	private function build_shortcode_attributes( $atts ) {
		$attributes = array();
		
		foreach ( $atts as $key => $value ) {
			if ( is_null( $value ) ) {
				continue;
			}

			if ( is_bool( $value ) ) {
				$value = ( true === $value ) ? 1 : 0;
			}

			$attributes[] = sprintf( '%s="%s"', $key, $value );
		}
		
		return implode( ' ', $attributes );
	}

}
