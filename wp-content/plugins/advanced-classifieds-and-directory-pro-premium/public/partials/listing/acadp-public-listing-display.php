<?php

/**
 * This template displays the file detail page content.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<!--Mel: 29/01/22. To ensure title stays on top of page in responsive mode-->
<div class="acadp-post-title">
    <h1 class="text-center"><?php echo esc_html( $post->post_title ); ?></h1>
</div>

<div class="acadp acadp-listing">
	<div class="row">    	
        <!-- Main content -->
        <div class="<?php echo $has_sidebar ? 'col-md-8' : 'col-md-12'; ?>">  
        	<!-- Header -->      
            <!-- <div class="pull-left acadp-post-title">        	 -->
                <!--Mel: 27/02/22<h1 class="acadp-no-margin"><?php //echo esc_html( $post->post_title ); ?></h1>-->
                <?php //the_acadp_listing_labels( $post_meta ); ?>
                <?php				
                //$usermeta = array();
                
                // if ( $can_show_date ) {
                //     $usermeta[] = sprintf( esc_html__( 'Posted %s ago', 'advanced-classifieds-and-directory-pro' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
                // }
                            
                // if ( $can_show_user ) {			
                //     $usermeta[] = '<a href="' . esc_url( acadp_get_user_page_link( $post->post_author ) ) . '">' . get_the_author() . '</a>';
                // }
                
                // $meta = array();
                
                // if ( $can_show_category ) {
                //     $category_links = array();
                //     foreach ( $categories as $category ) {						
                //         $category_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_category_page_link( $category ) ), esc_html( $category->name ) );						
                //     }
				// 	$meta[] = sprintf( '<span class="glyphicon glyphicon-briefcase"></span>&nbsp;%s', implode( ', ', $category_links ) );
                // }

                // //Mel: 27/01/22
                // $meta[] = sprintf( '<span class="glyphicon glyphicon-zoom-in"></span>&nbsp;<a href="https://explorer.testnet.aurora.dev/tx/%s">%s</a>', $post_meta['tx_hash'][0], esc_html__( 'View on Block Explorer', 'advanced-classifieds-and-directory-pro' ) );

                // if ( count( $usermeta ) ) {
                //     $meta[] = implode( ' ' . esc_html__( 'by', 'advanced-classifieds-and-directory-pro' ) . ' ', $usermeta );
                // }
                
                // if ( $can_show_views ) {
                //     $meta[] = sprintf( esc_html__( "%d views", 'advanced-classifieds-and-directory-pro' ), $post_meta['views'][0] );
                // }
                
                // if ( count( $meta ) ) {
                //     //Mel: 26/11/21
				// 	//echo '<p><small>' . implode( ' / ', $meta ) . '</small></p>';
				// 	echo '<p><small class="text-muted">' . implode( ' / ', $meta ) . '</small></p>';
                // }
                // ?>
            <!-- </div> -->
            
            <!-- Price -->
            <?php if ( $can_show_price ) : ?>
                <div class="pull-right text-right acadp-price-block">
                    <?php
                    $price = acadp_format_amount( $post_meta['price'][0] );						
                    echo '<p class="lead acadp-no-margin">' . esc_html( acadp_currency_filter( $price ) ) . '</p>';
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="clearfix"></div>
            
            <!-- Image(s) -->
            <?php if ( $can_show_images ) : $images = unserialize( $post_meta['images'][0] ); ?>
				<?php if ( 1 == count( $images ) ) : $image_attributes = wp_get_attachment_image_src( $images[0], 'large' ); ?>
                    <p>
                        <a class="acadp-image-popup" href="<?php echo esc_url( $image_attributes[0] ); ?>">
                            
                            <!--Mel: 29/01/22-->
                            <!-- <div style="text-align: right">
                                <img id="main_img" src="<?php //echo esc_url( $image_attributes[0] ); ?>" />
                            </div> -->
							<img src="<?php echo esc_url( $image_attributes[0] ); ?>" />
                        </a>
                    </p>
                <?php else : ?>
                    <div id="acadp-slider-wrapper">                       
                        <!-- Slider for -->
                        <div class="acadp-slider-for">
                            <?php foreach ( $images as $index => $image ) : $image_attributes = wp_get_attachment_image_src( $images[ $index ], 'large' ); ?>
                            	<div class="acadp-slider-item">
                                    <div class="acadp-responsive-container">
                                        <?php //Mel: 28/01/22. To replace non-image files with a placeholder. Credit: https://commons.wikimedia.org/wiki/File:Image_not_available.png
                                        if ( empty( $image_attributes[0] ) ) {
                                            $image = apply_filters( 'acadp_no_image_file_path', ACADP_PLUGIN_URL . 'public/images/no-image-wide.png' );
                                            echo '<img src="' . $image . '" />';
                                        } else {
                                            echo '<img data-lazy="'. esc_url( $image_attributes[0] ) . '" class="acadp-responsive-item" />';
                                        }
                                        ?>
                                        <!-- <img data-lazy="<?php //echo esc_url( $image_attributes[0] ); ?>" class="acadp-responsive-item" /> -->
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Slider nav -->
                        <div class="acadp-slider-nav">
                            <?php foreach ( $images as $index => $image ) : $image_attributes = wp_get_attachment_image_src( $images[ $index ], 'thumbnail' ); ?>
                                <div class="acadp-slider-item">
                                    <div class="acadp-slider-item-inner">
                                        <img data-lazy="<?php echo esc_url( $image_attributes[0] ); ?>" />
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>        
                    </div>
                <?php endif; ?> 
            <?php endif; ?>

            <!--Mel: 27/01/22-->
            <div class="pull-left">
                <?php the_acadp_listing_labels( $post_meta ); ?>
                <?php				
                $usermeta = array();
                
                if ( $can_show_date ) {
                    $usermeta[] = sprintf( esc_html__( 'Posted %s ago', 'advanced-classifieds-and-directory-pro' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) );
                }
                            
                if ( $can_show_user ) {			
                    $usermeta[] = '<a href="' . esc_url( acadp_get_user_page_link( $post->post_author ) ) . '">' . get_the_author() . '</a>';
                }
                
                $meta = array();
                
                if ( $can_show_category ) {
                    $category_links = array();
                    foreach ( $categories as $category ) {						
                        $category_links[] = sprintf( '<a href="%s">%s</a>', esc_url( acadp_get_category_page_link( $category ) ), esc_html( $category->name ) );						
                    }
					$meta[] = sprintf( '<span class="glyphicon glyphicon-info-sign"></span>&nbsp;%s', implode( ', ', $category_links ) );
                }

                //Mel:
                $meta[] = sprintf( '<span class="glyphicon glyphicon-zoom-in"></span>&nbsp;<a href="https://explorer.xinfin.network/txs/%s">%s</a>', esc_html__($post_meta['tx_hash'][0]), esc_html__( 'View on Block Explorer', 'advanced-classifieds-and-directory-pro' ) );

                if (isset($post_meta['ipfs_metadata_cid'][0])) {
                    $meta[] = sprintf( '<span class="glyphicon glyphicon-file"></span>&nbsp;<a href="https://ipfs.io/ipfs/%s/metadata.json">%s</a>', esc_html__($post_meta['ipfs_metadata_cid'][0]), esc_html__( 'View on IPFS', 'advanced-classifieds-and-directory-pro' ) );
                }

                if ( count( $usermeta ) ) {
                    $meta[] = implode( ' ' . esc_html__( 'by', 'advanced-classifieds-and-directory-pro' ) . ' ', $usermeta );
                }
                
                if ( $can_show_views ) {
                    $meta[] = sprintf( esc_html__( "%d views", 'advanced-classifieds-and-directory-pro' ), $post_meta['views'][0] );
                }
                
                if ( count( $meta ) ) {
                    //Mel: 28/01/22
                    echo '<p><small class="text-muted">' . implode( ' <br /> ', $meta ) . '</small></p>';
					//echo '<p><small class="text-muted">' . implode( ' / ', $meta ) . '</small></p>';
                }
                ?>
            </div>
            <!--Mel: 27/01/22. End-->

            <!-- Description -->
            <?php //echo wp_kses_post( $description ); ?> 
            
            <!--Mel: 06/12/21
            <ul class="list-group acadp-margin-bottom">-->
                <!--Mel: 20/11/21
					<li class="list-group-item acadp-no-margin-left acadp-field-id">
                    <span class="text-primary"><?php //esc_html_e( 'Listing ID', 'advanced-classifieds-and-directory-pro' ); ?></span>:
                    <span class="text-muted"><?php //echo esc_html( $post->ID ); ?></span>
				</li>-->

                <!-- Custom fields -->
                <!--Mel: 06/12/21
                <?php //if ( count( $fields ) ) : ?>
                    <?php //foreach ( $fields as $field )  : ?>                
                        <?php //if ( ! empty( $post_meta[ $field->ID ][0] ) ) : ?>
                            <li class="list-group-item acadp-no-margin-left acadp-field-<?php //echo esc_attr( $field->type ); ?>">
                                <span class="text-primary"><?php //echo esc_html( $field->post_title ); ?></span>:
                                <span class="text-muted">
                                    <?php 
                                   // $value = acadp_get_custom_field_display_text( $post_meta[ $field->ID ][0], $field );
                                    
                                    /*if ( 'textarea' == $field->type ) {
                                        echo wp_kses_post( nl2br( $value ) );
                                    } elseif ( 'url' == $field->type ) {
                                        echo wp_kses_post( $value );
                                    } else {
                                        echo esc_html( $value );
                                    }*/
                                    ?>
                                </span>
                            </li>
                        <?php //endif; ?>                    
                    <?php //endforeach; ?>
                <?php //endif; ?>
            </ul>
            Mel: End-->
            
			            <!-- Description -->
            <?php //Mel: 06/12/21 echo wp_kses_post( $description ); ?>
            
            <!-- Footer -->
            <?php if ( $can_show_user || $can_add_favourites || $can_report_abuse ) : ?>
                <ol class="breadcrumb">
                    <?php if ( $can_show_user ) : ?>
                        <li class="acadp-no-margin">			
                            <a href="<?php echo esc_url( acadp_get_user_page_link( $post->post_author ) ); ?>"><?php esc_html_e( 'Check all listings by this user', 'advanced-classifieds-and-directory-pro' ); ?></a>
                        </li>
                    <?php endif; ?>
                        
                    <?php if ( $can_add_favourites ) : ?>
                        <li id="acadp-favourites" class="acadp-no-margin"><?php the_acadp_favourites_link(); ?></li>
                    <?php endif; ?>
                        
                    <?php if ( $can_report_abuse ) : ?>
                        <li class="acadp-no-margin">
                            <?php if ( is_user_logged_in() ) { ?>
                                <a href="#" data-toggle="modal" data-target="#acadp-report-abuse-modal"><?php esc_html_e( 'Report abuse', 'advanced-classifieds-and-directory-pro' ); ?></a>
                                    
                                <!-- Modal (report abuse form) -->
                                <div class="modal fade" id="acadp-report-abuse-modal" tabindex="-1" role="dialog" aria-labelledby="acadp-report-abuse-modal-label">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form id="acadp-report-abuse-form" class="form-vertical" role="form">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                                                    <h3 class="modal-title" id="acadp-report-abuse-modal-label"><?php esc_html_e( 'Report Abuse', 'advanced-classifieds-and-directory-pro' ); ?></h3>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="acadp-report-abuse-message"><?php esc_html_e( 'Your Complaint', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span></label>
                                                        <textarea class="form-control" id="acadp-report-abuse-message" rows="3" placeholder="<?php esc_attr_e( 'Message', 'advanced-classifieds-and-directory-pro' ); ?>..." required></textarea>
                                                    </div>
                                                    <div id="acadp-report-abuse-g-recaptcha"></div>
                                                    <div id="acadp-report-abuse-message-display"></div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php esc_html_e( 'Close', 'advanced-classifieds-and-directory-pro' ); ?></button>
                                                    <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Submit', 'advanced-classifieds-and-directory-pro' ); ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <a href="#" class="acadp-require-login"><?php esc_html_e( 'Report abuse', 'advanced-classifieds-and-directory-pro' ); ?></a>
                            <?php } ?>
                        </li>
                    <?php endif; ?>
                </ol>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <?php if ( $has_sidebar ) : ?>
            <div class="col-md-4">

            <!--Mel: 06/12/21-->
                <ul class="list-group acadp-margin-bottom">
                    <!-- Custom fields -->
                    <?php if ( count( $fields ) ) : ?>
                        <?php foreach ( $fields as $field )  : ?>                
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
                    <?php endif; ?>
                </ul>
                <?php echo wp_kses_post( $description ); ?>
                <!--Mel: End-->

            	<!-- Video -->
                <?php if ( $can_show_video ) : ?>
                	<div class="acadp-margin-bottom">
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item" src="<?php echo esc_url( $video_url ); ?>" allowfullscreen></iframe>
                        </div>
                    </div>
                <?php endif; ?> 
                
                <!-- Map & Address -->
                <?php if ( $has_location ) : ?>
                	<fieldset>
                    	<legend><?php //esc_html_e( 'Contact details', 'advanced-classifieds-and-directory-pro' ); ?></legend>
						<?php if ( $can_show_map ) : ?>
                            <div class="embed-responsive embed-responsive-16by9 acadp-margin-bottom" data-type="single-listing">
                                <div class="acadp-map embed-responsive-item">
                                    <div class="marker" data-latitude="<?php echo esc_attr( $post_meta['latitude'][0] ); ?>" data-longitude="<?php echo esc_attr( $post_meta['longitude'][0] ); ?>"></div> 
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Address -->
                        <?php 
                        //$location_id = ! empty( $location ) ? $location->term_id : 0;
                        //the_acadp_address( $post_meta, $location_id ); 
                        ?>
                    </fieldset>
                <?php endif; ?>
                
                <!-- Contact form -->
                <?php if ( $can_show_contact_form ) : ?>
                	<!--Mel: 20/11/21. Start
					<fieldset>
                    	<legend><?php //esc_html_e( 'Contact this listing owner', 'advanced-classifieds-and-directory-pro' ); ?></legend>
						<?php //if ( ! empty( $general_settings['contact_form_require_login'] ) && ! is_user_logged_in() ) { ?> 
                            <p class="text-muted">
                                <?php 
									/*if ( 'acadp' == $registration_settings['engine'] ) {
										printf( __( 'Please, <a href="%s">login</a> to contact this listing owner.', 'advanced-classifieds-and-directory-pro' ), esc_url( $login_url ) );
									} else {
										esc_html_e( 'Please, login to contact this listing owner.', 'advanced-classifieds-and-directory-pro' );
									}*/
								?>
                            </p>
                        <?php /*} else { 
                            $current_user = wp_get_current_user();*/
                            ?>
                            <form id="acadp-contact-form" class="form-vertical" role="form">
                                <div class="form-group">
                                    <label for="acadp-contact-name"><?php //esc_html_e( 'Your Name', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span></label>
                                    <input type="text" class="form-control" id="acadp-contact-name" value="<?php //echo esc_attr( $current_user->display_name ); ?>" placeholder="<?php //esc_attr_e( 'Name', 'advanced-classifieds-and-directory-pro' ); ?>" required />
                                </div>
                                
                                <div class="form-group">
                                    <label for="acadp-contact-email"><?php //esc_html_e( 'Your E-mail Address', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span></label>
                                    <input type="email" class="form-control" id="acadp-contact-email" value="<?php //echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php //esc_attr_e( 'Email', 'advanced-classifieds-and-directory-pro' ); ?>" required />
                                </div>
                                
                                <div class="form-group">
                                    <label for="acadp-contact-phone"><?php //esc_html_e( 'Your Phone Number', 'advanced-classifieds-and-directory-pro' ); ?></label>
                                    <input type="text" class="form-control" id="acadp-contact-phone" placeholder="<?php //esc_attr_e( 'Phone', 'advanced-classifieds-and-directory-pro' ); ?>" />
                                </div>
                                
                                <div class="form-group">
                                    <label for="acadp-contact-message"><?php //esc_html_e( 'Your Message', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span></label>
                                    <textarea class="form-control" id="acadp-contact-message" rows="3" placeholder="<?php //esc_attr_e( 'Message', 'advanced-classifieds-and-directory-pro' ); ?>..." required></textarea>
                                </div>
                                
                                <div id="acadp-contact-g-recaptcha"></div>
                                <div id="acadp-contact-message-display"></div>
                                
                                <button type="submit" class="btn btn-primary"><?php //esc_html_e( 'Submit', 'advanced-classifieds-and-directory-pro' ); ?></button>
                            </form> 
                        <?php //} ?>
                    </fieldset>
					End-->
                <?php endif; ?>
            </div>
        <?php endif; ?>                
    </div>

	<input type="hidden" id="acadp-post-id" value="<?php echo esc_attr( $post->ID ); ?>" />
</div>

<?php the_acadp_social_sharing_buttons();
