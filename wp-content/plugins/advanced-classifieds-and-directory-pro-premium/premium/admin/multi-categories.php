<?php
 
/**
 * Multi Categories.
 *
 * @link    https://pluginsware.com
 * @since   1.6.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Admin_Multi_Categories class.
 *
 * @since 1.6.5
 */ 
class ACADP_Premium_Admin_Multi_Categories {
	
	/**
     * Register "Multi Categories" settings section.
     *
	 * @since  1.7.3
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function register_settings_section( $sections ) {	
		$sections[] = array(
			'id'    => 'acadp_multi_categories_settings',
			'title' => __( 'Multi Categories', 'advanced-classifieds-and-directory-pro' ),		
			'tab'   => 'general',
			'slug'  => 'acadp_multi_categories_settings'
		);
		
		return $sections;	
	}

	/**
     * Register "Multi Categories" settings fields.
     *
	 * @since  1.7.3
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function register_settings_fields( $fields ) {
		$fields['acadp_multi_categories_settings'] = array(
			array(
				'name'              => 'enabled',
				'label'             => __( 'Enable / Disable', 'advanced-classifieds-and-directory-pro' ),
				'description'       => __( 'Check this to enable multi categories.', 'advanced-classifieds-and-directory-pro' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'custom_fields_rules',
				'label'             => __( 'Custom Fields Display Rules', 'advanced-classifieds-and-directory-pro' ),
				'description'       => __( 'Determine how the custom fields are displayed.', 'advanced-classifieds-and-directory-pro' ),
				'type'              => 'select',
				'options'           => array(
					'all' 	  => __( 'Display custom fields of all the selected categories', 'advanced-classifieds-and-directory-pro' ),
					'common'  => __( 'Display fields only common to the selected categories', 'advanced-classifieds-and-directory-pro' )
				),
				'sanitize_callback' => 'sanitize_key'
			)
		);

		return $fields;	
	}	

	/**
	 * Replaces categories dropdown with a multi categories checklist.
	 *
	 * @since  1.6.5
	 * @param  string $html    HTML. Categories dropdown.
	 * @param  int    $post_id Post ID.
	 * @return string $html    Multi catgories checklist.
	 */
	public function listing_form_categories_dropdown( $html, $post_id ) {		
		return acadp_premium_get_terms_checklist( $post_id );		
	}

}
