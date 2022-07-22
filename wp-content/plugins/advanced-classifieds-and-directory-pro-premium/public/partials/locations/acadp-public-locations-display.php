<?php

/**
 * This template displays the ACADP locations list.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-locations">
	<?php			
	$columns = (int) $attributes['columns'];
	$span = 'col-md-' . floor( 12 / $columns );
	$attributes['depth'] = (int) $attributes['depth'] - 1;
	$i = 0;
		
	foreach ( $terms as $term ) {			
		$attributes['term_id'] = $term->term_id;
		
		$location_url = acadp_get_location_page_link( $term );
		$title_attr = sprintf( __( "View all posts in %s", 'advanced-classifieds-and-directory-pro' ), $term->name );
		$count = acadp_get_listings_count_by_location( $term->term_id, $attributes['pad_counts'] );
		
		if ( $i % $columns == 0 ) {
			echo '<div class="row">';
		}
			
		echo '<div class="' . esc_attr( $span ) . '">'; 
		echo '<a href="' . esc_url( $location_url ) . '" title="' . esc_attr( $title_attr ) . '">';
		echo '<strong>' . esc_html( $term->name ) . '</strong>';
		if ( ! empty( $attributes['show_count'] ) ) {
			echo ' (' . esc_html( $count ) . ')';
		}
		echo '</a>';
		echo acadp_list_locations( $attributes );
		echo '</div>';
			
		$i++;
		if ( $i % $columns == 0 || $i == count( $terms ) ) {
			echo '</div>';
		}							
	}
	?>
</div>

<?php the_acadp_social_sharing_buttons();