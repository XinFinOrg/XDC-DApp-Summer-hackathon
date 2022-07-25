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
 * ACADP_Premium_Admin_Woocommerce_Plans class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Admin_Woocommerce_Plans {
	
	/**
     * Register "WooCommerce Plans" settings section.
     *
	 * @since  1.7.3
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function register_settings_section( $sections ) {	
		$sections[] = array(
			'id'    => 'acadp_wc_plans_settings',
			'title' => __( 'WooCommerce Plans', 'advanced-classifieds-and-directory-pro' ),		
			'tab'   => 'monetize',
			'slug'  => 'acadp_wc_plans_settings'
		);
		
		return $sections;	
	}

	/**
     * Register "WooCommerce Plans" settings fields.
     *
	 * @since  1.7.3
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function register_settings_fields( $fields ) {
		$fields['acadp_wc_plans_settings'] = array(
			array(
				'name'              => 'enabled',
				'label'             => __( 'Enable / Disable', 'advanced-classifieds-and-directory-pro' ),
				'description'       => __( 'Check this to enable WooCommerce plans', 'advanced-classifieds-and-directory-pro' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			),
			array(
				'name'              => 'product_type',
				'label'             => __( 'Product Type', 'advanced-classifieds-and-directory-pro' ),
				'description'       => '',
				'type'              => 'radio',
				'options'           => array(
					'listings_package' => __( 'Listings Package', 'advanced-classifieds-and-directory-pro' ),
					'subscription'     => sprintf(
						'%s <span class="description">(%s)</span>',
						__( 'Subscription', 'advanced-classifieds-and-directory-pro' ),
						__( 'WooCommerce Subscription plugin required', 'advanced-classifieds-and-directory-pro' )
					)
				),
				'sanitize_callback' => 'sanitize_key'
			),
			array(
				'name'              => 'disable_categories_listings_edit',
				'label'             => __( 'Disable "Categories" selection in edit listing form', 'advanced-classifieds-and-directory-pro' ),
				'description'       => __( 'Check this option if you have "Plans" created based on categories. This prevent users from changing categories( Free to Paid category ) by editing the listing form.', 'advanced-classifieds-and-directory-pro' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval'
			)
		);

		return $fields;	
	}
	
	/**
	 * Register meta boxes.
	 *
	 * @since 1.6.4
	 */
	public function add_meta_boxes() {
		add_meta_box( 
			'acadp-wc-plans-details', 
			__( 'WooCommerce Plans', 'advanced-classifieds-and-directory-pro' ), 
			array( $this, 'display_meta_box' ), 
			'acadp_listings', 
			'side', 
			'default' 
		);	
	}

	/**
	 * Display the meta box to show WooCommerce plans.
	 *
	 * @since 1.6.4
	 * @param WP_Post $post WordPress Post object.
	 */

	public function display_meta_box( $post ) {
		$post_meta = get_post_meta( $post->ID );
		
		$term_ids = wp_get_object_terms( $post->ID, 'acadp_categories', array( 'fields' => 'ids' ) );
		$term_id  = count( $term_ids ) ? $term_ids[0] : 0;

		$select = $this->plans_select( $post->ID, $term_id );
		
		// Add a nonce field so we can check for it later
    	wp_nonce_field( 'acadp_save_wc_plans', 'acadp_wc_plans_nonce' );
	
		// Add a WooCommerce plans select field		
		printf( '<div id="acadp-listings-wc-plans" data-post_id="%d">%s</div>', $post->ID, $select );
	}

	/**
	 * Display WooCommerce Plans listbox.
	 *
	 * @since 1.6.4
	 * @param int   $post_id Post ID.
	 * @param int   $term_id ACADP Category ID.
	 */
	public function ajax_callback_wc_plans() {	
		if ( isset( $_POST['term_id'] ) ) {
			$post_id = (int) $_POST['post_id'];
			$term_id = (int) $_POST['term_id'];
			
			echo $this->plans_select( $post_id, $term_id );
		}
		
		wp_die();			
	}	

	/**
	 * Save WooCommerce Plans.
	 *
	 * @since  1.6.4
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_plans( $post_id, $post ) {	
		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}
		
		// Check this is the "acadp_listings" custom post type
    	if ( 'acadp_listings' != $post->post_type ) {
        	return $post_id;
    	}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        	return $post_id;
		}
		
		// Check the logged in user has permission to edit this post
    	if ( ! acadp_current_user_can( 'edit_acadp_listings' ) ) {
        	return $post_id;
    	}		
		
		// Check if "acadp_wc_plans_nonce" nonce is set
    	if ( isset( $_POST['acadp_wc_plans_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['acadp_wc_plans_nonce'], 'acadp_save_wc_plans' ) ) {			
				// OK to save meta data
				$plan_id = (int) $_POST['wc_plan'];
    			update_post_meta( $post_id, 'wc_plan_id', $plan_id );				
			}		
		}
		
		return $post_id;	
	}

	/**
	 * Add our custom product type to the "types" array.
	 *
	 * @since  1.6.4
	 * @param  array  $types Array of WooCommerce product types.
	 * @return array  $types Array. Filtered WooCommerce product types.
	 */ 
	public function product_type_selector( $types ){	 
		$types['listings_package']  = __( 'Listings Package', 'advanced-classifieds-and-directory-pro' );
		$types['listings_featured'] = __( 'Listings Featured', 'advanced-classifieds-and-directory-pro' );

		return $types;
	}

	/**
	 * Display custom woocommerce fields.
	 *
	 * @since 1.6.4
	 */ 
	public function display_custom_fields() {	 
		global $woocommerce, $post;
			
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );
		
		// Is acadp subscription?
		$is_acadp_subscription = get_post_meta( $post->ID, 'is_acadp_subscription', true );
		$is_acadp_subscription = ! empty( $is_acadp_subscription ) ? 'yes' : 'no';

		// Images limit
		$listings_limit = get_post_meta( $post->ID, 'acadp_listings_limit', true );
		
		// Listing duration
		$listing_duration = get_post_meta( $post->ID, 'acadp_listing_duration', true );
		
		// Images limit
		$images_limit = get_post_meta( $post->ID, 'acadp_images_limit', true );
		
		// Featured
		$featured = get_post_meta( $post->ID, 'acadp_featured', true );
		$featured = ! empty( $featured ) ? 'yes' : 'no';
			
		// Categories
		$categories = (array) get_post_meta( $post->ID, 'acadp_categories', true );

		// Disable Repeat Purchase
		$disable_repeat_purchase = get_post_meta( $post->ID, 'acadp_disable_repeat_purchase', true );
		$disable_repeat_purchase = ! empty( $disable_repeat_purchase ) ? 'yes' : 'no';

		// ...
		require_once ACADP_PLUGIN_DIR . 'premium/admin/partials/woocommerce-fields.php';		
	}

	/**
	 * Save WooCommerce meta data.
	 *
	 * @since  1.6.4
	 * @param  int     $post_id Post ID
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_custom_fields( $post_id, $post ) {
		// Is acadp subscription?
		$is_acadp_subscription = isset( $_POST['is_acadp_subscription'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, 'is_acadp_subscription', $is_acadp_subscription );

		// Listings limit
		$listings_limit = isset( $_POST['acadp_listings_limit'] ) ? (int) $_POST['acadp_listings_limit'] : 0;
		update_post_meta( $post_id, 'acadp_listings_limit', $listings_limit );
			
		// Listing duration
		$listing_duration = isset( $_POST['acadp_listing_duration'] ) ? (int) $_POST['acadp_listing_duration'] : 0;
		update_post_meta( $post_id, 'acadp_listing_duration', $listing_duration );
			
		// Images limit
		$images_limit = isset( $_POST['acadp_images_limit'] ) ? (int) $_POST['acadp_images_limit'] : 0;
		update_post_meta( $post_id, 'acadp_images_limit', $images_limit );
			
		// Featured
		$featured = isset( $_POST['acadp_featured'] ) ? 1 : 0;
		update_post_meta( $post_id, 'acadp_featured', $featured );
			
		// Categories
		$categories = isset( $_POST['acadp_categories'] ) ? array_map( 'esc_attr', $_POST['acadp_categories'] ) : array( '-1' );
		update_post_meta( $post_id, 'acadp_categories', $categories );
		
		// Disable Repeat Purchase
		$disable_repeat_purchase = isset( $_POST['acadp_disable_repeat_purchase'] ) ? 1 : 0;
		update_post_meta( $post_id, 'acadp_disable_repeat_purchase', $disable_repeat_purchase );
	}
	
	/**
	 * Build WooCommerce Plans listbox.
	 *
	 * @since  1.6.4
	 * @access private 
	 * @param  int     $post_id Post ID.
	 * @param  int     $term_id ACADP Category ID.
	 * @return string  $select  WooCommerce Plans listbox.
	 */
	private function plans_select( $post_id = 0, $term_id = 0 ) {
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
			if ( $term_id > 0 ) {
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
			};
		}

		$count_meta_queries = count( $meta_queries );
		if ( $count_meta_queries ) {
			$args['meta_query'] = ( $count_meta_queries > 1 ) ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries;
		}
		
		$acadp_query = new WP_Query( $args );

		if ( $acadp_query->have_posts() ) {
			$plans = $acadp_query->posts;
		} else {
			$plans = array();
		}

		$options = array();

		$options[] = sprintf( 
			'<option value="0">%s</option>', 
			__( 'Select your plan', 'advanced-classifieds-and-directory-pro' ) 
		);		

		if ( ! empty( $plans ) ) {
			$active_plan = get_post_meta( $post_id, 'wc_plan_id', true );
			
			foreach ( $plans as $plan ) {
				$options[] = sprintf( 
					'<option value="%d"%s>%s</option>', 
					$plan->ID, 
					selected( $active_plan, $plan->ID, false ), 
					esc_html( $plan->post_title ) 
				);
			}
		}		
		
		$select = sprintf( 
			'<select name="wc_plan">%s</select>', 
			implode( "\n", $options ) 
		);		
		
		return $select;	
	}

}