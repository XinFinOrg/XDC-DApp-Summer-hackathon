<?php

/**
 * Display the "Contact Details" meta box.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<table class="acadp-input widefat" id="acadp-contact-details">
  <tbody>
    <tr>
      <td class="label">
        <label><?php esc_html_e( 'Address', 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
        <textarea id="acadp-address" class="textarea acadp-map-field" name="address" rows="6"><?php if ( isset( $post_meta['address'] ) ) echo esc_textarea( $post_meta['address'][0] ); ?></textarea>
      </td>
    </tr>
    <tr>
      <td class="label">
        <label><?php esc_html_e( 'Location', 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
        <?php
			  wp_dropdown_categories(array(
          'show_option_none'  => '-- ' . esc_html__( 'Select location', 'advanced-classifieds-and-directory-pro' ) . ' --',
				  'option_none_value' => (int) $general_settings['base_location'],
				  'child_of'          => max( 0, (int) $general_settings['base_location'] ),
          'taxonomy'          => 'acadp_locations',
          'name' 			        => 'acadp_location',
				  'class'             => 'postform acadp-map-field',
          'orderby'           => 'name',
          'selected'          => (int) $location,
          'hierarchical'      => true,
          'depth'             => 10,
          'show_count'        => false,
          'hide_empty'        => false,
        ));
		    ?>
      </td>
    </tr>
    <tr>
      <td class="label">
        <label><?php esc_html_e( 'Zip Code', 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
        <div class="acadp-input-wrap">
          <input type="text" id="acadp-zipcode" class="text acadp-map-field" name="zipcode" value="<?php if ( isset( $post_meta['zipcode'] ) ) echo esc_attr( $post_meta['zipcode'][0] ); ?>" />
        </div>
      </td>
    </tr>
    <tr>
      <td class="label">
        <label><?php esc_html_e( 'Phone', 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
        <div class="acadp-input-wrap">
          <input type="text" class="text" name="phone" value="<?php if ( isset( $post_meta['phone'] ) ) echo esc_attr( $post_meta['phone'][0] ); ?>" />
        </div>
      </td>
    </tr>   
    <tr>
      <td class="label">
        <label><?php esc_html_e( 'Email', 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
        <div class="acadp-input-wrap">
          <input type="text" class="text" name="email" value="<?php if ( isset( $post_meta['email'] ) ) echo esc_attr( $post_meta['email'][0] ); ?>" />
        </div>
      </td>
    </tr> 
    <tr>
      <td class="label">
        <label><?php esc_html_e( 'Website', 'advanced-classifieds-and-directory-pro' ); ?></label>
      </td>
      <td>
        <div class="acadp-input-wrap">
          <input type="text" class="text" name="website" value="<?php if ( isset( $post_meta['website'] ) ) echo esc_url( $post_meta['website'][0] ); ?>" />
        </div>
      </td>
    </tr> 
  </tbody>
</table>

<?php if ( ! empty( $general_settings['has_map'] ) ) : ?>
	<div class="acadp-map">
		<div class="marker"></div>    
	</div>
	<input type="hidden" name="latitude" id="acadp-latitude" value="<?php echo esc_attr( $latitude ); ?>" />
	<input type="hidden" name="longitude" id="acadp-longitude" value="<?php echo esc_attr( $longitude ); ?>" />
    
  <ul class="acadp-checkbox-list checkbox vertical">
	  <li>
     	<label><input type="checkbox" name="hide_map" value="1" <?php if ( isset( $post_meta['hide_map'] ) ) checked( $post_meta['hide_map'][0], 1 ); ?>><?php esc_html_e( "Don't show the Map", 'advanced-classifieds-and-directory-pro' ); ?></label>
    </li>
	</ul>
<?php endif;