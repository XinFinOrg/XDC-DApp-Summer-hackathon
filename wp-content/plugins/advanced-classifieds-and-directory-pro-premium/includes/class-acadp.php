<?php

/**
 * The file that defines the core plugin class.
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
 * ACADP - The main plugin class.
 *
 * @since 1.0.0
 */
class ACADP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *	 
	 * @since  1.0.0
	 * @access protected
	 * @var    ACADP_Loader
	 */
	protected $loader;

	/**
	 * Get things started.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_blocks_hooks();
		$this->define_widgets_hooks();
		$this->set_meta_caps();
		$this->set_cron();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once ACADP_PLUGIN_DIR . 'includes/class-acadp-loader.php';
		
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once ACADP_PLUGIN_DIR . 'includes/class-acadp-i18n.php';
		
		// The class responsible for enabling / disabling listings in parent categories
		require_once ACADP_PLUGIN_DIR . 'includes/class-acadp-walker-category-dropdown.php';
		
		/**
		 * The class responsible for the role creation and assignment of capabilities
		 * for those roles.
		 */
		require_once ACADP_PLUGIN_DIR . 'includes/class-acadp-roles.php';
		
		// The class responsible for defining scheduled events
		require_once ACADP_PLUGIN_DIR . 'includes/class-acadp-cron.php';
		
		// The file that holds the general helper functions
		require_once ACADP_PLUGIN_DIR . 'includes/functions-acadp-general.php';
		
		// The file that holds the functions those generate html elements
		require_once ACADP_PLUGIN_DIR . 'includes/functions-acadp-html.php';
		
		// The file that holds the functions those generate ACADP page permalinks
		require_once ACADP_PLUGIN_DIR . 'includes/functions-acadp-permalinks.php';
		
		// The file that holds the email related functions
		require_once ACADP_PLUGIN_DIR . 'includes/functions-acadp-email.php';		

		// The classes responsible for defining actions those occur in the admin area
		require_once ACADP_PLUGIN_DIR . 'admin/class-acadp-admin.php';
		require_once ACADP_PLUGIN_DIR . 'admin/class-acadp-admin-listings.php';
		require_once ACADP_PLUGIN_DIR . 'admin/class-acadp-admin-locations.php';
		require_once ACADP_PLUGIN_DIR . 'admin/class-acadp-admin-categories.php';		
		require_once ACADP_PLUGIN_DIR . 'admin/class-acadp-admin-fields.php';
		require_once ACADP_PLUGIN_DIR . 'admin/class-acadp-admin-payments.php';
		require_once ACADP_PLUGIN_DIR . 'admin/class-acadp-admin-settings.php';
		require_once ACADP_PLUGIN_DIR . 'admin/backward-compatibility.php';

		/**
		 * The classes responsible for defining actions those occur in the public-facing
		 * side of the site.
		 */
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public.php';
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-locations.php';	
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-categories.php';		
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-listings.php';
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-search.php';	
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-listing.php';
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-registration.php';
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-user.php';
		require_once ACADP_PLUGIN_DIR . 'public/class-acadp-public-payments.php';
		
		/**
		 * The class responsible for defining actions those occur in the blocks.
		 */
		require_once ACADP_PLUGIN_DIR . 'blocks/class-acadp-blocks.php';

		/**
		 * The classes responsible for defining actions those occur in the plugin widgets.
		 */
		require ACADP_PLUGIN_DIR . 'widgets/search/class-acadp-widget-search.php';
		require ACADP_PLUGIN_DIR . 'widgets/locations/class-acadp-widget-locations.php';
		require ACADP_PLUGIN_DIR . 'widgets/categories/class-acadp-widget-categories.php';
		require ACADP_PLUGIN_DIR . 'widgets/listings/class-acadp-widget-listings.php';
		require ACADP_PLUGIN_DIR . 'widgets/listing-address/class-acadp-widget-listing-address.php';
		require ACADP_PLUGIN_DIR . 'widgets/listing-contact/class-acadp-widget-listing-contact.php';
		require ACADP_PLUGIN_DIR . 'widgets/listing-video/class-acadp-widget-listing-video.php';

		// Create an instance of the loader
		$this->loader = new ACADP_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$plugin_i18n = new ACADP_i18n();		
		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		// Hooks common to all admin pages
		$plugin_admin = new ACADP_Admin();

		$this->loader->add_action( 'wp_loaded', $plugin_admin, 'manage_upgrades' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );		
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'wp_ajax_acadp_delete_attachment', $plugin_admin, 'ajax_callback_delete_attachment' );

		$this->loader->add_filter( 'use_block_editor_for_post_type', $plugin_admin, 'disable_gutenberg', 10, 2 );
		$this->loader->add_filter( 'gutenberg_can_edit_post_type', $plugin_admin, 'disable_gutenberg', 10, 2 );
		
		// Hooks specific to the custom post type "acadp_listings"
		$plugin_admin_listings = new ACADP_Admin_Listings();
		
		$this->loader->add_action( 'init', $plugin_admin_listings, 'register_custom_post_type' );
		
		if ( is_admin() ) {
			$this->loader->add_action( 'admin_menu', $plugin_admin_listings, 'admin_menu' );
			$this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin_listings, 'post_submitbox_misc_actions' );
			$this->loader->add_action( 'add_meta_boxes', $plugin_admin_listings, 'add_meta_boxes' );
			$this->loader->add_action( 'wp_ajax_acadp_custom_fields_listings', $plugin_admin_listings, 'ajax_callback_custom_fields' );
			$this->loader->add_action( 'save_post', $plugin_admin_listings, 'save_meta_data', 10, 2 );		
			$this->loader->add_action( 'transition_post_status', $plugin_admin_listings, 'transition_post_status', 10, 3 );
			$this->loader->add_action( 'restrict_manage_posts', $plugin_admin_listings, 'restrict_manage_posts' );
			$this->loader->add_action( 'manage_acadp_listings_posts_custom_column', $plugin_admin_listings, 'custom_column_content', 10, 2 );	
			$this->loader->add_action( 'before_delete_post', $plugin_admin_listings, 'before_delete_post' );	
		
			$this->loader->add_filter( 'parent_file', $plugin_admin_listings, 'parent_file' );
			$this->loader->add_filter( 'parse_query', $plugin_admin_listings, 'parse_query' );
			$this->loader->add_filter( 'manage_edit-acadp_listings_columns', $plugin_admin_listings, 'get_columns' );
			$this->loader->add_filter( 'post_row_actions', $plugin_admin_listings, 'remove_row_actions', 10, 2 );
		} 
		
		// Hooks specific to the custom taxonomy "acadp_locations"
		$plugin_admin_locations = new ACADP_Admin_Locations();
		
		$this->loader->add_action( 'init', $plugin_admin_locations, 'register_custom_taxonomy' );
		$this->loader->add_action( 'admin_menu', $plugin_admin_locations, 'admin_menu' );	
		
		$this->loader->add_filter( 'parent_file', $plugin_admin_locations, 'parent_file' );
		$this->loader->add_filter( "manage_edit-acadp_locations_columns", $plugin_admin_locations, 'get_columns' );
		$this->loader->add_filter( "manage_edit-acadp_locations_sortable_columns", $plugin_admin_locations, 'get_columns' );
		$this->loader->add_filter( "manage_acadp_locations_custom_column", $plugin_admin_locations, 'custom_column_content', 10, 3 );
		
		// Hooks specific to the custom taxonomy "acadp_categories"
		$plugin_admin_categories = new ACADP_Admin_Categories();
		
		$this->loader->add_action( 'init', $plugin_admin_categories, 'register_custom_taxonomy' );
		$this->loader->add_action( 'admin_menu', $plugin_admin_categories, 'admin_menu' );
		$this->loader->add_action( 'acadp_categories_add_form_fields', $plugin_admin_categories, 'add_image_field' );
		$this->loader->add_action( 'created_acadp_categories', $plugin_admin_categories, 'save_image_field' );
		$this->loader->add_action( 'acadp_categories_edit_form_fields', $plugin_admin_categories, 'edit_image_field' );
		$this->loader->add_action( 'edited_acadp_categories', $plugin_admin_categories, 'update_image_field' );
		
		$this->loader->add_filter( 'parent_file', $plugin_admin_categories, 'parent_file' );
		$this->loader->add_filter( "manage_edit-acadp_categories_columns", $plugin_admin_categories, 'get_columns' );
		$this->loader->add_filter( "manage_edit-acadp_categories_sortable_columns", $plugin_admin_categories, 'get_columns' );
		$this->loader->add_filter( "manage_acadp_categories_custom_column", $plugin_admin_categories, 'custom_column_content', 10, 3 );	
		
		// Hooks specific to the custom post type "acadp_fields"
		$plugin_admin_fields = new ACADP_Admin_Fields();
		
		$this->loader->add_action( 'init', $plugin_admin_fields, 'register_custom_post_type' );		
		
		if ( is_admin() ) {
			$this->loader->add_action( 'admin_menu', $plugin_admin_fields, 'admin_menu' );
			$this->loader->add_action( 'add_meta_boxes', $plugin_admin_fields, 'add_meta_boxes' );
			$this->loader->add_action( 'save_post', $plugin_admin_fields, 'save_meta_data', 10, 2 );
			$this->loader->add_action( 'restrict_manage_posts', $plugin_admin_fields, 'restrict_manage_posts' );
			$this->loader->add_action( 'parse_tax_query', $plugin_admin_fields, 'parse_tax_query' );
			$this->loader->add_action( 'pre_get_posts', $plugin_admin_fields, 'custom_order' );
			$this->loader->add_action( 'manage_acadp_fields_posts_custom_column', $plugin_admin_fields, 'custom_column_content', 10, 2 );
			
			$this->loader->add_filter( 'parent_file', $plugin_admin_fields, 'parent_file' );
			$this->loader->add_filter( 'parse_query', $plugin_admin_fields, 'parse_query' );
			$this->loader->add_filter( 'manage_edit-acadp_fields_columns', $plugin_admin_fields, 'get_columns' );
			$this->loader->add_filter( 'post_row_actions', $plugin_admin_fields, 'remove_row_actions', 10, 2 );
		}
		
		// Hooks specific to the custom post type "acadp_payments"
		$plugin_admin_payments = new ACADP_Admin_Payments();
		
		$this->loader->add_action( 'init', $plugin_admin_payments, 'register_custom_post_type' );
		
		if ( is_admin() ) {
			$this->loader->add_action( 'admin_menu', $plugin_admin_payments, 'admin_menu' );
			$this->loader->add_action( 'admin_footer-edit.php', $plugin_admin_payments, 'admin_footer_edit' );
			$this->loader->add_action( 'restrict_manage_posts', $plugin_admin_payments, 'restrict_manage_posts' );
			$this->loader->add_action( 'manage_acadp_payments_posts_custom_column', $plugin_admin_payments, 'custom_column_content', 10, 2 );
			$this->loader->add_action( 'load-edit.php', $plugin_admin_payments, 'load_edit' );
			$this->loader->add_action( 'admin_notices', $plugin_admin_payments, 'admin_notices' );
			
			$this->loader->add_filter( 'parent_file', $plugin_admin_payments, 'parent_file' );
			$this->loader->add_filter( 'parse_query', $plugin_admin_payments, 'parse_query' );
			$this->loader->add_filter( 'manage_edit-acadp_payments_columns', $plugin_admin_payments, 'get_columns' );
			$this->loader->add_filter( 'manage_edit-acadp_payments_sortable_columns', $plugin_admin_payments, 'get_sortable_columns' );
		}
			
		// Hooks specific to the 'settings' page of the plugin
		$plugin_admin_settings = new ACADP_Admin_Settings();
		
		if ( is_admin() ) {
			$this->loader->add_action( 'admin_init', $plugin_admin_settings, 'admin_init' );
			$this->loader->add_action( 'admin_menu', $plugin_admin_settings, 'admin_menu' );			
		}	
		
		// Backward Compatibility
		$backward_compatibility = new ACADP_Admin_Backward_Compatibility();
		
		if ( is_admin() ) {
			$this->loader->add_action( 'admin_init', $backward_compatibility, 'admin_init' );
			$this->loader->add_action( 'admin_menu', $backward_compatibility, 'admin_menu' );			
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private	 
	 */
	private function define_public_hooks() {
		// Hooks common to all public pages
		$plugin_public = new ACADP_Public();

		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect' );
		$this->loader->add_action( 'init', $plugin_public, 'output_buffer' );
		$this->loader->add_action( 'init', $plugin_public, 'add_rewrites' );
		$this->loader->add_action( 'wp_loaded', $plugin_public, 'maybe_flush_rules' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_enqueue_scripts' );
		$this->loader->add_action( 'wp_print_scripts', $plugin_public, 'dequeue_scripts', 100 );		
		$this->loader->add_action( 'wp_head', $plugin_public, 'og_metatags' );
		$this->loader->add_action( 'wp_ajax_acadp_public_dropdown_terms', $plugin_public, 'ajax_callback_dropdown_terms' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_dropdown_terms', $plugin_public, 'ajax_callback_dropdown_terms' );
		
		if ( acadp_can_use_yoast() ) {
			$this->loader->add_filter( 'wpseo_title', $plugin_public, 'wpseo_title' );
			$this->loader->add_filter( 'wpseo_metadesc', $plugin_public, 'wpseo_metadesc' );
			$this->loader->add_filter( 'wpseo_canonical', $plugin_public, 'wpseo_canonical' );
			$this->loader->add_filter( 'wpseo_opengraph_url', $plugin_public, 'wpseo_canonical' );
		} else {
			$this->loader->add_filter( 'wp_title', $plugin_public, 'wp_title', 99, 3 );
			$this->loader->add_filter( 'document_title_parts', $plugin_public, 'document_title_parts' );
		}
		$this->loader->add_filter( 'force_ssl', $plugin_public, 'force_ssl_https', 10, 2 );		
		$this->loader->add_filter( 'the_title', $plugin_public, 'the_title', 99, 2 );
		$this->loader->add_filter( 'single_post_title', $plugin_public, 'the_title', 99 );
		$this->loader->add_filter( 'term_link', $plugin_public, 'term_link', 10, 3 );
		$this->loader->add_filter( 'option_acadp_general_settings', $plugin_public, 'filter_general_settings' );
		
		// Hooks specific to the locations page
		$plugin_public_locations = new ACADP_Public_Locations();
		
		// Hooks specific to the categories page
		$plugin_public_categories = new ACADP_Public_Categories();

		// Hooks specific to the listings page
		$plugin_public_listings = new ACADP_Public_Listings();
		
		// Hooks specific to the search page
		$plugin_public_search = new ACADP_Public_Search();
		
		$this->loader->add_action( 'wp_ajax_acadp_custom_fields_search', $plugin_public_search, 'ajax_callback_custom_fields', 10, 2 );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_custom_fields_search', $plugin_public_search, 'ajax_callback_custom_fields', 10, 2 );
		
		// Hooks specific to the listing detail page
		$plugin_public_listing = new ACADP_Public_Listing();
		
		$this->loader->add_action( 'the_content', $plugin_public_listing, 'the_content', 20 );
		$this->loader->add_action( 'wp_ajax_acadp_public_add_remove_favorites', $plugin_public_listing, 'ajax_callback_add_remove_favorites' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_add_remove_favorites', $plugin_public_listing, 'ajax_callback_add_remove_favorites' );
		$this->loader->add_action( 'wp_ajax_acadp_public_report_abuse', $plugin_public_listing, 'ajax_callback_report_abuse' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_report_abuse', $plugin_public_listing, 'ajax_callback_report_abuse' );
		$this->loader->add_action( 'wp_ajax_acadp_public_send_contact_email', $plugin_public_listing, 'ajax_callback_send_contact_email' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_send_contact_email', $plugin_public_listing, 'ajax_callback_send_contact_email' );
		
		$this->loader->add_filter( 'post_thumbnail_html', $plugin_public_listing, 'post_thumbnail_html' );
		
		// Hooks specific to user registration, login, password reset
		if ( acadp_registration_enabled() ) {
			$plugin_public_registration = new ACADP_Public_Registration();
			
			//$this->loader->add_action( 'login_form_login', $plugin_public_registration, 'redirect_to_custom_login' );
			$this->loader->add_action( 'wp_logout', $plugin_public_registration, 'redirect_after_logout' );
			$this->loader->add_action( 'login_form_register', $plugin_public_registration, 'redirect_to_custom_register' );
			$this->loader->add_action( 'login_form_lostpassword', $plugin_public_registration, 'redirect_to_custom_lostpassword' );
			$this->loader->add_action( 'login_form_rp', $plugin_public_registration, 'redirect_to_custom_password_reset' );
			$this->loader->add_action( 'login_form_resetpass', $plugin_public_registration, 'redirect_to_custom_password_reset' );
			
			$this->loader->add_action( 'init', $plugin_public_registration, 'manage_actions' );
			$this->loader->add_action( 'login_form_register', $plugin_public_registration, 'do_register_user' );
			$this->loader->add_action( 'login_form_lostpassword', $plugin_public_registration, 'do_forgot_password' );
			$this->loader->add_action( 'login_form_rp', $plugin_public_registration, 'do_password_reset' );
			$this->loader->add_action( 'login_form_resetpass', $plugin_public_registration, 'do_password_reset' );
			
			$this->loader->add_filter( 'authenticate', $plugin_public_registration, 'maybe_redirect_at_authenticate', 101, 3 );
			$this->loader->add_filter( 'login_redirect', $plugin_public_registration, 'redirect_after_login', 10, 3 );
			$this->loader->add_filter( 'retrieve_password_message', $plugin_public_registration, 'replace_retrieve_password_message', 10, 4 );
		}
		
		// Hooks specific to the user pages
		$plugin_public_user = new ACADP_Public_User();
		
		//Mel: 25/01/22. To call the hook to process contract form
		$this->loader->add_action( 'init', $plugin_public_user, 'manage_actions_contract' );

		$this->loader->add_action( 'init', $plugin_public_user, 'manage_actions' );
		$this->loader->add_action( 'parse_request', $plugin_public_user, 'parse_request' );
		$this->loader->add_action( 'wp_ajax_acadp_public_custom_fields_listings', $plugin_public_user, 'ajax_callback_custom_fields' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_custom_fields_listings', $plugin_public_user, 'ajax_callback_custom_fields' );
		$this->loader->add_action( 'wp_ajax_acadp_public_image_upload', $plugin_public_user, 'ajax_callback_image_upload', 10, 2 );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_image_upload', $plugin_public_user, 'ajax_callback_image_upload', 10, 2 );
		
		//Mel: 07/11/21. To load the death cert file upload action.
		$this->loader->add_action( 'wp_ajax_acadp_public_file_upload', $plugin_public_user, 'ajax_callback_file_upload', 10, 2 );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_file_upload', $plugin_public_user, 'ajax_callback_file_upload', 10, 2 );
		
		//Mel: 12/11/21. To load action to record transaction ID after successful payment via crypto.
		$this->loader->add_action( 'wp_ajax_acadp_public_add_transaction_id', $plugin_public_user, 'ajax_callback_add_transaction_id', 10, 2 );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_add_transaction_id', $plugin_public_user, 'ajax_callback_add_transaction_id', 10, 2 );

		//Mel: 26/01/22. To save the metadata json file of the post
		$this->loader->add_action( 'wp_ajax_acadp_public_save_metadata', $plugin_public_user, 'ajax_callback_save_metadata' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_save_metadata', $plugin_public_user, 'ajax_callback_save_metadata' );
		
		$this->loader->add_action( 'wp_ajax_acadp_public_delete_attachment_listings', $plugin_public_user, 'ajax_callback_delete_attachment' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_public_delete_attachment_listings', $plugin_public_user, 'ajax_callback_delete_attachment' );
		
		// Hooks specific to the payment system
		$plugin_public_payments = new ACADP_Public_Payments();
		
		$this->loader->add_action( 'wp_ajax_acadp_checkout_format_total_amount', $plugin_public_payments, 'ajax_callback_format_total_amount' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_checkout_format_total_amount', $plugin_public_payments, 'ajax_callback_format_total_amount' );
	}

	/**
	 * Register all of the hooks related to the Gutenberg blocks.
	 *
	 * @since  1.6.1
	 * @access private	 
	 */
	private function define_blocks_hooks() {
		if ( is_admin() ) {
			global $pagenow;
			if ( 'widgets.php' === $pagenow ) return;
		}

		global $wp_version;

		$plugin_blocks = new ACADP_Blocks();

		$this->loader->add_action( 'plugins_loaded', $plugin_blocks, 'register_block_types' );
		$this->loader->add_action( 'enqueue_block_editor_assets', $plugin_blocks, 'enqueue_block_editor_assets' );

		if ( version_compare( $wp_version, '5.8', '>=' ) ) {
			$this->loader->add_filter( 'block_categories_all', $plugin_blocks, 'block_categories' );
		} else {
			$this->loader->add_filter( 'block_categories', $plugin_blocks, 'block_categories' );
		}
	}

	/**
	 * Register all of the hooks related to the widgets.
	 *
	 * @since  1.6.1
	 * @access private	 
	 */
	private function define_widgets_hooks() {
		$this->loader->add_action( 'widgets_init', $this, 'register_widgets' );
	}

	/**
	 * Register widgets.
	 *
	 * @since 1.6.1
	 */
	public function register_widgets() {		
		register_widget( 'ACADP_Widget_Locations' );
		register_widget( 'ACADP_Widget_Categories' );
		register_widget( 'ACADP_Widget_Listings' );	
		register_widget( 'ACADP_Widget_Search' );
		register_widget( 'ACADP_Widget_Listing_Address' );	
		register_widget( 'ACADP_Widget_Listing_Contact' );
		register_widget( 'ACADP_Widget_Listing_Video' );		
	}
	
	/**
	 * Map meta caps to primitive caps
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_meta_caps() {
		$plugin_roles = new ACADP_Roles();
		$this->loader->add_filter( 'map_meta_cap', $plugin_roles, 'meta_caps', 10, 4 );
	}
	
	/**
	 * Define CRON Jobs for this plugin.
	 *
	 * @since  1.0.0
	 * @access private	 
	 */
	private function set_cron() {	
		$plugin_cron = new ACADP_Cron();
		
		$this->loader->add_action( 'wp', $plugin_cron, 'schedule_events' );
		$this->loader->add_action( 'acadp_hourly_scheduled_events', $plugin_cron, 'hourly_scheduled_events' );	
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return ACADP_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

}
