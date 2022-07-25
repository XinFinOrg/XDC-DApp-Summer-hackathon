<?php

/**Mel: 15/11/21
 * Display the "Files" meta box.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<?php if ( $general_settings['has_images'] ) : ?>
	<table id="acadp-files" class="acadp-files acadp-no-border widefat">
		<tbody>
			<?php
			if ( isset( $post_meta['files'] ) ) {
				$files = unserialize( $post_meta['files'][0] );
			
				for ( $i = 0; $i < count( $files ); $i++ ) {	
					$file_attributes = wp_get_attachment_url( $files[ $i ] );
					
					if ( isset( $file_attributes ) )  {				
						echo '<tr class="acadp-file-row">' . 
							'<td class="acadp-handle"><span class="dashicons dashicons-screenoptions"></span></td>' .         	
							'<td class="acadp-file">' .  
								'<input type="hidden" name="files[]" value="' . esc_attr( $files[ $i ] ) . '" />' . 
							'</td>' . 
							'<td>' .
								'<a href="' . esc_html( $file_attributes ) . '">' . esc_html( $file_attributes ) . '</a><br />' . 
								'<a href="post.php?post=' . (int) $files[ $i ] . '&action=edit" target="_blank">' . esc_html__( 'Edit', 'advanced-classifieds-and-directory-pro' ) . '</a> | ' . 
								'<a href="javascript:void(0)" class="acadp-delete-file" data-attachment_id="' . esc_attr( $files[ $i ] ) . '">' . 
									esc_html__( 'Delete Permanently', 'advanced-classifieds-and-directory-pro' ) .
								'</a>' . 
							'</td>' .              
						'</tr>';						
					} // endif			
				} // endfor		
			} // endif
			?>
		</tbody>
	</table>

	<p class="hide-if-no-js">
    	<a class="button" href="javascript:;" id="acadp-upload-file"><?php esc_html_e( 'Upload File', 'advanced-classifieds-and-directory-pro' ); ?></a>
	</p>
<?php endif;