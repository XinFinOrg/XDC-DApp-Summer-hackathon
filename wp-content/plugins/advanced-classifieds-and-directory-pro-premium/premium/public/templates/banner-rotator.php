<?php

/**
 * This template displays the banner rotator.
 *
 * @link    https://pluginsware.com
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-banner-rotator acadp-slick" data-style_prev_arrow="<?php echo $atts['style_prev_arrow']; ?>" data-style_next_arrow="<?php echo $atts['style_next_arrow']; ?>" data-style_arrow_icon="<?php echo $atts['style_arrow_icon']; ?>" data-style_dots="<?php echo $atts['style_dots']; ?>" data-style_dots_active="<?php echo $atts['style_dots_active']; ?>" data-slick='<?php echo wp_json_encode( $atts['data'] ); ?>'>    
	<?php while ( $acadp_query->have_posts() ) : $acadp_query->the_post(); ?>
        <div class="acadp-slick-item">
        	<div class="acadp-slick-item-inner">
				<a href="<?php the_permalink(); ?>" class="acadp-responsive-item" style="padding-bottom:<?php echo (float) $atts['images_ratio'] * 100; ?>%">
                	<?php	
						$images = get_post_meta( get_the_ID(), 'images', true );
						$image = ACADP_PLUGIN_URL . 'public/images/no-image.png';
						
						if ( ! empty( $images ) ) {
							$image_attributes = wp_get_attachment_image_src( $images[0], 'full' );
							$image = $image_attributes[0];
						}
						
						if ( 'uniform' == $atts['images_scale_type'] ) {
							printf( '<img data-lazy="%s" />', $image );
						} else {
							printf( '<div class="acadp-slider-img-responsive" style="background-image:url(%s);"></div>', $image );
						}				
					?>
                </a>
                <div class="acadp-slick-caption">
               		<h2 class="acadp-slick-caption-title"><?php echo esc_html( get_the_title() ); ?></h2>
                   	<?php if ( ! empty( $atts['show_content'] ) && $post->post_content ) : ?>
                   		<div class="acadp-slick-caption-description hidden-sm hidden-xs"><?php echo get_the_excerpt(); ?></div>
                   	<?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>       
</div>

<!-- Use reset postdata to restore orginal query -->
<?php wp_reset_postdata(); ?>