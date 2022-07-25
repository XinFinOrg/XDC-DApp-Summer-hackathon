<?php

/**
 * CSV Import Form: Step 2.
 *
 * @link    https://pluginsware.com
 * @since   1.7.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<br />

<h3><?php esc_html_e( 'Map CSV Columns', 'advanced-classifieds-and-directory-pro' ); ?></h3>

<table id="acadp-import-step2" class="form-table">
	<tbody>
		<tr> 
			<th scope="row">
				<label>
					<?php esc_html_e( 'Column name', 'advanced-classifieds-and-directory-pro' ); ?>
				</label>

				<hr />
			</th>
			<td>
				<label>
					<strong><?php esc_html_e( 'Map to field', 'advanced-classifieds-and-directory-pro' ); ?></strong>
				</label>

				<hr />
			</td>
		</tr>

		<?php foreach ( $attributes['csv_headers'] as $header ) : ?>
			<tr> 
				<th scope="row">
					<label>
						<?php echo esc_html( $header ); ?>
					</label>
				</th>
				<td>
					<select name="collated_fields[]">
						<option value="">- <?php esc_html_e( 'Select listings field', 'advanced-classifieds-and-directory-pro' ); ?> -</option>
						<?php
						foreach ( $attributes['collation_fields'] as $value => $label ) {
							if ( 'post_title' == $value || 'acadp_categories' == $value ) {
								$label .= '*';
							}
							
							printf(
								'<option value="%s">%s</option>',
								esc_attr( $value ),
								esc_html( $label )
							);
						}
						?>
					</select>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
