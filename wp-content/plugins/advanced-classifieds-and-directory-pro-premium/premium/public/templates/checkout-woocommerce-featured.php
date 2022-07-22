<?php

/**
 * This template displays the checkout page.
 *
 * @link    https://pluginsware.com
 * @since   1.8.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-user acadp-wc-checkout">
    <form id="acadp-wc-checkout-form" class="form-vertical" method="post" action="" role="form">
		<table id="acadp-wc-checkout-form-data" class="table table-stripped table-bordered">
		<?php 
		global $post;

        while ( $acadp_query->have_posts() ) : 		
			$acadp_query->the_post();
            $product = wc_get_product( $post->ID );
            ?>            	
            <tr>
                <td class="text-center">
                    <input type="radio" name="wc_plan" class="acadp-wc-plan" value="<?php echo esc_attr( $post->ID ); ?>" checked />
                </td>
                <td>
                    <h4 class="acadp-no-margin"><?php echo esc_html( $post->post_title ); ?></h4>
                    <?php the_content(); ?>
                </td>
                <td>
                    <?php echo $product->get_price_html(); ?>
                </td>
            </tr>            	
            <?php 
        endwhile;
		
		// Use reset postdata to restore orginal query
    	wp_reset_postdata();
        ?>    		
    	</table>
        
        <p id="acadp-wc-checkout-errors" class="text-danger"></p>
        
        <?php wp_nonce_field( 'acadp_wc_process_payment', 'acadp_wc_checkout_nonce' ); ?>
        <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
        <div class="pull-right">
        	<a href="<?php echo esc_attr( acadp_get_manage_listings_page_link() ); ?>" class="btn btn-default"><?php esc_html_e( 'Not now', 'advanced-classifieds-and-directory-pro' ); ?></a>
        	<input type="submit" id="acadp-wc-checkout-submit-btn" class="btn btn-primary" value="<?php esc_html_e( 'Proceed to payment', 'advanced-classifieds-and-directory-pro' ); ?>" />
        </div>
    </form>
</div>