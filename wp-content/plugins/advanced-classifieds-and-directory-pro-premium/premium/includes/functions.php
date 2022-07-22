<?php

/**
 * This file holds the helper functions.
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
 * Get ACADP categories checklist.
 *
 * @since  1.6.5
 * @param  int    $post_id Post ID.
 * @return string $html	   ACADP categories checklist.
 */
function acadp_premium_get_terms_checklist( $post_id ) {
	$selected_cats = wp_get_object_terms( $post_id, 'acadp_categories', array( 'fields' => 'ids' ) );		
			
	$args = array(
		'descendants_and_self' => false,
		'selected_cats'        => ! empty( $selected_cats ) ? $selected_cats : false,
		'popular_cats'         => false,
		'taxonomy'             => 'acadp_categories',
		'checked_ontop'        => true,
		'walker'               => new ACADP_Walker_Category_Checklist,
		'echo'                 => false
	);

	$html = '<div id="acadp-multi-categories">';
	$html .= '<div id="acadp-multi-categories-all" class="acadp-multi-categories-all tabs-panel" style="display: block;">';
	$html .= '<input type="hidden" name="tax_input[acadp_categories][]" value="0">';
	$html .= '<ul id="acadp-multi-categories-checklist">';
	$html .= wp_terms_checklist( $post_id, $args );
	$html .= '</ul>';
	$html .= '</div>';		
	$html .= '</div>';
	
	return $html;	
}

/**
 * Check if WooCommerce plugin is installed and active.
 *
 * @since  1.6.4
 * @return bool  "true" if active, "false" if not.
 */
function acadp_premium_is_woocommerce_active() {
	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

	if ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
		return true;
	}

	return false;
}

/**
 * Get the slider attributes.
 *
 * @since  1.6.4
 * @param  array  $atts     An associative array of attributes.
 * @param  string $slider   "banner_rotator" or "carousel_slider".
 * @param  bool   $is_admin 1 or 0.
 * @return array  $atts     Updated array of attributes.
 */
function acadp_premium_slider_atts( $atts, $slider = "banner_rotator", $is_admin = 0 ) {
	$general_settings = get_option( 'acadp_general_settings' );
	$listings_settings = get_option( 'acadp_listings_settings' );
	$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
	
	// Define the array of defaults
	$defaults = array(
		'title'                => '',
		'base_location'        => max( 0, $general_settings['base_location'] ),
		'location'             => max( 0, $general_settings['base_location'] ),
		'follow_locations'     => 1,
		'category'             => 0,		
		'follow_categories'    => 1,
		'featured'             => 0,
		'filterby'             => '',
		'limit'                => 15,
		'orderby'              => $listings_settings['orderby'],
		'order'                => $listings_settings['order'],
		'images_ratio'         => 0.5625,
		'dots'                 => 1,
		'dots_bg_color'        => '#DDDDDD',
		'dots_active_bg_color' => '#008BCF',
		'arrows'               => 1,
		'arrows_bg_color'      => '#008BCF',
		'arrows_icon_color'    => '#FFFFFF',
		'autoplay'             => 0,
		'autoplay_interval'    => 5000,
		'speed'                => 500
	);

	if ( isset( $atts['featured'] ) && (int) $atts['featured'] > 1 ) {
		$defaults['filterby'] = 'featured';
	}
	
	if ( 'banner_rotator' == $slider ) {
		$defaults['images_scale_type']    = 'fill';
		$defaults['show_content']         = 1;
		$defaults['arrows_top_offset']    = '50%';
		$defaults['arrows_left_offset']   = '30px';
		$defaults['arrows_right_offset']  = '30px';
		$defaults['arrows_padding']       = '10px';
		$defaults['arrows_icon_size']     = '28px';
		$defaults['arrows_border_radius'] = '24px';
		$defaults['fade']                 = 1;
	}
	
	if ( 'carousel_slider' == $slider ) {
		$defaults['slides_to_show']       = 4;
		$defaults['slides_to_scroll']     = 1;
		$defaults['images_size']          = 'medium';
		$defaults['images_scale_type']    = 'uniform';
		$defaults['arrows_top_offset']    = '35%';
		$defaults['arrows_left_offset']   = '-30px';
		$defaults['arrows_right_offset']  = '-30px';
		$defaults['arrows_padding']       = '5px';
		$defaults['arrows_icon_size']     = '14px';
		$defaults['arrows_border_radius'] = '12px';
		$defaults['center_mode']          = 0;
	}
	
	// Merge incoming $atts array with $defaults
	if ( is_array( $atts ) ) {
		$atts = array_merge( $defaults, $atts );
	} else {
		$atts = $defaults;
	}
	
	// Return if widget back-end
	if ( $is_admin ) {
		return $atts;
	}

	// Slick data
	$atts['data'] = array(
		'dots'          => $atts['dots']     ? true : false,
		'arrows'        => $atts['arrows']   ? true : false,
		'autoplay'      => $atts['autoplay'] ? true : false,
		'autoplaySpeed' => (int) $atts['autoplay_interval'],
		'speed'         => (int) $atts['speed']
	);
	
	if ( "carousel_slider" == $slider ) {		
		$atts['slides_to_show']   = (int) $atts['slides_to_show'];
		$atts['slides_to_scroll'] = (int) $atts['slides_to_scroll'];
		
		$responsive = array();
		
		// 1024
		$slides_to_show   = 3;
		$slides_to_scroll = 1;

		if ( $atts['slides_to_show'] < 3 ) {
			$slides_to_show = $atts['slides_to_show'];
		}

		if ( $atts['slides_to_show'] == $atts['slides_to_scroll'] ) {
			$slides_to_scroll = $slides_to_show;
		}

		$responsive[] = array(
			'breakpoint' => 1024,
			'settings'   => array(
				'slidesToShow'   => $slides_to_show,
				'slidesToScroll' => $slides_to_scroll
			)
		);

		// 600
		$slides_to_show   = 2;
		$slides_to_scroll = 1;

		if ( $atts['slides_to_show'] < 2 ) {
			$slides_to_show = $atts['slides_to_show'];
		}

		if ( $atts['slides_to_show'] == $atts['slides_to_scroll'] ) {
			$slides_to_scroll = $slides_to_show;
		}

		$responsive[] = array(
			'breakpoint' => 600,
			'settings'   => array(
				'slidesToShow'   => $slides_to_show,
				'slidesToScroll' => $slides_to_scroll
			)
		);

		// 480
		$responsive[] = array(
			'breakpoint' => 480,
			'settings'   => array(
				'slidesToShow'   => 1,
				'slidesToScroll' => 1
			)	
		);
	
		// ...
		$atts['data']['slidesToShow'] = $atts['slides_to_show'];
		$atts['data']['slidesToScroll'] = $atts['slides_to_scroll'];
		$atts['data']['responsive'] = $responsive;
		$atts['data']['centerMode'] = $atts['center_mode'] ? true : false;			
	} else {		
		$atts['data']['fade'] = $atts['fade'] ? true : false;		
	}

	$atts['style_prev_arrow']  = sprintf( 'background:%s; padding:%s; top:%s; left:%s; border-radius:%s;', $atts['arrows_bg_color'], $atts['arrows_padding'], $atts['arrows_top_offset'], $atts['arrows_left_offset'], $atts['arrows_border_radius'] );
	$atts['style_next_arrow']  = sprintf( 'background:%s; padding:%s; top:%s; right:%s; border-radius:%s;', $atts['arrows_bg_color'], $atts['arrows_padding'], $atts['arrows_top_offset'], $atts['arrows_right_offset'], $atts['arrows_border_radius'] );
	$atts['style_arrow_icon']  = sprintf( 'font-size:%s; color:%s;', $atts['arrows_icon_size'], $atts['arrows_icon_color'] );
	$atts['style_dots']        = sprintf( 'background:%s;', $atts['dots_bg_color'] );
	$atts['style_dots_active'] = sprintf( 'background:%s;', $atts['dots_active_bg_color'] );
		
	// Query args
	$query = array(				
		'post_type' => 'acadp_listings',
		'posts_per_page' => ! empty( $atts['limit'] ) ? (int) $atts['limit'] : 500,
		'no_found_rows' => true,
		'update_post_term_cache' => false
	);
		
	$tax_queries  = array();
	$meta_queries = array();

	$has_location = ! empty( $general_settings['has_location'] ) ? 1 : 0;
	$location     = (int) $atts['location'];
	
	if ( $has_location && $atts['follow_locations'] ) {
		$term_slug = get_query_var( 'acadp_location' );
	
		if ( '' != $term_slug ) {		
			$term = get_term_by( 'slug', sanitize_text_field( $term_slug ), 'acadp_locations' );
			$location = $term->term_id;
		}
	}
	
	if ( $has_location && $location > 0 ) {			
		$tax_queries[] = array(
			'taxonomy'         => 'acadp_locations',
			'field'            => 'term_id',
			'terms'            => $location,
			'include_children' => isset( $listings_settings['include_results_from'] ) && in_array( 'child_locations', $listings_settings['include_results_from'] ) ? true : false,
		);					
	}
		
	$category = (int) $atts['category'];
	
	if ( $atts['follow_categories'] ) {
		$term_slug = get_query_var( 'acadp_category' );
		
		if ( '' != $term_slug ) {		
			$term = get_term_by( 'slug', sanitize_text_field( $term_slug ), 'acadp_categories' );
			$category = $term->term_id;
		}
	}
	
	if ( $category > 0 ) {		
		$tax_queries[] = array(
			'taxonomy'         => 'acadp_categories',
			'field'            => 'term_id',
			'terms'            => $category,
			'include_children' => isset( $listings_settings['include_results_from'] ) && in_array( 'child_categories', $listings_settings['include_results_from'] ) ? true : false,
		);					
	}
		
	if ( 'banner_rotator' == $slider ) {		
		$meta_queries[] = array(
			'key'     => 'images',
			'compare' => 'EXISTS',
		);		
	}
		
	if ( 'featured' == $atts['filterby'] ) {			
		$meta_queries[] = array(
			'key'     => 'featured',
			'value'   => 1,
			'compare' => '='
		);				
	}
		
	$count_tax_queries = count( $tax_queries );
	if ( $count_tax_queries ) {
		$query['tax_query'] = ( $count_tax_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $tax_queries ) : array( $tax_queries );
	}
	
	$count_meta_queries = count( $meta_queries );
	if ( $count_meta_queries ) {
		$query['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : array( $meta_queries );
	}
		
	$orderby = sanitize_text_field( $atts['orderby'] );
	$order   = sanitize_text_field( $atts['order'] );
	
	$query['orderby'] = $orderby;
	$query['order']   = $order;
	if ( 'price' == $orderby || 'views' == $orderby ) {
		$query['meta_key'] = $orderby;
		$query['orderby']  = 'meta_value_num';
	}
	
	$atts['query'] = $query;
	
	return $atts;		
}

/**
 * Output a select input box.
 *
 * @since 1.6.4
 * @param array $field
 */
function acadp_premium_woocommerce_get_categories_multiselect( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['value']         = isset( $field['value'] ) ? $field['value'] : ( get_post_meta( $thepostid, $field['id'], true ) ? get_post_meta( $thepostid, $field['id'], true ) : array() );

	printf(
		'<p class="form-field %s_field %s">',
		esc_attr( $field['id'] ),
		esc_attr( $field['wrapper_class'] )
	);

	printf(
		'<label for="%s">%s</label>',
		esc_attr( $field['id'] ),
		esc_html( $field['label'] )
	);

	$args = array(
		'taxonomy'     => 'acadp_categories',
		'name'         => esc_attr( $field['name'] ),
		'id'           => esc_attr( $field['id'] ),
		'class'        => esc_attr( $field['class'] ),
		'hierarchical' => 1,
		'hide_empty'   => 0,
		'echo'         => false
	);

	$dropdown = wp_dropdown_categories( $args );

	// Change the dropdown into an MultiSelect
	$dropdown = str_replace( 'id=', 'multiple="multiple" id=', $dropdown );

	// Display saved values
	$selected = array_map( 'intval', $field['value'] );
	if ( is_array( $selected ) ) {
		foreach ( $selected as $value ) { 
			$dropdown = str_replace( ' value="' . $value . '"', ' value="' . $value . '" selected="selected"', $dropdown ); 
		}
	} else { 
		$dropdown = str_replace( ' value="' . $selected . '"', ' value="' . $selected . '" selected="selected"', $dropdown );
	}	

	echo $dropdown;

	if ( ! empty( $field['description'] ) ) {
		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			printf(
				'<span class="woocommerce-help-tip" data-tip="%s"></span>',
				esc_attr( $field['description'] )
			);
		} else {
			printf(
				'<span class="description">%s</span>',
				esc_html( $field['description'] )
			);
		}
	}
	
	echo '</p>';	
}

/**
 * Generate permalink for the WooCommerce checkout page.
 *
 * @since  1.6.4
 * @param  string $checkout_url Default checkout page url.
 * @param  int    $post_id      Post ID.
 * @return string               Updated checkout page url.
 */
function acadp_premium_woocommerce_get_checkout_page_url( $checkout_url, $post_id ) {	
	$link = '/';
	
	if ( $checkout_url ) {
		$link = $checkout_url;
	
		if ( '' != get_option( 'permalink_structure' ) ) {
			$link = user_trailingslashit( trailingslashit( $link ) . $post_id );
		} else {
			$link = add_query_arg( 'acadp_listing', $post_id, $link );
		}
	}

	return $link;
}

/**
 * Get the active WooCommerce order id for the given product ID.
 *
 * @since  1.6.4
 * @param  int   $product_id WooCommerce product id.
 * @return int               WooCommerce order id.
 */
function acadp_premium_woocommerce_get_order_id( $product_id = 0 ) {
	$order_id = 0;

	if ( $product_id > 0 && is_user_logged_in() ) {
		$user_id = get_current_user_id();

		$orders = wc_get_orders( array(
			'meta_key'    => '_customer_user',
			'meta_value'  => $user_id,
			'post_status' => array( 'wc-completed' ),
			'numberposts' => -1
		) );

		$listings_limit = (int) get_post_meta( $product_id, 'acadp_listings_limit', true );

		foreach ( $orders as $order ) {
			// Order ID (added WooCommerce 3+ compatibility)
			$__order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;

			foreach ( $order->get_items() as $item_id => $item ) {
				// The corresponding product ID (added WooCommerce 3+ compatibility)
        		$__product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : $item['product_id'];

				if ( $__product_id == $product_id ) {
					if ( 0 == $listings_limit ) {		
						$order_id = $__order_id;
					} else {	
						$listings_submitted = (int) get_post_meta( $__order_id, 'acadp_listings_submitted', true );
					
						if ( $listings_submitted < $listings_limit ) {
							$order_id = $__order_id;
						}	
					}
					
					break;
				}				
			}

			if ( $order_id > 0 ) break;
		}
	}
	
	return $order_id;
}

/**
 * Check if the WooCommerce product is purchasable.
 *
 * @since  1.8.0
 * @param  bool   $purchasable Default status.
 * @param  object $product_id  WooCommerce product ID.
 * @return bool   $purchasable "true" if the product can be purchased, "false" if not.
 */
function acadp_premium_woocommerce_product_is_purchasable( $purchasable, $product_id ) {
	$disable_repeat_purchase = get_post_meta( $product_id, 'acadp_disable_repeat_purchase', true );
	$subscription_limit = get_post_meta( $product_id, '_subscription_limit', true );

	if ( $disable_repeat_purchase || 'any' == $subscription_limit ) {
		$current_user = wp_get_current_user();

		if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product_id ) ) {
			$purchasable = false;
		}		
	}
	
	return $purchasable;		
}

/**
 * Register a custom WooCommerce product type.
 *
 * @since 1.6.4
 */
function acadp_premium_woocommerce_register_product_type() {

	/**
	 * WC_Product_Listings_Package Class
	 *
	 * @since 1.6.4
	 */
	class WC_Product_Listings_Package extends WC_Product {
		
		public function __construct( $product ) {		
			$this->product_type = 'listings_package';		
			parent::__construct( $product );			
		}
		
	}

	/**
	 * WC_Product_Listings_Featured Class
	 *
	 * @since 1.8.3
	 */
	class WC_Product_Listings_Featured extends WC_Product {
		
		public function __construct( $product ) {		
			$this->product_type = 'listings_featured';		
			parent::__construct( $product );			
		}
		
	}

}

/**
 * Check if the user has active plan for the given product ID.
 *
 * @since  1.8.0
 * @param  int   $product_id WooCommerce product ID.
 * @return int               WooCommerce order/subscription ID.
 */
function acadp_premium_woocommerce_user_has_active_plan( $product_id ) {
	$wc_plans_settings = get_option( 'acadp_wc_plans_settings' );

	if ( isset( $wc_plans_settings['product_type'] ) && 'subscription' == $wc_plans_settings['product_type'] ) {
		return acadp_premium_woocommerce_user_has_active_subscription( $product_id );
	} else {
		return acadp_premium_woocommerce_get_order_id( $product_id );
	}
}

/**
 * Check if the user has active subscription for the given subscription product ID.
 *
 * @since  1.8.0
 * @param  int   $product_id      WooCommerce subscription product ID.
 * @return int   $subscription_id WooCommerce subscription ID.
 */
function acadp_premium_woocommerce_user_has_active_subscription( $product_id = 0 ) {
	$subscription_id = 0;

	if ( $product_id > 0 && is_user_logged_in() ) {
		$user_id = get_current_user_id();		
		$subscriptions = wcs_get_users_subscriptions( $user_id );
		$listings_limit = (int) get_post_meta( $product_id, 'acadp_listings_limit', true );

		foreach ( $subscriptions as $subscription ) {
			if ( $subscription->has_product( $product_id ) && $subscription->has_status( 'active' ) ) {
				if ( 0 == $listings_limit ) {		
					$subscription_id = $subscription->get_id();			
				} else {		
					$listings_submitted = (int) get_post_meta( $subscription->get_id(), 'acadp_listings_submitted', true );
						
					if ( $listings_submitted < $listings_limit ) {
						$subscription_id = $subscription->get_id();
					}			
				}

				if ( $subscription_id > 0 )	break;
			}
		}
	}

	return $subscription_id;
}

/**
 * Update listing status, expiry date.
 *
 * @since  1.6.4
 * @access public
 * @param  int    $listing_id  Listing ID.
 * @param  int    $product_id  WooCommerce product ID.
 * @return bool   $is_new      "true" if new, "false" if renewal.
 */
function acadp_premium_woocommerce_update_listing( $listing_id, $product_id ) {
	$general_settings = get_option( 'acadp_general_settings' );

	$current_time = current_time( 'mysql' );
	
	$expiry_date = get_post_meta( $listing_id, 'expiry_date', true );
	$old_post_status = get_post_status( $listing_id );

	$post_array = array(
		'ID' => $listing_id
	);	
	
	if ( empty( $expiry_date ) ) { // If new listing
		$is_new = 1;
		
		$post_status = $general_settings['new_listing_status'];

		if ( 'publish' == $post_status ) {
			$start_date_time = $current_time;
		}
	} else { // If renewal
		$is_new = 0;
		
		$post_status = 'publish';
		
		$old_listing_status = get_post_meta( $listing_id, 'listing_status', true );
		$start_date_time = ( 'expired' == $old_listing_status ) ? $current_time : $expiry_date;
		
		$post_array['post_date'] = $current_time;
		$post_array['post_date_gmt'] = get_gmt_from_date( $current_time );
	}
	
	$post_array['post_status'] = $post_status;

	// Update the post into the database
	wp_update_post( $post_array );
	
	// Update the post_meta into the database
	update_post_meta( $listing_id, 'wc_plan_id', $product_id );
	
	$featured = (int) get_post_meta( $product_id, 'acadp_featured', true );
	update_post_meta( $listing_id, 'featured', $featured );
				
	if ( 'publish' == $post_status ) {
		$expiry_date = acadp_listing_expiry_date( $listing_id, $start_date_time );
		update_post_meta( $listing_id, 'expiry_date', $expiry_date );
		
		update_post_meta( $listing_id, 'listing_status', 'post_status' );
		
		// Check if we are transitioning from draft|pending to publish
		if ( ! is_admin() && in_array( $old_post_status, array( 'draft', 'pending' ) ) ) {
			if ( 'draft' == $old_post_status ) {
				acadp_email_admin_listing_submitted( $listing_id );
				acadp_email_listing_owner_listing_submitted( $listing_id );
			}			

			acadp_email_listing_owner_listing_approved( $listing_id );			
		}
	}
	
	return $is_new;		
}