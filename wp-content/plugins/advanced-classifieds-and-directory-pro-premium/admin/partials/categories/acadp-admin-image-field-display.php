<?php

/**
 * Display "Image Field" in the ACADP categories page.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<?php if ( 'add' == $page ) : ?>
    <div class="form-field term-group">
        <label for="acadp-categories-image-id"><?php esc_html_e( 'Image', 'advanced-classifieds-and-directory-pro' ); ?></label>
        <input type="hidden" id="acadp-categories-image-id" name="image" />
        <div id="acadp-categories-image-wrapper"></div>
        <p>
            <input type="button" id="acadp-categories-upload-image" class="button button-secondary" value="<?php esc_attr_e( 'Add Image', 'advanced-classifieds-and-directory-pro' ); ?>" />
            <input type="button" id="acadp-categories-remove-image" class="button button-secondary" value="<?php esc_attr_e( 'Remove Image', 'advanced-classifieds-and-directory-pro' ); ?>" style="display: none;" />
        </p>
    </div>
<?php elseif ( 'edit' == $page ) : ?>
	<tr class="form-field term-group-wrap">
    	<th scope="row">
        	<label for="acadp-categories-image-id"><?php esc_html_e( 'Image', 'advanced-classifieds-and-directory-pro' ); ?></label>
        </th>
        <td>
            <input type="hidden" id="acadp-categories-image-id" name="image" value="<?php echo esc_attr( $image_id ); ?>" />
            <div id="acadp-categories-image-wrapper">
            	<?php if ( $image_src ) : ?>
            		<img src="<?php echo esc_url( $image_src ); ?>" />
                <?php endif; ?>
            </div>
            <p>
            	<input type="button" id="acadp-categories-upload-image" class="button button-secondary" value="<?php esc_attr_e( 'Add Image', 'advanced-classifieds-and-directory-pro' ); ?>" <?php if ( $image_src ) echo 'style="display: none;"'; ?>/>
            	<input type="button" id="acadp-categories-remove-image" class="button button-secondary" value="<?php esc_attr_e( 'Remove Image', 'advanced-classifieds-and-directory-pro' ); ?>" <?php if ( ! $image_src ) echo 'style="display: none;"'; ?>/>
        	</p>
        </td>
    </tr>
<?php endif;