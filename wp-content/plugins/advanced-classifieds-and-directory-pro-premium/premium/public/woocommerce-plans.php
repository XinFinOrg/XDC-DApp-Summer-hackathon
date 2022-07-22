<?php

/**
 * Woocommerce Plans.
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
 * ACADP_Premium_Public_Woocommerce_Plans class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Public_Woocommerce_Plans {	

	/**
	 * Manage form submissions.
	 *
	 * @since 1.6.4
	 */
	public function manage_actions() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {		
			if ( isset( $_POST['acadp_wc_checkout_nonce'] ) && wp_verify_nonce( $_POST['acadp_wc_checkout_nonce'], 'acadp_wc_process_payment' ) ) {
				ob_start();		
				$this->process_order();				
			}			
		}	
	}

	/**
	 * Process Order.
	 *
	 * @since  1.6.4
	 * @access private
	 */
	public function process_order() {
		global $woocommerce;

		$listing_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
		$product_id = isset( $_POST['wc_plan'] ) ? (int) $_POST['wc_plan'] : 0;			
		
		$woocommerce->cart->empty_cart();
		$woocommerce->cart->add_to_cart( $product_id );
				
		$checkout_url = wc_get_checkout_url();
		$redirect_url = acadp_premium_woocommerce_get_checkout_page_url( $checkout_url, $listing_id );			
		
		wp_redirect( $redirect_url );
		exit();				
	}

	/**
	 * Add rewrite rules.
	 *
	 * @since 1.6.4
	 */
	public function add_rewrites() {		
		$url = home_url();
		
		// WooCommerce Checkout Page
		$id = get_option( 'woocommerce_checkout_page_id' );
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );			
			$link = trim( $link, '/' );		
			
			add_rewrite_rule( "$link/([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&acadp_listing=$matches[1]', 'top' );
		}		
	}

	/**
	 * Overwrites [acadp_checkout] shortcode from the core plugin.
	 *
	 * @since 1.6.4
	 */
	public function add_remove_shortcodes() {	
		remove_shortcode( 'acadp_checkout' );
		add_shortcode( 'acadp_checkout', array( $this, 'run_shortcode_checkout' ) );	
	}

	/**
	 * Process the shortcode [acadp_checkout].
	 *
	 * @since 1.6.4
	 */
	public function run_shortcode_checkout() {	
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();		
		}
		
		$shortcode = 'acadp_checkout';		
		$post_id = get_query_var( 'acadp_listing' );
		
		if ( ! empty( $post_id ) ) {				
			// Dependencies
			wp_enqueue_style( ACADP_PLUGIN_NAME );

			wp_enqueue_script( ACADP_PLUGIN_NAME );
			wp_enqueue_script( ACADP_PLUGIN_NAME . '-premium-public-woocommerce-plans' );
			
			// ...
			$action = get_query_var( 'acadp_action' );

			if ( 'promote' == $action ) {
				// Define the query
				$args = array(
					'post_type' => 'product',			
					'post_status' => 'publish',
					'posts_per_page' => 1,
					'no_found_rows' => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'tax_query'	=> array(
						array(
							'taxonomy' => 'product_type',
							'field' => 'slug',
							'terms' => 'listings_featured'
						)
					)
				);
		
				$acadp_query = new WP_Query( $args );
				
				// Process output
				if ( $acadp_query->have_posts() ) {
					ob_start();
					include ACADP_PLUGIN_DIR . 'premium/public/templates/checkout-woocommerce-featured.php';
					return ob_get_clean();
				}
			} else {
				// Define the query
				$acadp_query = $this->get_plans( $post_id );
				
				// Process output
				if ( $acadp_query->have_posts() ) {
					$has_checkout_page = -1;

					foreach ( $acadp_query->posts as $product ) {
						$order_id = acadp_premium_woocommerce_user_has_active_plan( $product->ID );

						if (  $order_id > 0 ) {
							$has_checkout_page = 0;
							break;
						} else {
							$is_purchasable = acadp_premium_woocommerce_product_is_purchasable( true, $product->ID );

							if ( $is_purchasable ) {
								$has_checkout_page = 1;
							}
						}
					}

					if ( $has_checkout_page > 0 ) {
						ob_start();
						include ACADP_PLUGIN_DIR . 'premium/public/templates/checkout-woocommerce.php';
						return ob_get_clean();
					}
				}
			}
		}
		
		return '<span>' . __( 'Sorry, something went wrong.', 'advanced-classifieds-and-directory-pro' ) . '</span>';	
	}
	
	/**
	 * Change "publish" status of a new listing to "draft" when there are plans available
	 * for purchase. So, listings are published only if payment is success.
	 *
	 * @since  1.6.4
	 * @param  string $status Default status of a new listing.
	 * @return string $status Updated listing status.
	 */
	public function new_listing_status( $status ) {			
		$products = $this->get_plans( 0, $_POST['acadp_category'], 'array' );

		if ( count( $products ) ) {
			$has_checkout_page = -1;

			foreach ( $products as $product ) {
				$order_id = acadp_premium_woocommerce_user_has_active_plan( $product->ID );

				if (  $order_id > 0 ) {
					$has_checkout_page = 0;
					break;
				} else {
					$is_purchasable = acadp_premium_woocommerce_product_is_purchasable( true, $product->ID );

					if ( $is_purchasable ) {
						$has_checkout_page = 1;
					}
				}
			}

			if ( $has_checkout_page > 0 ) {
				$status = 'draft';
			}
		}
		
		return $status;		
	}

	/**
	 * Check if plans available.
	 *
	 * @since  1.6.4
	 * @param  bool   $has_checkout_page Has checkout page.
	 * @param  int    $post_id           Post ID.
	 * @param  string $filter            Monetization Type(Submission, Promotion or All).
	 * @return bool   $has_checkout_page True if available, incoming value if not.
	 */
	public function has_checkout_page( $has_checkout_page, $post_id, $filter = 'all' ) {
		$has_checkout_page = 0;	

		$products = $this->get_plans( $post_id, 0, 'array' );

		if ( count( $products ) && 'promotion' != $filter ) {
			$has_checkout_page = -1;

			foreach ( $products as $product ) {
				$order_id = acadp_premium_woocommerce_user_has_active_plan( $product->ID );

				if (  $order_id > 0 ) {
					$has_checkout_page = 0;

					if ( defined( 'ACADP_LISTING_SUBMISSION' ) || defined( 'ACADP_LISTING_RENEWAL' ) ) {
						update_post_meta( $post_id, 'wc_plan_id', $product->ID );

						$featured = (int) get_post_meta( $product->ID, 'acadp_featured', true );
						update_post_meta( $post_id, 'featured', $featured );

						if ( 'publish' != get_post_status( $post_id ) ) {
							update_post_meta( $post_id, 'listing_status', 'pending' );
						}
						
						$listings_submitted = (int) get_post_meta( $order_id, 'acadp_listings_submitted', true );
						update_post_meta( $order_id, 'acadp_listings_submitted', ++$listings_submitted );						
					}
					break;
				} else {
					$is_purchasable = acadp_premium_woocommerce_product_is_purchasable( true, $product->ID );

					if ( $is_purchasable ) {
						$has_checkout_page = 1;
					}
				}
			}
		}

		return ( $has_checkout_page > 0 ) ? 1 : 0;		
	}

	/**
	 * Get listing duration based for the selected WooCommerce plan.
	 *
	 * @since  1.6.4
	 * @param  int   $days    Listing duration.
	 * @param  int   $post_id Post ID.
	 * @return int   $days    Updated duration.
	 */
	public function listing_duration( $days, $post_id ) {	
		$product_id = get_post_meta( $post_id, 'wc_plan_id', true );
		
		if ( ! empty( $product_id ) ) {		
			$days = get_post_meta( $product_id, 'acadp_listing_duration', true );
		}
		
		return $days;		
	}

	/**
	 * "Featured" Listings are always enabled when using this plugin.
	 *
	 * @since  1.6.4
	 * @access private
	 * @param  bool    $featured Default "featured" status.
	 * @return bool              Always "true".
	 */
	public function has_featured( $featured ) {
		return true;
	}

	/**
	 * Hide "Promote" button in the manage listings page.
	 *
	 * @since  1.6.4
	 * @param  bool  $can_promote Default "promote" status.
	 * @return bool               Always "false".
	 */
	public function can_promote( $can_promote ) {
		// Define the query
		$args = array(
			'post_type' => 'product',			
			'post_status' => 'publish',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'tax_query'	=> array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => 'listings_featured'
				)
			)
		);

		$acadp_query = new WP_Query( $args );
		
		// Process output
		if ( $acadp_query->have_posts() ) {
			return true;
		}

		return false;
	}

	/**
	 * Add WooCommerce order details to the user's dashboard.
	 *
	 * @since 1.6.4
	 */
	public function user_dashboard() {	
		$wc_plans_settings = get_option( 'acadp_wc_plans_settings' );

		if ( isset( $wc_plans_settings['product_type'] ) && 'subscription' == $wc_plans_settings['product_type'] ) {
			if ( class_exists( 'WC_Subscriptions' ) ) {
				printf( 
					'<h2>%s</h2>',
					esc_html__( 'My subscriptions', 'advanced-classifieds-and-directory-pro' ) 
				);

				WC_Subscriptions::get_my_subscriptions_template();
			}
		}

		$args = array(
			'current_user' => get_current_user_id(),
			'order_count'  => -1 
		);

		echo wc_get_template( 'myaccount/my-orders.php', $args );
	}

	/**
	 * Retrieve the woocommerce orders table columns.
	 *
	 * @since  1.8.0
	 * @param  array $columns Array of default table columns.
	 * @return array $columns Updated list of table columns.
	 */
	public function get_orders_columns( $columns ) {
		$page_settings = get_option( 'acadp_page_settings' );
		$user_dashboard_page_id = (int) $page_settings['user_dashboard'];

		if ( $user_dashboard_page_id > 0 && is_page( $user_dashboard_page_id ) ) {
			$new_columns = array(
				'acadp_details' => __( 'Details', 'advanced-classifieds-and-directory-pro' )
			);

			$columns = acadp_array_insert_after( 'order-number', $columns, $new_columns );
		}

		return $columns;
	}

	/**
	 * Renders the custom columns in the woocommerce orders table.
	 *
	 * @since 1.8.0
	 * @param WC_Order $order A WooCommerce order object.
	 */
	public function orders_custom_column_content( $order ) {
		// Order ID (added WooCommerce 3+ compatibility)
		$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
		$product_id = get_post_meta( $order_id, 'acadp_plan_id', true );

		if ( ! empty( $product_id ) ) {
			$product = wc_get_product( $product_id );
			
			if ( $product->is_type( 'listings_featured' ) ) {
				echo $product->get_title();
			} else {		
				$listings_limit = (int) get_post_meta( $product_id, 'acadp_listings_limit', true );			

				if ( 0 == $listings_limit ) {
					printf(
						'%s<div>- %s: <strong>%s</strong></div>',
						$product->get_title(),
						esc_html__( 'Listings limit', 'advanced-classifieds-and-directory-pro' ),
						esc_html__( 'unlimited', 'advanced-classifieds-and-directory-pro' )
					);
				} else {
					$listings_submitted = get_post_meta( $order_id, 'acadp_listings_submitted', true );

					printf(
						'%s<div>- %s: <strong>%d</strong></div><div>- %s: <strong>%d</strong></div>',
						$product->get_title(),
						esc_html__( 'Listings limit', 'advanced-classifieds-and-directory-pro' ),
						$listings_limit,
						esc_html__( 'Listings submitted', 'advanced-classifieds-and-directory-pro' ),
						$listings_submitted
					);
				}
			}
		} else {
			foreach ( $order->get_items() as $item ) {
				echo $item->get_name();
			}
		}
	}

	/**
	 * Renders custom content after subscription id in the woocommerce subscriptions table.
	 *
	 * @since 1.8.0
	 * @param WC_Subscription $subscription A WooCommerce subscription object.
	 */
	public function subscriptions_custom_content( $subscription ) {
		$page_settings = get_option( 'acadp_page_settings' );
		$user_dashboard_page_id = (int) $page_settings['user_dashboard'];

		if ( $user_dashboard_page_id > 0 && is_page( $user_dashboard_page_id ) ) {
			$subscription_id = $subscription->get_id();	
			$product_id = get_post_meta( $subscription_id, 'acadp_plan_id', true );

			if ( ! empty( $product_id ) ) {	
				$product = wc_get_product( $product_id );

				$listings_limit = (int) get_post_meta( $product_id, 'acadp_listings_limit', true );			

				if ( 0 == $listings_limit ) {
					printf(
						'<div>%s</div><div>- %s: <strong>%s</strong></div>',
						$product->get_title(),
						esc_html__( 'Listings limit', 'advanced-classifieds-and-directory-pro' ),
						esc_html__( 'unlimited', 'advanced-classifieds-and-directory-pro' )
					);
				} else {
					$listings_submitted = get_post_meta( $subscription_id, 'acadp_listings_submitted', true );

					printf(
						'<div>%s</div><div>- %s: <strong>%d</strong></div><div>- %s: <strong>%d</strong></div>',
						$product->get_title(),
						esc_html__( 'Listings limit', 'advanced-classifieds-and-directory-pro' ),
						$listings_limit,
						esc_html__( 'Listings submitted', 'advanced-classifieds-and-directory-pro' ),
						$listings_submitted
					);
				}
			}
		}
	}

	/**
	 * Disable "Categories" dropdown in the listing edit form.
	 *
	 * @since  1.6.4
	 * @param  string $html    HTML. Categories dropdown.
	 * @param  int    $post_id Post ID.
	 * @return string $html    Filtered dropdown HTML.
	 */
	public function listing_form_categories_dropdown( $html, $post_id ) {		
		$plan_settings = get_option( 'acadp_wc_plans_settings' );
		
		if ( ! empty( $plan_settings['disable_categories_listings_edit'] ) ) {		
			if ( 0 == $post_id ) {				
				$html .= sprintf( 
					'<p class="help-block">%s</p>', 
					__( "Since our submission charges vary based on categories, you cannot change the category after the listing has been submitted. So, make sure that you've selected the right category before submitting this listing.", 'advanced-classifieds-and-directory-pro' ) 
				);
			} else {
				$categories = wp_get_object_terms( $post_id, 'acadp_categories' );				
				$titles = array();
				$html = '';

				foreach ( $categories as $category ) {
					$html .= sprintf( 
						'<input type="hidden" name="acadp_category[]" value="%d" />', 
						$category->term_id 
					);

					$titles[] = $category->name;
				}

				
				$html .= sprintf( 
					'<p class="form-control-static"><strong>%s</strong></p>', 
					implode( ',', $titles ) 
				);

				$html .= sprintf( 
					'<p class="help-block">%s</p>', 
					__( 'Since our submission charges vary based on categories, you cannot change it here. Contact the "Site Admin" in case you\'ve accidentally posted in the wrong category.', 'advanced-classifieds-and-directory-pro' ) 
				);
			}		
		}
		
		return $html;		
	}

	/**
	 * Get images count the user can upload.
	 *
	 * @since  1.6.4
	 * @param  int   $count   Default images count.
	 * @param  int   $post_id Post ID.
	 * @return int   $count   Filtered images count.
	 */
	public function images_limit( $count, $post_id ) {		
		$product_id = (int) get_post_meta( $post_id, 'wc_plan_id', true );
		
		if ( $product_id > 0 ) {
			$count = (int) get_post_meta( $product_id, 'acadp_images_limit', true );
		}
		
		return $count;		
	}

	/**
	 * Remove quantity fields.
	 *
	 * @since  1.6.4
	 * @param  bool   $individually Default status.
	 * @param  object $product      WooCommerce product.
	 * @return bool   $individually "true" if the product belongs to our plugin, "false" if not.
	 */
	public function is_sold_individually( $individually, $product ) {
		$product_id = $product->get_id();
		$product_type = $product->get_type();

		$is_acadp_subscription = get_post_meta( $product_id, 'is_acadp_subscription', true );
				
		if ( 'listings_package' == $product_type || 'listings_featured' == $product_type || ! empty( $is_acadp_subscription ) ) {		
			$individually = true;			
		}
		
		return $individually;		
	}

	/**
	 * Disable Repeat Purchase.
	 *
	 * @since  1.8.0
	 * @param  bool   $purchasable Default status.
	 * @param  object $product     WooCommerce product.
	 * @return bool   $purchasable "true" if the product can be purchased, "false" if not.
	 */
	public function is_purchasable( $purchasable, $product ) {
		$product_id = $product->get_id();
		$product_type = $product->get_type();

		$is_acadp_subscription = get_post_meta( $product_id, 'is_acadp_subscription', true );

		if ( 'listings_package' == $product_type || ! empty( $is_acadp_subscription ) ) {	
			$purchasable = acadp_premium_woocommerce_product_is_purchasable( $purchasable, $product_id );
		}
		
		return $purchasable;		
	}

	/**
	 * Output the simple product add to cart area.
	 *
	 * @since 1.8.0
	 */
	public function add_to_cart() {
		wc_get_template( 'single-product/add-to-cart/simple.php' );
	}

	/**
	 * Add a hidden field (listing_id) in the WooCommerce checkout page.
	 *
	 * @since 1.6.4
	 * @param array $checkout WooCommerce checkout page values.
	 */
	public function checkout_field( $checkout ) {		
		$listing_id = get_query_var( 'acadp_listing' );
		
		if ( isset( $listing_id ) ) {			
			echo '<input type="hidden" name="listing_id" value="' . $listing_id . '">';
		}		
	}

	/**
	 * Called after WooCommerce checkout order processed.
	 *
	 * @since 1.6.4
	 * @param int   $order_id WooCommerce Order ID.
	 */
	public function checkout_order_processed( $order_id ) { 
		$order = wc_get_order( $order_id );		
		
		foreach ( $order->get_items() as $key => $item ) {
			// The corresponding product ID (added WooCommerce 3+ compatibility)
			$product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : $item['product_id'];
			$product = wc_get_product( $product_id );
			
			if ( $product->is_type( 'listings_package' ) ) {				
				$listing_id = isset( $_POST['listing_id'] ) ? (int) $_POST['listing_id'] : 0;

				update_post_meta( $order_id, 'acadp_order_referrer', $listing_id );	
				update_post_meta( $order_id, 'acadp_listings_submitted', 0 );
				update_post_meta( $order_id, 'acadp_plan_id', $product_id );
			}
			
			if ( $product->is_type( 'listings_featured' ) ) {				
				$listing_id = isset( $_POST['listing_id'] ) ? (int) $_POST['listing_id'] : 0;

				update_post_meta( $order_id, 'acadp_order_referrer', $listing_id );	
				update_post_meta( $order_id, 'acadp_plan_id', $product_id );
			}
		}										
	}

	/**
	 * Called when WooCommerce order status changed to "Completed".
	 *
	 * @since 1.6.4
	 * @param int   $order_id WooCommerce Order ID.
	 */
	public function order_status_completed( $order_id ) {    	
		$order = wc_get_order( $order_id );		
		
		foreach ( $order->get_items() as $key => $item ) {			
			// The corresponding product ID (added WooCommerce 3+ compatibility)
			$product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : $item['product_id'];		
			$product = wc_get_product( $product_id );
			
			if ( $product->is_type( 'listings_package' ) ) {
				$listings_submitted = get_post_meta( $order_id, 'acadp_listings_submitted', true );

				if ( empty( $listings_submitted ) ) {				
					$listing_id = (int) get_post_meta( $order_id, 'acadp_order_referrer', true );
					$transient_key = 'acadp_listing_submitted_' . get_current_user_id();

					if ( $listing_id > 0 ) {
						acadp_premium_woocommerce_update_listing( $listing_id, $product_id );					
						update_post_meta( $order_id, 'acadp_listings_submitted', 1 );

						set_transient( $transient_key, 'yes' );
					} else {
						set_transient( $transient_key, 'no' );
					}				
				}			
			}
			
			if ( $product->is_type( 'listings_featured' ) ) {
				$listing_id = (int) get_post_meta( $order_id, 'acadp_order_referrer', true );

				if ( $listing_id > 0 ) {
					update_post_meta( $listing_id, 'featured', 1 );
				}				
			}
		}		
	}

	/**
	 * Called when a WooCommerce subscription is created.
	 *
	 * @since 1.8.0
	 * @param WC_Subscription $subscription A WooCommerce subscription object.
	 * @param WC_Order        $order        A WooCommerce order object.
	 */
	public function checkout_subscription_created( $subscription, $order ) {
		$subscription_id = $subscription->get_id();	
		
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
 			$product_id = $product->get_id();

			$is_acadp_subscription = get_post_meta( $product_id, 'is_acadp_subscription', true );
			if ( ! empty( $is_acadp_subscription ) ) {
				$listing_id = isset( $_POST['listing_id'] ) ? (int) $_POST['listing_id'] : 0;
								
				update_post_meta( $subscription_id, 'acadp_order_referrer', $listing_id );
				update_post_meta( $subscription_id, 'acadp_listings_submitted', 0 );
				update_post_meta( $subscription_id, 'acadp_plan_id', $product_id );					
			}							
		}										
	}

	/**
	 * Called when a WooCommerce subscription status changed to "active".
	 *
	 * @since 1.8.0
	 * @param WC_Subscription $subscription A WooCommerce subscription object.
	 */
	public function subscription_status_active( $subscription ) { 
		$subscription_id = $subscription->get_id();	

		$product_id = get_post_meta( $subscription_id, 'acadp_plan_id', true );
		if ( ! empty( $product_id ) ) {
			$listings_submitted = get_post_meta( $subscription_id, 'acadp_listings_submitted', true );

			if ( empty( $listings_submitted ) ) {					
				$listing_id = (int) get_post_meta( $subscription_id, 'acadp_order_referrer', true );
				$transient_key = 'acadp_listing_submitted_' . get_current_user_id();

				if ( $listing_id > 0 ) {
					acadp_premium_woocommerce_update_listing( $listing_id, $product_id );				
					update_post_meta( $subscription_id, 'acadp_listings_submitted', 1 );

					set_transient( $transient_key, 'yes' );
				} else {
					set_transient( $transient_key, 'no' );
				}					
			}
		}	
	}

	/**
	 * Set orders from ACADP marked as "Completed" automatically.
	 *
	 * @since  1.8.0
	 * @param  bool   $virtual_downloadable_item If item is virtual and downloadable.
	 * @param  Object $product                   Product Object.
	 * @return bool   $virtual_downloadable_item Should not be set to processing.
	 */
	public function order_item_needs_processing( $virtual_downloadable_item, $product ) {
		$product_id = $product->get_id();
		$is_acadp_subscription = get_post_meta( $product_id, 'is_acadp_subscription', true );

		if ( $product->is_type( 'listings_package' ) || $product->is_type( 'listings_featured' ) || ! empty( $is_acadp_subscription ) ) {
			$virtual_downloadable_item = false;
		}
	
		return $virtual_downloadable_item;
	}

	/**
	 * Display custom messages on the WooCommerce thank you page.
	 *
	 * @since 1.8.0
	 */
	public function before_thankyou() {
		if ( ! is_user_logged_in() ) return;

		$transient_key = 'acadp_listing_submitted_' . get_current_user_id();

		if ( $value = get_transient( $transient_key ) ) {
			echo '<div class="acadp">';
			echo '<div class="alert alert-info" role="alert">';

			if ( 'yes' == $value ) {
				printf( 
					__( 'Congrats! <a href="%s"><strong>CLICK HERE</strong></a> to view your listings.', 'advanced-classifieds-and-directory-pro' ),
					esc_url( acadp_get_manage_listings_page_link() )
				);
			} else {
				printf( 
					__( 'Congrats! <a href="%s"><strong>CLICK HERE</strong></a> to submit your listing.', 'advanced-classifieds-and-directory-pro' ),
					esc_url( acadp_get_listing_form_page_link() )
				);
			}

			echo '</div>';
			echo '</div>';

			delete_transient( $transient_key );
		}    
	}

	/**
	 * Get the list of plans.
	 *
	 * @since  1.6.5
	 * @access private
	 * @param  int            $post_id       Listing ID.
	 * @param  int            $term_id       Listing term ID.
	 * @param  string         $response_type Response data type.
	 * @return array|WP_Query $plans         Plans based on the $response_type.
	 */
	private function get_plans( $post_id = 0, $term_id = 0, $response_type = 'WP_Query' ) {
		$wc_plans_settings = get_option( 'acadp_wc_plans_settings' );
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );

		$args = array(
			'post_type' => 'product',			
			'post_status' => 'publish',
			'posts_per_page' => 500,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		);

		$meta_queries = array();

		if ( isset( $wc_plans_settings['product_type'] ) && 'subscription' == $wc_plans_settings['product_type'] ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => 'subscription', 
				),
			);

			$meta_queries[] = array(
				'key' => 'is_acadp_subscription',
				'value' => 'yes',
				'compare' => '='
			);
		} else {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_type',
					'field' => 'slug',
					'terms' => 'listings_package', 
				),
			);
		}

		if ( empty( $multi_categories_settings['enabled'] ) ) {
			if ( empty( $term_id ) && $post_id > 0 ) {
				$terms = wp_get_object_terms( $post_id, 'acadp_categories', array( 'fields' => 'ids' ) );
				$term_id = $terms[0];
			}

			if ( ! empty( $term_id ) ) {
				$meta_queries[] = array(
					'relation' => 'OR',
					array(
						'key' => 'acadp_categories',
						'value' => '"' . (int) $term_id . '"',
						'compare' => 'LIKE'
					),
					array(
						'key' => 'acadp_categories',
						'value' => '-1',
						'compare' => 'LIKE'
					)
				);
			}			
		}

		$count_meta_queries = count( $meta_queries );
		if ( $count_meta_queries ) {
			$args['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries;
		}

		$acadp_query = new WP_Query( $args );

		if ( 'array' == $response_type ) {
			$plans = array();

			if ( $acadp_query->have_posts() ) {
				$plans = $acadp_query->posts;
			}
		} else {
			$plans = $acadp_query;
		}		

		return $plans;
	}

}
