<?php

/**
 * This template displays the ACADP listings in grid view.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

$fields = array();
if ( isset( $can_show_custom_fields ) && 1 == $can_show_custom_fields ) {
    $fields = acadp_get_custom_fields_listings_archive();
}
?>

<div class="acadp acadp-listings acadp-grid-view">
	<?php if ( $can_show_header ) : ?>
		<!-- header here -->
        <?php if ( ! empty( $pre_content ) ) echo '<p>' . wp_kses_post( $pre_content ) . '</p>'; ?>
        
    	<div class="row acadp-no-margin">
            <form action="<?php echo esc_url( get_permalink() ); ?>" method="POST" class="form-inline">
                <div class="btn-toolbar" role="toolbar">
                    <?php if ( $can_show_listings_count ) : ?>
                        <!-- total items count -->
                        <p class="btn-group pull-left text-muted acadp-xs-clear-float">
                            <?php 
                            $count = ( is_front_page() && is_home() ) ? $acadp_query->post_count : $acadp_query->found_posts;
                            printf( esc_html__( "%d item(s) found", 'advanced-classifieds-and-directory-pro' ), $count );
                            ?>
                        </p>
                    <?php endif; ?>
            
                    <?php if ( $can_show_orderby_dropdown ) : ?>
                        <!-- Orderby dropdown -->
                        <div class="btn-group pull-right acadp-xs-clear-float" role="group">
                            <div class="form-group">
                                <select name="sort" class="form-control" onchange="this.form.action=this.value; this.form.submit();">
                                    <?php
                                    printf(
                                        '<option value="%s">-- %s --</option>',
                                        esc_url( add_query_arg( 'sort', $current_order ) ),
                                        esc_html__( 'Sort by', 'advanced-classifieds-and-directory-pro' )
                                    );
            
                                    $options = acadp_get_listings_orderby_options();
                                    foreach ( $options as $value => $label ) {
                                        printf( 
                                            '<option value="%s" %s>%s</option>',
                                            esc_url( add_query_arg( 'sort', $value ) ),
                                            selected( $value, $current_order, false ),                                    
                                            $label
                                        );
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $can_show_views_selector ) : ?>
                        <!-- Views dropdown -->
                        <div class="btn-group pull-right acadp-xs-clear-float" role="group">
                            <div class="form-group">
                                <select name="view" class="form-control" onchange="this.form.action=this.value; this.form.submit();">
                                    <?php
                                    printf(
                                        '<option value="%s">-- %s --</option>',
                                        esc_url( add_query_arg( 'view', 'grid' ) ),
                                        esc_html__( 'View as', 'advanced-classifieds-and-directory-pro' )
                                    );
            
                                    $views = acadp_get_listings_view_options();
                                    foreach ( $views as $value => $label ) {
                                        printf( 
                                            '<option value="%s" %s>%s</option>',
                                            esc_url( add_query_arg( 'view', $value ) ),
                                            selected( $value, 'grid', false ),                                    
                                            $label
                                        );
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>                   
                </div>
            </form>
		</div>
    <?php endif; ?>
    
	<div class="acadp-divider"></div>
    
	<!-- the loop -->
    <div class="acadp-body">
		<?php 
        $columns = $listings_settings['columns'];
        $span = 'col-md-' . floor( 12 / $columns );
        $i = 0; 
        
        while ( $acadp_query->have_posts() ) : 
            $acadp_query->the_post(); 
            $post_meta = get_post_meta( $post->ID ); 
            ?>
    
            <?php if ( $i % $columns == 0 ) : ?>
                <div class="row">
            <?php endif; ?>            
                <div class="<?php echo esc_attr( $span ); ?>">
                    <div <?php the_acadp_listing_entry_class( $post_meta, 'thumbnail' ); ?>>
                        <?php if ( $can_show_images ) : ?>
                            <a href="<?php the_permalink(); ?>" class="acadp-responsive-container"><?php the_acadp_listing_thumbnail( $post_meta ); ?></a>      	
                        <?php endif; ?>
                
                        <div class="caption">
                            <div class="acadp-listings-title-block">
                                <h3 class="acadp-no-margin"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
                                <?php the_acadp_listing_labels( $post_meta ); ?>
                            </div>
                            
                            <?php
                            // author meta
                            $info = array();					
        
                            if ( $can_show_date ) {
                                $info[] = sprintf( esc_html__( 'Posted %s ago', 'advanced-classifieds-and-directory-pro' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
                            }
                            
                            if ( $can_show_user ) {			
                                $info[] = '<a href="' . esc_url( acadp_get_user_page_link( $post->post_author ) ) . '">' . get_the_author() . '</a>';
                            }

                            echo '<p class="acadp-no-margin"><small class="text-muted">' . implode( ' ' . esc_html__( "by", 'advanced-classifieds-and-directory-pro' ) . ' ', $info ) . '</small></p>';
                            
                            // description
                            if ( ! empty( $listings_settings['excerpt_length'] ) && ! empty( $post->post_content ) ) : ?>
                                <p class="acadp-listings-desc"><?php echo wp_trim_words( $post->post_content, $listings_settings['excerpt_length'], '...' ); ?></p>
                            <?php endif;

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
        
                            if ( $can_show_category && $categories = wp_get_object_terms( $post->ID, 'acadp_categories' ) ) {
                                $category_links = array();
                                foreach ( $categories as $category ) {						
                                    $category_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_category_page_link( $category ) ), esc_html( $category->name ) );						
                                }
                                $info[] = sprintf( '<span class="glyphicon glyphicon-briefcase"></span>&nbsp;%s', implode( ', ', $category_links ) );
                            }
                    
                            if ( $can_show_location && $locations = wp_get_object_terms( $post->ID, 'acadp_locations' ) ) {
                                $location_links = array();
                                foreach ( $locations as $location ) {						
                                    $location_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_location_page_link( $location ) ), esc_html( $location->name ) );						
                                }
                                $info[] = sprintf( '<span class="glyphicon glyphicon-map-marker"></span>&nbsp;%s', implode( ', ', $location_links ) );
                            }
                            
                            if ( 'acadp_favourite_listings' == $shortcode ) {
                                $info[] = '<a href="' . esc_url( acadp_get_remove_favourites_page_link( $post->ID ) ) . '">' . esc_html__( 'Remove from favourites', 'advanced-classifieds-and-directory-pro' ) . '</a>';
                            }
                    
                            if ( $can_show_views && ! empty( $post_meta['views'][0] ) ) {
                                $info[] = sprintf( esc_html__( "%d views", 'advanced-classifieds-and-directory-pro' ), $post_meta['views'][0] );
                            }

                            echo '<p class="acadp-no-margin"><small>' . implode( ' / ', $info ) . '</small></p>';
    
                            // price
                            if ( $can_show_price && isset( $post_meta['price'] ) && $post_meta['price'][0] > 0 ) {
                                $price = acadp_format_amount( $post_meta['price'][0] );						
                                echo '<p class="lead acadp-listings-price">' . esc_html( acadp_currency_filter( $price ) ) . '</p>';
                            }            		
                            ?>
                            
                            <?php do_action( 'acadp_after_listing_content', $post->ID, 'grid' ); ?>
                        </div>
                    </div>
                </div>
                
            <?php 
            $i++;
            if( $i % $columns == 0 || $i == $acadp_query->post_count ) : ?>
                </div>
            <?php endif; ?>                   
        <?php endwhile; ?>
    </div>
    <!-- end of the loop -->
    
    <!-- use reset postdata to restore orginal query -->
    <?php wp_reset_postdata(); ?>
    
    <!-- pagination here -->
    <?php if ( $can_show_pagination ) the_acadp_pagination( $acadp_query->max_num_pages, "", $paged ); ?>
</div>

<?php the_acadp_social_sharing_buttons();