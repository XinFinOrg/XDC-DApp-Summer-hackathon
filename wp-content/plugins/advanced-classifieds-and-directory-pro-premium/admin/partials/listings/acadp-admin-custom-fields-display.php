<?php

/**
 * Display "Custom Fields" under "Listing Details" meta box.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<?php if ( count( $posts ) ) : ?>
	<table class="acadp-input widefat">
		<tbody>
			<?php foreach ( $posts as $post ) : $field_meta = get_post_meta( $post->ID ); ?>
				<tr>
					<td class="label">
						<label>
							<?php echo esc_html( $post->post_title ); ?>
							<?php if ( 1 == $field_meta['required'][0] ) echo '<i>*</i>'; ?>
						</label>
						<?php if ( isset( $field_meta['instructions'] ) ) : ?>
							<p class="description"><?php echo esc_textarea( $field_meta['instructions'][0] ); ?></p>
						<?php endif; ?>
					</td>
					
					<td>
						<?php
						$value = $field_meta['default_value'][0];
						if ( isset( $post_meta[ $post->ID ] ) ) {
							$value = $post_meta[ $post->ID ][0];
						}
						
						switch ( $field_meta['type'][0] ) {
							case 'text' :	
								echo '<div class="acadp-input-wrap">';
								printf( '<input type="text" name="acadp_fields[%d]" class="text" placeholder="%s" value="%s"/>', $post->ID, esc_attr( $field_meta['placeholder'][0] ), esc_attr( $value ) );
								echo '</div>';
								break;
							case 'textarea' :
								printf( '<textarea name="acadp_fields[%d]" class="textarea" rows="%d" placeholder="%s">%s</textarea>', $post->ID, (int) $field_meta['rows'][0], esc_attr( $field_meta['placeholder'][0] ), esc_textarea( $value ) );
								break;
							case 'select' :
								$choices = $field_meta['choices'][0];
								$choices = explode( "\n", trim( $choices ) );
					
								printf( '<select name="acadp_fields[%d]" class="select">', $post->ID );
								if ( ! empty( $field_meta['allow_null'][0] ) ) {
									printf( '<option value="">%s</option>', '- ' . esc_html__( 'Select an Option', 'advanced-classifieds-and-directory-pro' ) . ' -' );
								}

								foreach ( $choices as $choice ) {
									if ( strpos( $choice, ':' ) !== false ) {
										$_choice = explode( ':', $choice );
										$_choice = array_map( 'trim', $_choice );
								
										$_value  = $_choice[0];
										$_label  = $_choice[1];
									} else {
										$_value  = trim( $choice );
										$_label  = $_value;
									}
							
									$_selected = '';
									if ( trim( $value ) == $_value ) {
										$_selected = ' selected="selected"';
									}
						
									printf( '<option value="%s"%s>%s</option>', esc_attr( $_value ), $_selected, esc_html( $_label ) );
								} 
								echo '</select>';
								break;
							case 'checkbox' :
								$choices = $field_meta['choices'][0];
								$choices = explode( "\n", trim( $choices ) );
					
								$values = explode( "\n", $value );
								$values = array_map( 'trim', $values );
					
								echo '<ul class="acadp-checkbox-list checkbox vertical">';
								foreach ( $choices as $choice ) {
									if ( strpos( $choice, ':' ) !== false ) {
										$_choice = explode( ':', $choice );
										$_choice = array_map( 'trim', $_choice );
								
										$_value  = $_choice[0];
										$_label  = $_choice[1];
									} else {
										$_value  = trim( $choice );
										$_label  = $_value;
									}
							
									$_checked = '';
									if ( in_array( $_value, $values ) ) {
										$_checked = ' checked="checked"';
									}
						
									printf( '<li><label><input type="hidden" name="acadp_fields[%s][]" value="" /><input type="checkbox" name="acadp_fields[%d][]" value="%s"%s>%s</label></li>', $post->ID, $post->ID, esc_attr( $_value ), $_checked, esc_html( $_label ) );
								}
								echo '</ul>';
								break;
							case 'radio' :
								$choices = $field_meta['choices'][0];
								$choices = explode( "\n", trim( $choices ) );
					
								echo '<ul class="acadp-radio-list radio vertical">';
								foreach ( $choices as $choice ) {
									if ( strpos( $choice, ':' ) !== false ) {
										$_choice = explode( ':', $choice );
										$_choice = array_map( 'trim', $_choice );
								
										$_value  = $_choice[0];
										$_label  = $_choice[1];
									} else {
										$_value  = trim( $choice );
										$_label  = $_value;
									}
						
									$_checked = '';
									if ( trim( $value ) == $_value ) {
										$_checked = ' checked="checked"';
									}
						
									printf( '<li><label><input type="radio" name="acadp_fields[%d]" value="%s"%s>%s</label></li>', $post->ID, esc_attr( $_value ), $_checked, esc_html( $_label ) );
								}
								echo '</ul>';
								break;
							case 'url'  :				
								echo '<div class="acadp-input-wrap">';
								printf( '<input type="text" name="acadp_fields[%d]" class="text" placeholder="%s" value="%s"/>', $post->ID, esc_attr( $field_meta['placeholder'][0] ), esc_url( $value ) );
								echo '</div>';
								break;
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif;