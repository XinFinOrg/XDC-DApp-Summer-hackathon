<?php

/**
 * This template displays the public-facing aspects of the widget.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

$fields = array();
if ( isset( $instance['show_custom_fields'] ) && 1 == $instance['show_custom_fields'] ) {
    $fields = acadp_get_custom_fields_listings_archive();
}
?>

<div class="acadp acadp-widget-listings acadp-map-view">	
    <!-- the loop -->
    <div class="acadp-map embed-responsive embed-responsive-16by9 acadp-margin-bottom" data-type="markerclusterer"> 
		<?php 
		while ( $acadp_query->have_posts() ) : 
			$acadp_query->the_post(); 
			$post_meta = get_post_meta( $post->ID ); 
			?>
    
    		<?php if ( ! empty( $post_meta['latitude'][0] ) && ! empty( $post_meta['longitude'][0] ) ) : ?>
        		<div class="marker" data-latitude="<?php echo esc_attr( $post_meta['latitude'][0] ); ?>" data-longitude="<?php echo esc_attr( $post_meta['longitude'][0] ); ?>">
            		<div <?php the_acadp_listing_entry_class( $post_meta, 'media' ); ?>>
                		<?php if ( $instance['has_images'] && $instance['show_image'] ) : ?>
                        	<div class="media-left">
                				<a href="<?php the_permalink(); ?>"><?php the_acadp_listing_thumbnail( $post_meta ); ?></a> 
                            </div>     	
            			<?php endif; ?>
            
            			<div class="media-body">
                    		<div class="acadp-listings-title-block">
                    			<h3 class="acadp-no-margin"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
                            	<?php the_acadp_listing_labels( $post_meta ); ?>
                        	</div>
                        
                        	<?php
							// author meta
							$info = array();					
	
							if ( $instance['show_date'] ) {
								$info[] = sprintf( esc_html__( 'Posted %s ago', 'advanced-classifieds-and-directory-pro' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
							}
						
							if ( $instance['show_user'] ) {			
								$info[] = '<a href="' . esc_url( acadp_get_user_page_link( $post->post_author ) ) . '">' . get_the_author() . '</a>';
							}

							echo '<p class="acadp-no-margin"><small class="text-muted">' . implode( ' ' . esc_html__( "by", 'advanced-classifieds-and-directory-pro' ) . ' ', $info ) . '</small></p>';
							
							// description
							if ( $instance['show_description'] ) {
								if ( ! empty( $post->post_content ) && ! empty( $this->listings_settings['excerpt_length'] ) ) {
									echo '<p class="acadp-listings-desc">' . wp_trim_words( get_the_content(), $this->listings_settings['excerpt_length'], '...' ) . '</p>';
								}
							}

							// custom fields
							if ( count( $fields ) ) : ?>
								<ul class="list-group acadp-margin-bottom">
									<?php foreach ( $fields as $field ) : ?> 
										<?php if ( ! empty( $post_meta[ $field->ID ][0] ) ) : ?>
											<li class="list-group-item acadp-no-margin-left acadp-field-<?php echo esc_attr( $field->type ); ?>">
												<span class="text-primary"><?php echo esc_html( $field->post_title ); ?></span>:
												<span class="text-muted">
													<?php 
													$value = acadp_get_custom_field_display_text( $post_meta[ $field->ID ][0], $field );
													
													if ( 'textarea' == $field->type ) {
														echo wp_kses_post( nl2br( $value ) );
													} elseif ( 'url' == $field->type ) {
														echo wp_kses_post( $value );
													} else {
														echo esc_html( $value );
													}
													?>
												</span>
											</li>
										<?php endif; ?>                    
									<?php endforeach; ?>
								</ul>
							<?php endif;

							// categories, locations & views
							$info = array();					
	
							if ( $instance['show_category'] && $categories = wp_get_object_terms( $post->ID, 'acadp_categories' ) ) {
								$category_links = array();
								foreach ( $categories as $category ) {						
									$category_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_category_page_link( $category ) ), esc_html( $category->name ) );						
								}
								$info[] = sprintf( '<span class="glyphicon glyphicon-briefcase"></span>&nbsp;%s', implode( ', ', $category_links ) );
							}
				
							if ( $instance['has_location'] && $instance['show_location'] && $locations = wp_get_object_terms( $post->ID, 'acadp_locations' ) ) {
								$location_links = array();
                                foreach ( $locations as $location ) {						
                                    $location_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_location_page_link( $location ) ), esc_html( $location->name ) );						
                                }
                                $info[] = sprintf( '<span class="glyphicon glyphicon-map-marker"></span>&nbsp;%s', implode( ', ', $location_links ) );
							}
				
							if ( $instance['show_views'] && ! empty( $post_meta['views'][0] ) ) {
								$info[] = sprintf( esc_html__( "%d views", 'advanced-classifieds-and-directory-pro' ), $post_meta['views'][0] );
							}

							echo '<p class="acadp-no-margin"><small>'.implode( ' / ', $info ).'</small></p>';

							// price
							if ( $instance['has_price'] && $instance['show_price'] && isset( $post_meta['price'] ) && $post_meta['price'][0] > 0 ) {
								$price = acadp_format_amount( $post_meta['price'][0] );						
								echo '<p class="lead acadp-listings-price">' . esc_html( acadp_currency_filter( $price ) ) . '</p>';
							}            		
                			?>
                    	</div>
                	</div>
            	</div>
        	<?php endif; ?>               
  		<?php endwhile; ?>
    </div>
    <!-- end of the loop -->
    
    <!-- use reset postdata to restore orginal query -->
    <?php wp_reset_postdata(); ?>    
</div>