<?php

/**
 * Multi Categories.
 *
 * @link    https://pluginsware.com
 * @since   1.6.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Public_Multi_Categories class.
 *
 * @since 1.6.5
 */
class ACADP_Premium_Public_Multi_Categories {
		
	/**
	 * Replaces categories dropdown with a multi categories checklist.
	 *
	 * @since  1.6.5
	 * @param  string $html    HTML. Categories dropdown.
	 * @param  int    $post_id Post ID.
	 * @return string $html    Multi catgories checklist.
	 */
	public function listing_form_categories_dropdown( $html, $post_id ) {		
		return acadp_premium_get_terms_checklist( $post_id );		
	}

	/**
	 * Filter custom fields tax query parameter. 
	 *
	 * @since  1.6.5
	 * @param  int   $tax_query The default tax query array.
	 * @param  array $terms     Array of term IDs.
	 * @return array $tax_query Filtered tax query array.
	 */
	public function custom_fields_tax_queries( $tax_query, $terms ) {	
		$multi_categories_settings = get_option( 'acadp_multi_categories_settings' );
		
		if ( ! empty( $terms ) ) {
			$terms = is_array( $terms ) ? $terms : array( $terms );
			$tax_queries = array();

			if ( 'all' == $multi_categories_settings['custom_fields_rules'] ) {		
				$tax_queries[] = array(
					'taxonomy' => 'acadp_categories',
					'field' => 'term_id',
					'terms' => array_map( 'intval', $terms ),
					'include_children' => false,
				);		
			} else {			
				foreach ( $terms as $term_id ) {			
					$tax_queries[] = array(
						'taxonomy' => 'acadp_categories',
						'field' => 'term_id',
						'terms' => (int) $term_id,
						'include_children' => false,
					);
				}			
			}
			
			$tax_queries_count = count( $tax_queries );	
			if ( $tax_queries_count ) {
				$tax_query = ( $tax_queries_count > 1 ) ? array_merge( array( 'relation' => 'AND' ), $tax_queries ) : $tax_queries;
			}
		}	
		
		return $tax_query;
	}
	
}
