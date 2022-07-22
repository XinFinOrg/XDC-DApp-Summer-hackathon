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
 * ACADP_Premium_Admin_Fee_Plans class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Admin_Fee_Plans {	

	/**
     * Register "Fee Plans" settings section.
     *
	 * @since  1.7.3
	 * @param  array $sections Core settings sections array.
     * @return array $sections Updated settings sections array.
     */
    public function register_settings_section( $sections ) {	
		$sections[] = array(
			'id'    => 'acadp_fee_plans_settings',
			'title' => __( 'Fee Plans', 'advanced-classifieds-and-directory-pro' ),		
			'tab'   => 'monetize',
			'slug'  => 'acadp_fee_plans_settings'
		);
		
		return $sections;	
	}

	/**
     * Register "Fee Plans" settings fields.
     *
	 * @since  1.7.3
	 * @param  array $fields Core settings fields array.
     * @return array $fields Updated settings fields array.
     */
    public function register_settings_fields( $fields ) {
		$fields['acadp_fee_plans_settings'] = array(
			array(
				'name'              => 'enabled',
				'label'             => __( 'Enable / Disable', 'advanced-classifieds-and-directory-pro' ),
				'description'       => __( 'Check this to enable fee plans', 'advanced-classifieds-and-directory-pro' ),
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
	 * Register a custom post type "acadp_fee_plans".
	 *
	 * @since 1.6.4
	 */
	public function register_custom_post_type() {		
		$labels = array(
			'name'                => _x( 'Fee Plans', 'Post Type General Name', 'advanced-classifieds-and-directory-pro' ),
			'singular_name'       => _x( 'Fee Plan', 'Post Type Singular Name', 'advanced-classifieds-and-directory-pro' ),
			'menu_name'           => __( 'Fee Plans', 'advanced-classifieds-and-directory-pro' ),
			'name_admin_bar'      => __( 'Fee Plan', 'advanced-classifieds-and-directory-pro' ),
			'all_items'           => __( 'Fee Plans', 'advanced-classifieds-and-directory-pro' ),
			'add_new_item'        => __( 'Add New Fee', 'advanced-classifieds-and-directory-pro' ),
			'add_new'             => __( 'Add New', 'advanced-classifieds-and-directory-pro' ),
			'new_item'            => __( 'New Fee', 'advanced-classifieds-and-directory-pro' ),
			'edit_item'           => __( 'Edit Fee', 'advanced-classifieds-and-directory-pro' ),
			'update_item'         => __( 'Update Fee', 'advanced-classifieds-and-directory-pro' ),
			'view_item'           => __( 'View Fee', 'advanced-classifieds-and-directory-pro' ),
			'search_items'        => __( 'Search Fee', 'advanced-classifieds-and-directory-pro' ),
			'not_found'           => __( 'Not found', 'advanced-classifieds-and-directory-pro' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'advanced-classifieds-and-directory-pro' ),
		);
		
		$args = array(
			'label'               => __( 'acadp_fee_plans', 'advanced-classifieds-and-directory-pro' ),
			'description'         => __( 'Post Type Description', 'advanced-classifieds-and-directory-pro' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor' ),
			'taxonomies'          => array( 'acadp_categories' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'has_archive'         => false,
			'rewrite'             => false, 
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'acadp_fee_plan',
			'map_meta_cap'        => true,
		);
				
		register_post_type( 'acadp_fee_plans', $args );
	}

	/**
	 * Add "Fee Plans" menu.
	 *
	 * @since 1.7.3
	 */
	public function admin_menu() {	
		global $submenu;
		
		add_submenu_page(
			'advanced-classifieds-and-directory-pro',
			__( 'Advanced Classifieds and Directory Pro - Fee Plans', 'advanced-classifieds-and-directory-pro' ),
			__( 'Fee Plans', 'advanced-classifieds-and-directory-pro' ),
			'manage_acadp_options',
			'edit.php?post_type=acadp_fee_plans'
		);

		if ( array_key_exists( 'advanced-classifieds-and-directory-pro', $submenu ) ) {		
			$before = $after = array();
			
			$payments_slug = 'acadp_payments';
			$settings_slug = 'acadp_settings';
	
			foreach ( $submenu['advanced-classifieds-and-directory-pro'] as $item ) {
				if ( strpos( $item[2], $payments_slug ) !== false || $item[2] == $settings_slug ) {
					$after[]  = $item;
				} else {
					$before[] = $item;
				}
			}
			
			$submenu['advanced-classifieds-and-directory-pro'] = array_values( array_merge( $before, $after ) );		
		}		
	}

	/**
	 * Move "Fee Plans" submenu under our plugin's main menu.
	 *
	 * @since  1.7.3
	 * @param  string $parent_file The parent file.
	 * @return string $parent_file The parent file.
	 */
	public function parent_file( $parent_file ) {	
		global $submenu_file, $current_screen;

		if ( 'acadp_fee_plans' == $current_screen->post_type ) {
			$submenu_file = 'edit.php?post_type=acadp_fee_plans';
			$parent_file  = 'advanced-classifieds-and-directory-pro';
		}

		return $parent_file;
	}

	/**
	 * Replacing the default "Enter title here" placeholder text in the title input box.
	 *
	 * @since 1.6.4
	 */	
	public function change_default_title( $title ) {	
    	$screen = get_current_screen();
	
    	if ( 'acadp_fee_plans' == $screen->post_type ) {
        	$title = __( 'Enter your plan name', 'advanced-classifieds-and-directory-pro' );
    	}
	
    	return $title;	
	}

	/**
	 * Register meta boxes.
	 *
	 * @since 1.6.4
	 */
	public function add_meta_boxes_fee_details() {
		global $wp_meta_boxes;

		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );

		remove_meta_box( 'slugdiv', 'acadp_fee_plans', 'normal' );			
		add_meta_box( 'acadp-fee-details', __( 'Fee Details', 'advanced-classifieds-and-directory-pro' ), array( $this, 'display_meta_box_fee_details' ), 'acadp_fee_plans', 'normal', 'high' );
		
		if ( ! empty( $multi_categories_settings['enabled'] ) ) {
			$wp_meta_boxes['acadp_fee_plans']['side']['core']['acadp_categoriesdiv']['callback'] = array( $this, 'display_categories_disabled_note' );
		}
	}

	/**
	 * Display the field details meta box in the custom post type "acadp_fee_plans".
	 *
	 * @since 1.6.4
	 * @param WP_Post $post WordPress Post object
	 */
	public function display_meta_box_fee_details( $post ) {
		$post_meta = get_post_meta( $post->ID );

		// Add a nonce field so we can check for it later
    	wp_nonce_field( 'acadp_save_fee_details', 'acadp_fee_details_nonce' );
	
		require_once ACADP_PLUGIN_DIR . 'premium/admin/partials/fee-details.php';
	}

	/**
	 * Display the categories disabled note when multi categories enabled.
	 *
	 * @since 1.6.5
	 * @param WP_Post $post WordPress Post object
	 */
	public function display_categories_disabled_note( $post ) {
		$categories_disabled_note = sprintf( 
			__( 'Sorry, you cannot create category based plans when %s enabled.', 'advanced-classifieds-and-directory-pro' ), 
			'<a href="' . esc_url( admin_url( 'edit.php?post_type=acadp_listings&page=acadp_settings#multicategories-settings' ) ) . '">' . __( 'Multi categroies', 'advanced-classifieds-and-directory-pro' ) . '</a>'			
		);

		printf(
			'<span class="description">%s</span>',
			$categories_disabled_note
		);
	}

	/**
	 * Save meta data.
	 *
	 * @since  1.6.4
	 * @param  int     $post_id Post ID
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_meta_data( $post_id, $post ) {	
		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}
		
		// Check this is the "acadp_fee_plans" custom post type
    	if ( 'acadp_fee_plans' != $post->post_type ) {
        	return $post_id;
    	}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        	return $post_id;
		}
		
		// Check the logged in user has permission to edit this post
    	if ( ! acadp_current_user_can( 'edit_acadp_fee_plans' ) ) {
        	return $post_id;
    	}
		
		// Check if "acadp_fee_details_nonce" nonce is set
    	if ( isset( $_POST['acadp_fee_details_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['acadp_fee_details_nonce'], 'acadp_save_fee_details' ) ) {			
				// OK to save meta data
				$price = acadp_sanitize_amount( $_POST['price'] );
    			update_post_meta( $post_id, 'price', $price );
				
				$listing_duration = (int) $_POST['listing_duration'];
    			update_post_meta( $post_id, 'listing_duration', $listing_duration );				
			}		
		}
		
		return $post_id;	
	}

	/**
	 * Add custom filter options.
	 *
	 * @since 1.6.4
	 */
	public function restrict_manage_posts() {	
		global $typenow, $wp_query;
		
		if ( 'acadp_fee_plans' == $typenow ) {			
			// Restrict by category
        	wp_dropdown_categories(array(
            	'show_option_none'  =>  __( "All Categories", 'advanced-classifieds-and-directory-pro' ),
				'option_none_value' => 0,
            	'taxonomy'          =>  'acadp_categories',
            	'name'              =>  'acadp_categories',
            	'orderby'           =>  'name',
            	'selected'          =>  isset( $wp_query->query['acadp_categories'] ) ? $wp_query->query['acadp_categories'] : '',
            	'hierarchical'      =>  true,
            	'depth'             =>  3,
            	'show_count'        =>  false,
            	'hide_empty'        =>  false,
        	));		
    	}	
	}

	/**
	 * Filter fields(posts) by categories(taxonomy).
	 *
	 * @since 1.6.4
	 * @param WP_Query $query WordPress Query object
	 */
	public function parse_query( $query ) {	
		global $pagenow, $post_type;
		
    	if ( 'edit.php' == $pagenow && 'acadp_fee_plans' == $post_type ) {		
			// Convert category id to taxonomy term in query
			if ( isset( $query->query_vars['acadp_categories'] ) && ctype_digit( $query->query_vars['acadp_categories'] ) && $query->query_vars['acadp_categories'] != 0 ) {		
        		$term = get_term_by( 'id', $query->query_vars['acadp_categories'], 'acadp_categories' );
        		$query->query_vars['acadp_categories'] = $term->slug;				
			}			
    	}	
	}

	/**
	 * Exclude child categories(taxonomy) from the result.
	 *
	 * @since 1.6.4
	 * @param WP_Query $query WordPress Query object
	 */
	public function parse_tax_query( $query ) {	
		global $pagenow, $post_type;
		
    	if ( 'edit.php' == $pagenow && 'acadp_fee_plans' == $post_type ) {		
			if ( ! empty( $query->tax_query->queries ) ) {							
				$query->tax_query->queries[0]['include_children'] = 0;				
			}			
    	}	
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since  1.6.4
	 * @param  array $columns Array of default table columns.
	 * @return array $columns Updated list of table columns.
	 */
	public function get_columns( $columns ) {		
		$new_columns = array(
			'price'            => __( 'Amount', 'advanced-classifieds-and-directory-pro' ),
			'listing_duration' => __( 'Days', 'advanced-classifieds-and-directory-pro' )
		);		
		$columns = acadp_array_insert_after( 'taxonomy-acadp_categories', $columns, $new_columns );
		
		return $columns;		
	}

	/**
	 * This function renders the custom columns in the list table.
	 *
	 * @since 1.6.4
	 * @param string $column  The name of the column.
	 * @param string $post_id Post ID.
	 */
	public function custom_column_content( $column, $post_id ) {	
		switch ( $column ) {
			case 'price':
				$amount = get_post_meta( $post_id, 'price', true );
				$amount = acadp_format_payment_amount( $amount );
					
				$value = acadp_payment_currency_filter( $amount );
				echo $value;
				break;
			case 'listing_duration':
				$value = get_post_meta( $post_id, 'listing_duration', true );
				echo $value;
				break;	
		}		
	}

	/**
	 * Register meta boxes.
	 *
	 * @since 1.6.4
	 */
	public function add_meta_boxes_listings_fee_plans() {
		add_meta_box( 'acadp-fee-plans', __( 'Fee Plans', 'advanced-classifieds-and-directory-pro' ), array( $this, 'display_meta_box_listings_fee_plans' ), 'acadp_listings', 'side', 'default' );
	}

	/**
	 * Display the field plans meta box in the custom post type "acadp_listings".
	 *
	 * @since 1.6.4
	 * @param WP_Post $post WordPress Post object
	 */
	public function display_meta_box_listings_fee_plans( $post ) {
		$post_meta = get_post_meta( $post->ID );
		
		$term_ids = wp_get_object_terms( $post->ID, 'acadp_categories', array( 'fields' => 'ids' ) );
		$term_id  = count( $term_ids ) ? $term_ids[0] : 0;

		$select = $this->fee_plans_select( $post->ID, $term_id );
		
		// Add a nonce field so we can check for it later
    	wp_nonce_field( 'acadp_save_fee_plan', 'acadp_fee_plan_nonce' );
	
		// Add a fee plans select field		
		printf( '<div id="acadp-listings-fee-plans" data-post_id="%d">%s</div>', $post->ID, $select );
	}

	/**
	 * Display Fee Plans listbox.
	 *
	 * @since 1.6.4
	 * @param int   $post_id Post ID.
	 * @param int   $term_id ACADP Category ID.
	 */
	public function ajax_callback_fee_plans() {	
		if ( isset( $_POST['term_id'] ) ) {
			$post_id = (int) $_POST['post_id'];
			$term_id = (int) $_POST['term_id'];
			
			echo $this->fee_plans_select( $post_id, $term_id );
		}
		
		wp_die();			
	}

	/**
	 * Build Fee Plans listbox.
	 *
	 * @since  1.6.4
	 * @param  int    $post_id Post ID.
	 * @param  int    $term_id ACADP Category ID.
	 * @return string $select  Fee Plans listbox.
	 */
	public function fee_plans_select( $post_id = 0, $term_id = 0 ) {
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );
		
		$args = array(
			'post_type' => 'acadp_fee_plans',
			'post_status' => 'publish',
			'posts_per_page' => 500,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		);
		
		if ( empty( $multi_categories_settings['enabled'] ) ) {
			if ( $term_id > 0 ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'acadp_categories',
						'field' => 'term_id',
						'terms' => $term_id,
						'include_children' => false,
					),
				);
			};
		}
		
		$acadp_query = new WP_Query( $args );

		if ( $acadp_query->have_posts() ) {
			$plans = $acadp_query->posts;
		} else {
			$plans = array();
		}		
	
		$options = array();
		$options[] = sprintf( '<option value="0">%s</option>', __( 'Select your plan', 'advanced-classifieds-and-directory-pro' ) );					
		if ( ! empty( $plans ) ) {
			$active_plan = get_post_meta( $post_id, 'fee_plan_id', true );

			foreach ( $plans as $plan ) {
				$options[] = sprintf( '<option value="%d"%s>%s</option>', $plan->ID, selected( $active_plan, $plan->ID, false ), esc_html( $plan->post_title ) );
			}
		}		
		
		$select = sprintf( '<select name="fee_plan">%s</select>', implode( "\n", $options ) );		
		
		return $select;	
	}

	/**
	 * Save fee plan.
	 *
	 * @since  1.6.4
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    The post object.
	 * @return int     $post_id If the save was successful or not.
	 */
	public function save_fee_plan( $post_id, $post ) {	
		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}
		
		// Check this is the "acadp_fee_plans" custom post type
    	if ( 'acadp_listings' != $post->post_type ) {
        	return $post_id;
    	}
		
		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        	return $post_id;
		}
		
		// Check the logged in user has permission to edit this post
    	if ( ! acadp_current_user_can( 'edit_acadp_listings' ) ) {
        	return $post_id;
    	}
		
		// Check if fee plans enabled
		$settings = get_option( 'acadp_fee_plans_settings' );
		if ( empty( $settings['enabled'] ) ) {
			return $post_id;
		}
		
		// Check if "acadp_fee_plan_nonce" nonce is set
    	if ( isset( $_POST['acadp_fee_plan_nonce'] ) ) {		
			// Verify that the nonce is valid
    		if ( wp_verify_nonce( $_POST['acadp_fee_plan_nonce'], 'acadp_save_fee_plan' ) ) {			
				// OK to save meta data
				$fee_plan_id = (int) $_POST['fee_plan'];
    			update_post_meta( $post_id, 'fee_plan_id', $fee_plan_id );				
			}		
		}
		
		return $post_id;	
	}

}
