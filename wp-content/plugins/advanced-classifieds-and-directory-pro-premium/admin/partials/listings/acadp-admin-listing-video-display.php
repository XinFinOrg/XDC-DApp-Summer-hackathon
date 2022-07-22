<?php

/**
 * Display the "Video" meta box.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<?php if ( $general_settings['has_video'] ) : ?>
	<table id="acadp-video" class="acadp-input acadp-video acadp-no-border widefat">
		<tr>
    		<td class="label">
        		<label><?php esc_html_e( 'Your Video URL', 'advanced-classifieds-and-directory-pro' ); ?></label>
                <p class="description"><?php esc_html_e( 'Only YouTube &  Vimeo URLs', 'advanced-classifieds-and-directory-pro' ); ?></p>
      		</td>
      		<td>
        		<div class="acadp-input-wrap">
          			<input type="text" class="text" name="video" value="<?php if ( isset( $post_meta['video'] ) ) echo esc_attr( $post_meta['video'][0] ); ?>" />
        		</div>
      		</td>
    	</tr>
	</table>
<?php endif;