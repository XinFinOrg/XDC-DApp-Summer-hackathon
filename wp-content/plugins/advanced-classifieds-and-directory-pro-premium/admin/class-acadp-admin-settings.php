<?php

/**
 * Plugin Settings.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Admin_Settings Class
 *
 * @since 1.7.3
 */
class ACADP_Admin_Settings {

	/**
	 * Settings tabs array.
	 *
	 * @since  1.7.3
	 * @access protected
	 * @var    array
	 */
	protected $tabs = array();

	/**
     * Settings sections array.
     *
	 * @since  1.7.3
	 * @access protected
     * @var    array
     */
    protected $sections = array();
	
	/**
     * Settings fields array.
     *
	 * @since  1.7.3
	 * @access protected
     * @var    array
     */
	protected $fields = array();	

	/**
	 * Initiate settings.
	 *
	 * @since 1.7.3
	 */
	public function admin_init() {	
		$this->tabs     = $this->get_tabs();
        $this->sections = $this->get_sections();
        $this->fields   = $this->get_fields();
		
        // Initialize settings
        $this->initialize_settings();		
	}

	/**
     * Get settings tabs.
     *
	 * @since  1.7.3
     * @return array $tabs Setting tabs array.
     */
    public function get_tabs() {	
		$tabs = array(
			'general'  => __( 'General', 'advanced-classifieds-and-directory-pro' ),
			'display'  => __( 'Display', 'advanced-classifieds-and-directory-pro' ),
			'monetize' => __( 'Monetize', 'advanced-classifieds-and-directory-pro' ),
			'gateways' => __( 'Payment Gateways', 'advanced-classifieds-and-directory-pro' ),
			'email'    => __( 'Email', 'advanced-classifieds-and-directory-pro' ),
			'misc'     => __( 'Advanced', 'advanced-classifieds-and-directory-pro' )
		);		

		return apply_filters( 'acadp_register_settings_tabs', $tabs );	
	}

	/**
     * Get settings sections.
     *
	 * @since  1.7.3
     * @return array $sections Setting sections array.
     */
    public function get_sections() {		
		$sections = array(
			array(
                'id'          => 'acadp_general_settings',
				'title'       => __( 'General', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'general',
				'slug'        => 'acadp_general_settings'
			),
			array(
                'id'          => 'acadp_badges_settings',
				'title'       => __( 'Listing Badges', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'general',
				'slug'        => 'acadp_badges_settings'
			),
			array(
                'id'          => 'acadp_registration_settings',
				'title'       => __( 'Login / Registration', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'general',
				'slug'        => 'acadp_registration_settings'
			),
			array(
                'id'          => 'acadp_currency_settings',
				'title'       => __( 'Currency', 'advanced-classifieds-and-directory-pro' ),
				'description' => sprintf( 
					'%s <a href="%s">%s</a>', 
					__( 'Currency settings under this section are used to format the display of listing Price. You can have separate currency to accept payments from your users.', 'advanced-classifieds-and-directory-pro' ), 
					esc_url( admin_url( 'admin.php?page=acadp_settings&tab=gateways&section=acadp_gateways_settings' ) ), 
					__( 'Configure payment currency', 'advanced-classifieds-and-directory-pro' ) 
				),
				'tab'         => 'general',
				'slug'        => 'acadp_currency_settings'
			),
			array(
                'id'          => 'acadp_map_settings',
				'title'       => __( 'Map', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'general',
				'slug'        => 'acadp_map_settings'
			),
			array(
                'id'          => 'acadp_listings_settings',
				'title'       => __( 'Listings Page', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'display',
				'slug'        => 'acadp_listings_settings'
			),
			array(
                'id'          => 'acadp_listing_settings',
				'title'       => __( 'Single Listing Page', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'display',
				'slug'        => 'acadp_listing_settings'
			),			
			array(
                'id'          => 'acadp_locations_settings',
				'title'       => __( 'Locations Page', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'display',
				'slug'        => 'acadp_locations_settings'
			),
			array(
                'id'          => 'acadp_categories_settings',
				'title'       => __( 'Categories Page', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'display',
				'slug'        => 'acadp_categories_settings'
			),
			array(
                'id'          => 'acadp_socialshare_settings',
				'title'       => __( 'Social Sharing', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'display',
				'slug'        => 'acadp_socialshare_settings'
			),
			array(
                'id'          => 'acadp_recaptcha_settings',
				'title'       => __( 'reCAPTCHA', 'advanced-classifieds-and-directory-pro' ),	
				'description' => '',			
				'tab'         => 'display',
				'slug'        => 'acadp_recaptcha_settings'
			),
			array(
                'id'          => 'acadp_terms_of_agreement',
				'title'       => __( 'TOS', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'display',
				'slug'        => 'acadp_terms_of_agreement'
			),						
			array(
                'id'          => 'acadp_gateway_settings',
				'title'       => __( 'General', 'advanced-classifieds-and-directory-pro' ),
				'description' => sprintf( 
					'%s <a href="%s">%s</a>', 
					__( 'Currency settings under this section are used only to accept payments from your users.', 'advanced-classifieds-and-directory-pro' ), 
					esc_url( admin_url( 'admin.php?page=acadp_settings&tab=general&section=acadp_currency_settings' ) ), 
					__( 'Configure listing currency', 'advanced-classifieds-and-directory-pro' ) 
				),
				'tab'         => 'gateways',
				'slug'        => 'acadp_gateways_settings'
			),
			array(
                'id'          => 'acadp_gateway_offline_settings',
				'title'       => __( 'Offline (Bank Transfer)', 'advanced-classifieds-and-directory-pro' ),
				'description' => __( 'There\'s nothing automatic in this offline payment system, you should use this when you don\'t want to collect money automatically. So once money is in your bank account you change the status of the order manually under "Payment History" menu.', 'advanced-classifieds-and-directory-pro' ),
				'tab'         => 'gateways',
				'slug'        => 'acadp_gateway_offline_settings'
			),
			array(
                'id'          => 'acadp_featured_listing_settings',
				'title'       => __( 'Featured Listings', 'advanced-classifieds-and-directory-pro' ),
				'description' => __( 'Featured listings will always appear on top of regular listings.', 'advanced-classifieds-and-directory-pro' ),
				'tab'         => 'monetize',
				'slug'        => 'acadp_monetize_settings'
			),
			array(
                'id'          => 'acadp_email_settings',
				'title'       => __( 'General', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'tab'         => 'email',
				'slug'        => 'acadp_email_settings'
			),
			array(
                'id'          => 'acadp_email_template_listing_submitted',
				'title'       => __( 'Listing submitted', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_listing_submitted'
			),
			array(
                'id'          => 'acadp_email_template_listing_published',
				'title'       => __( 'Listing published', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_listing_published',
			),
			array(
                'id'          => 'acadp_email_template_listing_renewal',
				'title'       => __( 'Listing renewal', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_listing_renewal'
			),
			array(
                'id'          => 'acadp_email_template_listing_expired',
				'title'       => __( 'Listing expired', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_listing_expired',
			),
			array(
                'id'          => 'acadp_email_template_renewal_reminder',
				'title'       => __( 'Renewal reminder', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_renewal_reminder'
			),
			array(
                'id'          => 'acadp_email_template_order_created',
				'title'       => __( 'Order created', 'advanced-classifieds-and-directory-pro' ),	
				'description' => '',			
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_order_created'
			),
			array(
                'id'          => 'acadp_email_template_order_created_offline',
				'title'       => __( 'Order created (offline)', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',				
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_order_created'
			),
			array(
                'id'          => 'acadp_email_template_order_completed',
				'title'       => __( 'Order completed', 'advanced-classifieds-and-directory-pro' ),	
				'description' => '',			
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_order_completed'
			),
			array(
                'id'          => 'acadp_email_template_listing_contact',
				'title'       => __( 'Contact listing owner', 'advanced-classifieds-and-directory-pro' ),	
				'description' => '',			
				'tab'         => 'email',
				'slug'        => 'acadp_email_template_listing_contact'
			),
			array(
                'id'          => 'acadp_misc_settings',
				'title'       => __( 'Miscellaneous', 'advanced-classifieds-and-directory-pro' ),
				'description' => '',
				'tab'         => 'misc',
				'slug'        => 'acadp_misc_settings'
			),				
			array(
                'id'          => 'acadp_permalink_settings',
				'title'       => __( 'Permalink', 'advanced-classifieds-and-directory-pro' ),
				'description' => __( 'Just make sure that, after updating the fields in this section, you flush the rewrite rules by visiting Settings > Permalinks. Otherwise you\'ll still see the old links.', 'advanced-classifieds-and-directory-pro' ),
				'tab'         => 'misc',
				'slug'        => 'acadp_permalink_settings'
			),			
			array(
                'id'          => 'acadp_page_settings',
				'title'       => __( 'Pages', 'advanced-classifieds-and-directory-pro' ),
				'description' => __( 'We ourselves have generated all the required pages and configured them right for you here. So, don\'t change these settings unless necessary. Mis-configuration of these settings may break the plugin from working correctly. So, care should be taken while editing these page settings.', 'advanced-classifieds-and-directory-pro' ),
				'tab'         => 'misc',
				'slug'        => 'acadp_pages_settings'
			)			
        );
		
		return apply_filters( 'acadp_register_settings_sections', $sections );		
	}

	/**
     * Get settings fields.
     *
	 * @since  1.7.3
     * @return array $fields Setting fields array.
     */
    public function get_fields() {
		$fields = array(
			'acadp_general_settings' => array(
				array(
                    'name'              => 'load_bootstrap',
                    'label'             => __( 'Bootstrap options', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This plugin uses bootstrap 3. Disable these options if your theme already include them.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'multicheck',
					'options'           => array(
						'css'        => __( 'Include bootstrap CSS', 'advanced-classifieds-and-directory-pro' ),
						'javascript' => __( 'Include bootstrap javascript libraries', 'advanced-classifieds-and-directory-pro' )						
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'listing_duration',
                    'label'             => __( 'Listing duration (in days)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Use a value of "0" to keep a listing alive indefinitely.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'has_location',
                    'label'             => __( 'Enable locations', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Allow users to enter listing "Contact Details"', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'base_location',
                    'label'             => __( 'Base location', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Where does your directory operate from? (This list is populated using the data from "Locations" menu)', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'locations',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'default_location',
                    'label'             => __( 'Default location', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the location selected by default when adding a new listing. (This list is populated using the data from "Locations" menu)', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'locations',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'disable_parent_categories',
                    'label'             => __( 'Prevent listings from being posted to top level categories?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'text_editor',
                    'label'             => __( 'Text Editor', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Select the Text Editor you like to have in the front-end listing submission form.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'select',
					'options'           => array(
						'wp_editor' => __( 'WP Editor', 'advanced-classifieds-and-directory-pro' ),
						'textarea'  => __( 'TextArea', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'has_price',
                    'label'             => __( 'Enable price', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Allow users to enter price amount for their listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'has_images',
                    'label'             => __( 'Enable images', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Allow users to upload images for their listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'maximum_images_per_listing',
                    'label'             => __( 'Maximum images allowed per listing', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'has_video',
                    'label'             => __( 'Enable videos', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Allow users to add videos for their listings. Only YouTube &  Vimeo URLs.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'has_map',
                    'label'             => __( 'Enable map', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Allow users to add map for their listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),				
				array(
                    'name'              => 'new_listing_status',
                    'label'             => __( 'Default new listing status', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'publish' => __( 'Publish', 'advanced-classifieds-and-directory-pro' ),
						'pending' => __( 'Pending', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'edit_listing_status',
                    'label'             => __( 'Edit listing status', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'publish' => __( 'Publish', 'advanced-classifieds-and-directory-pro' ),
						'pending' => __( 'Pending', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),								
				array(
                    'name'              => 'has_listing_renewal',
                    'label'             => __( 'Turn on listing renewal option?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'delete_expired_listings',
                    'label'             => __( 'Delete expired Listings (in days)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'If you have the renewal option enabled, this will be the number of days after the "Renewal Reminder" email was sent.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				)
			),
			'acadp_badges_settings' => array(
				array(
                    'name'              => 'show_new_tag',
                    'label'             => __( 'Show "New" badge', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this option to show "New" badge on the listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),				
				array(
                    'name'              => 'new_listing_label',
                    'label'             => __( 'Custom text for "New" badge', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the text you want to use inside the "New" badge.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'new_listing_threshold',
                    'label'             => __( 'New listing threshold (in days)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the number of days the listing will be tagged as "New" from the day it is published.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'show_popular_tag',
                    'label'             => __( 'Show "Popular" badge', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this option to show "Popular" badge on the listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'popular_listing_label',
                    'label'             => __( 'Custom text for "Popular" badge', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the text you want to use inside the "Popular" badge.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'popular_listing_threshold',
                    'label'             => __( 'Popular listing threshold (in views count)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the minimum number of views required for a listing to be tagged as "Popular".', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),				
				array(
                    'name'              => 'mark_as_sold',
                    'label'             => __( 'Mark as "Sold"', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this option to allow users to mark their listings as "Sold"', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'sold_listing_label',
                    'label'             => __( 'Custom text for "Sold" badge', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the text you want to use inside the "Sold" badge.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				)
			),
			'acadp_registration_settings' => array(
				array(
                    'name'              => 'engine',
                    'label'             => __( 'Enable / Disable', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'radio',
					'options'           => array(
						'acadp'  => __( 'Check this to allow the plugin to take care of user Login / Registration.', 'advanced-classifieds-and-directory-pro' ),
						'others' => __( 'Check this if you already have a registration system. You will need to add the Login / Registration / Forgot Password Page URLs of your registration system in the fields below to get this work. Checking this option and leaving the following fields empty will simply enable the standard WordPress Login / Registration mechanism.', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'custom_login',
                    'label'             => __( 'Custom Login URL', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Optional. Add your custom Login Page URL or a [shortcode] that renders the Login form. Leave this field empty to add the standard WordPress Login form.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'custom_register',
                    'label'             => __( 'Custom Registration URL', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Optional. Add your custom Registration Page URL. Leave this field empty to use the standard WordPress Registration URL.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'esc_url_raw'
				),
				array(
                    'name'              => 'custom_forgot_password',
                    'label'             => __( 'Custom Forgot Password URL', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Optional. Add your custom Forgot Password Page URL. Leave this field empty to use the standard WordPress Forgot Password URL.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'esc_url_raw'
				),
			),
			'acadp_currency_settings' => array(
				array(
                    'name'              => 'currency',
                    'label'             => __( 'Currency', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter your currency.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'position',
                    'label'             => __( 'Currency position', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Choose the location of the currency sign.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'select',
					'options'           => array(
						'before' => __( 'Before - $10', 'advanced-classifieds-and-directory-pro' ),
						'after'  => __( 'After - 10$', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'thousands_separator',
                    'label'             => __( 'Thousands separator', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'The symbol (usually , or .) to separate thousands.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'acadp_sanitize_thousands_separator'
				),
				array(
                    'name'              => 'decimal_separator',
                    'label'             => __( 'Decimal separator', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'The symbol (usually , or .) to separate decimal points.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
			),
			'acadp_map_settings' => array(
				array(
                    'name'              => 'service',
                    'label'             => __( 'Map service', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'radio',
					'options'           => array(
						'osm'    => __( 'OpenStreetMap (OSM)', 'advanced-classifieds-and-directory-pro' ),
						'google' => __( 'Google Maps', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'api_key',
                    'label'             => __( 'API key', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Your Google Maps API Key.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'snap_to_user_location',
                    'label'             => __( 'Snap to user location', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this option to pan the map to the current user location on the listings map view', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'zoom_level',
                    'label'             => __( 'Zoom level', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( '0 = zoomed out; 21 = zoomed in', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				)				
			),
			'acadp_listings_settings' => array(
				array(
                    'name'              => 'view_options',
                    'label'             => __( 'Display options', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'You must select at least one view.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'multicheck',
					'options'           => array(
						'list' => __( 'List view', 'advanced-classifieds-and-directory-pro' ),
						'grid' => __( 'Grid view', 'advanced-classifieds-and-directory-pro' ),
						'map'  => __( 'Map view', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'default_view',
                    'label'             => __( 'Default view', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'list' => __( 'List view', 'advanced-classifieds-and-directory-pro' ),
						'grid' => __( 'Grid view', 'advanced-classifieds-and-directory-pro' ),
						'map'  => __( 'Map view', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'include_results_from',
                    'label'             => __( 'Include results from', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'child_categories' => __( 'Child categories', 'advanced-classifieds-and-directory-pro' ),
						'child_locations'  => __( 'Child locations', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'orderby',
                    'label'             => __( 'Order listings by', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'title' => __( 'Title', 'advanced-classifieds-and-directory-pro' ),
						'date'  => __( 'Date posted', 'advanced-classifieds-and-directory-pro' ),
						'price' => __( 'Price', 'advanced-classifieds-and-directory-pro' ),
						'views' => __( 'Views count', 'advanced-classifieds-and-directory-pro' ),
						'rand'  => __( 'Random sort', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'order',
                    'label'             => __( 'Sort listings by', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
						'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'columns',
                    'label'             => __( 'Number of columns', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the number of columns you like to have in the "Grid" view.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'listings_per_page',
                    'label'             => __( 'Listings per page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Number of listings to show per page. Use a value of "0" to show all listings.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'display_in_header',
                    'label'             => __( 'Show / Hide (in header)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'listings_count'   => __( 'Listings count', 'advanced-classifieds-and-directory-pro' ),
						'views_selector'   => __( 'Views selector', 'advanced-classifieds-and-directory-pro' ),
						'orderby_dropdown' => __( '"Sort by" dropdown', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'display_in_listing',
                    'label'             => __( 'Show / Hide (in each listing)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'category'      => __( 'Category name', 'advanced-classifieds-and-directory-pro' ),
						'location'      => __( 'Location name', 'advanced-classifieds-and-directory-pro' ), 
						'price'         => __( 'Item price (only if applicable)', 'advanced-classifieds-and-directory-pro' ),					
						'date'          => __( 'Date added', 'advanced-classifieds-and-directory-pro' ),					
						'user'          => __( 'Listing owner name', 'advanced-classifieds-and-directory-pro' ),
						'views'         => __( 'Views count', 'advanced-classifieds-and-directory-pro' ),
						'custom_fields' => __( 'Custom Fields', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'excerpt_length',
                    'label'             => __( 'Description length', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Number of characters.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
			),
			'acadp_listing_settings' => array(
				array(
                    'name'              => 'show_phone_number',
                    'label'             => __( 'Show phone number', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'When you select the "Closed" option, the phone number will be masked with a text like "Show Phone Number" and shown to the users only when he/she clicks on the text.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'select',
					'options'           => array(
						'never'  => __( 'Never', 'advanced-classifieds-and-directory-pro' ),
						'open'   => __( 'Open', 'advanced-classifieds-and-directory-pro' ),
						'closed' => __( 'Closed', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'show_email_address',
                    'label'             => __( 'Show email address', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'When you select the "Registered" option, the email address will be shown only to the logged in users.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'select',
					'options'           => array(
						'never'      => __( 'Never', 'advanced-classifieds-and-directory-pro' ),
						'public'     => __( 'Public', 'advanced-classifieds-and-directory-pro' ),
						'registered' => __( 'Registered', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),				
				array(
                    'name'              => 'has_contact_form',
                    'label'             => __( 'Contact form', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Allows visitors to contact listing authors privately. Authors will receive the messages via email.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'contact_form_require_login',
                    'label'             => __( 'Require login for using the contact form?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'has_comment_form',
                    'label'             => __( 'Comment form', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Allow visitors to discuss listings using the standard WordPress comment form. Comments are public.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'has_report_abuse',
                    'label'             => __( 'Report abuse', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to enable Report abuse', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'has_favourites',
                    'label'             => __( 'Add to favourites', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to enable favourite Listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'display_options',
                    'label'             => __( 'Show / Hide', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'category'      => __( 'Category name', 'advanced-classifieds-and-directory-pro' ),
						'date'          => __( 'Date added', 'advanced-classifieds-and-directory-pro' ),					
						'user'          => __( 'Listing owner name', 'advanced-classifieds-and-directory-pro' ),
						'views'         => __( 'Views count', 'advanced-classifieds-and-directory-pro' ),
						'category_desc'	=> __( 'Category description', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				)
			),
			'acadp_locations_settings' => array(
				array(
                    'name'              => 'columns',
                    'label'             => __( 'Number of columns', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the number of columns you like to have in your locations page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'depth',
                    'label'             => __( 'Depth', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the maximum number of location sub-levels to show.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'orderby',
                    'label'             => __( 'Order locations by', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'id'    => __( 'ID', 'advanced-classifieds-and-directory-pro' ),
						'count' => __( 'Count', 'advanced-classifieds-and-directory-pro' ),
						'name'  => __( 'Name', 'advanced-classifieds-and-directory-pro' ),
						'slug'  => __( 'Slug', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'order',
                    'label'             => __( 'Sort locations by', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
						'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'show_count',
                    'label'             => __( 'Show listings count?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to show the listings count next to the location name', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'hide_empty',
                    'label'             => __( 'Hide empty locations?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to hide locations with no listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
			),
			'acadp_categories_settings' => array(
				array(
                    'name'              => 'view',
                    'label'             => __( 'Display as', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'image_grid' => __( 'Thumbnail grid', 'advanced-classifieds-and-directory-pro' ),
						'text_list'  => __( 'Text-only menu items', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'columns',
                    'label'             => __( 'Number of columns', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the number of columns you like to have in your categories page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'depth',
                    'label'             => __( 'Depth', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the maximum number of category sub-levels to show in the "Text-only Menu Items" view.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'orderby',
                    'label'             => __( 'Order categories by', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'id'    => __( 'ID', 'advanced-classifieds-and-directory-pro' ),
						'count' => __( 'Count', 'advanced-classifieds-and-directory-pro' ),
						'name'  => __( 'Name', 'advanced-classifieds-and-directory-pro' ),
						'slug'  => __( 'Slug', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'order',
                    'label'             => __( 'Sort categories by', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
						'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'show_count',
                    'label'             => __( 'Show listings count?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to show the listings count next to the category name', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'hide_empty',
                    'label'             => __( 'Hide empty categories?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to hide categories with no listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
			),
			'acadp_socialshare_settings' => array(
				array(
                    'name'              => 'services',
                    'label'             => __( 'Share buttons', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'facebook'  => __( 'Facebook', 'advanced-classifieds-and-directory-pro' ),
						'twitter'   => __( 'Twitter', 'advanced-classifieds-and-directory-pro' ),					
						'linkedin'  => __( 'Linkedin', 'advanced-classifieds-and-directory-pro' ),
						'pinterest' => __( 'Pinterest', 'advanced-classifieds-and-directory-pro' ),
						'whatsapp'  => __( 'WhatsApp', 'advanced-classifieds-and-directory-pro' )					
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'pages',
                    'label'             => __( 'Show buttons in', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'listing'    => __( 'Listing detail page', 'advanced-classifieds-and-directory-pro' ),
						'listings'   => __( 'Listings page', 'advanced-classifieds-and-directory-pro' ),
						'categories' => __( 'Categories page', 'advanced-classifieds-and-directory-pro' ),
						'locations'  => __( 'Locations page', 'advanced-classifieds-and-directory-pro' )					
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
			),
			'acadp_recaptcha_settings' => array(
				array(
                    'name'              => 'forms',
                    'label'             => __( 'Enable reCAPTCHA in', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'registration' => __( 'User Registration form', 'advanced-classifieds-and-directory-pro' ),
						'listing'      => __( 'New Listing form', 'advanced-classifieds-and-directory-pro' ),
						'contact'      => __( 'Contact form', 'advanced-classifieds-and-directory-pro' ),
						'report_abuse' => __( 'Report abuse form', 'advanced-classifieds-and-directory-pro' )				
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'site_key',
                    'label'             => __( 'Site key', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'secret_key',
                    'label'             => __( 'Secret key', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
			),
			'acadp_terms_of_agreement' => array(
				array(
                    'name'              => 'show_agree_to_terms',
                    'label'             => __( 'Agree to terms', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to show an agree to terms on the listing form that users must agree to before submitting their listing', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'agree_label',
                    'label'             => __( 'Agree to terms label', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Label shown next to the agree to terms check box.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'agree_text',
                    'label'             => __( 'Agreement text', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'If "Agree to terms" is checked, enter the agreement terms or an URL starting with http. If you use an URL, the "Agree to terms label" will be linked to this given URL.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),						
			'acadp_gateway_settings' => array(
				array(
                    'name'              => 'gateways',
                    'label'             => __( 'Enable / Disable', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => acadp_get_payment_gateways(),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'test_mode',
                    'label'             => __( 'Test mode', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'use_https',
                    'label'             => __( 'Enforce SSL on checkout', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'currency',
                    'label'             => __( 'Currency', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter your currency. Note that some payment gateways have currency restrictions.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'position',
                    'label'             => __( 'Currency position', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Choose the location of the currency sign.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'select',
					'options'           => array(
						'before' => __( 'Before - $10', 'advanced-classifieds-and-directory-pro' ),
						'after'  => __( 'After - 10$', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'sanitize_key'
				),
				array(
                    'name'              => 'thousands_separator',
                    'label'             => __( 'Thousands separator', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'The symbol (usually , or .) to separate thousands.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'acadp_sanitize_thousands_separator'
				),
				array(
                    'name'              => 'decimal_separator',
                    'label'             => __( 'Decimal separator', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'The symbol (usually , or .) to separate decimal points.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
			),
			'acadp_gateway_offline_settings' => array(
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
                    'name'              => 'instructions',
                    'label'             => __( 'Instructions', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_featured_listing_settings' => array(
				array(
                    'name'              => 'enabled',
                    'label'             => __( 'Enable / Disable', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this to enable featured listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'label',
                    'label'             => __( 'Title', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'You can give your own name for this feature using this field.', 'advanced-classifieds-and-directory-pro' ),
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
                    'name'              => 'price',
                    'label'             => sprintf( __( "Price [%s]", 'advanced-classifieds-and-directory-pro' ), acadp_get_payment_currency() ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'acadp_sanitize_amount'
				),
				array(
                    'name'              => 'show_featured_tag',
                    'label'             => __( 'Show "Featured" badge', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this option to show "Featured" badge on the featured listings', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
				),
			),
			'acadp_email_settings' => array(
				array(
                    'name'              => 'from_name',
                    'label'             => __( 'From name', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'The name system generated emails are sent from. This should probably be your site or directory name.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'from_email',
                    'label'             => __( 'From email', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'The email id system generated emails are sent from. This will act as the "from" and "reply-to" address.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_email'
				),
				array(
                    'name'              => 'admin_notice_emails',
                    'label'             => __( 'Admin notification emails', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Enter the email address(es) that should receive admin notification emails, one per line.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'textarea',
					'sanitize_callback' => 'sanitize_textarea_field'
				),
				array(
                    'name'              => 'notify_admin',
                    'label'             => __( 'Notify admin via email when', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'listing_submitted' => __( 'A new listing is submitted', 'advanced-classifieds-and-directory-pro' ),
						'listing_edited'    => __( 'A listing is edited', 'advanced-classifieds-and-directory-pro' ),
						'listing_expired'   => __( 'A listing expired', 'advanced-classifieds-and-directory-pro' ),
						'order_created'     => __( 'Order created', 'advanced-classifieds-and-directory-pro' ),
						'payment_received'  => __( 'Payment received', 'advanced-classifieds-and-directory-pro' ),
						'listing_contact'   => __( 'A contact message is sent to a listing owner', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),
				array(
                    'name'              => 'notify_users',
                    'label'             => __( 'Notify users via email when their', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'multicheck',
					'options'           => array(
						'listing_submitted' => __( 'Listing is submitted', 'advanced-classifieds-and-directory-pro' ),
						'listing_published' => __( 'Listing is approved/published', 'advanced-classifieds-and-directory-pro' ),
						'listing_renewal'   => __( 'Listing is about to expire (reached renewal email threshold)', 'advanced-classifieds-and-directory-pro' ),
						'listing_expired'   => __( 'Listing expired', 'advanced-classifieds-and-directory-pro' ),					
						'remind_renewal'    => __( 'Listing expired and reached renewal reminder email threshold', 'advanced-classifieds-and-directory-pro' ),
						'order_created'     => __( 'Order created', 'advanced-classifieds-and-directory-pro' ),
						'order_completed'   => __( 'Order completed', 'advanced-classifieds-and-directory-pro' )
					),
					'sanitize_callback' => 'acadp_sanitize_array'
				),				
			),
			'acadp_email_template_listing_submitted' => array(
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_listing_published' => array(
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_listing_renewal' => array(
				array(
                    'name'              => 'email_threshold',
                    'label'             => __( 'Listing renewal email threshold (in days)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Configure how many days before listing expiration is the renewal email sent.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{expiration_date} - ' . __( 'Expiration date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{category_name} - ' . __( 'Category name that is going to expire', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{renewal_link} - ' . __( 'Link to renewal page', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .	
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .							
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_listing_expired' => array(
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{expiration_date} - ' . __( 'Expiration date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{category_name} - ' . __( 'Category name that is going to expire', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{renewal_link} - ' . __( 'Link to renewal page', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .	
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .							
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_renewal_reminder' => array(
				array(
                    'name'              => 'reminder_threshold',
                    'label'             => __( 'Listing renewal reminder email threshold (in days)', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Configure how many days after the expiration of a listing an email reminder should be sent to the owner.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{expiration_date} - ' . __( 'Expiration date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{category_name} - ' . __( 'Category name that is going to expire', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{renewal_link} - ' . __( 'Link to renewal page', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .	
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .							
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_order_created' => array(
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{order_id} - ' . __( 'Payment Order ID', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{order_page} - ' . __( 'Adds a link so users can view their order directly on your website', 'advanced-classifieds-and-directory-pro' ) . '<br>' .		
						'{order_details} - ' . __( 'Payment Order details', 'advanced-classifieds-and-directory-pro' ) . '<br>' .	
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .													
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_order_created_offline' => array(
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{order_id} - ' . __( 'Payment Order ID', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{order_page} - ' . __( 'Adds a link so users can view their order directly on your website', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{order_details} - ' . __( 'Payment Order details', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_order_completed' => array(
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{order_id} - ' . __( 'Payment Order ID', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{order_page} - ' . __( 'Adds a link so users can view their order directly on your website', 'advanced-classifieds-and-directory-pro' ) . '<br>' .		
						'{order_details} - ' . __( 'Payment Order details', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_email_template_listing_contact' => array(
				array(
                    'name'              => 'subject',
                    'label'             => __( 'Subject', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => '',
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
                    'name'              => 'body',
                    'label'             => __( 'Body', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'HTML is accepted. You can use the following placeholders:', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{name} - ' . __( 'The listing owner\'s display name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{username} - ' . __( 'The listing owner\'s user name on the site', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_name} - ' . __( 'Your site name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_link} - ' . __( 'Your site name with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{site_url} - ' . __( 'Your site url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_title} - ' . __( 'Listing\'s title', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_link} - ' . __( 'Listing\'s title with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{listing_url} - ' . __( 'Listing\'s url with link', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{sender_name} - ' . __( 'Sender\'s name', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{sender_email} - ' . __( 'Sender\'s email address', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{sender_phone} - ' . __( 'Sender\'s phone number', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{message} - ' . __( 'Contact message', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{today} - ' . __( 'Current date', 'advanced-classifieds-and-directory-pro' ) . '<br>' .
						'{now} - ' . __( 'Current time', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'wysiwyg',
					'sanitize_callback' => 'wp_kses_post'
				),
			),
			'acadp_misc_settings' => array(
				array(
                    'name'              => 'delete_plugin_data',
                    'label'             => __( 'Remove data on uninstall?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this box to delete all of the plugin data (database stored content) when uninstalled', 'advanced-classifieds-and-directory-pro' ),
                    'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
                ),
                array(
                    'name'              => 'delete_media_files',
                    'label'             => __( 'Delete media files?', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Check this box to also delete the associated media files when a listing or category is deleted', 'advanced-classifieds-and-directory-pro' ),
                    'type'              => 'checkbox',
					'sanitize_callback' => 'intval'
                )
            ),
			'acadp_permalink_settings' => array(
				array(
                    'name'              => 'listing',
                    'label'             => __( 'Listing detail page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'Replaces the SLUG value used by custom post type "acadp_listings".', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field'
				),
			),		
			'acadp_page_settings' => array(
				array(
                    'name'              => 'listings',
                    'label'             => __( 'Listings page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where all the active listings are displayed. The [acadp_listings] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'locations',
                    'label'             => __( 'Locations page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where all the locations are displayed. The [acadp_locations] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'location',
                    'label'             => __( 'Single location page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the listings from a particular location is displayed. The [acadp_location] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'categories',
                    'label'             => __( 'Categories page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where all the categories are displayed. The [acadp_categories] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'category',
                    'label'             => __( 'Single category page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the listings from a particular category is displayed. The [acadp_category] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'search',
                    'label'             => __( 'Search page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the search results are displayed. The [acadp_search] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'user_listings',
                    'label'             => __( 'User listings page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the listings from a particular user is displayed. The [acadp_user_listings] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'user_dashboard',
                    'label'             => __( 'User dashboard page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the user home page where the current user can add, edit listings, manage favourite listings, view payment history, etc... The [acadp_user_dashboard] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'listing_form',
                    'label'             => __( 'Listing form page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the listing form page used to add or edit listing details. The [acadp_listing_form] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'manage_listings',
                    'label'             => __( 'Manage listings page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the current user can add a new listing or modify, delete their existing listings. The [acadp_manage_listings] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'favourite_listings',
                    'label'             => __( 'Favourite listings page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the current user\'s favourite listings are displayed. The [acadp_favourite_listings] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'checkout',
                    'label'             => __( 'Checkout page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the checkout page where users will complete their purchases. The [acadp_checkout] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'payment_receipt',
                    'label'             => __( 'Payment receipt page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page users are sent to after completing their payments. The [acadp_payment_receipt] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'payment_failure',
                    'label'             => __( 'Failed Transaction Page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page users are sent to if their transaction is cancelled or fails. The [acadp_payment_errors]...[/acadp_payment_errors] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'payment_history',
                    'label'             => __( 'Payment history page', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the users can view their payment history. The [acadp_payment_history] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'login_form',
                    'label'             => __( 'Login form', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the users can login to the site. The [acadp_login] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'register_form',
                    'label'             => __( 'Registration form', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the users can register an account in the site. The [acadp_register] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'user_account',
                    'label'             => __( 'User account', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page where the users can view/edit their account info. The [acadp_user_account] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'forgot_password',
                    'label'             => __( 'Forgot Password', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page users are sent to when clicking the forgot password link. The [acadp_forgot_password] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
				array(
                    'name'              => 'password_reset',
                    'label'             => __( 'Password Reset', 'advanced-classifieds-and-directory-pro' ),
                    'description'       => __( 'This is the page users are sent to when clicking the password reset link. The [acadp_password_reset] short code must be on this page.', 'advanced-classifieds-and-directory-pro' ),
					'type'              => 'pages',
					'sanitize_callback' => 'intval'
				),
			)
		);

		return apply_filters( 'acadp_register_settings_fields', $fields );	
	}
	
	/**
	 * Initialize settings.
	 *
	 * @since 1.7.3
	 */
	public function initialize_settings() {
		// Register settings sections & fields
        foreach ( $this->sections as $section ) {
			$page_hook = $section['slug'];
			
			// Sections
            if ( false == get_option( $section['id'] ) ) {
                add_option( $section['id'] );
            }
			
            if ( isset( $section['description'] ) && ! empty( $section['description'] ) ) {
                $callback = array( $this, 'settings_section_callback' );
            } elseif ( isset( $section['callback'] ) ) {
                $callback = $section['callback'];
            } else {
                $callback = null;
            }
			
            add_settings_section( $section['id'], $section['title'], $callback, $page_hook );
			
			// Fields			
			$fields = $this->fields[ $section['id'] ];
			
			foreach ( $fields as $option ) {			
                $name     = $option['name'];
                $type     = isset( $option['type'] ) ? $option['type'] : 'text';
                $label    = isset( $option['label'] ) ? $option['label'] : '';
                $callback = isset( $option['callback'] ) ? $option['callback'] : array( $this, 'callback_' . $type );				
                $args     = array(
                    'id'                => $name,
                    'class'             => isset( $option['class'] ) ? $option['class'] : $name,
                    'label_for'         => "{$section['id']}[{$name}]",
                    'description'       => isset( $option['description'] ) ? $option['description'] : '',
                    'name'              => $label,
                    'section'           => $section['id'],
                    'size'              => isset( $option['size'] ) ? $option['size'] : null,
                    'options'           => isset( $option['options'] ) ? $option['options'] : '',
                    'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                    'type'              => $type,
                    'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
                    'min'               => isset( $option['min'] ) ? $option['min'] : '',
                    'max'               => isset( $option['max'] ) ? $option['max'] : '',
                    'step'              => isset( $option['step'] ) ? $option['step'] : ''					
                );
				
                add_settings_field( "{$section['id']}[{$name}]", $label, $callback, $page_hook, $section['id'], $args );
            }
			
			// Creates our settings in the options table
        	register_setting( $page_hook, $section['id'], array( $this, 'sanitize_options' ) );			
		}
		
		// Hook for developers to register custom settings. Maintained for backward compatibility (version < 1.7.3)
		foreach ( $this->tabs as $page_hook => $title ) {
			do_action( 'acadp_register_' . $page_hook . '_settings', 'acadp_' . $page_hook . '_settings' );
		}
		do_action( 'acadp_register_pages_settings', 'acadp_pages_settings' );
	}

	/**
 	 * Displays a section description.
 	 *
	 * @since 1.7.3
	 * @param array $args Settings section args.
 	 */
	  public function settings_section_callback( $args ) {
        foreach ( $this->sections as $section ) {
            if ( $section['id'] == $args['id'] ) {
                printf( '<div class="inside">%s</div>', $section['description'] ); 
                break;
            }
        }
	}
	
	/**
     * Displays a text field for a settings field.
     *
	 * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_text( $args ) {	
        $value       = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $type        = isset( $args['type'] ) ? $args['type'] : 'text';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		
        $html        = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a url field for a settings field.
     *
	 * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_url( $args ) {
        $this->callback_text( $args );
    }
	
	/**
     * Displays a number field for a settings field.
     *
	 * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_number( $args ) {	
        $value       = esc_attr( $this->get_option( $args['id'], $args['section'], 0 ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $type        = isset( $args['type'] ) ? $args['type'] : 'number';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
        $min         = empty( $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
        $max         = empty( $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
        $step        = empty( $args['max'] ) ? '' : ' step="' . $args['step'] . '"';
		
        $html        = sprintf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a checkbox for a settings field.
     *
	 * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_checkbox( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], 0 ) );
		
        $html  = '<fieldset>';
        $html  .= sprintf( '<label for="%1$s[%2$s]">', $args['section'], $args['id'] );
        // $html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="0" />', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s />', $args['section'], $args['id'], checked( $value, 1, false ) );
        $html  .= sprintf( '%1$s</label>', $args['description'] );
        $html  .= '</fieldset>';
		
        echo $html;		
    }
	
	/**
     * Displays a multicheckbox for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_multicheck( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], array() );
		
        $html  = '<fieldset>';
        // $html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $checked  = in_array( $key, $value ) ? 'checked="checked"' : '';
            $html    .= sprintf( '<label for="%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
            $html    .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, $checked );
            $html    .= sprintf( '%1$s</label><br>',  $label );
        }
        $html .= $this->get_field_description( $args );
        $html .= '</fieldset>';
		
        echo $html;		
    }
	
	/**
     * Displays a radio button for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_radio( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], '' );
		
        $html  = '<fieldset>';
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<label for="%1$s[%2$s][%3$s]">',  $args['section'], $args['id'], $key );
            $html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
            $html .= sprintf( '%1$s</label><br>', $label );
        }
        $html .= $this->get_field_description( $args );
        $html .= '</fieldset>';
		
        echo $html;		
    }
	
	/**
     * Displays a selectbox for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_select( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		
        $html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
        }
        $html .= sprintf( '</select>' );
        $html .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a textarea for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_textarea( $args ) {	
        $value       = esc_textarea( $this->get_option( $args['id'], $args['section'], '' ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="'.$args['placeholder'].'"';
		
        $html        = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays the html for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_html( $args ) {
        echo $this->get_field_description( $args );
    }
	
	 /**
     * Displays a rich text textarea for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_wysiwyg( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], '' );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';
		
        echo '<div style="max-width: ' . $size . ';">';
        $editor_settings = array(
            'teeny'         => true,
            'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
            'textarea_rows' => 10
        );
        if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
            $editor_settings = array_merge( $editor_settings, $args['options'] );
        }
        wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );
		echo '</div>';
		
		if ( ! empty( $args['description'] ) ) {
            printf( '<pre class="description">%s</pre>', $args['description'] );
        }
    }
	
	/**
     * Displays a file upload field for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_file( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $id    = $args['section'] . '[' . $args['id'] . ']';
        $label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File', 'advanced-classifieds-and-directory-pro' );
		
        $html  = sprintf( '<input type="text" class="%1$s-text acadp-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
        $html .= '<input type="button" class="button acadp-browse" value="' . $label . '" />';
        $html .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a password field for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_password( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		
        $html  = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
        $html .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a color picker field for a settings field.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_color( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '#ffffff' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		
        $html  = sprintf( '<input type="text" class="%1$s-text acadp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, '#ffffff' );
        $html .= $this->get_field_description( $args );
		
        echo $html;		
    }
	
	/**
     * Displays a list of wordpress pages in a select with the field description.
     *
     * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function callback_pages( $args ) {	
        $dropdown_args = array(
			'show_option_none'  => '-- ' . __( 'Select a page', 'advanced-classifieds-and-directory-pro' ) . ' --',
			'option_none_value' => -1,
            'selected'          => esc_attr( $this->get_option( $args['id'], $args['section'], -1 ) ),
            'name'              => $args['section'] . '[' . $args['id'] . ']',
            'id'                => $args['section'] . '[' . $args['id'] . ']',
            'echo'              => 0			
        );
		
        $html  = wp_dropdown_pages( $dropdown_args );
		$html .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
	 * Displays a list of ACADP locations in a select with the field description.
	 *
	 * @since 1.7.3
	 * @param array $args Settings field args.
	 */	
	public function callback_locations( $args ) {
		$dropdown_args = array(
			'show_option_none' => '-- ' . __( 'Select location', 'advanced-classifieds-and-directory-pro' ) . ' --',
			'taxonomy'         => 'acadp_locations',
			'name'             => $args['section'] . '[' . $args['id'] . ']',
			'id'               => $args['section'] . '[' . $args['id'] . ']',						
			'orderby'          => 'name',
			'selected'         => esc_attr( $this->get_option( $args['id'], $args['section'], -1 ) ),
			'hierarchical'     => true,
			'depth'            => 10,
			'show_count'       => false,
			'hide_empty'       => false,
			'echo'             => 0
		);

		$html  = wp_dropdown_categories( $dropdown_args );		
		$html .= $this->get_field_description( $args );

		echo $html;
	}
	
	/**
     * Get field description for display.
     *
	 * @since 1.7.3
     * @param array $args Settings field args.
     */
    public function get_field_description( $args ) {	
        if ( ! empty( $args['description'] ) ) {
            $description = sprintf( '<p class="description">%s</p>', $args['description'] );
        } else {
            $description = '';
        }
		
        return $description;		
    }
	
	/**
     * Sanitize callback for Settings API.
     *
	 * @since  1.7.3
     * @param  array $options The unsanitized collection of options.
     * @return                The collection of sanitized values.
     */
    public function sanitize_options( $options ) {	
        if ( ! $options ) {
            return $options;
        }
		
        foreach ( $options as $option_slug => $option_value ) {		
            $sanitize_callback = $this->get_sanitize_callback( $option_slug );
			
            // If callback is set, call it
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }			
        }
		
        return $options;		
    }
	
	/**
     * Get sanitization callback for given option slug.
     *
	 * @since  1.7.3
     * @param  string $slug Option slug.
     * @return mixed        String or bool false.
     */
    public function get_sanitize_callback( $slug = '' ) {	
        if ( empty( $slug ) ) {
            return false;
        }
		
        // Iterate over registered fields and see if we can find proper callback
        foreach ( $this->fields as $section => $options ) {
            foreach ( $options as $option ) {
                if ( $option['name'] != $slug ) {
                    continue;
                }
				
                // Return the callback name
                return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
            }
        }
		
        return false;		
    }
	
	/**
     * Get the value of a settings field.
     *
	 * @since  1.7.3
     * @param  string $option  Settings field name.
     * @param  string $section The section name this field belongs to.
     * @param  string $default Default text if it's not found.
     * @return string
     */
    public function get_option( $option, $section, $default = '' ) {	
        $options = get_option( $section );
		
		//Mel: 04/01/22. Fix bug by pluginsware
        if ( isset( $options[ $option ] ) ) {
		//if ( ! empty( $options[ $option ] ) ) {
            return $options[ $option ];
        }
		
        return $default;		
	}
	
	/**
	 * Add "Settings" menu.
	 *
	 * @since 1.7.3
	 */
	public function admin_menu() {	
		add_submenu_page(
			'advanced-classifieds-and-directory-pro',
			__( 'Advanced Classifieds and Directory Pro - Settings', 'advanced-classifieds-and-directory-pro' ),
			__( 'Settings', 'advanced-classifieds-and-directory-pro' ),
			'manage_acadp_options',
			'acadp_settings',
			array( $this, 'display_settings_form' )
		);	
	}
	
	/**
	 * Display settings form.
	 *
	 * @since 1.7.3
	 */
	public function display_settings_form() {		
		require_once ACADP_PLUGIN_DIR . 'admin/partials/settings/acadp-admin-settings-display.php';	
	}
	
}
