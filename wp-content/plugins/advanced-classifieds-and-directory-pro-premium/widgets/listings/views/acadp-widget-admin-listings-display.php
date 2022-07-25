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

<?php if ( $instance['has_location'] ) : ?>
    <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'location' ) ); ?>"><?php esc_html_e( 'Select Location', 'advanced-classifieds-and-directory-pro' ); ?></label> 
        <?php
        wp_dropdown_categories(array(
            'show_option_none'  => '-- ' . esc_html__( 'Select Location', 'advanced-classifieds-and-directory-pro' ) . ' --',
            'option_none_value' => (int) $instance['base_location'],
            'child_of'          => (int) $instance['base_location'],
            'taxonomy'          => 'acadp_locations',
            'name' 			    => esc_attr( $this->get_field_name( 'location' ) ),
            'class'             => 'widefat',
            'orderby'           => 'name',
            'selected'          => (int) $instance['location'],
            'hierarchical'      => true,
            'depth'             => 10,
            'show_count'        => false,
            'hide_empty'        => false,
        ));
        ?>
    </p>
<?php endif; ?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Select category', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<?php
    wp_dropdown_categories(array(
        'show_option_none'  => '-- ' . esc_html__( 'Select category', 'advanced-classifieds-and-directory-pro' ) . ' --',
        'option_none_value' => 0,
        'taxonomy'          => 'acadp_categories',
        'name' 			    => esc_attr( $this->get_field_name( 'category' ) ),
        'class'             => 'widefat',
        'orderby'           => 'name',
        'selected'          => (int) $instance['category'],
        'hierarchical'      => true,
        'depth'             => 10,
        'show_count'        => false,
        'hide_empty'        => false,
    ));
	?>
</p>

<?php if ( $instance['has_featured'] ) : ?>
    <p>
        <input type="checkbox" value="1" <?php checked( $instance['featured'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'featured' ) ); ?>" />
        <label for="<?php echo esc_attr( $this->get_field_id( 'featured' ) ); ?>"><?php esc_html_e( 'Featured Only', 'advanced-classifieds-and-directory-pro' ); ?></label>
    </p>
<?php endif; ?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order By', 'advanced-classifieds-and-directory-pro' ); ?></label>
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>"> 
		<?php
        $options = array(
            'title' => esc_html__( 'Title', 'advanced-classifieds-and-directory-pro' ),
            'date'  => esc_html__( 'Date posted', 'advanced-classifieds-and-directory-pro' ),
            'price' => esc_html__( 'Price', 'advanced-classifieds-and-directory-pro' ),
            'views' => esc_html__( 'Views count', 'advanced-classifieds-and-directory-pro' ),
            'rand'  => esc_html__( 'Random', 'advanced-classifieds-and-directory-pro' )
        );
    
        foreach ( $options as $key => $value ) {
            printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['orderby'] ), $value );
        }
		?>
    </select>
</p>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_html_e( 'Order', 'advanced-classifieds-and-directory-pro' ); ?></label>
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>"> 
		<?php
        $options = array(
            'asc'  => esc_html__( 'ASC', 'advanced-classifieds-and-directory-pro' ),
            'desc' => esc_html__( 'DESC', 'advanced-classifieds-and-directory-pro' )
        );
    
        foreach ( $options as $key => $value ) {
            printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['order'] ), $value );
        }
		?>
    </select>
</p>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Limit', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['limit'] ); ?>">
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['related_listings'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'related_listings' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'related_listings' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'related_listings' ) ); ?>"><?php esc_html_e( 'Related Listings', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<div class="acadp-widget-section-header widget-title">
	<?php esc_html_e( 'Display Options', 'advanced-classifieds-and-directory-pro' ); ?>
</div>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'view' ) ); ?>"><?php esc_html_e( 'View', 'advanced-classifieds-and-directory-pro' ); ?></label>
    <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'view' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'view' ) ); ?>"> 
		<?php
        $options = array(
            'standard' => esc_html__( 'Standard', 'advanced-classifieds-and-directory-pro' ),
            'map'      => esc_html__( 'Map', 'advanced-classifieds-and-directory-pro' )
        );
    
        foreach ( $options as $key => $value ) {
            printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['view'] ), $value );
        }
		?>
    </select>
</p>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"><?php esc_html_e( 'Number of columns', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['columns'] ); ?>">
</p>

<?php if ( $instance['has_images'] ) : ?>
    <p>
        <input type="checkbox" value="1" <?php checked( $instance['show_image'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_image' ) ); ?>" />
        <label for="<?php echo esc_attr( $this->get_field_id( 'show_image' ) ); ?>"><?php esc_html_e( 'Show Image', 'advanced-classifieds-and-directory-pro' ); ?></label>
    </p>
    
    <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'image_position' ) ); ?>"><?php esc_html_e( 'Image Position', 'advanced-classifieds-and-directory-pro' ); ?></label>
        <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'image_position' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_position' ) ); ?>"> 
            <?php
            $options = array(
                'top'  => esc_html__( 'Top', 'advanced-classifieds-and-directory-pro' ),
                'left' => esc_html__( 'Left', 'advanced-classifieds-and-directory-pro' )
            );
        
            foreach ( $options as $key => $value ) {
                printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['image_position'] ), $value );
            }
            ?>
        </select>
    </p>
<?php endif; ?>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_description'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_description' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_description' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_description' ) ); ?>"><?php esc_html_e( 'Show Description', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_category'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_category' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_category' ) ); ?>"><?php esc_html_e( 'Show Category', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<?php if ( $instance['has_location'] ) : ?>
    <p>
        <input type="checkbox" value="1" <?php checked( $instance['show_location'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_location' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_location' ) ); ?>" />
        <label for="<?php echo esc_attr( $this->get_field_id( 'show_location' ) ); ?>"><?php esc_html_e( 'Show Location', 'advanced-classifieds-and-directory-pro' ); ?></label>
    </p>
<?php endif; ?>

<?php if ( $instance['has_price'] ) : ?>
    <p>
        <input type="checkbox" value="1" <?php checked( $instance['show_price'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_price' ) ); ?>" />
        <label for="<?php echo esc_attr( $this->get_field_id( 'show_price' ) ); ?>"><?php esc_html_e( 'Show Price', 'advanced-classifieds-and-directory-pro' ); ?></label>
    </p>
<?php endif; ?>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_date'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( 'Show Date', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_user'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_user' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_user' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_user' ) ); ?>"><?php esc_html_e( 'Show User', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_views'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_views' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_views' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_views' ) ); ?>"><?php esc_html_e( 'Show Views', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_custom_fields'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_custom_fields' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_custom_fields' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_custom_fields' ) ); ?>"><?php esc_html_e( 'Show Custom Fields', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>