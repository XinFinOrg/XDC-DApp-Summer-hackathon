<?php

/**
 * Initialize Premium Features.
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
 * ACADP_Premium_Init - The premium class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Init {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *	 
	 * @since  1.6.4
	 * @access protected
	 * @var    ACADP_Loader
	 */
	protected $loader;

	/**
	 * Get things started.
	 *
	 * @since 1.6.4
	 */
	public function __construct() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/admin.php';		
		require_once ACADP_PLUGIN_DIR . 'premium/public/public.php';
		require_once ACADP_PLUGIN_DIR . 'premium/includes/functions.php';

		// Loader
		$this->loader = new ACADP_Loader();

		// Admin
		$admin = new ACADP_Premium_Admin();

		$this->loader->add_action( 'wp_loaded', $admin, 'wp_loaded' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		// Public
		$public = new ACADP_Premium_Public();

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'register_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'register_scripts' );

		// Modules
		$this->init_woocommerce_plans();
		$this->init_fee_plans();
		$this->init_paypal();
		$this->init_stripe();		
		$this->init_slider();
		$this->init_multi_categories();
		$this->init_import_export();
		
		//Mel: 27/12/21
		$this->init_bitcoin();
		
		//Mel: 03/01/22
		$this->init_ethereum();
		
	}

	/**
	 * Initialize WooCommerce Plans.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	private function init_woocommerce_plans() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/woocommerce-plans.php';	
		require_once ACADP_PLUGIN_DIR . 'premium/public/woocommerce-plans.php';

		$wc_plans_settings = get_option( 'acadp_wc_plans_settings', array() );

		// Admin
		$admin_woocommerce_plans = new ACADP_Premium_Admin_Woocommerce_plans();

		$this->loader->add_filter( 'acadp_register_settings_sections', $admin_woocommerce_plans, 'register_settings_section' );
		$this->loader->add_filter( 'acadp_register_settings_fields', $admin_woocommerce_plans, 'register_settings_fields' );
		
		if ( acadp_premium_is_woocommerce_active() && ! empty( $wc_plans_settings['enabled'] ) ) {
			add_action( 'plugins_loaded', 'acadp_premium_woocommerce_register_product_type' );
			
			$this->loader->add_action( 'add_meta_boxes', $admin_woocommerce_plans, 'add_meta_boxes' );
			$this->loader->add_action( 'wp_ajax_acadp_listings_wc_plans', $admin_woocommerce_plans, 'ajax_callback_wc_plans' );
			$this->loader->add_action( 'save_post', $admin_woocommerce_plans, 'save_plans', 11, 2 );	

			$this->loader->add_action( 'woocommerce_product_options_general_product_data', $admin_woocommerce_plans, 'display_custom_fields' );
			$this->loader->add_action( 'woocommerce_process_product_meta', $admin_woocommerce_plans, 'save_custom_fields', 11, 2 );

			$this->loader->add_filter( 'product_type_selector', $admin_woocommerce_plans, 'product_type_selector' );

			// Public
			$public_woocommerce_plans = new ACADP_Premium_Public_Woocommerce_Plans();

			$this->loader->add_action( 'wp_loaded', $public_woocommerce_plans, 'manage_actions' );
			$this->loader->add_action( 'init', $public_woocommerce_plans, 'add_rewrites' );
			$this->loader->add_action( 'init', $public_woocommerce_plans, 'add_remove_shortcodes' );
			$this->loader->add_action( 'acadp_after_user_dashboard_content', $public_woocommerce_plans, 'user_dashboard' );	

			$this->loader->add_action( 'woocommerce_listings_package_add_to_cart', $public_woocommerce_plans, 'add_to_cart', 30 );
			$this->loader->add_action( 'woocommerce_after_order_notes', $public_woocommerce_plans, 'checkout_field' );
			$this->loader->add_action( 'woocommerce_checkout_order_processed', $public_woocommerce_plans, 'checkout_order_processed' );
			$this->loader->add_action( 'woocommerce_checkout_subscription_created', $public_woocommerce_plans, 'checkout_subscription_created', 10, 2 );
			$this->loader->add_action( 'woocommerce_order_status_completed', $public_woocommerce_plans, 'order_status_completed' );
			$this->loader->add_action( 'woocommerce_subscription_status_active', $public_woocommerce_plans, 'subscription_status_active' );
			$this->loader->add_action( 'woocommerce_before_thankyou', $public_woocommerce_plans, 'before_thankyou' );
			$this->loader->add_action( 'woocommerce_my_account_my_orders_column_acadp_details', $public_woocommerce_plans, 'orders_custom_column_content' );
			$this->loader->add_action( 'woocommerce_my_subscriptions_after_subscription_id', $public_woocommerce_plans, 'subscriptions_custom_content' );
			
			$this->loader->add_filter( 'acadp_new_listing_status', $public_woocommerce_plans, 'new_listing_status' );
			$this->loader->add_filter( 'acadp_has_checkout_page', $public_woocommerce_plans, 'has_checkout_page', 10, 3 );
			$this->loader->add_filter( 'acadp_listing_duration', $public_woocommerce_plans, 'listing_duration', 10, 2 );
			$this->loader->add_filter( 'acadp_has_featured', $public_woocommerce_plans, 'has_featured' );
			$this->loader->add_filter( 'acadp_can_promote', $public_woocommerce_plans, 'can_promote' );
			$this->loader->add_filter( 'acadp_listing_form_categories_dropdown', $public_woocommerce_plans, 'listing_form_categories_dropdown', 11, 2 );
			$this->loader->add_filter( 'acadp_images_limit', $public_woocommerce_plans, 'images_limit', 10, 2 );

			$this->loader->add_filter( 'woocommerce_my_account_my_orders_columns', $public_woocommerce_plans, 'get_orders_columns', 10, 1 );
			$this->loader->add_filter( 'woocommerce_is_sold_individually', $public_woocommerce_plans, 'is_sold_individually', 10, 2 );
			$this->loader->add_filter( 'woocommerce_is_purchasable', $public_woocommerce_plans, 'is_purchasable', 10, 2 );
			$this->loader->add_filter( 'woocommerce_order_item_needs_processing', $public_woocommerce_plans, 'order_item_needs_processing', 999, 2 );
		}
	}

	/**
	 * Initialize Fee Plans.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	private function init_fee_plans() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/fee-plans.php';	
		require_once ACADP_PLUGIN_DIR . 'premium/public/fee-plans.php';

		$fee_plans_settings = get_option( 'acadp_fee_plans_settings', array() );
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings', array() );

		// Admin
		$admin_fee_plans = new ACADP_Premium_Admin_Fee_plans();

		$this->loader->add_filter( 'acadp_register_settings_sections', $admin_fee_plans, 'register_settings_section' );
		$this->loader->add_filter( 'acadp_register_settings_fields', $admin_fee_plans, 'register_settings_fields' );

		if ( ! empty( $fee_plans_settings['enabled'] ) ) {
			$this->loader->add_action( 'init', $admin_fee_plans, 'register_custom_post_type', 11 );
			$this->loader->add_action( 'admin_menu', $admin_fee_plans, 'admin_menu', 99 );

			$this->loader->add_action( 'add_meta_boxes', $admin_fee_plans, 'add_meta_boxes_fee_details' );	
			$this->loader->add_action( 'save_post', $admin_fee_plans, 'save_meta_data', 10, 2 );			
			$this->loader->add_action( 'manage_acadp_fee_plans_posts_custom_column', $admin_fee_plans, 'custom_column_content', 10, 2 );

			$this->loader->add_action( 'add_meta_boxes', $admin_fee_plans, 'add_meta_boxes_listings_fee_plans' );
			$this->loader->add_action( 'wp_ajax_acadp_listings_fee_plans', $admin_fee_plans, 'ajax_callback_fee_plans' );
			$this->loader->add_action( 'save_post', $admin_fee_plans, 'save_fee_plan', 9, 2 );

			$this->loader->add_filter( 'parent_file', $admin_fee_plans, 'parent_file' );
			$this->loader->add_filter( 'enter_title_here', $admin_fee_plans, 'change_default_title' );			
			$this->loader->add_filter( 'manage_edit-acadp_fee_plans_columns', $admin_fee_plans, 'get_columns' );

			if ( empty( $multi_categories_settings['enabled'] ) ) {
				$this->loader->add_action( 'restrict_manage_posts', $admin_fee_plans, 'restrict_manage_posts' );
				$this->loader->add_action( 'parse_tax_query', $admin_fee_plans, 'parse_tax_query' );

				$this->loader->add_filter( 'parse_query', $admin_fee_plans, 'parse_query' );
			}

			// Public
			$public_fee_plans = new ACADP_Premium_Public_Fee_Plans();

			$this->loader->add_action( 'acadp_order_created', $public_fee_plans, 'order_created' );
			$this->loader->add_action( 'acadp_order_completed', $public_fee_plans, 'order_completed' );

			$this->loader->add_filter( 'acadp_listing_form_categories_dropdown', $public_fee_plans, 'listing_form_categories_dropdown', 11, 2 );
			$this->loader->add_filter( 'acadp_new_listing_status', $public_fee_plans, 'new_listing_status' );
			$this->loader->add_filter( 'acadp_has_checkout_page', $public_fee_plans, 'has_checkout_page', 10, 3 );
			$this->loader->add_filter( 'acadp_checkout_form_data', $public_fee_plans, 'checkout_form_data', 10, 2 );
			$this->loader->add_filter( 'acadp_listing_duration', $public_fee_plans, 'listing_duration', 10, 2 );
			$this->loader->add_filter( 'acadp_order_details', $public_fee_plans, 'order_details', 10, 2 );
			$this->loader->add_filter( 'acadp_order_status_changed', $public_fee_plans, 'order_status_changed', 10, 3 );
		}
	}
	
	/**
	 * Initialize PayPal.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	private function init_paypal() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/paypal.php';	
		require_once ACADP_PLUGIN_DIR . 'premium/public/paypal.php';

		// Admin
		$admin_paypal = new ACADP_Premium_Admin_PayPal();

		$this->loader->add_action( 'acadp_payment_gateways', $admin_paypal, 'register_gateway' );
		$this->loader->add_filter( 'acadp_register_settings_sections', $admin_paypal, 'register_settings_section' );
		$this->loader->add_filter( 'acadp_register_settings_fields', $admin_paypal, 'register_settings_fields' );

		// Public
		$public_paypal = new ACADP_Premium_Public_PayPal();

		$this->loader->add_action( 'parse_request', $public_paypal, 'parse_request' );
		$this->loader->add_action( 'acadp_process_paypal_payment', $public_paypal, 'process_payment' );
	}
	
	/**Mel: 27/12/21
	 * Initialize Bitcoin.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	private function init_bitcoin() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/bitcoin.php';	
		require_once ACADP_PLUGIN_DIR . 'premium/public/bitcoin.php';

		// Admin
		$admin_bitcoin = new ACADP_Premium_Admin_Bitcoin();

		$this->loader->add_action( 'acadp_payment_gateways', $admin_bitcoin, 'register_gateway' );
		$this->loader->add_filter( 'acadp_register_settings_sections', $admin_bitcoin, 'register_settings_section' );
		$this->loader->add_filter( 'acadp_register_settings_fields', $admin_bitcoin, 'register_settings_fields' );

		// Public
		$public_bitcoin = new ACADP_Premium_Public_Bitcoin();

		//$this->loader->add_action( 'parse_request', $public_bitcoin, 'parse_request' );
		$this->loader->add_action( 'acadp_process_bitcoin_payment', $public_bitcoin, 'process_payment' );
	}
	
	/**Mel: 03/01/22
	 * Initialize Ethereum.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	private function init_ethereum() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/ethereum.php';	
		require_once ACADP_PLUGIN_DIR . 'premium/public/ethereum.php';

		// Admin
		$admin_ethereum = new ACADP_Premium_Admin_Ethereum();

		$this->loader->add_action( 'acadp_payment_gateways', $admin_ethereum, 'register_gateway' );
		$this->loader->add_filter( 'acadp_register_settings_sections', $admin_ethereum, 'register_settings_section' );
		$this->loader->add_filter( 'acadp_register_settings_fields', $admin_ethereum, 'register_settings_fields' );

		// Public
		$public_ethereum = new ACADP_Premium_Public_Ethereum();

		$this->loader->add_action( 'acadp_process_ethereum_payment', $public_ethereum, 'process_payment' );
	}

	/**
	 * Initialize Stripe.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	private function init_stripe() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/stripe.php';	
		require_once ACADP_PLUGIN_DIR . 'premium/public/stripe.php';

		// Admin
		$admin_stripe = new ACADP_Premium_Admin_Stripe();

		$this->loader->add_action( 'acadp_payment_gateways', $admin_stripe, 'register_gateway' );
		$this->loader->add_filter( 'acadp_register_settings_sections', $admin_stripe, 'register_settings_section' );
		$this->loader->add_filter( 'acadp_register_settings_fields', $admin_stripe, 'register_settings_fields' );

		// Public
		$public_stripe = new ACADP_Premium_Public_Stripe();

		$this->loader->add_action( 'acadp_before_checkout_form', $public_stripe, 'enqueue_styles_scripts' );
		$this->loader->add_action( 'wp_ajax_acadp_cc_form_stripe', $public_stripe, 'cc_form' );
		$this->loader->add_action( 'wp_ajax_nopriv_acadp_cc_form_stripe', $public_stripe, 'cc_form' );
		$this->loader->add_action( 'acadp_process_stripe_payment', $public_stripe, 'process_payment' );
	}

	/**
	 * Initialize Slider.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	private function init_slider() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/slider.php';	
		require_once ACADP_PLUGIN_DIR . 'premium/public/slider.php';			
		require_once ACADP_PLUGIN_DIR . 'premium/widgets/banner-rotator.php';
		require_once ACADP_PLUGIN_DIR . 'premium/widgets/carousel-slider.php';

		// Admin
		$admin_slider = new ACADP_Premium_Admin_Slider();

		$this->loader->add_filter( 'acadp_shortcode_fields', $admin_slider, 'register_shortcode_fields' );

		// Public
		$public_slider = new ACADP_Premium_Public_Slider();

		// Widgets
		$this->loader->add_action( 'widgets_init', $this, 'register_widgets' );
	}

	/**
	 * Initialize Multi Categories.
	 *
	 * @since  1.6.5
	 * @access private
	 */
	private function init_multi_categories() {
		// Dependencies
		if ( ! function_exists( 'wp_terms_checklist' ) ) {	
			include ABSPATH . 'wp-admin/includes/template.php';		
		}

		require_once ACADP_PLUGIN_DIR . 'premium/includes/class-acadp-walker-category-checklist.php';
		require_once ACADP_PLUGIN_DIR . 'premium/admin/multi-categories.php';
		require_once ACADP_PLUGIN_DIR . 'premium/public/multi-categories.php';

		$multi_categories_settings = get_option( 'acadp_multi_categories_settings', array() );

		// Admin
		$admin_multi_categories = new ACADP_Premium_Admin_Multi_Categories();

		$this->loader->add_filter( 'acadp_register_settings_sections', $admin_multi_categories, 'register_settings_section' );
		$this->loader->add_filter( 'acadp_register_settings_fields', $admin_multi_categories, 'register_settings_fields' );

		if ( ! empty( $multi_categories_settings['enabled'] ) ) {
			$this->loader->add_filter( 'acadp_admin_listing_form_categories_dropdown', $admin_multi_categories, 'listing_form_categories_dropdown', 10, 2 );

			// Public
			$public_multi_categories = new ACADP_Premium_Public_Multi_Categories();

			$this->loader->add_filter( 'acadp_listing_form_categories_dropdown', $public_multi_categories, 'listing_form_categories_dropdown', 10, 2 );
			$this->loader->add_filter( 'acadp_custom_fields_tax_queries', $public_multi_categories, 'custom_fields_tax_queries', 10, 2 );
		}
	}

	/**
	 * Initialize CSV Import/Export.
	 *
	 * @since  1.7.5
	 * @access private
	 */
	private function init_import_export() {
		// Dependencies
		require_once ACADP_PLUGIN_DIR . 'premium/admin/import-export.php';	

		// Admin
		$admin_import_export = new ACADP_Premium_Admin_Import_Export();

		$this->loader->add_action( 'admin_init', $admin_import_export, 'admin_init' );
		$this->loader->add_action( 'admin_menu', $admin_import_export, 'admin_menu', 99 );		
		$this->loader->add_action( 'wp_ajax_acadp_import', $admin_import_export, 'ajax_callback_import' );
	}
	
	/**
	 * Register widgets.
	 *
	 * @since 1.6.4
	 */
	public function register_widgets() {
		register_widget( 'ACADP_Widget_Banner_Rotator' );
		register_widget( 'ACADP_Widget_Carousel_Slider' );
	}	

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.6.4
	 */
	public function run() {
		$this->loader->run();
	}

}
