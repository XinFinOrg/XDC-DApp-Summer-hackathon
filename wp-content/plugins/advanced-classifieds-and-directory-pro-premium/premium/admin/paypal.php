<?php

/**
 * PayPal Payment Gateway.
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
 * ACADP_Premium_Admin_PayPal class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Admin_PayPal {

	/**
	 * Registers the gateway
	 *
	 * @since 1.6.4
	 */
	public function register_gateway( $gateways ) {	
		$gateways['paypal'] = __( 'PayPal', 'advanced-classifieds-and-directory-pro' );		
		return $gateways;		
	}
	
	/**
     * Register "PayPal" settings section.
     *
	 * @since  1.7.3
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function register_settings_section( $sections ) {	
		$sections[] = array(
			'id'    => 'acadp_gateway_paypal_settings',
			'title' => __( 'PayPal', 'advanced-classifieds-and-directory-pro' ),		
			'tab'   => 'gateways',
			'slug'  => 'acadp_gateway_paypal_settings'
		);
		
		return $sections;	
	}

	/**
     * Register "PayPal" settings fields.
     *
	 * @since  1.7.3
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function register_settings_fields( $fields ) {
		$fields['acadp_gateway_paypal_settings'] = array(
			array(
				'name'              => 'label',
				'label'             => __( 'Title', 'advanced-classifieds-and-directory-pro' ),
				'description'       => '',
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field'
			),
			array(
				'name'              => 'description',
				'label'             => __( 'Description', 'advanced-classifieds-and-directory-pro' ),
				'description'       => '',
				'type'              => 'textarea',
				'sanitize_callback' => 'sanitize_textarea_field'
			),
			array(
				'name'              => 'email',
				'label'             => __( 'Business email', 'advanced-classifieds-and-directory-pro' ),
				'description'       => __( "Enter your PayPal account's email.", 'advanced-classifieds-and-directory-pro' ),
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_email'
			)
		);

		return $fields;	
	}

}
