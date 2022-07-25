<?php

/**
 * Walker Category Checklist.
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
 * ACADP_Walker_Category_Checklist class.
 *
 * @since 1.6.5
 */
class ACADP_Walker_Category_Checklist extends Walker {

	public $tree_type = 'acadp_categories';
	
	public $db_fields = array(	
		'parent' => 'parent',		
		'id'     => 'term_id',		
	);	
	
	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker:start_lvl()
	 *
	 * @since 1.6.5
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {	
		$indent  = str_repeat( "\t", $depth );		
		$output .= "$indent<ul class='children'>\n";		
	}
	
	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 1.6.5
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. @see wp_terms_checklist()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {	
		$indent  = str_repeat( "\t", $depth );		
		$output .= "$indent</ul>\n";		
	}
	
	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 1.6.5
	 * @param string $output   Used to append additional content (passed by reference).
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$general_settings = get_option( 'acadp_general_settings' );

		if ( empty( $args['taxonomy'] ) ) {		
			$taxonomy = 'category';			
		} else {		
			$taxonomy = $args['taxonomy'];			
		}

		if ( $taxonomy == 'category' ) {		
			$name = 'post_category';			
		} else {		
			$name = 'acadp_category';			
		}

		$args['popular_cats']      = empty( $args['popular_cats'] ) ? array() : $args['popular_cats'];		
		$class                     = in_array( $category->term_id, $args['popular_cats'] ) ? ' class="checkbox popular-category"' : ' class="checkbox"';		
		$args['selected_cats']     = empty( $args['selected_cats'] ) ? array() : $args['selected_cats'];		
		$disable_parent_categories = empty( $general_settings['disable_parent_categories'] ) ? false : true;		
		
		if ( ! empty( $args['list_only'] ) ) {		
			$aria_checked = 'false';			
			$inner_class  = 'category';
			
			if ( in_array( $category->term_id, $args['selected_cats'] ) ) {			
				$inner_class .= ' selected';				
				$aria_checked = 'true';				
			}
			
			$output .= "\n" . '<li' . $class . '>' .
				'<div class="' . $inner_class . '" data-term-id=' . $category->term_id . ' tabindex="0" role="checkbox" aria-checked="' . $aria_checked . '">' .
				esc_html( apply_filters( 'the_category', $category->name, '', '' ) ) . 
				'</div>';				
		} else {			
			if ( $disable_parent_categories && 0 === (int) $category->parent ) {			
				$categories_acadp = '<label class="bold"><strong>' . esc_html( apply_filters( 'the_category', $category->name, '', '' ) ) . '</strong></label>';			
			} else {			
				$categories_acadp = '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" class="acadp-category-checkbox" data-cb_required="acadp-category-checkbox"' . checked( in_array( $category->term_id, $args['selected_cats'] ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name, '', '' ) ) . '</label>';				
			}
			
			$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" . $categories_acadp;			
		}		
	}
	
	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 1.6.5
	 * @param string $output   Used to append additional content (passed by reference).
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 */
	public function end_el( &$output, $category, $depth = 0, $args = array() ) {	
		$output .= "</li>\n";		
	}
	
}