<?php

/**
 * CSV Export Form.
 *
 * @link    https://pluginsware.com
 * @since   1.7.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=acadp_import_export&tab=export' ) ); ?>">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label>
						<?php esc_html_e( 'Items offset', 'advanced-classifieds-and-directory-pro' ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="offset" value="0" />
					<p class="description">
						<?php esc_html_e( 'Enter the offset of items to start with. Enter 0 to start from the beginning.', 'advanced-classifieds-and-directory-pro' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label>
						<?php esc_html_e( 'Items number', 'advanced-classifieds-and-directory-pro' ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="limit" value="1000" />
					<p class="description">
						<?php esc_html_e( 'Enter the number of items to export. Reduce the number if you get a timeout message.', 'advanced-classifieds-and-directory-pro' ); ?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>

	<?php wp_nonce_field( 'acadp_do_export', 'acadp_export_nonce' ); ?>

	<p id="acadp-export-actions" class="submit">
		<input type="submit" name="action" id="acadp-export-listings-button" class="button button-primary" value="<?php esc_attr_e( 'Export Listings', 'advanced-classifieds-and-directory-pro' ); ?>" />
		<input type="submit" name="action" id="acadp-export-images-button" class="button button-primary" value="<?php esc_attr_e( 'Download Images', 'advanced-classifieds-and-directory-pro' ); ?>" />
		<span id="acadp-export-status"></span>
	</p>
</form>
