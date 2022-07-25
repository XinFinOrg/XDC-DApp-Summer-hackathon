<?php

/**
 * Fee Plans.
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
 * ACADP_Premium_Public_Fee_Plans class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Public_Fee_Plans {
	
	/**
	 * Disable "Categories" dropdown in the listing edit form.
	 *
	 * @since  1.6.4
	 * @param  string $html    HTML. Categories dropdown.
	 * @param  int    $post_id Post ID.
	 * @return string $html    Filtered dropdown HTML.
	 */
	public function listing_form_categories_dropdown( $html, $post_id ) {		
		$plan_settings = get_option( 'acadp_fee_plans_settings' );
		
		if ( ! empty( $plan_settings['disable_categories_listings_edit'] ) ) {		
			if ( 0 == $post_id ) {				
				$html .= sprintf( '<p class="help-block">%s</p>', __( "Since our submission charges vary based on categories, you cannot change the category after the listing has been submitted. So, make sure that you've selected the right category before submitting this listing.", 'advanced-classifieds-and-directory-pro' ) );
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
	 * Change "publish" status of a new listing to "draft". So, listings are published
	 * only if payment is success.
	 *
	 * @since  1.6.4
	 * @param  string $status Default status of a new listing.
	 * @return string $status Updated listing status.
	 */
	public function new_listing_status( $status ) {	
		$settings = get_option( 'acadp_fee_plans_settings' );
		
		if ( ! empty( $settings['enabled'] ) && isset( $_POST['acadp_category'] ) ) {			
			$plans = $this->get_plans( 0, (int) $_POST['acadp_category'] );
			if ( count( $plans ) ) $status = 'draft';			
		}
		
		return $status;		
	}

	/**
	 * Check if fee plans available.
	 *
	 * @since  1.6.4
	 * @param  bool   $has_checkout_page Has checkout page.
	 * @param  int    $post_id           Post ID.
	 * @param  string $filter            Monetization Type(Submission, Promotion or All).
	 * @return bool   $has_checkout_page True if available, incoming value if not.
	 */
	public function has_checkout_page( $has_checkout_page, $post_id, $filter = 'all' ) {	
		$settings = get_option( 'acadp_fee_plans_settings' );
		
		if ( ! empty( $settings['enabled'] ) ) {			
			$plans = $this->get_plans( $post_id );

			if ( count( $plans ) && 'promotion' != $filter ) {
				$has_checkout_page = 1;
			}			
		}

		return $has_checkout_page;		
	}

	/**
	 * Add "Fee Plans" in the listing checkout form.
	 *
	 * @since  1.6.4
	 * @param  array $options Associative array containing checkout form data.
	 * @param  int   $post_id Post ID.
	 * @return array $options Updated checkout form data.
	 */
	public function checkout_form_data( $options, $post_id ) {	
		$settings = get_option( 'acadp_fee_plans_settings' );
		$action   = get_query_var( 'acadp_action', 'all' );
		
		if ( ! empty( $settings['enabled'] ) && 'promote' != $action ) {			
			$plans = $this->get_plans( $post_id );
			
			if ( count( $plans ) ) {
				$options[] = array(
					'type'        => 'header',
					'label'       => $settings['label'],
					'description' => $settings['description']
				);
			
				foreach ( $plans as $key => $value ) {
					$plan = array(
						'type'        => 'radio',
						'name'        => 'fee_plan',
						'value'       => $value->ID,
						'label'       => $value->post_title,
						'description' => $value->post_content,
						'days'        => get_post_meta( $value->ID, 'listing_duration', true ),
						'price'       => get_post_meta( $value->ID, 'price', true )
					);
					
					if ( 0 == $key ) {
						$plan['selected'] = 1;
					}
					
					$options[] = $plan;
				}
			}			
		}

		return $options;		
	}

	/**
	 * Update listing duration based on the "Fee Plan".
	 *
	 * @since  1.6.4
	 * @param  int   $days    Listing duration.
	 * @param  int   $post_id Post ID.
	 * @return int   $days    Updated duration.
	 */
	public function listing_duration( $days, $post_id ) {	
		$fee_plan_id = get_post_meta( $post_id, 'fee_plan_id', true );
		
		if ( ! empty( $fee_plan_id ) ) {		
			$days = get_post_meta( $fee_plan_id, 'listing_duration', true );
		}
		
		return $days;		
	}	

	/**
	 * Called when order created.
	 *
	 * @since 1.6.4
	 * @param int   $order_id Order ID.
	 */
	public function order_created( $order_id ) {	
		if ( isset( $_POST['fee_plan'] ) ) {
			$post_id  = get_post_meta( $order_id, 'listing_id', true );
			$fee_plan_id = (int) $_POST['fee_plan'];
		
			update_post_meta( $order_id, 'fee_plan_id', $fee_plan_id );
			update_post_meta( $post_id, 'fee_plan_id', $fee_plan_id );
			
			$fee = get_post_meta( $fee_plan_id, 'price', true );
			if ( $fee == 0 ) $this->update_listing( $post_id );
		}		
	}

	/**
	 * Called when order completed.
	 *
	 * @since 1.6.4
	 * @param int   $order_id Order ID.
	 */
	public function order_completed( $order_id ) {		
		$fee_plan_id = get_post_meta( $order_id, 'fee_plan_id', true );
		
		if ( ! empty( $fee_plan_id ) ) {		
			$fee = get_post_meta( $fee_plan_id, 'price', true );
			if ( $fee > 0 ) {
				$post_id = get_post_meta( $order_id, 'listing_id', true );
				$this->update_listing( $post_id );
			}			
		}				
	}

	/**
	 * Update listing status, expiry date.
	 *
	 * @since 1.6.4
	 * @param int   $post_id Post ID.
	 */
	public function update_listing( $post_id ) {	
		$general_settings = get_option( 'acadp_general_settings' );

		$current_time = current_time( 'mysql' );
		
		$expiry_date = get_post_meta( $post_id, 'expiry_date', true );
		$old_post_status = get_post_status( $post_id );
		
		$post_array = array(
			'ID' => $post_id
		);		
		
		if ( empty( $expiry_date ) ) { // If new listing
			$post_status = $general_settings['new_listing_status'];
			
			if ( 'publish' == $post_status ) {
				$start_date_time = $current_time;
			}
		} else { // If renewal
			$post_status = 'publish';
			
			$old_listing_status = get_post_meta( $post_id, 'listing_status', true );
			$start_date_time = ( 'expired' == $old_listing_status ) ? $current_time : $expiry_date; 
			
			$post_array['post_date'] = $current_time;
			$post_array['post_date_gmt'] = get_gmt_from_date( $current_time );	
		}
			
		$post_array['post_status'] = $post_status;

		// Update the post into the database
 		wp_update_post( $post_array );
			
		// Update the post_meta into the database			
		if ( 'publish' == $post_status ) {
			$expiry_date = acadp_listing_expiry_date( $post_id, $start_date_time );
			update_post_meta( $post_id, 'expiry_date', $expiry_date );
			
			update_post_meta( $post_id, 'listing_status', 'post_status' );
			
			// Check if we are transitioning from draft|pending to publish
			if ( ! is_admin() && in_array( $old_post_status, array( 'draft', 'pending' ) ) ) {
				if ( 'draft' == $old_post_status ) {
					acadp_email_admin_listing_submitted( $post_id );
					acadp_email_listing_owner_listing_submitted( $post_id );
				}			

				acadp_email_listing_owner_listing_approved( $post_id );			
			}
		}			
	}

	/**
	 * Add plan details.
	 *
	 * @since  1.6.4
	 * @param  array $order_details Associative array containing listing order details.
	 * @param  int   $order_id      Order ID.
	 * @return array $order_details Updated order details.
	 */
	public function order_details( $order_details, $order_id ) {	
		$fee_plan_id = get_post_meta( $order_id, 'fee_plan_id', true );
		
		if ( ! empty( $fee_plan_id ) ) {
			$settings = get_option( 'acadp_fee_plans_settings' );
			$plan = get_post( $fee_plan_id );
	
			$order_details[] = array(
				'label'       => $settings['label'] . ': ' . $plan->post_title,
				'description' => $plan->post_content,
				'days'        => get_post_meta( $plan->ID, 'listing_duration', true ),
				'price'       => get_post_meta( $plan->ID, 'price', true )
			);
		}
		
		return $order_details;		
	}

	/**
	 * Called when order status changed by admin in ACADP back-end.
	 *
	 * @since 1.6.4
	 * @param string $new_status Transition to this order status.
	 * @param string $old_status Previous order status.
	 * @param int    $order_id   Order ID.
	 */
	public function order_status_changed( $new_status, $old_status, $order_id ) {	
		$fee_plan_id = get_post_meta( $order_id, 'fee_plan_id', true );
		
		if ( ! empty( $fee_plan_id ) ) {
			$post_id = get_post_meta( $order_id, 'listing_id', true );
			
			$options = array( 'created', 'pending', 'failed', 'cancelled', 'refunded' );
		
			if ( 'completed' == $old_status && in_array( $new_status, $options ) ) {			
				$post_array = array(
      				'ID'          => $post_id,
      				'post_status' => 'pending',
  				);
				
				wp_update_post( $post_array );

				delete_post_meta( $post_id, 'expiry_date' );				
			} elseif ( in_array( $old_status, $options ) && 'completed' == $new_status ) {			
				$post_array = array(
      				'ID'          => $post_id,
      				'post_status' => 'publish',
  				);
				
				wp_update_post( $post_array );
				
				$expiry_date = get_post_meta( $post_id, 'expiry_date', true );
				$old_listing_status = get_post_meta( $post_id, 'listing_status', true );
				
				if ( empty( $expiry_date ) || 'expired' == $old_listing_status ) {
					$start_date_time = current_time( 'mysql' );
				} else {
					$start_date_time = $expiry_date;
				}
				
				$expiry_date = acadp_listing_expiry_date( $post_id, $start_date_time );
				update_post_meta( $post_id, 'expiry_date', $expiry_date );
				update_post_meta( $post_id, 'listing_status', 'post_status' );				
			}
		}	
	}

	/**
	 * Get the list of plans.
	 *
	 * @since  1.6.5
	 * @param  int   $post_id Listing ID.
	 * @param  array $terms   Listing terms.
	 * @return array $plans   Array of fee plans.
	 */
	private function get_plans( $post_id = 0, $terms = array() ) {
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );

		$args = array(
			'post_type' => 'acadp_fee_plans',
			'posts_per_page' => 500,
			'post_status' => 'publish',
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		);

		if ( empty( $multi_categories_settings['enabled'] ) ) {
			if ( empty( $terms ) && $post_id > 0 ) {
				$terms = wp_get_object_terms( $post_id, 'acadp_categories', array( 'fields' => 'ids' ) );
			}

			if ( ! empty( $terms ) ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'acadp_categories',
						'field' => 'term_id',
						'terms' => $terms,
						'include_children' => false,
					),
				);
			}			
		}

		$acadp_query = new WP_Query( $args );

		if ( $acadp_query->have_posts() ) {
			$plans = $acadp_query->posts;
		} else {
			$plans = array();
		}

		return $plans;
	}

}
