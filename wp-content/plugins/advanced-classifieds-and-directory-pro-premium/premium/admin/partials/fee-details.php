<?php

/**
 * "Fee Details" meta box.
 *
 * @link    https://pluginsware.com
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<table class="acadp-input widefat">
  <tbody>
  	<tr>
      <td class="label">
       	<label><?php printf( '%s [%s]', __( "Fee Amount", 'advanced-classifieds-and-directory-pro' ), acadp_get_payment_currency() ); ?></label>
      </td>
      <td>
       	<div class="acadp-input-wrap">
      	  <input type="text" class="text" name="price" placeholder="<?php _e( 'The cost of the plan. Use a value of 0.00 for a free plan.', 'advanced-classifieds-and-directory-pro' ); ?>" value="<?php if ( isset( $post_meta['price'] ) ) echo acadp_format_payment_amount( $post_meta['price'][0] ); ?>" />
        </div>
      </td>
    </tr>  

	<tr>
      <td class="label">
       	<label><?php _e( "Listing Duration (in days)", 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
       	<div class="acadp-input-wrap">
      	  <input type="text" class="text" name="listing_duration" placeholder="<?php _e( 'Use a value of 0 to keep a listing alive indefinitely.', 'advanced-classifieds-and-directory-pro' ); ?>" value="<?php if ( isset( $post_meta['listing_duration'] ) ) echo esc_attr( $post_meta['listing_duration'][0] ); ?>" />
        </div>
      </td>
    </tr>
  </tbody>
</table>