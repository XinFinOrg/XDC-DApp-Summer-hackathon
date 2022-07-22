<?php

/**
 * This template displays the ACADP user listings dashboard.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-user acadp-manage-listings acadp-list-view">
	<?php acadp_status_messages(); ?>
    
	<!-- header here -->
    <div class="row acadp-no-margin">
    	<div class="pull-left">
        	<form action="<?php echo esc_url( acadp_get_manage_listings_page_link( true ) ); ?>" class="form-inline" role="form">
            	<?php if ( ! get_option('permalink_structure') ) : ?>
        			<input type="hidden" name="page_id" value="<?php if ( $page_settings['manage_listings'] > 0 ) echo esc_attr( $page_settings['manage_listings'] ); ?>">
        		<?php endif; ?>
        
            	<div class="form-group">
                	<?php $search_query = isset( $_REQUEST['u'] ) ? $_REQUEST['u'] : ''; ?>
    				<input type="text" name="u" class="form-control" placeholder="<?php esc_attr_e( "Search by title", 'advanced-classifieds-and-directory-pro' ); ?>" value="<?php echo esc_attr( $search_query ); ?>" />
  				</div>
				  <!--Mel: 28/01/22-->
                <button type="submit" class="btn btn-default"><?php esc_html_e( "Search", 'advanced-classifieds-and-directory-pro' ); ?></button>
				  <!-- <button type="submit" class="btn btn-primary"><?php //esc_html_e( "Search", 'advanced-classifieds-and-directory-pro' ); ?></button> -->
                <a href="<?php echo esc_url( acadp_get_manage_listings_page_link() ); ?>" class="btn btn-default"><?php esc_html_e( "Reset", 'advanced-classifieds-and-directory-pro' ); ?></a>
            </form>
        </div>
        <div class="pull-right">
        	<a href="<?php echo esc_url( acadp_get_listing_form_page_link() ); ?>" class="btn btn-success"><?php esc_html_e( 'Add New Listing', 'advanced-classifieds-and-directory-pro' ); ?></a>
        </div>
        <div class="clearfix"></div>
    </div>
    
    <div class="acadp-divider"></div>
    
    <!-- the loop -->
	<?php while ( $acadp_query->have_posts() ) : 
		$acadp_query->the_post(); 
		$post_meta = get_post_meta( $post->ID ); 
		?>
    	<div class="row acadp-no-margin">
        	<?php if ( $can_show_images ) : ?>
        		<div class="col-md-2">   
                	<a href="<?php the_permalink(); ?>"><?php the_acadp_listing_thumbnail( $post_meta ); ?></a>      	
            	</div>
            <?php endif; ?>
            
            <div class="<?php echo esc_attr( $span_middle ); ?>">            
            	<div class="acadp-listings-title-block">
            		<h3 class="acadp-no-margin"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
                    <?php the_acadp_listing_labels( $post_meta ); ?>
                </div>

				<p>
                	<small class="text-muted">
						<?php printf( esc_html__( 'Posted %s ago', 'advanced-classifieds-and-directory-pro' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
                    </small>
                </p>

				<?php
				$info = array();					
	
				if ( $categories = wp_get_object_terms( $post->ID, 'acadp_categories' ) ) {
					$category_links = array();
					foreach ( $categories as $category ) {						
						$category_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_category_page_link( $category ) ), esc_html( $category->name ) );						
					}
					$info[] = sprintf( '<span class="glyphicon glyphicon-info-sign"></span>&nbsp;%s', implode( ', ', $category_links ) );
				}
				
				if ( $has_location && $locations = wp_get_object_terms( $post->ID, 'acadp_locations' ) ) {
					$location_links = array();
					foreach ( $locations as $location ) {						
						$location_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_location_page_link( $location ) ), esc_html( $location->name ) );						
					}
					$info[] = sprintf( '<span class="glyphicon glyphicon-map-marker"></span>&nbsp;%s', implode( ', ', $location_links ) );
				}
				
				if ( ! empty( $post_meta['views'][0] ) ) {
					$info[] = sprintf( esc_html__( "%d views", 'advanced-classifieds-and-directory-pro' ), $post_meta['views'][0] );
				}
				
				if ( ! empty( $post_meta['price'][0] ) ) {
					$price = acadp_format_amount( $post_meta['price'][0] );						
					$info[] = esc_html( acadp_currency_filter( $price ) );
				}

				echo '<p class=""><small>' . implode( ' / ', $info ) . '</small></p>';
				?>
                
                <p>
                	<!--Mel: 28/01/22 <strong><?php //_e( 'Status', 'advanced-classifieds-and-directory-pro' ); ?></strong>: 
                    <?php //echo esc_html( acadp_get_listing_status_i18n( $post->post_status ) ); ?> -->
                </p>
                
                <?php //if ( ! empty( $post_meta['never_expires'] ) ) : ?>
                	<p>
                		<!-- <strong><?php //esc_html_e( 'Expires on', 'advanced-classifieds-and-directory-pro' ); ?></strong>:  -->
                    	<?php //esc_html_e( 'Never Expires', 'advanced-classifieds-and-directory-pro' ); ?>
                	</p>                
                <?php //elseif ( ! empty( $post_meta['expiry_date'] ) ) : ?>
                	<p>
                		<!-- <strong><?php //esc_html_e( 'Expires on', 'advanced-classifieds-and-directory-pro' ); ?></strong>:  -->
                    	<?php //echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $post_meta['expiry_date'][0] ) ); ?>
                	</p>
                <?php //endif; ?>
             </div>
            
            <div class="col-md-3 text-right">
            	<?php
				/*$listing_status = ! empty( $post_meta['listing_status'][0] ) ? $post_meta['listing_status'][0] : '';
				$can_edit = 1;
				
				if ( in_array( $listing_status, array( 'renewal', 'expired' ) ) ) {
					if ( 'expired' == $listing_status ) {
						$can_edit = 0;
					}					

					if ( $can_renew ) {
						printf( '<p><a href="%s" class="btn btn-primary btn-sm btn-block">%s</a></p>', esc_url( acadp_get_listing_renewal_page_link( $post->ID ) ), esc_html__( 'Renew', 'advanced-classifieds-and-directory-pro' ) );
					}								
				} else {							
					if ( 'pending' == $post->post_status ) {
						$can_edit = 0;
					}
					
					if ( $can_promote && empty( $post_meta['featured'][0] ) && 'publish' == $post->post_status ) {
						printf( '<p><a href="%s" class="btn btn-primary btn-sm btn-block">%s</a></p>', esc_url( acadp_get_listing_promote_page_link( $post->ID ) ), esc_html__( 'Promote', 'advanced-classifieds-and-directory-pro' ) );
					}
				}*/
             	?>
                
                <div class="btn-group btn-group-justified">
                	<?php //if ( $can_edit ) : ?>
                        <!-- <a href="<?php //echo esc_url( acadp_get_listing_edit_page_link( $post->ID ) ); ?>" class="btn btn-default btn-sm">
                            <?php //esc_html_e( 'Edit', 'advanced-classifieds-and-directory-pro' ); ?>
                        </a> -->
                    <?php //endif; ?>
                    
					<!--Mel: 29/01/22-->
               		<!-- <a href="<?php //echo esc_url( acadp_get_listing_delete_page_link( $post->ID ) ); ?>" class="btn btn-danger btn-sm" onclick="return confirm( '<?php //esc_attr_e( 'Are you sure you want to delete this listing?', 'advanced-classifieds-and-directory-pro' ); ?>' );">
						<?php //esc_html_e( 'Delete', 'advanced-classifieds-and-directory-pro' ); ?> -->
                	</a>
                </div>
            </div>
    	</div>
        
        <div class="acadp-divider"></div>
    <?php endwhile; ?>
    <!-- end of the loop -->
    
    <!-- pagination here -->
    <?php the_acadp_pagination( $acadp_query->max_num_pages, "", $paged ); ?>
</div>