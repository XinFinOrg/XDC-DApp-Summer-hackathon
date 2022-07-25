<?php

/**
 * Locations
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
 * ACADP_Admin_Locations Class.
 *
 * @since 1.0.0
 */
class ACADP_Admin_Locations {
	
	/**
	 * Register a custom taxonomy.
	 *
	 * @since 1.0.0
	 */
	public function register_custom_taxonomy() {	
		$general_settings = get_option( 'acadp_general_settings' );
		$has_location = empty( $general_settings['has_location'] ) ? false : true;
		
		$labels = array(
			'name'                       => _x( 'Locations', 'Taxonomy General Name', 'advanced-classifieds-and-directory-pro' ),
			'singular_name'              => _x( 'Location', 'Taxonomy Singular Name', 'advanced-classifieds-and-directory-pro' ),
			'menu_name'                  => __( 'Locations', 'advanced-classifieds-and-directory-pro' ),
			'all_items'                  => __( 'All Locations', 'advanced-classifieds-and-directory-pro' ),
			'parent_item'                => __( 'Parent Location', 'advanced-classifieds-and-directory-pro' ),
			'parent_item_colon'          => __( 'Parent Location:', 'advanced-classifieds-and-directory-pro' ),
			'new_item_name'              => __( 'New Location Name', 'advanced-classifieds-and-directory-pro' ),
			'add_new_item'               => __( 'Add New Location', 'advanced-classifieds-and-directory-pro' ),
			'edit_item'                  => __( 'Edit Location', 'advanced-classifieds-and-directory-pro' ),
			'update_item'                => __( 'Update Location', 'advanced-classifieds-and-directory-pro' ),
			'view_item'                  => __( 'View Location', 'advanced-classifieds-and-directory-pro' ),
			'separate_items_with_commas' => __( 'Separate Locations with commas', 'advanced-classifieds-and-directory-pro' ),
			'add_or_remove_items'        => __( 'Add or remove Locations', 'advanced-classifieds-and-directory-pro' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'advanced-classifieds-and-directory-pro' ),
			'popular_items'              => NULL,
			'search_items'               => __( 'Search Locations', 'advanced-classifieds-and-directory-pro' ),
			'not_found'                  => __( 'Not Found', 'advanced-classifieds-and-directory-pro' ),
		);
		
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => $has_location,
			'show_admin_column'          => $has_location,
			'show_in_menu'               => false,
			'show_in_nav_menus'          => true,
			'show_in_rest'               => true,
			'show_tagcloud'              => false,
			'query_var'                  => true,
			'capabilities'               => array(
				'manage_terms' => 'manage_acadp_options',
				'edit_terms'   => 'manage_acadp_options',				
				'delete_terms' => 'manage_acadp_options',
				'assign_terms' => 'edit_acadp_listings'
			),
		);
		
		register_taxonomy( 'acadp_locations', array( 'acadp_listings' ), $args );	
	}

	/**
	 * Add "Locations" menu.
	 *
	 * @since 1.7.3
	 */
	public function admin_menu() {
		$general_settings = get_option( 'acadp_general_settings' );
		$has_location = empty( $general_settings['has_location'] ) ? false : true;

		if ( $has_location ) {
			add_submenu_page(
				'advanced-classifieds-and-directory-pro',
				__( 'Advanced Classifieds and Directory Pro - Locations', 'advanced-classifieds-and-directory-pro' ),
				__( 'Locations', 'advanced-classifieds-and-directory-pro' ),
				'manage_acadp_options',
				'edit-tags.php?taxonomy=acadp_locations&post_type=acadp_listings'
			);
		}			
	}

	/**
	 * Move "Locations" submenu under our plugin's main menu.
	 *
	 * @since  1.7.3
	 * @param  string $parent_file The parent file.
	 * @return string $parent_file The parent file.
	 */
	public function parent_file( $parent_file ) {	
		global $submenu_file, $current_screen;

		if ( 'acadp_locations' == $current_screen->taxonomy ) {
			$submenu_file = 'edit-tags.php?taxonomy=acadp_locations&post_type=acadp_listings';
			$parent_file  = 'advanced-classifieds-and-directory-pro';
		}

		return $parent_file;
	}
	
	/**
	 * Retrieve the table columns.
	 *
	 * @since  1.5.8
	 * @param  array $columns Array of default table columns.
	 * @return array $columns Updated list of table columns.
	 */
	public function get_columns( $columns ) {	
		$columns['tax_id'] = __( 'ID', 'advanced-classifieds-and-directory-pro' );
    	return $columns;		
	}
	
	/**
	 * This function renders the custom columns in the list table.
	 *
	 * @since 1.5.8
	 * @param string $content Content of the column.
	 * @param string $column  Name of the column.
	 * @param string $term_id Term ID.
	 */
	public function custom_column_content( $content, $column, $term_id ) {		
		if ( 'tax_id' == $column ) {
        	$content = $term_id;
    	}
		
		return $content;	
	}
		
}
