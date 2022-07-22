<?php

/**
 * This template displays the administration form of the widget.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
</p>
     
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>"><?php esc_html_e( 'Style', 'advanced-classifieds-and-directory-pro' ); ?></label>
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>"> 
		<?php
		$options = array(
			'vertical' => esc_html__( 'Vertical', 'advanced-classifieds-and-directory-pro' ),
			'inline'   => esc_html__( 'Inline', 'advanced-classifieds-and-directory-pro' )
		);
	
		foreach ( $options as $key => $value ) {
			printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['style'] ), $value );
		}
		?>
    </select>
</p>
   
<p>
	<input type="checkbox" value="1" <?php checked( $instance['search_by_category'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'search_by_category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_by_category' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'search_by_category' ) ); ?>"><?php esc_html_e( 'Search by Category', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<?php if ( $has_location ) : ?>
	<p>
		<input type="checkbox" value="1" <?php checked( $instance['search_by_location'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'search_by_location' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_by_location' ) ); ?>" />
		<label for="<?php echo esc_attr( $this->get_field_id( 'search_by_location' ) ); ?>"><?php esc_html_e( 'Search by Location', 'advanced-classifieds-and-directory-pro' ); ?></label>
	</p>
<?php endif; ?>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['search_by_custom_fields'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'search_by_custom_fields' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_by_custom_fields' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'search_by_custom_fields' ) ); ?>"><?php esc_html_e( 'Search by Custom Fields', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<?php if ( $has_price ) : ?>
	<p>
		<input type="checkbox" value="1" <?php checked( $instance['search_by_price'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'search_by_price' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'search_by_price' ) ); ?>" />
		<label for="<?php echo esc_attr( $this->get_field_id( 'search_by_price' ) ); ?>"><?php esc_html_e( 'Search by Price', 'advanced-classifieds-and-directory-pro' ); ?></label>
	</p>
<?php endif;