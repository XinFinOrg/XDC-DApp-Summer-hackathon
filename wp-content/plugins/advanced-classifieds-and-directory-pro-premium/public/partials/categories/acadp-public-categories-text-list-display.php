<?php

/**
 * This template displays the ACADP categories list.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-categories acadp-text-list">
	<?php			
	$columns = (int) $attributes['columns'];
	$span = 'col-md-' . floor( 12 / $columns );
	$attributes['depth'] = (int) $attributes['depth'] - 1;
	$i = 0;
		
	foreach ( $terms as $term ) {			
		$attributes['term_id'] = $term->term_id;
		
		$count = 0;
		if ( ! empty( $attributes['hide_empty'] ) || ! empty( $attributes['show_count'] ) ) {
			$count = acadp_get_listings_count_by_category( $term->term_id, $attributes['pad_counts'] );
			
			if ( ! empty( $attributes['hide_empty'] ) && 0 == $count ) continue;
		}
		
		$category_url = acadp_get_category_page_link( $term );
		$title_attr = sprintf( __( "View all posts in %s", 'advanced-classifieds-and-directory-pro' ), $term->name );

		if ( $i % $columns == 0 ) {
			echo '<div class="row acadp-no-margin">';
		}
			
		echo '<div class="' . esc_attr( $span ) . '">'; 
		echo '<a href="' . esc_url( $category_url ) . '" title="' . esc_attr( $title_attr ) . '">';
		echo '<strong>' . esc_html( $term->name ) . '</strong>';
		if ( ! empty( $attributes['show_count'] ) ) {
			echo ' (' . esc_html( $count ) . ')';
		}
		echo '</a>';
		echo acadp_list_categories( $attributes );
		echo '</div>';
			
		$i++;
		if ( $i % $columns == 0 || $i == count( $terms ) ) {
			echo '</div>';
		}							
	}
	?>
</div>

<?php the_acadp_social_sharing_buttons();