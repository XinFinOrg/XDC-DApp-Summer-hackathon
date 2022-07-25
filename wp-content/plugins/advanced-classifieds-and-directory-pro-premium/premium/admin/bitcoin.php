<?php

/**
 * Bitcoin Payment Gateway.
 *
 * @author	Melvin
 * @date	27/12/21 
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Admin_Bitcoin class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Admin_Bitcoin {

	/**
	 * Registers the gateway
	 *
	 * @since 1.6.4
	 */
	public function register_gateway( $gateways ) {	
		$gateways['bitcoin'] = __( 'Bitcoin', 'advanced-classifieds-and-directory-pro' );		
		return $gateways;		
	}
	
	/**
     * Register "Bitcoin" settings section.
     *
	 * @since  1.7.3
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function register_settings_section( $sections ) {	
		$sections[] = array(
			'id'    => 'acadp_gateway_bitcoin_settings',
			'title' => __( 'Bitcoin', 'advanced-classifieds-and-directory-pro' ),		
			'tab'   => 'gateways',
			'slug'  => 'acadp_gateway_bitcoin_settings'
		);
		
		return $sections;	
	}

	/**
     * Register "Bitcoin" settings fields.
     *
	 * @since  1.7.3
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function register_settings_fields( $fields ) {
		$fields['acadp_gateway_bitcoin_settings'] = array(
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
				'name'              => 'payment_address',
				'label'             => __( 'Bitcoin Address', 'advanced-classifieds-and-directory-pro' ),
				'description'       => __( "Enter your Bitcoin wallet address.", 'advanced-classifieds-and-directory-pro' ),
				'type'              => 'text',
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		return $fields;	
	}

}
