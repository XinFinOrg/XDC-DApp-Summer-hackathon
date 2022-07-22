<?php

/**
 * The admin-specific functionality of the plugin.
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
 * ACADP_Premium_Admin class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Admin {

	/**
	 * Insert missing plugin options.
	 *
	 * @since 1.6.4
	 */
	public function wp_loaded() {
		// Insert WooCommerce Plans Settings
		if ( false == get_option( 'acadp_wc_plans_settings' ) ) {
			$defaults = array(
				'enabled'                          => acadp_premium_is_woocommerce_active() ? 1 : 0,
				'product_type'                     => 'listings_package',
				'disable_categories_listings_edit' => 0
			);

        	add_option( 'acadp_wc_plans_settings', $defaults );			
		}
		
		// Insert Fee Plans Settings
		if ( false == get_option( 'acadp_fee_plans_settings' ) ) {			
			$defaults = array(
				'enabled'                          => 0,
				'label'                            => __( 'Fee Plans', 'advanced-classifieds-and-directory-pro' ),
				'description'                      => '',
				'disable_categories_listings_edit' => 0
			);

			add_option( 'acadp_fee_plans_settings', $defaults );			
			
			// Adds Custom User Capabilities
			global $wp_roles;

			if ( class_exists( 'WP_Roles' ) ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}
			
			if ( is_object( $wp_roles ) ) {			
				$wp_roles->add_cap( 'administrator', "edit_acadp_fee_plan" );
				$wp_roles->add_cap( 'administrator', "read_acadp_fee_plan" );
				$wp_roles->add_cap( 'administrator', "delete_acadp_fee_plan" );
				$wp_roles->add_cap( 'administrator', "edit_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "edit_others_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "publish_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "read_private_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "delete_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "delete_private_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "delete_published_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "delete_others_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "edit_private_acadp_fee_plans" );
				$wp_roles->add_cap( 'administrator', "edit_published_acadp_fee_plans" );			
			}
		}
		
		// Insert PayPal Settings
		if ( false == get_option( 'acadp_gateway_paypal_settings' ) ) {	
			$defaults = array(			
				'label'       => __( 'PayPal', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'email'       => 'paypalemail@site.com'
			);

        	add_option( 'acadp_gateway_paypal_settings', $defaults );			
		}
		
		//Mel: 27/12/21. Insert Bitcoin Payment Settings
		if ( false == get_option( 'acadp_gateway_bitcoin_settings' ) ) {	
			$defaults = array(			
				'label'       => __( 'Bitcoin', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'payment_address'	=> 'bc1q0ag3grv8egdvyuu0kfxv27uv8ghm70xaruen07'
			);

        	add_option( 'acadp_gateway_bitcoin_settings', $defaults );			
		}
		
		//Mel: 03/01/22. Insert Ethereum Payment Settings
		if ( false == get_option( 'acadp_gateway_ethereum_settings' ) ) {	
			$defaults = array(			
				'label'       => __( 'Ethereum', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'payment_address'	=> '0x3bCf9cc8b3241BA324b983E8EC995BE4F40EAA59'
			);

        	add_option( 'acadp_gateway_ethereum_settings', $defaults );			
		}
		
		// Insert Stripe Settings
		if ( false == get_option( 'acadp_gateway_stripe_settings' ) ) {
			$defaults = array(
				'label'                => __( 'Stripe', 'advanced-classifieds-and-directory-pro' ),
				'description'          => '',
				'live_secret_key'      => '',
				'live_publishable_key' => '',
				'test_secret_key'      => '',
				'test_publishable_key' => ''
			);

        	add_option( 'acadp_gateway_stripe_settings', $defaults );			
		}
		
		// Insert Multi Categories Settings
		if ( false == get_option( 'acadp_multi_categories_settings' ) ) {
			$defaults = array(
				'enabled'             => 0,
				'custom_fields_rules' => 'all'
			);

        	add_option( 'acadp_multi_categories_settings', $defaults );			
    	}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.6.4
	 */
	public function enqueue_styles() {		
		wp_enqueue_style( 
			ACADP_PLUGIN_NAME . '-premium-admin', 
			ACADP_PLUGIN_URL . 'premium/admin/assets/css/admin.css', 
			array(), 
			ACADP_VERSION_NUM, 
			'all' 
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.6.4
	 */
	public function enqueue_scripts() {
		$wc_plans_settings = get_option( 'acadp_wc_plans_settings' );
		$fee_plans_settings = get_option( 'acadp_fee_plans_settings' );
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );		

		wp_enqueue_script( 
			ACADP_PLUGIN_NAME . '-premium-admin', 
			ACADP_PLUGIN_URL . 'premium/admin/assets/js/admin.js', 
			array( 'jquery' ), 
			ACADP_VERSION_NUM, 
			false 
		);

		wp_localize_script( ACADP_PLUGIN_NAME . '-premium-admin', 'acadp_premium', array(
			'is_woocommerce_plans_enabled' => ! empty( $wc_plans_settings['enabled'] ) ? 1 : 0,
			'is_fee_plans_enabled'         => ! empty( $fee_plans_settings['enabled'] ) ? 1 : 0,
			'is_multi_categories_enabled'  => ! empty( $multi_categories_settings['enabled'] ) ? 1 : 0,
			'i18n'                         => array(
				'import_listings' => __( 'Import Listings', 'advanced-classifieds-and-directory-pro' ),
				'import_new_file' => __( 'Import New File', 'advanced-classifieds-and-directory-pro' )
			)
		));	
	}	

}
