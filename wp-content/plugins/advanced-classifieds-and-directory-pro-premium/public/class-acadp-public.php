<?php

/**
 * The public-facing functionality of the plugin.
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
 * ACADP_Public Class.
 *
 * @since 1.0.0
 */
class ACADP_Public {

	/**
	 * Always keep using our Custom Pages for ACADP Post Types & Taxonomies.
	 *
	 * @since 1.5.0
	 */
	public function template_redirect() {	
		$redirect_url = '';
		
		if ( ! is_feed() ) {		
			// If ACADP Listings Page
			if ( is_post_type_archive( 'acadp_listings' ) ) {			
				$redirect_url = acadp_get_listings_page_link();				
			}

			// If ACADP Locations Page
			elseif ( is_tax( 'acadp_locations' ) ) {			
				$term = get_queried_object();
				$redirect_url = acadp_get_location_page_link( $term );				
			}	
			 
			// If ACADP Categories Page
			elseif ( is_tax( 'acadp_categories' ) ) {			
				$term = get_queried_object();
				$redirect_url = acadp_get_category_page_link( $term );				
			}
			
			// If other custom ACADP pages those require login
			elseif ( ! is_user_logged_in() ) {
				global $post;								
				if ( ! isset( $post ) ) return;
		
				$page_settings = get_option( 'acadp_page_settings' );
				$registration_settings = get_option( 'acadp_registration_settings', array() );
				
				$user_only_pages = array(
					$page_settings['user_dashboard'],
					$page_settings['listing_form'],
					$page_settings['manage_listings'],
					$page_settings['favourite_listings'],
					$page_settings['checkout'],
					$page_settings['payment_receipt'],
					$page_settings['payment_history']
				);
				
				if ( in_array( $post->ID, $user_only_pages ) && ! empty( $registration_settings['engine'] ) && 'others' == $registration_settings['engine'] ) {					
					if ( ! filter_var( $registration_settings['custom_login'], FILTER_VALIDATE_URL ) === FALSE ) {
						$redirect_url = $registration_settings['custom_login'];
					}					
				}
			}		
		}

		// Redirect
		if ( ! empty( $redirect_url ) ) {		
			wp_redirect( $redirect_url );
        	exit();			
		}	
	}
	
	/**
	 * Output buffer.
	 *
	 * @since 1.0.0
	 */
	public function output_buffer() {	
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {		
			if ( ( isset( $_POST['acadp_listing_nonce'] )  && wp_verify_nonce( $_POST['acadp_listing_nonce'], 'acadp_save_listing' ) ) ||
				( isset( $_POST['acadp_checkout_nonce'] ) && wp_verify_nonce( $_POST['acadp_checkout_nonce'], 'acadp_process_payment' ) ) ) {
				ob_start();
			}			
		}	
	}
	
	/**
	 * Add rewrite rules.
	 *
	 * @since 1.0.0
	 */
	public function add_rewrites() {
		$page_settings = get_option( 'acadp_page_settings' );
		$url = home_url();
		
		// Listings Page
		$id = $page_settings['listings'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/page/?([0-9]{1,})/?$", 'index.php?page_id='.$id.'&paged=$matches[1]', 'top' );
		}
		
		// Single Location Page
		$id = $page_settings['location'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_location=$matches[1]&paged=$matches[2]', 'top' );
			add_rewrite_rule( "$link/([^/]+)/?$", 'index.php?page_id='.$id.'&acadp_location=$matches[1]', 'top' );
		}
		
		// Single Category Page
		$id = $page_settings['category'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_category=$matches[1]&paged=$matches[2]', 'top' );
			add_rewrite_rule( "$link/([^/]+)/?$", 'index.php?page_id='.$id.'&acadp_category=$matches[1]', 'top' );
		}

		// User Listings Page
		$id = $page_settings['user_listings'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_user=$matches[1]&paged=$matches[2]', 'top' );
			add_rewrite_rule( "$link/([^/]+)/?$", 'index.php?page_id='.$id.'&acadp_user=$matches[1]', 'top' );
		}
		
		// Listings Edit, Delete (or) Renew Pages
		$id = $page_settings['listing_form'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/([^/]+)/([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_action=$matches[1]&acadp_listing=$matches[2]', 'top' );
		}
		
		// Remove from Favourites Page
		$id = $page_settings['favourite_listings'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/([^/]+)/([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_action=$matches[1]&acadp_listing=$matches[2]', 'top' );
		}
		
		// Checkout page
		$id = $page_settings['checkout'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/submission/([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_action=submission&acadp_listing=$matches[1]', 'top' );
			add_rewrite_rule( "$link/promote/([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_action=promote&acadp_listing=$matches[1]', 'top' );
			add_rewrite_rule( "$link/([^/]+)/([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_action=$matches[1]&acadp_order=$matches[2]', 'top' );
		}
		
		// Payment success page
		$id = $page_settings['payment_receipt'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/order/([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_action=order&acadp_order=$matches[1]', 'top' );
		}
		
		// Payment failure page
		$id = $page_settings['payment_failure'];
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/order/([0-9]{1,})/?$", 'index.php?page_id='.$id.'&acadp_action=order&acadp_order=$matches[1]', 'top' );
		}
		
		// Rewrite tags
		add_rewrite_tag( '%acadp_location%', '([^/]+)' );
		add_rewrite_tag( '%acadp_category%', '([^/]+)' );
		add_rewrite_tag( '%acadp_user%', '([^/]+)' );
		add_rewrite_tag( '%acadp_listing%', '([0-9]{1,})' );
		add_rewrite_tag( '%acadp_action%', '([^/]+)' );
		add_rewrite_tag( '%acadp_order%', '([0-9]{1,})' );	
	}
	
	/**
	 * Flush rewrite rules when it's necessary.
	 *
	 * @since 1.0.0
	 */
	 public function maybe_flush_rules() {
		$rewrite_rules = get_option( 'rewrite_rules' );
				
		if ( $rewrite_rules ) {		
			global $wp_rewrite;
			
			foreach ( $rewrite_rules as $rule => $rewrite ) {
				$rewrite_rules_array[$rule]['rewrite'] = $rewrite;
			}
			$rewrite_rules_array = array_reverse( $rewrite_rules_array, true );
		
			$maybe_missing = $wp_rewrite->rewrite_rules();
			$missing_rules = false;		
		
			foreach ( $maybe_missing as $rule => $rewrite ) {
				if ( ! array_key_exists( $rule, $rewrite_rules_array ) ) {
					$missing_rules = true;
					break;
				}
			}
		
			if ( true === $missing_rules ) {
				flush_rewrite_rules();
			}		
		}	
	}
	 
	/**
	 * Registers and enqueues the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function register_enqueue_styles() {
		$general_settings = get_option( 'acadp_general_settings' );
		$map_settings     = get_option( 'acadp_map_settings' );
		
		$deps = array();		

		wp_register_style( 
			ACADP_PLUGIN_NAME . '-slick', 
			ACADP_PLUGIN_URL . 'vendor/slick/slick.css', 
			array(), 
			'1.6.0', 
			'all' 
		);

		wp_register_style( 
			ACADP_PLUGIN_NAME . '-magnific-popup', 
			ACADP_PLUGIN_URL . 'vendor/magnific-popup/magnific-popup.css', 
			array(), 
			'1.1.0', 
			'all' 
		);
		
		if ( isset( $general_settings['load_bootstrap'] ) && in_array( 'css' , $general_settings['load_bootstrap'] ) ) {
			wp_register_style( 
				ACADP_PLUGIN_NAME . '-bootstrap', 
				ACADP_PLUGIN_URL . 'vendor/bootstrap/bootstrap.css', 
				array(), 
				'3.3.5', 
				'all' 
			);

			$deps[] = ACADP_PLUGIN_NAME . '-bootstrap';
		}
		
		wp_register_style( 
			ACADP_PLUGIN_NAME, 
			ACADP_PLUGIN_URL . 'public/css/public.css', 
			$deps, 
			ACADP_VERSION_NUM, 
			'all' 
		);

		if ( 'osm' == $map_settings['service'] ) {
			wp_register_style( 
				ACADP_PLUGIN_NAME . '-map', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/leaflet.css', 
				array(), 
				'1.7.1', 
				'all' 
			);

			wp_register_style( 
				ACADP_PLUGIN_NAME . '-markerclusterer-core', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/MarkerCluster.css', 
				array( ACADP_PLUGIN_NAME . '-map' ), 
				'1.4.1', 
				'all' 
			);

			wp_register_style( 
				ACADP_PLUGIN_NAME . '-markerclusterer', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/MarkerCluster.Default.css', 
				array( ACADP_PLUGIN_NAME . '-markerclusterer-core' ), 
				'1.4.1', 
				'all' 
			);
		}
		
		// Enqueue style dependencies
		if ( is_singular('acadp_listings') ) {
			if ( ! empty( $general_settings['has_images'] ) ) {
				wp_enqueue_style( ACADP_PLUGIN_NAME . '-slick' );
				wp_enqueue_style( ACADP_PLUGIN_NAME . '-magnific-popup' );
			}

			wp_enqueue_style( ACADP_PLUGIN_NAME );

			if ( wp_style_is( ACADP_PLUGIN_NAME . '-map', 'registered' ) ) {
				wp_enqueue_style( ACADP_PLUGIN_NAME . '-map' );				
			}
		}
	}

	/**
	 * Registers and enqueues javascript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function register_enqueue_scripts() {	
		$general_settings   = get_option( 'acadp_general_settings' );
		$map_settings       = get_option( 'acadp_map_settings' );
		$recaptcha_settings = get_option( 'acadp_recaptcha_settings' );

		wp_register_script( 
			ACADP_PLUGIN_NAME . '-slick', 
			ACADP_PLUGIN_URL . 'vendor/slick/slick.min.js', 
			array( 'jquery' ), 
			'1.6.0', 
			true 
		);

		wp_register_script( 
			ACADP_PLUGIN_NAME . '-magnific-popup', 
			ACADP_PLUGIN_URL . 'vendor/magnific-popup/jquery.magnific-popup.min.js', 
			array( 'jquery' ), 
			'1.1.0', 
			true 
		);
		
		if ( isset( $general_settings['load_bootstrap'] ) && in_array( 'javascript' , $general_settings['load_bootstrap'] ) ) {
			wp_register_script( 
				ACADP_PLUGIN_NAME . '-bootstrap', 
				ACADP_PLUGIN_URL . 'vendor/bootstrap/bootstrap.min.js', 
				array( 'jquery' ), 
				'3.3.5', 
				true 
			);
		}
		
		wp_register_script( 
			ACADP_PLUGIN_NAME . '-validator', 
			ACADP_PLUGIN_URL . 'vendor/validator.min.js', 
			array( 'jquery' ), 
			'0.9.0', 
			true 
		);
		
		wp_register_script( 
			ACADP_PLUGIN_NAME, 
			ACADP_PLUGIN_URL . 'public/js/public.js', 
			array( 'jquery' ), 
			ACADP_VERSION_NUM, 
			true 
		);
		
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
		
		wp_localize_script( ACADP_PLUGIN_NAME, 'acadp', array(
				'is_rtl'                       => is_rtl(),
				'plugin_url'                   => ACADP_PLUGIN_URL,
				'ajax_url'                     => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'                   => wp_create_nonce( 'acadp_ajax_nonce' ),
				'maximum_images_per_listing'   => $general_settings['maximum_images_per_listing'],
				'map_service'                  => $map_settings['service'],
				'snap_to_user_location'        => ! empty( $map_settings['snap_to_user_location'] ) ? true : false,
				'zoom_level'                   => $map_settings['zoom_level'],
				'recaptcha_registration'       => $recaptcha_registration,
				'recaptcha_site_key'           => $recaptcha_site_key,				
				'recaptcha_listing'            => $recaptcha_listing,
				'recaptcha_contact'            => $recaptcha_contact,
				'recaptcha_report_abuse'       => $recaptcha_report_abuse,
				'recaptchas'                   => array( 'registration' => 0, 'listing' => 0, 'contact' => 0, 'report_abuse' => 0 ),
				'recaptcha_invalid_message'    => __( "You can't leave Captcha Code empty", 'advanced-classifieds-and-directory-pro' ),
				'user_login_alert_message'     => __( 'Sorry, you need to login first.', 'advanced-classifieds-and-directory-pro' ),				
				'upload_limit_alert_message'   => __( 'Sorry, you have only %d images pending.', 'advanced-classifieds-and-directory-pro' ),
				'delete_label'                 => __( 'Delete Permanently', 'advanced-classifieds-and-directory-pro' ),
				'proceed_to_payment_btn_label' => __( 'Proceed to payment', 'advanced-classifieds-and-directory-pro' ),
				'finish_submission_btn_label'  => __( 'Finish submission', 'advanced-classifieds-and-directory-pro' )				
			)
		);
		
		if ( 'osm' == $map_settings['service'] ) {
			wp_register_script( 
				ACADP_PLUGIN_NAME . '-map', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/leaflet.js', 
				array( ACADP_PLUGIN_NAME ), 
				'1.7.1', 
				true 
			);

			wp_register_script( 
				ACADP_PLUGIN_NAME . '-markerclusterer', 
				ACADP_PLUGIN_URL . 'vendor/leaflet/leaflet.markercluster.js', 
				array( ACADP_PLUGIN_NAME . '-map' ), 
				'1.4.1', 
				true 
			);
		} else {
			$map_api_key = ! empty( $map_settings['api_key'] ) ? '&key=' . $map_settings['api_key'] : '';

			wp_register_script( 
				ACADP_PLUGIN_NAME . '-google-map', // An ugly fallback for custom plugins those use this handle
				'https://maps.googleapis.com/maps/api/js?v=3.exp' . $map_api_key, 
				array( ACADP_PLUGIN_NAME ), 
				'', 
				true 
			);

			wp_register_script( 
				ACADP_PLUGIN_NAME . '-map', 
				'https://maps.googleapis.com/maps/api/js?v=3.exp' . $map_api_key, 
				array( ACADP_PLUGIN_NAME ), 
				'', 
				true 
			);

			wp_register_script( 
				ACADP_PLUGIN_NAME . '-markerclusterer', 
				ACADP_PLUGIN_URL . 'vendor/markerclusterer/markerclusterer.js', 
				array( ACADP_PLUGIN_NAME . '-map' ), 
				'1.0.0', 
				true 
			);
		}
		
		wp_register_script( 
			ACADP_PLUGIN_NAME . '-recaptcha', 
			'https://www.google.com/recaptcha/api.js?onload=acadp_on_recaptcha_load&render=explicit', 
			array( ACADP_PLUGIN_NAME ), 
			'', 
			true 
		);
		
		// Enqueue script dependencies
		if ( is_singular('acadp_listings') ) {
			if ( ! empty( $general_settings['has_images'] ) ) {
				wp_enqueue_script( ACADP_PLUGIN_NAME . '-slick' );
				wp_enqueue_script( ACADP_PLUGIN_NAME . '-magnific-popup' );
			}

			if ( wp_script_is( ACADP_PLUGIN_NAME . '-bootstrap', 'registered' ) ) {
				wp_enqueue_script( ACADP_PLUGIN_NAME . '-bootstrap' );				
			}

			wp_enqueue_script( ACADP_PLUGIN_NAME . '-validator' );			
			wp_enqueue_script( ACADP_PLUGIN_NAME . '-map' );

			if ( $recaptcha_contact > 0 || $recaptcha_report_abuse > 0 ) {
				wp_enqueue_script( ACADP_PLUGIN_NAME . '-recaptcha' );
			}

			wp_enqueue_script( ACADP_PLUGIN_NAME );
		}		
	}
	
	/**
	 * Dequeue scripts.
	 *
	 * @since 1.5.6
	 */
	public function dequeue_scripts() {		
		$page_settings = get_option( 'acadp_page_settings' );
		
		if ( ( is_user_logged_in() && is_page( (int) $page_settings['listing_form'] ) ) || is_singular('acadp_listings') ) {
			wp_dequeue_script( 'recaptcha' );
		}	
	}
	
	/**		 
	 * Override the default post/page title depending on the ACADP view.
	 *		
	 * @since  1.5.5
	 * @param  string $title       The document title.	 
     * @param  string $sep         Title separator.
     * @param  string $seplocation Location of the separator (left or right).		 
	 * @return string              The filtered title.		 
	*/
	public function wp_title( $title, $sep, $seplocation ) {		
		global $post;
		
		if ( ! isset( $post ) ) {
			return $title;
		}
		
		$page_settings = get_option( 'acadp_page_settings' );
		$custom_title = '';
		$site_name = get_bloginfo( 'name' );
		
		// Get Location page title
		if ( $post->ID == $page_settings['location'] ) {		
			if ( $slug = get_query_var( 'acadp_location' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_locations' );
				$custom_title = $term->name;			
			}			
		}
		
		// Get Category page title
		if ( $post->ID == $page_settings['category'] ) {			
			if ( $slug = get_query_var( 'acadp_category' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_categories' );
				$custom_title = $term->name;			
			}				
		}

		// Get User Listings page title
		if ( $post->ID == $page_settings['user_listings'] ) {		
			if ( $slug = acadp_get_user_slug() ) {
				$user = get_user_by( 'slug', $slug );
				$custom_title = $user->display_name;		
			}			
		}
		
		// ...
		if ( ! empty( $custom_title ) ) {
			$title = ( 'left' == $seplocation ) ? "$site_name $sep $custom_title" : "$custom_title $sep $site_name";
		}
		
		return $title;		
	}
	
	/**
	 * Override the default post/page title depending on the ACADP view.
	 *
	 * @since  1.5.6
	 * @param  array $title The document title parts.
	 * @return              Filtered title parts.
	 */
	public function document_title_parts( $title ) {	
		global $post;
		
		if ( ! isset( $post ) ) {
			return $title;
		}
		
		$page_settings = get_option( 'acadp_page_settings' );
		
		// Get Category page title
		if ( $post->ID == $page_settings['category'] ) {			
			if ( $slug = get_query_var( 'acadp_category' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_categories' );
				$title['title'] = $term->name;			
			}				
		}
		
		// Get Location page title
		if ( $post->ID == $page_settings['location'] ) {		
			if ( $slug = get_query_var( 'acadp_location' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_locations' );
				$title['title'] = $term->name;			
			}			
		}
		
		// Get User Listings page title
		if ( $post->ID == $page_settings['user_listings'] ) {		
			if ( $slug = acadp_get_user_slug() ) {
				$user = get_user_by( 'slug', $slug );
				$title['title'] = $user->display_name;		
			}			
		}
		
		// ...
		return $title;	
	}

	/**
	 * Construct Yoast SEO title for our category, location & user_listings pages.
	 *
	 * @since  1.6.1
	 * @param  array $title The Yoast title.
	 * @return              Modified title.
	 */
	public function wpseo_title( $title ) {
		global $post;
		
		if ( ! isset( $post ) ) {
			return $title;
		}

		$page_settings = get_option( 'acadp_page_settings' );

		if ( $post->ID != $page_settings['category'] && $post->ID != $page_settings['location'] && $post->ID != $page_settings['user_listings'] ) {
			return $title;
		}

		$wpseo_titles = get_option( 'wpseo_titles' );

		$sep_options = WPSEO_Option_Titles::get_instance()->get_separator_options();

		if ( isset( $wpseo_titles['separator'] ) && isset( $sep_options[ $wpseo_titles['separator'] ] ) ) {
			$sep = $sep_options[ $wpseo_titles['separator'] ];
		} else {
			$sep = '-'; // Setting default separator if Admin didn't set it from backed
		}

		$replacements = array(
			'%%sep%%'              => $sep,						
			'%%page%%'             => '',
			'%%primary_category%%' => '',
			'%%sitename%%'         => get_bloginfo( 'name' )
		);

		$title_template = '';

		// Category page
		if ( $post->ID == $page_settings['category'] ) {			
			if ( $slug = get_query_var( 'acadp_category' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_categories' );			
				$replacements['%%term_title%%'] = $term->name;
				
				// Get Archive SEO title
				if ( array_key_exists( 'title-tax-acadp_categories', $wpseo_titles ) ) {
					$title_template = $wpseo_titles['title-tax-acadp_categories'];
				}				

				// Get Term SEO title
				$meta = get_option( 'wpseo_taxonomy_meta' );

				if ( array_key_exists( 'acadp_categories', $meta ) ) {
					if ( array_key_exists( $term->term_id, $meta['acadp_categories'] ) ) {
						if ( array_key_exists( 'wpseo_title', $meta['acadp_categories'][ $term->term_id ] ) ) {
							$title_template = $meta['acadp_categories'][ $term->term_id ]['wpseo_title'];
						}
					}
				}
			}				
		}

		// Location page
		if ( $post->ID == $page_settings['location'] ) {			
			if ( $slug = get_query_var( 'acadp_location' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_locations' );			
				$replacements['%%term_title%%'] = $term->name;
				
				// Get Archive SEO title
				if ( array_key_exists( 'title-tax-acadp_locations', $wpseo_titles ) ) {
					$title_template = $wpseo_titles['title-tax-acadp_locations'];
				}				

				// Get Term SEO title
				$meta = get_option( 'wpseo_taxonomy_meta' );

				if ( array_key_exists( 'acadp_locations', $meta ) ) {
					if ( array_key_exists( $term->term_id, $meta['acadp_locations'] ) ) {
						if ( array_key_exists( 'wpseo_title', $meta['acadp_locations'][ $term->term_id ] ) ) {
							$title_template = $meta['acadp_locations'][ $term->term_id ]['wpseo_title'];
						}
					}
				}
			}				
		}

		// User listings page
		if ( $post->ID == $page_settings['user_listings'] ) {		
			if ( $slug = acadp_get_user_slug() ) {
				$user = get_user_by( 'slug', $slug );
				$replacements['%%title%%'] = $user->display_name;
				
				// Get Archive SEO title
				if ( array_key_exists( 'title-page', $wpseo_titles ) ) {
					$title_template = $wpseo_titles['title-page'];
				}		
				
				// Get page meta title
				$meta = get_post_meta( $post->ID, '_yoast_wpseo_title', true );

				if ( ! empty( $meta ) ) {
					$title_template = $meta;
				}
			}			
		}

		// Return
		if ( ! empty( $title_template ) ) {
			$title = strtr( $title_template, $replacements );
		}

		return $title;
	}

	/**
	 * Construct Yoast SEO description for our category, location & user_listings pages.
	 *
	 * @since  1.6.1
	 * @param  array $desc The Yoast description.
	 * @return             Modified description.
	 */
	public function wpseo_metadesc( $desc ) {
		global $post;

		if ( ! isset( $post ) ) {
			return $desc;
		}

		$page_settings = get_option( 'acadp_page_settings' );

		if ( $post->ID != $page_settings['category'] && $post->ID != $page_settings['location'] && $post->ID != $page_settings['user_listings'] ) {
			return $desc;
		}

		$wpseo_titles = get_option( 'wpseo_titles' );

		$sep_options = WPSEO_Option_Titles::get_instance()->get_separator_options();

		if ( isset( $wpseo_titles['separator'] ) && isset( $sep_options[ $wpseo_titles['separator'] ] ) ) {
			$sep = $sep_options[ $wpseo_titles['separator'] ];
		} else {
			$sep = '-'; // Setting default separator if Admin didn't set it from backed
		}

		$replacements = array(
			'%%sep%%'              => $sep,						
			'%%page%%'             => '',
			'%%primary_category%%' => '',
			'%%sitename%%'         => get_bloginfo( 'name' )
		);

		$desc_template = '';

		// Category page
		if ( $post->ID == $page_settings['category'] ) {			
			if ( $slug = get_query_var( 'acadp_category' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_categories' );
				$replacements['%%term_title%%'] = $term->name;	
				
				// Get Archive SEO desc
				if ( array_key_exists( 'metadesc-tax-acadp_categories', $wpseo_titles ) ) {
					$desc_template = $wpseo_titles['metadesc-tax-acadp_categories'];
				}				

				// Get Term SEO desc
				$meta = get_option( 'wpseo_taxonomy_meta' );

				if ( array_key_exists( 'acadp_categories', $meta ) ) {
					if ( array_key_exists( $term->term_id, $meta['acadp_categories'] ) ) {
						if ( array_key_exists( 'wpseo_desc', $meta['acadp_categories'][ $term->term_id ] ) ) {
							$desc_template = $meta['acadp_categories'][ $term->term_id ]['wpseo_desc'];
						}
					}
				}
			}				
		}

		// Location page
		if ( $post->ID == $page_settings['location'] ) {			
			if ( $slug = get_query_var( 'acadp_location' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_locations' );
				$replacements['%%term_title%%'] = $term->name;	
				
				// Get Archive SEO desc
				if ( array_key_exists( 'metadesc-tax-acadp_locations', $wpseo_titles ) ) {
					$desc_template = $wpseo_titles['metadesc-tax-acadp_locations'];
				}				

				// Get Term SEO desc
				$meta = get_option( 'wpseo_taxonomy_meta' );

				if ( array_key_exists( 'acadp_locations', $meta ) ) {
					if ( array_key_exists( $term->term_id, $meta['acadp_locations'] ) ) {
						if ( array_key_exists( 'wpseo_desc', $meta['acadp_locations'][ $term->term_id ] ) ) {
							$desc_template = $meta['acadp_locations'][ $term->term_id ]['wpseo_desc'];
						}
					}
				}
			}				
		}

		// User listings page
		if ( $post->ID == $page_settings['user_listings'] ) {		
			if ( $slug = acadp_get_user_slug() ) {
				$user = get_user_by( 'slug', $slug );
				$replacements['%%title%%'] = $user->display_name;
				
				// Get Archive SEO desc				
				if ( array_key_exists( 'metadesc-page', $wpseo_titles ) ) {
					$desc_template = $wpseo_titles['metadesc-page'];
				}		
				
				// Get page meta desc
				$meta = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

				if ( ! empty( $meta ) ) {
					$desc_template = $meta;
				}
			}			
		}

		// Return
		if ( ! empty( $desc_template ) ) {
			$desc = strtr( $desc_template, $replacements );
		}

		return $desc;
	}

	/**
	 * Override the Yoast SEO canonical URL on our category, location & user_listings pages.
	 *
	 * @since  1.6.1
	 * @param  array $url The Yoast canonical URL.
	 * @return            Modified canonical URL.
	 */
	public function wpseo_canonical( $url ) {
		global $post;

		if ( ! isset( $post ) ) {
			return $url;
		}

		$page_settings = get_option( 'acadp_page_settings' );

		// Category page
		if ( $post->ID == $page_settings['category'] ) {			
			if ( $slug = get_query_var( 'acadp_category' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_categories' );
				$url = acadp_get_category_page_link( $term );
			}				
		}

		// Location page
		if ( $post->ID == $page_settings['location'] ) {			
			if ( $slug = get_query_var( 'acadp_location' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_locations' );
				$url = acadp_get_location_page_link( $term );
			}				
		}

		// User listings page
		if ( $post->ID == $page_settings['user_listings'] ) {		
			if ( $slug = acadp_get_user_slug() ) {
				$user = get_user_by( 'slug', $slug );
				$url = acadp_get_user_page_link( $user->ID );
			}			
		}

		return $url;
	}
	
	/**
	 * Adds the Facebook OG tags and Twitter Cards.
	 *
	 * @since 1.0.0
	 */
	public function og_metatags() {	
		global $post;
		
		if ( ! isset( $post ) ) {
			return;
		}
		
		$page_settings        = get_option( 'acadp_page_settings' );
		$socialshare_settings = get_option( 'acadp_socialshare_settings' );
			
		$page = '';
		if ( is_singular('acadp_listings') ) {			
			$page = 'listing';				
		} else {			
			if ( $page_settings['locations'] == $post->ID ) {
				$page = 'locations';
			}
			
			if ( $page_settings['categories'] == $post->ID ) {
				$page = 'categories';
			}
				
			if ( in_array( $post->ID, array( $page_settings['listings'], $page_settings['user_listings'], $page_settings['location'], $page_settings['category'], $page_settings['search'] ) ) ) {
				$page = 'listings';
			}				
		}
			
		if ( isset( $socialshare_settings['pages'] ) && in_array( $page, $socialshare_settings['pages'] ) ) {			
			$title = esc_html( get_the_title() );
			
			// Get Location page title
			if ( $post->ID == $page_settings['location'] ) {			
				if ( $slug = get_query_var( 'acadp_location' ) ) {
					$term = get_term_by( 'slug', $slug, 'acadp_locations' );
					$title = $term->name;			
				}				
			}
			
			// Get Category page title
			if ( $post->ID == $page_settings['category'] ) {			
				if ( $slug = get_query_var( 'acadp_category' ) ) {
					$term = get_term_by( 'slug', $slug, 'acadp_categories' );
					$title = $term->name;			
				}				
			}

			// Get User Listings page title
			if ( $post->ID == $page_settings['user_listings'] ) {			
				if ( $slug = acadp_get_user_slug() ) {
					$user = get_user_by( 'slug', $slug );
					$title = $user->display_name;		
				}				
			}
			
			echo '<meta property="og:url" content="' . acadp_get_current_url() . '" />';
			echo '<meta property="og:type" content="article" />';	
			echo '<meta property="og:title" content="' . $title . '" />';				
			if ( 'listing' == $page ) {
				if ( ! empty( $post->post_content ) ) {
					echo '<meta property="og:description" content="' . wp_trim_words( $post->post_content, 150 ) . '" />';
				}
					
				$images = get_post_meta( $post->ID, 'images', true );			
				if ( ! empty( $images ) ) { 
					$thumbnail = wp_get_attachment_image_src( $images[0], 'full' );
					if ( ! empty( $thumbnail ) ) echo '<meta property="og:image" content="' . $thumbnail[0] . '" />';
				}					
			}
			echo '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />';
			echo '<meta name="twitter:card" content="summary">';				
		}		
	}
	
	/**
	 * Force Specific Pages to load with the SSL Certificate.
	 *
	 * @since  1.0.0
	 * @param  boolean $force_ssl Whether to force SSL in current page.
	 * @param  int     $post_id   Page ID.
	 * @return boolean    		  True to force SSL, false if not.
	 */
	public function force_ssl_https( $force_ssl, $post_id ) {	
		$page_settings = get_option( 'acadp_page_settings' );
		
		if ( $post_id == ( int ) $page_settings['checkout'] ) {		
			$gateway_settings = get_option( 'acadp_gateway_settings' );
			
			if ( ! empty( $gateway_settings['use_https'] ) ) {
        		return true;
			}			
    	}
		
    	return $force_ssl;	
	}
	
	/**
	 * Change the current page title if applicable.
	 *
	 * @since  1.0.0
	 * @param  string $title Current page title.
	 * @param  int    $id    Post ID.
	 * @return string $title Modified page title.
	 */
	public function the_title( $title, $id = 0 ) {
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $title;
		}
		
		if ( is_singular('acadp_listings') ) {
			return '';
		}
		
		global $post, $wp_query;

		$post_id = $wp_query->get_queried_object_id();		
		if ( $id > 0 && $id != $post_id ) {
			return $title;
		}
		
		$page_settings = get_option( 'acadp_page_settings' );
		
		// Change Location page title
		if ( $post->ID == $page_settings['location'] ) {		
			if ( $slug = get_query_var( 'acadp_location' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_locations' );
				$title = $term->name;			
			}			
		}
		
		// Change Category page title
		if ( $post->ID == $page_settings['category'] ) {		
			if ( $slug = get_query_var( 'acadp_category' ) ) {
				$term = get_term_by( 'slug', $slug, 'acadp_categories' );
				$title = $term->name;			
			}			
		}
		
		// Change User Listings page title
		if ( $post->ID == $page_settings['user_listings'] ) {		
			if ( $slug = acadp_get_user_slug() ) {
				$user = get_user_by( 'slug', $slug );
				$title = $user->display_name;		
			}			
		}	
		
		// Change Search page title
		if ( $post->ID == $page_settings['search'] ) {		
			if ( isset( $_GET['q'] ) && ! empty( $_GET['q'] ) ) {
				$title = sprintf( __( 'You have searched for "%s"...', 'advanced-classifieds-and-directory-pro' ), sanitize_text_field( $_GET['q'] ) );			
			}			
		}		
		
		return $title;	
	}
	
	/**
	 * Outputs ACADP child terms dropdown.
	 *
	 * @since 1.5.5
	 */
	public function ajax_callback_dropdown_terms() {
		check_ajax_referer( 'acadp_ajax_nonce', 'security' );

		if ( isset( $_POST['taxonomy'] ) && isset( $_POST['parent'] ) ) {
			$args = array( 
				'taxonomy'  => sanitize_text_field( $_POST['taxonomy'] ),
				'base_term' => 0,
				'parent'    => (int) $_POST['parent']
			);
			
			if ( 'acadp_locations' == $args['taxonomy'] ) {
				$general_settings = get_option( 'acadp_general_settings' );
				$locations_settings = get_option( 'acadp_locations_settings' );
				
				$args['base_term'] = max( 0, $general_settings['base_location'] );
				$args['orderby'] = $locations_settings['orderby'];
				$args['order'] = $locations_settings['order'];
			}
			
			if ( 'acadp_categories' == $args['taxonomy'] ) {
				$categories_settings = get_option( 'acadp_categories_settings' );
				
				$args['orderby'] = $categories_settings['orderby'];
				$args['order'] = $categories_settings['order'];
			}
			
			if ( isset( $_POST['class'] ) && '' != trim( $_POST['class'] ) ) {
				$args['class'] = sanitize_text_field( $_POST['class'] );
			}
			
			if ( $args['parent'] != $args['base_term'] ) {
				ob_start();
				acadp_dropdown_terms( $args );
				$output = ob_get_clean();			
				print $output;
			}			
		}

		wp_die();	
	}

	/**
	 * Always use our custom page for ACADP locations and categories.
	 *
	 * @since  1.0.0
	 * @param  string $url      The term URL.
	 * @param  object $term     The term object.
	 * @param  string $taxonomy The taxonomy slug.
	 * @return string $url      Filtered term URL.
	 */
	public function term_link( $url, $term, $taxonomy ) {	
		// If ACADP Locations
		if ( 'acadp_locations' == $taxonomy ) {
			$url = acadp_get_location_page_link( $term );
		}

		// If ACADP Categories
		if ( 'acadp_categories' == $taxonomy ) {
			$url = acadp_get_category_page_link( $term );
		}
		
		return $url;		
	}

	/**
	 * Filters the 'acadp_general_settings' option.
	 *
	 * @since  1.7.7
	 * @param  array $general_settings General settings array.
	 * @return array $general_settings Filtered array of general settings.
	 */
	public function filter_general_settings( $general_settings ) {
		$badges_settings  = get_option( 'acadp_badges_settings', array() );
		$listing_settings = get_option( 'acadp_listing_settings', array() );

		if ( ! empty( $listing_settings ) ) {
			unset(
				$general_settings['show_new_tag'],
				$general_settings['new_listing_threshold'],
				$general_settings['new_listing_label'],
				$general_settings['show_popular_tag'],
				$general_settings['popular_listing_threshold'],
				$general_settings['popular_listing_label'],
				$general_settings['show_phone_number_publicly'],	
				$general_settings['show_email_address_publicly'],							
				$general_settings['has_contact_form'],
				$general_settings['contact_form_require_login'],
				$general_settings['has_comment_form'],	
				$general_settings['has_report_abuse'],
				$general_settings['has_favourites'],	
				$general_settings['display_options']
			);		

			if ( 'open' == $listing_settings['show_phone_number'] ) {
				$general_settings['show_phone_number_publicly'] = 1;
			}

			if ( 'public' == $listing_settings['show_email_address'] ) {
				$general_settings['show_email_address_publicly'] = 1;
			}

			return array_merge( $general_settings, $badges_settings, $listing_settings );
		}
		
		return $general_settings;		
	}

}
