<?php

/**
 * User
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
 * ACADP_Public_User Class.
 *
 * @since 1.0.0
 */
class ACADP_Public_User {

	/**
	 * Get things going.
	 *
	 * @since 1.0.0 
	 */ 
	public function __construct() {		
		// Register shortcodes used by the user page
		add_shortcode( "acadp_user_listings", array( $this, "run_shortcode_user_listings" ) );
		add_shortcode( "acadp_user_dashboard", array( $this, "run_shortcode_user_dashboard" ) );
		add_shortcode( "acadp_listing_form", array( $this, "run_shortcode_listing_form" ) );
		add_shortcode( "acadp_manage_listings", array( $this, "run_shortcode_manage_listings" ) );
		add_shortcode( "acadp_favourite_listings", array( $this, "run_shortcode_favourite_listings" ) );

		//Mel: 24/01/22
		add_shortcode( "acadp_contract_form", array( $this, "run_shortcode_contract_form" ) );
		add_shortcode( "acadp_contract_receipt", array( $this, "run_shortcode_contract_receipt" ) );
		add_shortcode( "acadp_contract_receipt", array( $this, "run_shortcode_contract_receipt" ) );
		add_shortcode( "acadp_mint_token", array( $this, "run_shortcode_mint_token" ) );
	}
	
	/**
	 * Manage form submissions.
	 *
	 * @since 1.0.0
	 */
	public function manage_actions() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['acadp_listing_nonce'] ) && wp_verify_nonce( $_POST['acadp_listing_nonce'], 'acadp_save_listing' ) ) {
			if ( isset( $_POST['post_id'] ) ) {
				if ( ! acadp_current_user_can('edit_acadp_listing', (int) $_POST['post_id']) ) {
					return;
				}				
			} else {			
				if ( ! acadp_current_user_can('edit_acadp_listings') ) {
					return;
				}
			
				if ( ! acadp_is_human('listing') ) {
					echo '<span>' . __( 'Invalid Captcha: Please try again.', 'advanced-classifieds-and-directory-pro' ) . '</span>';
					exit();
				}			
			}		
			
			$this->save_listing();		
		}	
	}

   /**
	 * Manage smart contract form submissions.
	 *
	 * @since 1.0.0
	 */
	public function manage_actions_contract() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['acadp_contract_nonce'] ) && wp_verify_nonce( $_POST['acadp_contract_nonce'], 'acadp_save_contract' ) ) {
			if ( isset( $_POST['post_id'] ) ) {
				if ( ! acadp_current_user_can('edit_acadp_listing', (int) $_POST['post_id']) ) {
					return;
				}				
			} else {			
				if ( ! acadp_current_user_can('edit_acadp_listings') ) {
					return;
				}
			
				if ( ! acadp_is_human('listing') ) {
					echo '<span>' . __( 'Invalid Captcha: Please try again.', 'advanced-classifieds-and-directory-pro' ) . '</span>';
					exit();
				}			
			}		
			
			$this->save_contract();		
		}	
	}
	
	/**
	 * Parse request to find correct WordPress query.
	 *
	 * @since 1.0.0
	 * @param WP_Query $wp WordPress Query object.
	 */
	public function parse_request( $wp ) {	
		if ( array_key_exists( 'acadp_action', $wp->query_vars ) && array_key_exists( 'acadp_listing', $wp->query_vars ) && (int) $wp->query_vars['acadp_listing'] > 0 ) {
			$id = (int) $wp->query_vars['acadp_listing'];
			
			if ( 'renew' == $wp->query_vars['acadp_action'] ) {
				if ( ! acadp_current_user_can('edit_acadp_listing', $id) ) {
					return;
				}
				
				$this->renew_listing( $id );
			}
			
			if ( 'delete' == $wp->query_vars['acadp_action'] ) {
				if ( isset( $_REQUEST['acadp_nonce'] ) && wp_verify_nonce( $_REQUEST['acadp_nonce'], 'acadp_delete_nonce' ) ) {
					if ( ! acadp_current_user_can( 'delete_acadp_listing', $id ) ) {
						return;
					}
					
					$this->delete_listing( $id );
				}
			}
			
			if ( 'remove-favourites' == $wp->query_vars['acadp_action'] ) {
				$this->remove_favourites( $id );
			}			
    	}		
	}
	
	/**
	 * Process the shortcode [acadp_user_listings].
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array  $atts An associative array of attributes.
	 */
	public function run_shortcode_user_listings( $atts ) {	
		$shortcode = 'acadp_user_listings';
		
		$user_slug = acadp_get_user_slug();
		if ( '' == $user_slug ) {
			if ( ! empty( $atts['id'] ) ) {
				$user_slug = get_the_author_meta( 'user_nicename', (int) $atts['id'] );	
			} elseif ( is_user_logged_in() ) {
				$user_slug = get_the_author_meta( 'user_nicename', get_current_user_id() );				
			}
		}
		
		if ( '' != $user_slug ) {		
			$general_settings = get_option( 'acadp_general_settings' );
			$listings_settings = get_option( 'acadp_listings_settings' );
			$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
			
			$atts = shortcode_atts( array(
				'view'              => $listings_settings['default_view'],
				'featured'          => 1,
				'filterby'          => '',
				'orderby'           => $listings_settings['orderby'],
				'order'             => $listings_settings['order'],
				'listings_per_page' => ! empty( $listings_settings['listings_per_page'] ) ? $listings_settings['listings_per_page'] : -1,
				'pagination'        => 1,
				'header'            => 1
			), $atts );
		
			$view = acadp_get_listings_current_view_name( $atts['view'] );
		
			// Enqueue style dependencies
			wp_enqueue_style( ACADP_PLUGIN_NAME );

			if ( 'map' == $view && wp_style_is( ACADP_PLUGIN_NAME . '-markerclusterer', 'registered' ) ) {
				wp_enqueue_style( ACADP_PLUGIN_NAME . '-markerclusterer' );				
			}
		
			// Enqueue script dependencies
			if ( 'map' == $view ) {
				wp_enqueue_script( ACADP_PLUGIN_NAME . '-markerclusterer' );
				wp_enqueue_script( ACADP_PLUGIN_NAME );
			}
		
			// ...
			$can_show_header           = empty( $listings_settings['display_in_header'] ) ? 0 : (int) $atts['header'];
			$pre_content               = '';
			$can_show_listings_count   = $can_show_header && in_array( 'listings_count', $listings_settings['display_in_header'] ) ? true : false;
			$can_show_views_selector   = $can_show_header && in_array( 'views_selector', $listings_settings['display_in_header'] ) ? true : false;
			$can_show_orderby_dropdown = $can_show_header && in_array( 'orderby_dropdown', $listings_settings['display_in_header'] ) ? true : false;
					
			$can_show_date          = isset( $listings_settings['display_in_listing'] ) && in_array( 'date', $listings_settings['display_in_listing'] ) ? true : false;
			$can_show_user          = isset( $listings_settings['display_in_listing'] ) && in_array( 'user', $listings_settings['display_in_listing'] ) ? true : false;
			$can_show_category      = isset( $listings_settings['display_in_listing'] ) && in_array( 'category', $listings_settings['display_in_listing'] ) ? true : false;
			$can_show_views         = isset( $listings_settings['display_in_listing'] ) && in_array( 'views', $listings_settings['display_in_listing'] ) ? true : false;
			$can_show_custom_fields = isset( $listings_settings['display_in_listing'] ) && in_array( 'custom_fields', $listings_settings['display_in_listing'] ) ? true : false;

			$can_show_images = empty( $general_settings['has_images'] ) ? false : true;			
			
			$has_featured = apply_filters( 'acadp_has_featured', empty( $featured_listing_settings['enabled'] ) ? false : true );
			if ( $has_featured ) {
				$has_featured = $atts['featured'];
			}
			
			$current_order       = acadp_get_listings_current_order( $atts['orderby'] . '-' . $atts['order'] );
			$can_show_pagination = (int) $atts['pagination'];
			
			$has_price = empty( $general_settings['has_price'] ) ? false : true;
			$can_show_price = false;
		
			if ( $has_price ) {
				$can_show_price = isset( $listings_settings['display_in_listing'] ) && in_array( 'price', $listings_settings['display_in_listing'] ) ? true : false;
			}
			
			$has_location = empty( $general_settings['has_location'] ) ? false : true;
			$can_show_location = false;
		
			if ( $has_location ) {
				$can_show_location = isset( $listings_settings['display_in_listing'] ) && in_array( 'location', $listings_settings['display_in_listing'] ) ? true : false;
			}
		
			$span = 12;
			if ( $can_show_images ) $span = $span - 2;
			if ( $can_show_price ) $span = $span - 3;
			$span_middle = 'col-md-' . $span;

			// Define the query
			$paged = acadp_get_page_number();
			
			$args = array(				
				'post_type'      => 'acadp_listings',
				'post_status'    => 'publish',
				'posts_per_page' => (int) $atts['listings_per_page'],
				'paged'          => $paged,
				'author_name'    => $user_slug,
	  		);
			
			if ( $has_location && $general_settings['base_location'] > 0 ) {			
				$args['tax_query'] = array(
					array(
						'taxonomy'         => 'acadp_locations',
						'field'            => 'term_id',
						'terms'            => $general_settings['base_location'],
						'include_children' => true,
					),
				);				
			}
			
			$meta_queries = array();
			
			if ( 'map' == $view ) {
				$meta_queries['hide_map'] = array(
					'key'     => 'hide_map',
					'value'   => 0,
					'compare' => '='
				);
			}
			
			if ( $has_featured ) {			
				if ( 'featured' == $atts['filterby'] ) {
					$meta_queries['featured'] = array(
						'key'     => 'featured',
						'value'   => 1,
						'compare' => '='
					);
				} else {
					$meta_queries['featured'] = array(
						'key'     => 'featured',
						'type'    => 'NUMERIC',
						'compare' => 'EXISTS',
					);
				}					
			}
		
			switch ( $current_order ) {
				case 'title-asc' :
					if ( $has_featured ) {
						$args['meta_key'] = 'featured';
						$args['orderby']  = array(
							'meta_value_num' => 'DESC',
							'title'          => 'ASC',
						);
					} else {
						$args['orderby'] = 'title';
						$args['order']   = 'ASC';
					};
					break;
				case 'title-desc' :
					if ( $has_featured ) {
						$args['meta_key'] = 'featured';
						$args['orderby']  = array(
							'meta_value_num' => 'DESC',
							'title'          => 'DESC',
						);
					} else {
						$args['orderby'] = 'title';
						$args['order']   = 'DESC';
					};
					break;
				case 'date-asc' :
					if ( $has_featured ) {
						$args['meta_key'] = 'featured';
						$args['orderby']  = array(
							'meta_value_num' => 'DESC',
							'date'           => 'ASC',
						);
					} else {
						$args['orderby'] = 'date';
						$args['order']   = 'ASC';
					};
					break;
				case 'date-desc' :
					if ( $has_featured ) {
						$args['meta_key'] = 'featured';
						$args['orderby']  = array(
							'meta_value_num' => 'DESC',
							'date'           => 'DESC',
						);
					} else {
						$args['orderby'] = 'date';
						$args['order']   = 'DESC';
					};
					break;
				case 'price-asc' :
					if ( $has_featured ) {
						$meta_queries['price'] = array(
							'key'     => 'price',
							'type'    => 'NUMERIC',
							'compare' => 'EXISTS',
						);

						$args['orderby']  = array( 
							'featured' => 'DESC',
							'price'    => 'ASC',
						);
					} else {
						$args['meta_key'] = 'price';
						$args['orderby']  = 'meta_value_num';
						$args['order']    = 'ASC';
					};
					break;
				case 'price-desc' :
					if ( $has_featured ) {
						$meta_queries['price'] = array(
							'key'     => 'price',
							'type'    => 'NUMERIC',
							'compare' => 'EXISTS',
						);

						$args['orderby']  = array( 
							'featured' => 'DESC',
							'price'    => 'DESC',
						);
					} else {
						$args['meta_key'] = 'price';
						$args['orderby']  = 'meta_value_num';
						$args['order']    = 'DESC';
					};
					break;
				case 'views-asc' :
					if ( $has_featured ) {
						$meta_queries['views'] = array(
							'key'     => 'views',
							'type'    => 'NUMERIC',
							'compare' => 'EXISTS',
						);

						$args['orderby']  = array( 
							'featured' => 'DESC',
							'views'    => 'ASC',
						);
					} else {
						$args['meta_key'] = 'views';
						$args['orderby']  = 'meta_value_num';
						$args['order']    = 'ASC';
					};
					break;
				case 'views-desc' :
					if ( $has_featured ) {
						$meta_queries['views'] = array(
							'key'     => 'views',
							'type'    => 'NUMERIC',
							'compare' => 'EXISTS',
						);

						$args['orderby']  = array( 
							'featured' => 'DESC',
							'views'    => 'DESC',
						);
					} else {
						$args['meta_key'] = 'views';
						$args['orderby']  = 'meta_value_num';
						$args['order']    = 'DESC';
					};
					break;
				case 'rand-asc' :
				case 'rand-desc' :
					if ( $has_featured ) {
						$args['meta_key'] = 'featured';
						$args['orderby']  = 'meta_value_num rand';
					} else {
						$args['orderby'] = 'rand';
					};
					break;
			}
			
			$count_meta_queries = count( $meta_queries );
			if ( $count_meta_queries ) {
				$args['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries;
			}
			
			$args = apply_filters( 'acadp_query_args', $args, $shortcode );
			$acadp_query = new WP_Query( $args );
			
			// Start the Loop
			global $post;
			
			// Process output
			if ( $acadp_query->have_posts() ) {
				ob_start();
				include( acadp_get_template( "listings/acadp-public-listings-$view-display.php" ) );
				return ob_get_clean();			
			} else {		
				return '<span>' . __( 'No Results Found.', 'advanced-classifieds-and-directory-pro' ) . '</span>';		
			}		
		}		
	}
	
	/**
	 * Process the shortcode [acadp_user_dashboard].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_user_dashboard() {		
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}		

		$shortcode = 'acadp_user_dashboard';
		
		$userid = get_current_user_id();
		$user = get_userdata( $userid );
		
		// Enqueue style dependencies
		wp_enqueue_style( ACADP_PLUGIN_NAME );
		
		// ...		
		ob_start();
		do_action( 'acadp_before_user_dashboard_content' );
		include( acadp_get_template( "user/acadp-public-user-dashboard-display.php" ) );
		do_action( 'acadp_after_user_dashboard_content' );
		return ob_get_clean();		
	}
	
	/**
	 * Process the shortcode [acadp_listing_form].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_listing_form() {		
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}
		
		//Mel: 25/01/22
		$contractExist = get_user_meta(get_current_user_id(), 'contract_address', true);
		
		$post_id  = 'edit' == get_query_var( 'acadp_action' ) ? get_query_var( 'acadp_listing', 0 ) : 0;
		$has_permission = true;
		
		if ( $post_id > 0 ) {
			if ( ! acadp_current_user_can('edit_acadp_listing', $post_id) ) {
				$has_permission = false;
			}
		} elseif ( ! acadp_current_user_can('edit_acadp_listings') ) {
			$has_permission = false;
		}
		
		if ( ! $has_permission ) {
			return __( 'You do not have sufficient permissions to access this page.', 'advanced-classifieds-and-directory-pro' );
		}

		//Mel: 25/01/22
		if ( empty($contractExist) ) {
			return __( 'You need a smart contract to proceed.', 'advanced-classifieds-and-directory-pro' );
		}
		
		$shortcode = 'acadp_listing_form';
		
		$general_settings    = get_option( 'acadp_general_settings' );
		$locations_settings  = get_option( 'acadp_locations_settings' );
		$categories_settings = get_option( 'acadp_categories_settings' );
		$recaptcha_settings  = get_option( 'acadp_recaptcha_settings' );		
		
		// Enqueue style dependencies		
		wp_enqueue_style( ACADP_PLUGIN_NAME );

		if ( wp_style_is( ACADP_PLUGIN_NAME . '-map', 'registered' ) ) {
			wp_enqueue_style( ACADP_PLUGIN_NAME . '-map' );				
		}
		
		// Enqueue script dependencies		
		wp_enqueue_script( 'jquery-form', array('jquery'), false, true );		
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-touch-punch' );
		
		if ( wp_script_is( ACADP_PLUGIN_NAME . '-bootstrap', 'registered' ) ) {
			wp_enqueue_script( ACADP_PLUGIN_NAME . '-bootstrap' );
		}
		
		wp_enqueue_script( ACADP_PLUGIN_NAME . '-validator' );			
		wp_enqueue_script( ACADP_PLUGIN_NAME . '-map' );
		
		if ( isset( $recaptcha_settings['forms'] ) && in_array( 'listing', $recaptcha_settings['forms'] ) ) {
			wp_enqueue_script( ACADP_PLUGIN_NAME . "-recaptcha" );
		}

		wp_enqueue_script( ACADP_PLUGIN_NAME );	

		// ...
		$has_draft = 1;
		$category  = 0;
		$default_location = '';

		$disable_parent_categories = empty( $general_settings['disable_parent_categories'] ) ? false : true;		
		$editor = ! empty( $general_settings['text_editor'] ) ? $general_settings['text_editor'] : 'wp_editor';
		
		$can_add_price    = empty( $general_settings['has_price'] )    ? false : true;
		$can_add_images   = empty( $general_settings['has_images'] )   ? false : true;
		$can_add_video    = empty( $general_settings['has_video'] )    ? false : true;	
		$can_add_location = empty( $general_settings['has_location'] ) ? false : true;
		$has_map          = empty( $general_settings['has_map'] )      ? false : true;
		$mark_as_sold     = empty( $general_settings['mark_as_sold'] ) ? false : true;
		
		$images_limit = apply_filters( 'acadp_images_limit', (int) $general_settings['maximum_images_per_listing'], $post_id );
		
		if ( $can_add_location ) {
			$location = ( $general_settings['default_location'] > 0 ) ? $general_settings['default_location'] : $general_settings['base_location'];
			if ( $location > 0 ) {
				$term = get_term_by( 'id', $location, 'acadp_locations' );
				$default_location = $term->name;
			}
		}

		if ( $post_id > 0 ) {			
			$post = get_post( $post_id );
			setup_postdata( $post );
			
			$post_meta = get_post_meta( $post_id);
			
			if ( $post->post_status !== 'draft' ) {
				$has_draft = 0;
			}
			
			$category = wp_get_object_terms( $post_id, 'acadp_categories', array( 'fields' => 'ids' ) );
			$category = $category[0];
			
			if ( $can_add_location ) {
				$location = wp_get_object_terms( $post_id, 'acadp_locations', array( 'fields' => 'ids' ) );
				$location = ! empty( $location ) ? $location[0] : -1;
			}			
		}
		
		ob_start();
		include( acadp_get_template( "user/acadp-public-edit-listing-display.php" ) );
		wp_reset_postdata(); // Restore global post data stomped by the_post()
		return ob_get_clean();	
	}

	/**Mel: 24/01/22
	 * Process the shortcode [acadp_contract_form].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_contract_form() {		
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}		
		
		$has_permission = true;
		
		if ( ! acadp_current_user_can('edit_acadp_listings') ) {
			$has_permission = false;
		}
		
		if ( ! $has_permission ) {
			return __( 'You do not have sufficient permissions to access this page.', 'advanced-classifieds-and-directory-pro' );
		}

		//Mel: 29/01/22
		$contractExist = get_user_meta(get_current_user_id(), 'contract_address', true);

		//Mel: 29/01/22
		if ( !empty($contractExist) ) {
			return __( 'You already have a smart contract. Currently, you can only have one contract.', 'advanced-classifieds-and-directory-pro' );
		}
		
		$shortcode = 'acadp_contract_form';
		
		$general_settings    = get_option( 'acadp_general_settings' );
		$locations_settings  = get_option( 'acadp_locations_settings' );
		$categories_settings = get_option( 'acadp_categories_settings' );
		$recaptcha_settings  = get_option( 'acadp_recaptcha_settings' );		
		
		// Enqueue style dependencies		
		wp_enqueue_style( ACADP_PLUGIN_NAME );
		
		// Enqueue script dependencies		
		wp_enqueue_script( 'jquery-form', array('jquery'), false, true );		
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-touch-punch' );
		
		if ( wp_script_is( ACADP_PLUGIN_NAME . '-bootstrap', 'registered' ) ) {
			wp_enqueue_script( ACADP_PLUGIN_NAME . '-bootstrap' );
		}
		
		
		if ( isset( $recaptcha_settings['forms'] ) && in_array( 'listing', $recaptcha_settings['forms'] ) ) {
			wp_enqueue_script( ACADP_PLUGIN_NAME . "-recaptcha" );
		}

		wp_enqueue_script( ACADP_PLUGIN_NAME );	

		// ...
		$has_draft = 1;
		$category  = 0;
		$default_location = '';
		
		ob_start();
		include( acadp_get_template( "user/acadp-public-create-contract-display.php" ) );
		wp_reset_postdata(); // Restore global post data stomped by the_post()
		return ob_get_clean();	
	}

	/**Mel: 25/01/22
	 * Process the shortcode [acadp_contract_receipt].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_contract_receipt() {		
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}		
		
		$has_permission = true;
		
		if ( ! acadp_current_user_can('edit_acadp_listings') ) {
			$has_permission = false;
		}
		
		if ( ! $has_permission ) {
			return __( 'You do not have sufficient permissions to access this page.', 'advanced-classifieds-and-directory-pro' );
		}
		
		$shortcode = 'acadp_contract_receipt';	
		
		// Enqueue style dependencies		
		wp_enqueue_style( ACADP_PLUGIN_NAME );
		
		if ( wp_script_is( ACADP_PLUGIN_NAME . '-bootstrap', 'registered' ) ) {
			wp_enqueue_script( ACADP_PLUGIN_NAME . '-bootstrap' );
		}

		wp_enqueue_script( ACADP_PLUGIN_NAME );	
		
		ob_start();
		include( acadp_get_template( "user/acadp-public-create-contract-receipt.php" ) );
		wp_reset_postdata(); // Restore global post data stomped by the_post()
		return ob_get_clean();	
	}
	
	/**
	 * Display custom fields.
	 *
	 * @since 1.0.0
	 * @param int   $post_id Post ID.
	 */
	public function ajax_callback_custom_fields( $post_id = 0 ) {	
		$ajax = false;
		$terms = array();
		
		if ( isset( $_POST['terms'] ) ) {
			check_ajax_referer( 'acadp_ajax_nonce', 'security' );

			$ajax = true;
			$post_id = (int) $_POST['post_id'];
			$terms = is_array( $_POST['terms'] ) ? array_map( 'intval', $_POST['terms'] ) : (int) $_POST['terms'];
		} else {
			$post_id = (int) $post_id;
			
			if ( $post_id > 0 ) {
				$terms = wp_get_object_terms( $post_id, 'acadp_categories', array( 'fields' => 'ids' ) );
			}
		}
		
		// Get post meta for the given post_id
		$post_meta = get_post_meta( $post_id  );
		
		// Get custom fields
		$custom_field_ids = acadp_get_custom_field_ids( $terms );
		
		if ( ! empty( $custom_field_ids ) ) {
			$args = array(
				'post_type'      => 'acadp_fields',
				'post_status'    => 'publish',
				'posts_per_page' => 500,		
				'post__in'		 => $custom_field_ids,	
				'meta_key'       => 'order',
				'orderby'        => 'meta_value_num',			
				'order'          => 'ASC',
			);
			
			$acadp_query = new WP_Query( $args );
			
			// Start the Loop
			global $post;
			
			// Process output
			ob_start();
			include( acadp_get_template( "user/acadp-public-custom-fields-display.php" ) );
			wp_reset_postdata(); // Restore global post data stomped by the_post()
			$output = ob_get_clean();
				
			print $output;
		}
		
		if ( $ajax ) {
			wp_die();
		}	
	}
	
	/**
	 * Upload image.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_image_upload() {			
		if ( isset( $_POST['acadp_images_nonce'] ) && wp_verify_nonce( $_POST['acadp_images_nonce'], 'acadp_upload_images' ) ) {
			$data = array();

			if ( $_FILES ) {			
				require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
				require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
				require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
				
				$files   = $_FILES['acadp_image'];
				$post_id = 0;
				
				foreach ( $files['name'] as $index => $value ) {				
					if ( $files['name'][ $index ] ) {					
						$data[ $index ] = array(
							'error'   => 0,
							'message' => ''
						);
						
						$file = array(
							'name'     => $files['name'][ $index ],
							'type'     => $files['type'][ $index ],
							'tmp_name' => $files['tmp_name'][ $index ],
							'error'    => $files['error'][ $index ],
							'size'     => $files['size'][ $index ]
						);
						
						//Mel: 28/01/22/. Comment out to allow non-image upload
						// if ( getimagesize( $file['tmp_name'] ) === FALSE ) {
						// 	$data[ $index ]['error']   = 1;
						// 	$data[ $index ]['message'] = __( 'File is not an image.', 'advanced-classifieds-and-directory-pro' );
						// } 
						
						//Mel: 28/01/22
						if ( ! in_array( $file['type'], array( 'image/jpeg', 'image/jpg', 'image/png', 'image/bmp', 'image/gif', 'image/tiff', 'application/pdf', 'application/x-pdf', 'text/plain', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/mspowerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/excel', 'text/html', 'application/xml', 'application/vnd.apple.pages', 'application/vnd.apple.keynote', 'application/vnd.apple.numbers ') ) ) {
						//if ( ! in_array( $file['type'], array( 'image/jpeg', 'image/jpg', 'image/png' ) ) ) {
							$data[ $index ]['error']   = 1;
							$data[ $index ]['message'] = __( 'Invalid file format', 'advanced-classifieds-and-directory-pro' );
						}
						
						if ( $file['error'] !== UPLOAD_ERR_OK ) {
							$data[ $index ]['error']   = 1;
							$data[ $index ]['message'] = $file['error'];
						} 
						
						if ( 0 == $data[ $index ]['error'] ) {							
							$_FILES = array( 'acadp_image' => $file );
							
							$_FILES['acadp_image'] = acadp_exif_rotate( $_FILES['acadp_image'] );
							$img_id = media_handle_upload( 'acadp_image', $post_id );
							
							$data[ $index ]['id'] = $img_id;
							
							//Mel: 28/01/02. Get attached file url
							$image = wp_get_attachment_url( $img_id );
							//$image = wp_get_attachment_image_src( $img_id );
							
							//Mel: 28/01/02. To display file URL on form
							$data[ $index ]['url'] = $image;
							//$data[ $index ]['url'] = $image[0];
						}
					}													
				}				
			}
					
			echo wp_json_encode( $data );		  
		}

  		wp_die();	
	}
	
	
	/**Mel: 07/11/21

	 * Upload file like cert.

	 *

	 * @since 1.0.0

	 */

	public function ajax_callback_file_upload() {			

		if ( isset( $_POST['acadp_files_nonce'] ) && wp_verify_nonce( $_POST['acadp_files_nonce'], 'acadp_upload_files' ) ) {

			$data2 = array();


			if ( $_FILES ) {			

				require_once( ABSPATH . "wp-admin" . '/includes/image.php' );

				require_once( ABSPATH . "wp-admin" . '/includes/file.php' );

				require_once( ABSPATH . "wp-admin" . '/includes/media.php' );

				

				$files   = $_FILES['acadp_file'];

				$post_id = 0;

				

				foreach ( $files['name'] as $index => $value ) {				

					if ( $files['name'][ $index ] ) {					

						$data2[ $index ] = array(

							'error'   => 0,

							'message' => ''

						);

						

						$file = array(

							'name'     => $files['name'][ $index ],

							'type'     => $files['type'][ $index ],

							'tmp_name' => $files['tmp_name'][ $index ],

							'error'    => $files['error'][ $index ],

							'size'     => $files['size'][ $index ]

						);

						

						/* if ( getimagesize( $file['tmp_name'] ) === FALSE ) {

							$data[ $index ]['error']   = 1;

							$data[ $index ]['message'] = __( 'File is not an image.', 'advanced-classifieds-and-directory-pro' );

						}  */

						

						if ( ! in_array( $file['type'], array( 'application/pdf', 'application/x-pdf', 'image/jpeg', 'image/jpg', 'image/png' ) ) ) {

							$data2[ $index ]['error']   = 1;

							$data2[ $index ]['message'] = __( 'Invalid file format', 'advanced-classifieds-and-directory-pro' );

						}

						

						if ( $file['error'] !== UPLOAD_ERR_OK ) {

							$data2[ $index ]['error']   = 1;

							$data2[ $index ]['message'] = $file['error'];

						} 

						

						if ( 0 == $data2[ $index ]['error'] ) {							

							$_FILES = array( 'acadp_file' => $file );

							

							$_FILES['acadp_file'] = acadp_exif_rotate( $_FILES['acadp_file'] );

							$file_id = media_handle_upload( 'acadp_file', $post_id );

							

							$data2[ $index ]['id'] = $file_id;

							//Get attached file url
							$file = wp_get_attachment_url( $file_id );
							//$image = wp_get_attachment_image_src( $img_id );
							
							$data2[ $index ]['url'] = $file;	
							//$data2[ $index ]['url'] = $file[0];							

						}

					}													

				}				

			}

					

			echo wp_json_encode( $data2 );		  

		}



  		wp_die();	

	}
	//Mel:End 07/11/21
	

	/**Mel: 12/11/21
	 * Update the post meta with transaction id and url after succesful payment using crypto.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_add_transaction_id() {			
		
		//Store the transaction id
		if ( isset( $_POST['order_id'] ) ) {
			
			$order_id = $_POST['order_id'];
			
			update_post_meta( $order_id, 'transaction_id', wp_generate_password( 12, false ) );
		}
		
		//Store sender's wallet address
		if ( isset( $_POST['from_wallet'] ) && isset( $_POST['post_id'] ) ) {
			
			$post_id = $_POST['post_id'];
			$wallet_address = $_POST['from_wallet'];
			
			update_post_meta( $post_id, 'from_wallet', $wallet_address );
		}
		
		//Mel: 23/11/21
		//Store payment method
		if ( isset( $_POST['payment_method'] ) && isset( $_POST['post_id'] ) ) {
			
			$post_id = $_POST['post_id'];
			$payment_method = $_POST['payment_method'];
			
			update_post_meta( $post_id, 'payment_method', $payment_method );
		}
		//Mel: End
		
		//Store the transaction url from Etherscan
		if ( isset( $_POST['tx_url'] ) ) {
			
			$tx_url = $_POST['tx_url'];
			
			update_post_meta( $order_id, 'tx_url', $tx_url );
		}
		
  		wp_die();	

	}
	//Mel:End 

	/**Mel: 
	 * To save the form data into a metadata that is stored in a json file. This file will be then uploaded to IPFS
	 *
	 *
	 */
	public function ajax_callback_save_metadata() {

		//Create the array to store the metadata
		$metadata = [];

		$ipfs_cid = ( isset($_POST['ipfs_cid'] ) ? $_POST['ipfs_cid'] : '' );
		$filenames = ( isset($_POST['filenames'] ) ? $_POST['filenames'] : '' );	//Read the filename array from the form

		$metadata['name'] = ( isset($_POST['name'] ) ? $_POST['name'] : '' );
		$metadata['description'] = ( isset($_POST['description'] ) ? $_POST['description'] : '' );

		$unique_id = uniqid();	//Generate a unique ID to represent the metadata filename

		if ( !empty( $filenames ) ) {
			$x = 0; 
			foreach ($filenames as $filename) {

				$file_extension = pathinfo($filename, PATHINFO_EXTENSION);

				switch ($file_extension) {
					case 'png':
						$is_image = true;
						break;
					case 'jpg':
						$is_image = true;
						break;
					case 'jpeg':
						$is_image = true;
						break;
					case 'bmp':
						$is_image = true;
						break;
					case 'gif':
						$is_image = true;
						break;
					case 'tiff':
						$is_image = true;
						break;
					case 'bmp':
						$is_image = true;
						break;
					case 'webp':
						$is_image = true;
						break;
					default:
						$is_image = false;
				}

				//If the file is image, the JSON object literal should state "image" not "file"
				if ($is_image) {

					//Append the filename at the end of IPFS CID and store in metadata array
					if ($x == 0) {
						$metadata['image'] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;	
					} else {
						$metadata['image' . '-' . $x] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;
					}
					$x++;

				} else {

					//Append the filename at the end of IPFS CID and store in metadata array
					if ($x == 0) {
						$metadata['file'] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;	
					} else {
						$metadata['file' . '-' . $x] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;
					}
					$x++;

				}

				//Append the filename at the end of IPFS CID and store in metadata array
				// if ($x == 0) {
				// 	$metadata['file'] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;	
				// } else {
				// 	$metadata['file' . '-' . $x] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;
				// }
				// $x++;
			}
		}
		
		//Convert the metadata array to JSON string.
		$json = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'metadata';

		if ( wp_mkdir_p( $uploads_dir ) ) {		//If folder does not exist, it will be created
			//Create a json file based on unique ID
			$myfile = fopen( trailingslashit($uploads_dir) . $unique_id . ".json", "w+");
		}
		
		//Write the metadata into a json file
		if ( fwrite($myfile, $json) ) {
			fclose($myfile);
			echo $unique_id . ".json";	//Return the json filename
		}

		//DEBUG
		error_log(basename($unique_id . ".json"));

	}
	
	/**
	 * Delete an attachment.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_delete_attachment() {	
		check_ajax_referer( 'acadp_ajax_nonce', 'security' );
		
		$misc_settings = get_option( 'acadp_misc_settings' );

		if ( ! empty( $misc_settings['delete_media_files'] ) ) {
			if ( isset( $_POST['attachment_id'] ) ) {
				wp_delete_attachment( (int) $_POST['attachment_id'], true );
			}
		}
		
		wp_die();	
	}
	
	/**
	 * Save Listing.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function save_listing() {
		define( 'ACADP_LISTING_SUBMISSION', 1 );

		$general_settings = get_option( 'acadp_general_settings' );
		
		$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
		$is_new = ( $post_id > 0 ) ? 0 : 1;

		$new_listing_status = apply_filters( 'acadp_new_listing_status', $general_settings['new_listing_status'] );
		$post_status = $new_listing_status;
		
		if ( isset( $_POST['action'] ) && __( 'Save Draft', 'advanced-classifieds-and-directory-pro' ) == $_POST['action'] ) {
			$post_status = 'draft';
		} elseif ( $post_id > 0 ) {	
			$post_status = get_post_status( $post_id );

			if ( 'pending' === $post_status ) {				
				$redirect_url = add_query_arg( 'status', 'permission_denied', acadp_get_listing_edit_page_link( $post_id ) );
    			wp_redirect( $redirect_url );
   				exit();	
			}

			if ( 'draft' === $post_status ) {
				$post_status = $new_listing_status;	
				$is_new = 1;
			} else {
				$post_status = $general_settings['edit_listing_status'];
			}
		}		
		
		// Add the content of the form to $post as an array
		$post_array = array(
			'post_title'   => wp_strip_all_tags( $_POST['title'] ),
			'post_name'    => sanitize_title( $_POST['title'] ),
			'post_content' => isset( $_POST['description'] ) ? $_POST['description'] : '',
			'post_status'  => $post_status,
			'post_type'	   => 'acadp_listings'
		);
		
		if ( $post_id > 0 ) {		
			// update the existing post
			$post_array['ID'] = $post_id;
			wp_update_post( $post_array );			
		} else {			
			// save a new post
			$post_array['post_author'] = get_current_user_id();
			$post_id = wp_insert_post( $post_array );			
		}
		
		if ( $post_id ) {		
			// insert category taxonomy
			$cat_ids = array_map( 'intval', (array) $_POST['acadp_category'] );
			$cat_ids = array_unique( $cat_ids );

			wp_set_object_terms( $post_id, null, 'acadp_categories' );
			wp_set_object_terms( $post_id, $cat_ids, 'acadp_categories', true );
			
			// insert custom fields
			if ( isset( $_POST['acadp_fields'] ) ) {			
				foreach ( $_POST['acadp_fields'] as $key => $value ) {
					$key  = sanitize_key( $key );
					$type = get_post_meta( $key, 'type', true );
					
					switch ( $type ) {
						case 'text':
							$value = sanitize_text_field( $value );
							break;
						case 'textarea':
							$value = sanitize_textarea_field( $value );
							break;	
						case 'select':
						case 'radio':
							$value = sanitize_text_field( $value );
							break;					
						case 'checkbox':
							$value = array_map( 'sanitize_text_field', $value );
							$value = implode( "\n", array_filter( $value ) );
							break;
						case 'url':
							$value = esc_url_raw( $value );
							break;	
						default:
							$value = sanitize_text_field( $value );
					}
					
					update_post_meta( $post_id, $key, $value );
				}			
			}
			
			// insert images
			if ( ! empty( $general_settings['has_images'] ) && isset( $_POST['images'] ) ) {
				// OK to save meta data	
				$images = array_filter( $_POST['images'] );	
				$images = array_map( 'intval', $images );

        		if ( count( $images ) ) {				
					$images_limit = apply_filters( 'acadp_images_limit', (int) $general_settings['maximum_images_per_listing'], $post_id );
					if( $images_limit > 0 ) $images = array_slice( $images, 0, $images_limit );
					
            		update_post_meta( $post_id, 'images', $images );
					set_post_thumbnail( $post_id, $images[0] );
        		} else { 
            		delete_post_meta( $post_id, 'images' );
					delete_post_thumbnail( $post_id );
				}					
			} else {				
				// Nothing received, all fields are empty, delete option					
				delete_post_meta( $post_id, 'images' );
				delete_post_thumbnail( $post_id );				
			}
			
			
			//Mel: 07/11/21
			// Insert cert file
			if ( isset( $_POST['files'] ) ) {
				// OK to save meta data	
				$files = array_filter( filter_input_array($_POST['files'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) );	
				$files = array_map( 'intval', $files );				

        		if ( count( $files ) ) {				
					$images_limit = apply_filters( 'acadp_files_limit', (int) $general_settings['maximum_images_per_listing'], $post_id );
					if( $images_limit > 0 ) $files = array_slice( $files, 0, $images_limit );
					
            		update_post_meta( $post_id, 'files', $files );
					//set_post_thumbnail( $post_id, $images[0] );					
        		} else { 
            		delete_post_meta( $post_id, 'files' );
					//delete_post_thumbnail( $post_id );
				}					
			} else {				
				// Nothing received, all fields are empty, delete option					
				delete_post_meta( $post_id, 'files' );
				//delete_post_thumbnail( $post_id );				
			}
			//Mel: End
			
			//Mel:
			// Save IPFS CID (content address identifier)
			if ( isset( $_POST['ipfs_cid'] ) ) {
				
				//Add CID key can value into post meta
				update_post_meta( $post_id, 'ipfs_cid', sanitize_text_field($_POST['ipfs_cid']));

				$ipfs_cid = sanitize_text_field($_POST['ipfs_cid']);

				$filenames = wporg_recursive_sanitize_text_field($_POST['filename']);	//Read the filename array from the form

				//Create the array to store the metadata
				$metadata = [];

				$metadata['name'] = $post_array['post_title'];
				$metadata['description'] = $post_array['post_content'];

				$x = 0; 
				foreach ($filenames as $filename) {

					//Append the image filename at the end of IPFS CID and store in metadata array
					if ($x == 0) {
						$metadata['image'] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;	
					} else {
						$metadata['image' . '-' . $x] = "https://ipfs.io/ipfs/" . $ipfs_cid . "/" . $filename;
					}
					$x++;
				}
				
				//Convert the metadata array to JSON string.
				$json = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

				$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'metadata';

				if ( wp_mkdir_p( $uploads_dir ) ) {		//If folder does not exist, it will be created
					//Create a json file based on post ID
					$myfile = fopen( trailingslashit($uploads_dir) . $post_id . ".json", "w+");
				}
				
				//Write the metadata into json file
				fwrite($myfile, $json);
				fclose($myfile);
				
			}

			//Mel: Process file hash
			if ( isset( $_POST['hash'] ) ) {
				$hashes = wporg_recursive_sanitize_text_field($_POST['hash']);	//Read the hash array from the form
				update_post_meta( $post_id, 'hash', implode(',' , $hashes));
			}
			
			if ( isset( $_POST['ipfs_metadata_cid'] ) ) {
				//Save the IPFS hash of the metadata json file
				update_post_meta( $post_id, 'ipfs_metadata_cid', sanitize_text_field($_POST['ipfs_metadata_cid']));
			}
			
			// insert video
			if ( ! empty( $general_settings['has_video'] ) && isset( $_POST['video'] ) ) {
				$video = esc_url_raw( $_POST['video'] );
    			update_post_meta( $post_id, 'video', $video );
			}
			
			// insert contact details
			if ( ! empty( $general_settings['has_location'] ) ) {
				$address = sanitize_textarea_field( $_POST['address'] );
    			update_post_meta( $post_id, 'address', $address );
			
				wp_set_object_terms( $post_id, (int) $_POST['acadp_location'], 'acadp_locations' );
					
				$zipcode = sanitize_text_field( $_POST['zipcode'] );
    			update_post_meta( $post_id, 'zipcode', $zipcode );
				
				$phone = sanitize_text_field( $_POST['phone'] );
    			update_post_meta( $post_id, 'phone', $phone );
				
				$email = sanitize_email( $_POST['email'] );
    			update_post_meta( $post_id, 'email', $email );
				
				$website = esc_url_raw( $_POST['website'] );
    			update_post_meta( $post_id, 'website', $website );
				
				$latitude = isset( $_POST['latitude'] ) ? sanitize_text_field( $_POST['latitude'] ) : '';
    			update_post_meta( $post_id, 'latitude', $latitude );
				
				$longitude = isset( $_POST['longitude'] ) ? sanitize_text_field( $_POST['longitude'] ) : '';
    			update_post_meta( $post_id, 'longitude', $longitude );

				$hide_map = isset( $_POST['hide_map'] ) ? (int) $_POST['hide_map'] : 0;
    			update_post_meta( $post_id, 'hide_map', $hide_map );
			}
			
			if ( ! empty( $general_settings['has_price'] ) ) {
				$price = acadp_sanitize_amount( $_POST['price'] );
    			update_post_meta( $post_id, 'price', $price );
			}
			
			if ( ! empty( $general_settings['mark_as_sold'] ) ) {
				$sold = isset( $_POST['sold'] ) ? (int) $_POST['sold'] : 0;
				update_post_meta( $post_id, 'sold', $sold );
			}

			$featured = get_post_meta( $post_id, 'featured', true );
			if ( empty( $featured ) ) {
				update_post_meta( $post_id, 'featured', 0 );
			}

			$views = get_post_meta( $post_id, 'views', true );
			if ( empty( $views ) ) {
				update_post_meta( $post_id, 'views', 0 );
			}

			$listing_status = get_post_meta( $post_id, 'listing_status', true );
			if ( empty( $listing_status ) ) {
				update_post_meta( $post_id, 'listing_status', 'post_status' );
			}
			
			// ...			
			$redirect_url = home_url();
			$redirect_status = $post_status;
			
			if ( isset( $_POST['action'] ) && __( 'Save Draft', 'advanced-classifieds-and-directory-pro' ) == $_POST['action'] ) {
				$redirect_url = acadp_get_listing_edit_page_link( $post_id );
			} else {
				$redirect_url = acadp_get_manage_listings_page_link();
				
				if ( $is_new ) {
					$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
	
					$has_checkout_page = 0;
					
					if ( ! empty( $featured_listing_settings['enabled'] ) && $featured_listing_settings['price'] > 0 ) {
						$has_checkout_page = 1;
					}
					
					$has_checkout_page = apply_filters( 'acadp_has_checkout_page', $has_checkout_page, $post_id );				
					
					if ( $has_checkout_page ) {
						$redirect_url = acadp_get_checkout_page_link( $post_id );
					}					

					if ( 'draft' != $post_status ) {
						acadp_email_admin_listing_submitted( $post_id );
						acadp_email_listing_owner_listing_submitted( $post_id );
					}
					
					if ( 'publish' == $post_status ) {
						$expiry_date = acadp_listing_expiry_date( $post_id );
						update_post_meta( $post_id, 'expiry_date', $expiry_date );
					
						acadp_email_listing_owner_listing_approved( $post_id );
					}
				} else {
					$redirect_status = 'updated';
					acadp_email_admin_listing_edited( $post_id );
				}
			}
			
			do_action( 'acadp_listing_form_after_save', $post_id );
			
			// redirect
			if ( ! empty( $redirect_status ) ) {
				$redirect_url = add_query_arg( 'status', $redirect_status, $redirect_url );
			}

			$redirect_url = apply_filters( 'acadp_listing_form_redirect_url', $redirect_url, $post_id );
    		wp_redirect( $redirect_url );
   			exit();		
		}	
	}

   /** Mel:
	 * Save smart contract during contract form submission.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function save_contract() {
		$user_id = get_current_user_id();

		if ( isset( $_POST['wallet_address'] ) ) {

			$wallet_address = sanitize_text_field($_POST['wallet_address']);
				
			//Add contract address into user meta
			update_user_meta($user_id, 'wallet_address', $wallet_address);
									
		} 

		if ( isset( $_POST['contract_address'] ) ) {

			$contract_address = sanitize_text_field($_POST['contract_address']);
				
			//Add contract address into user meta
			update_user_meta($user_id, 'contract_address', $contract_address);
									
		} 

		if ( isset( $_POST['contract_tx_hash'] ) ) {
			
			$contract_tx_hash = sanitize_text_field($_POST['contract_tx_hash']); 
			
			//Add contract creation hash  into user meta
			update_user_meta($user_id, 'contract_tx_hash', $contract_tx_hash);
									
		} 
		
		//Redirect to the contract creation status page to show success
		$redirect_url = acadp_get_create_contract_receipt_page_link( $contract_address, $contract_tx_hash, $wallet_address );

    	wp_redirect( $redirect_url );
   		exit();		
	}
	
	/**
	 * Renew Listing.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  int     $post_id Post ID.
	 */
	private function renew_listing( $post_id ) {
		define( 'ACADP_LISTING_RENEWAL', 1 );

		// Disable featured
		update_post_meta( $post_id, 'featured', 0 );
		
		// Hook for developers
		do_action( 'acadp_before_renewal', $post_id );
		
		// ...
		$has_paid_submission = apply_filters( 'acadp_has_checkout_page', 0, $post_id, 'submission' );	
		
		if ( $has_paid_submission ) {		 
			$redirect_url = acadp_get_checkout_page_link( $post_id );		
		} else {			
			$time = current_time( 'mysql' );
			
			// Update post $post_id
  			$post_array = array(
      			'ID'          	=> $post_id,
      			'post_status' 	=> 'publish',
				'post_date'   	=> $time,
				'post_date_gmt' => get_gmt_from_date( $time )
  			);

			// Update the post into the database
 			wp_update_post( $post_array );
			
			// Update the post_meta into the database
			$old_listing_status = get_post_meta( $post_id, 'listing_status', true );
			if ( 'expired' == $old_listing_status ) {
				$expiry_date = acadp_listing_expiry_date( $post_id );
			} else {
				$old_expiry_date = get_post_meta( $post_id, 'expiry_date', true ); 	
				$expiry_date = acadp_listing_expiry_date( $post_id, $old_expiry_date );
			}
			update_post_meta( $post_id, 'expiry_date', $expiry_date );
			update_post_meta( $post_id, 'listing_status', 'post_status' );		
		
			// redirect
			$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
				
			$has_checkout_page = 0;
			if ( ! empty( $featured_listing_settings['enabled'] ) && $featured_listing_settings['price'] > 0 ) {
				$has_checkout_page = 1;			
			}
			
			$has_checkout_page = apply_filters( 'acadp_has_checkout_page', $has_checkout_page, $post_id, 'promotion' );	
			
			if ( $has_checkout_page ) {
				$redirect_url = add_query_arg( 'status', 'renewed', acadp_get_checkout_page_link( $post_id ) );
			} else {
				$redirect_url = add_query_arg( 'status', 'renewed', acadp_get_manage_listings_page_link() );
			}		
		}
				
    	wp_redirect( $redirect_url );
   		exit();	
	}

	/**
	 * Delete Listing.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  int     $post_id Post ID.
	 */
	private function delete_listing( $post_id ) {
		$misc_settings = get_option( 'acadp_misc_settings' );
		
		if ( ! empty( $misc_settings['delete_media_files'] ) ) {
			$images = get_post_meta( $post_id, 'images', true );
			
			if ( ! empty( $images ) ) {		
				foreach ( $images as $image ) {
					wp_delete_attachment( $image, true );
				}		
			}
		}
		
		wp_delete_post( $post_id, true );
		
		// redirect
		$redirect_url = add_query_arg( 'status', 'deleted', acadp_get_manage_listings_page_link() );
    	wp_redirect( $redirect_url );
   		exit();	
	}
	
	/**
	 * Process the shortcode [acadp_manage_listings].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_manage_listings() {	
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}		

		if ( ! acadp_current_user_can('edit_acadp_listings') ) {
			return __( 'You do not have sufficient permissions to access this page.', 'advanced-classifieds-and-directory-pro' );
		}
		
		$shortcode = 'acadp_manage_listings';
		
		$general_settings          = get_option( 'acadp_general_settings' );
		$listings_settings         = get_option( 'acadp_listings_settings' );
		$page_settings             = get_option( 'acadp_page_settings' );
		$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
		
		$can_show_images = empty( $general_settings['has_images'] ) ? false : true;
		$can_renew       = empty( $general_settings['has_listing_renewal'] ) ? false : true;
		$has_location    = empty( $general_settings['has_location'] ) ? false : true;
			
		$span = 9;
		if ( $can_show_images ) $span = 7;
		$span_middle = 'col-md-'.$span;
		
		$can_promote = false;
		if ( ! empty( $featured_listing_settings['enabled'] ) && $featured_listing_settings['price'] > 0 ) {
			$can_promote = true;
		}
		$can_promote = apply_filters( 'acadp_can_promote', $can_promote );
		
		// Enqueue style dependencies
		wp_enqueue_style( ACADP_PLUGIN_NAME );

		// Define the query
		$paged = acadp_get_page_number();
			
		$args = array(				
			'post_type'      => 'acadp_listings',
			'post_status'    => 'any',
			'posts_per_page' => ! empty( $listings_settings['listings_per_page'] ) ? $listings_settings['listings_per_page'] : -1,
			'paged'          => $paged,
			'author'         => get_current_user_id(),
			's'              => isset( $_REQUEST['u'] ) ? sanitize_text_field( $_REQUEST['u'] ) : ''
	  	);
			
		$acadp_query = new WP_Query( $args );
			
		// Start the Loop
		global $post;
			
		// Process output
		ob_start();
		include( acadp_get_template( "user/acadp-public-manage-listings-display.php" ) );
		wp_reset_postdata(); // Use reset postdata to restore orginal query
		return ob_get_clean();			
	}
	
	/**
	 * Process the shortcode [acadp_favourite_listings].
	 *
	 * @since 1.0.0
	 * @param array $atts An associative array of attributes.
	 */
	public function run_shortcode_favourite_listings( $atts ) {	
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}	
		
		$shortcode = 'acadp_favourite_listings';
		
		$general_settings = get_option( 'acadp_general_settings' );
		$listings_settings = get_option( 'acadp_listings_settings' );
		$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
		
		$atts = shortcode_atts( array(
			'view'              => $listings_settings['default_view'],
			'featured'          => 1,
			'filterby'          => '',
			'orderby'           => $listings_settings['orderby'],
			'order'             => $listings_settings['order'],
			'listings_per_page' => ! empty( $listings_settings['listings_per_page'] ) ? $listings_settings['listings_per_page'] : -1,
			'pagination'        => 1,
			'header'            => 1
		), $atts );
		
		$view = acadp_get_listings_current_view_name( $atts['view'] );
		
		// Enqueue style dependencies
		wp_enqueue_style( ACADP_PLUGIN_NAME );

		if ( 'map' == $view && wp_style_is( ACADP_PLUGIN_NAME . '-markerclusterer', 'registered' ) ) {
			wp_enqueue_style( ACADP_PLUGIN_NAME . '-markerclusterer' );				
		}
		
		// Enqueue script dependencies
		if ( 'map' == $view ) {
			wp_enqueue_script( ACADP_PLUGIN_NAME . '-markerclusterer' );
		}

		wp_enqueue_script( ACADP_PLUGIN_NAME );
		
		// ...
		$can_show_header           = empty( $listings_settings['display_in_header'] ) ? 0 : (int) $atts['header'];
		$pre_content               = '';
		$can_show_listings_count   = $can_show_header && in_array( 'listings_count', $listings_settings['display_in_header'] ) ? true : false;
		$can_show_views_selector   = $can_show_header && in_array( 'views_selector', $listings_settings['display_in_header'] ) ? true : false;
		$can_show_orderby_dropdown = $can_show_header && in_array( 'orderby_dropdown', $listings_settings['display_in_header'] ) ? true : false;
			
		$can_show_date          = isset( $listings_settings['display_in_listing'] ) && in_array( 'date', $listings_settings['display_in_listing'] ) ? true : false;
		$can_show_user          = isset( $listings_settings['display_in_listing'] ) && in_array( 'user', $listings_settings['display_in_listing'] ) ? true : false;
		$can_show_category      = isset( $listings_settings['display_in_listing'] ) && in_array( 'category', $listings_settings['display_in_listing'] ) ? true : false;
		$can_show_views         = isset( $listings_settings['display_in_listing'] ) && in_array( 'views', $listings_settings['display_in_listing'] ) ? true : false;
		$can_show_custom_fields = isset( $listings_settings['display_in_listing'] ) && in_array( 'custom_fields', $listings_settings['display_in_listing'] ) ? true : false;
		
		$can_show_images = empty( $general_settings['has_images'] ) ? false : true;
		
		$has_featured = apply_filters( 'acadp_has_featured', empty( $featured_listing_settings['enabled'] ) ? false : true );
		if ( $has_featured ) {
			$has_featured = $atts['featured'];
		}
				
		$current_order       = acadp_get_listings_current_order( $atts['orderby'] . '-' . $atts['order'] );
		$can_show_pagination = (int) $atts['pagination'];
		
		$has_price = empty( $general_settings['has_price'] ) ? false : true;
		$can_show_price = false;
		
		if ( $has_price ) {
			$can_show_price = isset( $listings_settings['display_in_listing'] ) && in_array( 'price', $listings_settings['display_in_listing'] ) ? true : false;
		}
			
		$has_location = empty( $general_settings['has_location'] ) ? false : true;
		$can_show_location = false;
		
		if ( $has_location ) {
			$can_show_location = isset( $listings_settings['display_in_listing'] ) && in_array( 'location', $listings_settings['display_in_listing'] ) ? true : false;
		}
		
		$span = 12;
		if ( $can_show_images ) $span = $span - 2;
		if ( $can_show_price ) $span = $span - 3;
		$span_middle = 'col-md-' . $span;
		
		// Define the query
		$paged = acadp_get_page_number();
		$favourite_posts = get_user_meta( get_current_user_id(), 'acadp_favourites', true );
			
		$args = array(				
			'post_type'      => 'acadp_listings',
			'post_status'    => 'publish',		
			'posts_per_page' => (int) $atts['listings_per_page'],
			'paged'          => $paged,
			'post__in'       => ! empty( $favourite_posts ) ? $favourite_posts : array(0)
	  	);
		
		if ( $has_location && $general_settings['base_location'] > 0 ) {			
			$args['tax_query'] = array(
				array(
					'taxonomy'         => 'acadp_locations',
					'field'            => 'term_id',
					'terms'            => $general_settings['base_location'],
					'include_children' => true,
				),
			);				
		}
			
		$meta_queries = array();
			
		if ( 'map' == $view ) {
			$meta_queries['hide_map'] = array(
				'key'     => 'hide_map',
				'value'   => 0,
				'compare' => '='
			);
		}
		
		if ( $has_featured ) {			
			if ( 'featured' == $atts['filterby'] ) {
				$meta_queries['featured'] = array(
					'key'     => 'featured',
					'value'   => 1,
					'compare' => '='
				);
			} else {
				$meta_queries['featured'] = array(
					'key'     => 'featured',
					'type'    => 'NUMERIC',
					'compare' => 'EXISTS',
				);
			}				
		}
			
		switch ( $current_order ) {
			case 'title-asc' :
				if ( $has_featured ) {
					$args['meta_key'] = 'featured';
					$args['orderby']  = array(
						'meta_value_num' => 'DESC',
						'title'          => 'ASC',
					);
				} else {
					$args['orderby'] = 'title';
					$args['order']   = 'ASC';
				};
				break;
			case 'title-desc' :
				if ( $has_featured ) {
					$args['meta_key'] = 'featured';
					$args['orderby']  = array(
						'meta_value_num' => 'DESC',
						'title'          => 'DESC',
					);
				} else {
					$args['orderby'] = 'title';
					$args['order']   = 'DESC';
				};
				break;
			case 'date-asc' :
				if ( $has_featured ) {
					$args['meta_key'] = 'featured';
					$args['orderby']  = array(
						'meta_value_num' => 'DESC',
						'date'           => 'ASC',
					);
				} else {
					$args['orderby'] = 'date';
					$args['order']   = 'ASC';
				};
				break;
			case 'date-desc' :
				if ( $has_featured ) {
					$args['meta_key'] = 'featured';
					$args['orderby']  = array(
						'meta_value_num' => 'DESC',
						'date'           => 'DESC',
					);
				} else {
					$args['orderby'] = 'date';
					$args['order']   = 'DESC';
				};
				break;
			case 'price-asc' :
				if ( $has_featured ) {
					$meta_queries['price'] = array(
						'key'     => 'price',
						'type'    => 'NUMERIC',
						'compare' => 'EXISTS',
					);
					
					$args['orderby']  = array( 
						'featured' => 'DESC',
						'price'    => 'ASC',
					);
				} else {
					$args['meta_key'] = 'price';
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'ASC';
				};
				break;
			case 'price-desc' :
				if ( $has_featured ) {
					$meta_queries['price'] = array(
						'key'     => 'price',
						'type'    => 'NUMERIC',
						'compare' => 'EXISTS',
					);

					$args['orderby']  = array( 
						'featured' => 'DESC',
						'price'    => 'DESC',
					);
				} else {
					$args['meta_key'] = 'price';
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'DESC';
				};
				break;
			case 'views-asc' :
				if ( $has_featured ) {
					$meta_queries['views'] = array(
						'key'     => 'views',
						'type'    => 'NUMERIC',
						'compare' => 'EXISTS',
					);

					$args['orderby']  = array( 
						'featured' => 'DESC',
						'views'    => 'ASC',
					);
				} else {
					$args['meta_key'] = 'views';
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'ASC';
				};
				break;
			case 'views-desc' :
				if ( $has_featured ) {
					$meta_queries['views'] = array(
						'key'     => 'views',
						'type'    => 'NUMERIC',
						'compare' => 'EXISTS',
					);

					$args['orderby']  = array( 
						'featured' => 'DESC',
						'views'    => 'DESC',
					);
				} else {
					$args['meta_key'] = 'views';
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'DESC';
				};
				break;
			case 'rand-asc' :
			case 'rand-desc' :
				if ( $has_featured ) {
					$args['meta_key'] = 'featured';
					$args['orderby']  = 'meta_value_num rand';
				} else {
					$args['orderby'] = 'rand';
				};
				break;
		}
			
		$count_meta_queries = count( $meta_queries );
		if ( $count_meta_queries ) {
			$args['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries;
		}
			
		$acadp_query = new WP_Query( $args );
			
		// Start the Loop
		global $post;
			
		// Process output
		if ( $acadp_query->have_posts() ) {
			ob_start();
			include( acadp_get_template( "listings/acadp-public-listings-$view-display.php" ) );
			return ob_get_clean();		
		} else {		
			return '<span>' . __( 'No Results Found.', 'advanced-classifieds-and-directory-pro' ) . '</span>';		
		}			
	}	
	
	/**
	 * Remove favourites.
	 *
	 * @since 1.0.0
	 * @param int   $post_id Post ID.
	 */
	public function remove_favourites( $post_id ) {	
		$favourites = (array) get_user_meta( get_current_user_id(), 'acadp_favourites', true );
		
		if ( in_array( $post_id, $favourites ) ) {
			if ( ( $key = array_search( $post_id, $favourites ) ) !== false ) {
    			unset( $favourites[ $key ] );
			}
		}
		
		$favourites = array_filter( $favourites );
		$favourites = array_values( $favourites );
		
		delete_user_meta( get_current_user_id(), 'acadp_favourites' );
		update_user_meta( get_current_user_id(), 'acadp_favourites', $favourites );

		// redirect
		$redirect_url = acadp_get_favourites_page_link();
    	wp_redirect( $redirect_url );
   		exit();		
	}

}

	/***Mel
	 * To ensure arrays are properly sanitized to WordPress Codex standards,
	 * they encourage usage of sanitize_text_field(). That only works with a single
	 * variable (string). This function allows for a full blown array to get sanitized
	 * properly, while sanitizing each individual value in a key -> value pair.
	 *
	 * Source: https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
	 * Author: Broshi, answered Feb 5 '17 at 9:14
	 */
	function wporg_recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = wporg_recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}
		return $array;
	}