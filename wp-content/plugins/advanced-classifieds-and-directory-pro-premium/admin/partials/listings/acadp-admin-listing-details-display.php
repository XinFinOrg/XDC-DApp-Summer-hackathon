<?php

/**
 * Display the "Listing Details" meta box.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<table class="acadp-input widefat">
  <tbody>
    <tr>
      <td class="label">
        <label><?php esc_html_e( 'Category', 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
		<?php
		$categories = apply_filters( 'acadp_admin_listing_form_categories_dropdown', '', $post->ID );

		if ( empty( $categories ) ) {
			$selected_category = count( $category ) ? $category[0] : -1;
			
			$args = array(
				'show_option_none' => '-- ' . esc_html__( 'Select category', 'advanced-classifieds-and-directory-pro' ) . ' --',
				'taxonomy'         => 'acadp_categories',
				'name' 			   => 'acadp_category',
				'orderby'          => 'name',
				'selected'         => (int) $selected_category,
				'hierarchical'     => true,
				'depth'            => 10,
				'show_count'       => false,
				'hide_empty'       => false,
				'echo'			   => false,
			);
			
			if ( $disable_parent_categories ) {
				$args['walker'] = new ACADP_Walker_CategoryDropdown;
			}
			
			$categories = wp_dropdown_categories( $args );
		}

		echo $categories; 
		?>
      </td>
    </tr>
    <?php if ( $has_price ) : ?>
    	<tr>
      		<td class="label">
        		<label><?php printf( '%s [%s]', esc_html__( "Price", 'advanced-classifieds-and-directory-pro' ), acadp_get_currency() ); ?></label>
      		</td>
      		<td>
        		<div class="acadp-input-wrap">
          			<input type="text" class="text" name="price" placeholder="<?php esc_html_e( 'How much do you want it to be listed for?', 'advanced-classifieds-and-directory-pro' ); ?>" value="<?php if ( isset( $post_meta['price'] ) ) echo acadp_format_amount( $post_meta['price'][0] ); ?>" />
        		</div>
      		</td>
    	</tr>  
    <?php endif; ?> 
  </tbody>
</table>

<div id="acadp-custom-fields-list" data-post_id="<?php echo esc_attr( $post->ID ); ?>">
  <?php do_action( 'wp_ajax_acadp_custom_fields_listings', $post->ID ); ?>
</div>

<table class="acadp-input widefat">
  <tbody>
    <tr>
      <td class="label" style="border-top: 1px solid #f0f0f0;">
        <label><?php esc_html_e( "Views count", 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td style="border-top: 1px solid #f0f0f0;">
        <div class="acadp-input-wrap">
          <input type="text" class="text" name="views" value="<?php if ( isset( $post_meta['views'] ) ) echo esc_attr( $post_meta['views'][0] ); ?>" />
        </div>
      </td>
    </tr>   
  </tbody>
</table>
