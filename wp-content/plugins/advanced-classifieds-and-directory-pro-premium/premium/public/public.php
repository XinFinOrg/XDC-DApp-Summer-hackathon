<?php

/**
 * The public-facing functionality of the plugin.
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
 * ACADP_Premium_Public class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Public {

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.6.4
	 */
	public function register_styles() {
		wp_register_style( 
			ACADP_PLUGIN_NAME . '-premium-public', 
			ACADP_PLUGIN_URL . 'premium/public/assets/css/public.css', 
			array(), 
			ACADP_VERSION_NUM, 
			'all' 
		);

		wp_register_style( 
			ACADP_PLUGIN_NAME . '-premium-public-slider', 
			ACADP_PLUGIN_URL . 'premium/public/assets/css/slider.css', 
			array(), 
			ACADP_VERSION_NUM, 
			'all' 
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.6.4
	 */
	public function register_scripts() {
		$page_settings = get_option( 'acadp_page_settings' );
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );
		$gateway_settings = get_option( 'acadp_gateway_settings' );
		$stripe_settings = get_option( 'acadp_gateway_stripe_settings' );
		
		$publishable_key = empty( $gateway_settings['test_mode'] ) ? $stripe_settings['live_publishable_key'] : $stripe_settings['test_publishable_key'];

		wp_register_script( 
			ACADP_PLUGIN_NAME . '-premium-public', 
			ACADP_PLUGIN_URL . 'premium/public/assets/js/public.js', 
			array( 'jquery' ), 
			ACADP_VERSION_NUM, 
			false 
		);

		wp_register_script( 
			ACADP_PLUGIN_NAME . '-premium-public-woocommerce-plans', 
			ACADP_PLUGIN_URL . 'premium/public/assets/js/woocommerce-plans.js', 
			array( 'jquery' ), 
			ACADP_VERSION_NUM, 
			false 
		);

		wp_register_script( 
			ACADP_PLUGIN_NAME . '-premium-public-slider', 
			ACADP_PLUGIN_URL . 'premium/public/assets/js/slider.js', 
			array( 'jquery' ), 
			ACADP_VERSION_NUM, 
			false 
		);	

		wp_register_script( ACADP_PLUGIN_NAME . '-premium-public-stripe', 'https://js.stripe.com/v1/' );
		wp_register_script( ACADP_PLUGIN_NAME . '-premium-public-stripe-processing', ACADP_PLUGIN_URL . 'premium/public/assets/js/stripe.js' );
		wp_localize_script( ACADP_PLUGIN_NAME . '-premium-public-stripe-processing', 'acadp_stripe', array(
			'publishable_key'              => $publishable_key,
			'card_number_validation_error' => __( 'The credit card number appears to be invalid.', 'advanced-classifieds-and-directory-pro' ),
			'card_cvc_validation_error'    => __( 'The CVC number appears to be invalid.', 'advanced-classifieds-and-directory-pro' ),
			'card_expiry_validation_error' => __( 'The expiration date appears to be invalid.', 'advanced-classifieds-and-directory-pro' )
		));
		
		// Enqueue Styles & Scripts
		$listing_form_page_id = (int) $page_settings['listing_form'];

		if ( $listing_form_page_id > 0 && is_page( $listing_form_page_id ) ) {
			if ( ! empty( $multi_categories_settings['enabled'] ) ) {
				wp_enqueue_style( ACADP_PLUGIN_NAME . '-premium-public' );
				wp_enqueue_script( ACADP_PLUGIN_NAME . '-premium-public' );
			}
		}
	}

}
