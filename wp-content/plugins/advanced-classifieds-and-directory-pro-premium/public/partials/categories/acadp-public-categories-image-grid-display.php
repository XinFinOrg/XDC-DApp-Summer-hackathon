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

<div class="acadp acadp-categories acadp-image-grid">
	<?php			
	$columns = (int) $attributes['columns'];
	$span = 'col-md-' . floor( 12 / $columns );
	$i = 0;
		
	foreach ( $terms as $term ) {		
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
		echo '<div class="thumbnail">';
		
		$image_id = get_term_meta( $term->term_id, 'image', true );
		if ( $image_id ) {
			$image_attributes = wp_get_attachment_image_src( (int) $image_id, 'medium' );
			$image = $image_attributes[0];
			
			if ( ! empty( $image ) ) {
				echo '<a href="' . esc_url( $category_url ) . '" class="acadp-responsive-container" title="' . esc_attr( $title_attr ) . '">';
				echo '<img src="' . esc_url( $image ) . '" class="acadp-responsive-item" />';
				echo '</a>';
			}
		}
	
		echo '<div class="caption">';
		echo '<h3 class="acadp-no-margin">';
		echo '<a href="' . esc_url( $category_url ) . '" title="' . esc_attr( $title_attr ) . '">';
		echo '<strong>' . esc_html( $term->name ) . '</strong>';
		if ( ! empty( $attributes['show_count'] ) ) {
			echo ' (' .  esc_html( $count ) . ')';
		}
		echo '</a>';
		echo '</h3>';
		echo '</div>';
		
		echo '</div>';			
		echo '</div>';
			
		$i++;
		if ( $i % $columns == 0 || $i == count( $terms ) ) {
			echo '</div>';
		}
						
	}
	?>
</div>

<?php the_acadp_social_sharing_buttons();