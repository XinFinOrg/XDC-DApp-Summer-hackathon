<?php

/**
 * This template displays the administration form of the widget.
 *
 * @link    https://pluginsware.com
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'location' ); ?>"><?php _e( 'Filter by Location', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<?php
    	wp_dropdown_categories( array(
        	'show_option_none'  => '-- ' . __( 'Select location', 'advanced-classifieds-and-directory-pro' ) . ' --',
			'option_none_value' => $instance['base_location'],
			'child_of'          => $instance['base_location'],
            'taxonomy'          => 'acadp_locations',
            'name' 			    => $this->get_field_name( 'location' ),
			'class'             => 'widefat',
            'orderby'           => 'name',
			'selected'          => (int) $instance['location'],
            'hierarchical'      => true,
            'depth'             => 10,
            'show_count'        => false,
            'hide_empty'        => false,
        ) );
	?>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Filter by Category', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<?php
    	wp_dropdown_categories( array(
        	'show_option_none'  => '-- ' . __( 'Select category', 'advanced-classifieds-and-directory-pro' ) . ' --',
			'option_none_value' => 0,
            'taxonomy'          => 'acadp_categories',
            'name' 			    => $this->get_field_name( 'category' ),
			'class'             => 'widefat',
            'orderby'           => 'name',
			'selected'          => (int) $instance['category'],
            'hierarchical'      => true,
            'depth'             => 10,
            'show_count'        => false,
            'hide_empty'        => false,
        ) );
	?>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['follow_locations'] ); ?> id="<?php echo $this->get_field_id( 'follow_locations' ); ?>" name="<?php echo $this->get_field_name( 'follow_locations' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'follow_locations' ); ?>"><?php _e( 'Follow Locations', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['follow_categories'] ); ?> id="<?php echo $this->get_field_id( 'follow_categories' ); ?>" name="<?php echo $this->get_field_name( 'follow_categories' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'follow_categories' ); ?>"><?php _e( 'Follow Categories', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['featured'] ); ?> id="<?php echo $this->get_field_id( 'featured' ); ?>" name="<?php echo $this->get_field_name( 'featured' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'featured' ); ?>"><?php _e( 'Featured Only', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit', 'acadp-slider' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $instance['limit'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By', 'advanced-classifieds-and-directory-pro' ); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>"> 
		<?php
			$options = array(
				'title' => __( 'Title', 'advanced-classifieds-and-directory-pro' ),
				'date'  => __( 'Date posted', 'advanced-classifieds-and-directory-pro' ),
				'price' => __( 'Price', 'advanced-classifieds-and-directory-pro' ),
				'views' => __( 'Views count', 'advanced-classifieds-and-directory-pro' )
			);
		
			foreach ( $options as $key => $value ) {
				printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['orderby'] ), $value );
			}
		?>
    </select>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order', 'advanced-classifieds-and-directory-pro' ); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>"> 
		<?php
			$options = array(
				'asc'  => __( 'ASC', 'advanced-classifieds-and-directory-pro' ),
				'desc' => __( 'DESC', 'advanced-classifieds-and-directory-pro' )
			);
		
			foreach ( $options as $key => $value ) {
				printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['order'] ), $value );
			}
		?>
    </select>
</p>

<div class="acadp-widget-section-header widget-title">
	<?php _e( 'Slider Options', 'advanced-classifieds-and-directory-pro' ); ?>
</div>

<p>
	<label for="<?php echo $this->get_field_id( 'images_ratio' ); ?>"><?php _e( 'Images Scaling Ratio', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'images_ratio' ); ?>" name="<?php echo $this->get_field_name( 'images_ratio' ); ?>" type="text" value="<?php echo esc_attr( $instance['images_ratio'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'images_scale_type' ); ?>"><?php _e( 'Images Scaling Method', 'advanced-classifieds-and-directory-pro' ); ?></label>
    <select class="widefat" id="<?php echo $this->get_field_id( 'images_scale_type' ); ?>" name="<?php echo $this->get_field_name( 'images_scale_type' ); ?>"> 
		<?php
			$options = array(
				'uniform' => __( 'Uniform', 'advanced-classifieds-and-directory-pro' ),
				'fill'    => __( 'Fill', 'advanced-classifieds-and-directory-pro' )
			);
		
			foreach ( $options as $key => $value ) {
				printf( '<option value="%s"%s>%s</option>', $key, selected( $key, $instance['images_scale_type'] ), $value );
			}
		?>
    </select>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_content'] ); ?> id="<?php echo $this->get_field_id( 'show_content' ); ?>" name="<?php echo $this->get_field_name( 'show_content' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'show_content' ); ?>"><?php _e( 'Show Content', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['dots'] ); ?> id="<?php echo $this->get_field_id( 'dots' ); ?>" name="<?php echo $this->get_field_name( 'dots' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'dots' ); ?>"><?php _e( 'Dots', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
    <label for="<?php echo $this->get_field_id( 'dots_bg_color' ); ?>" style="display:block;"><?php _e( 'Dots BG Color', 'advanced-classifieds-and-directory-pro' ); ?></label> 
    <input class="widefat acadp-slider-color-picker" id="<?php echo $this->get_field_id( 'dots_bg_color' ); ?>" name="<?php echo $this->get_field_name( 'dots_bg_color' ); ?>" type="text" value="<?php echo esc_attr( $instance['dots_bg_color'] ); ?>" />
</p>

<p>
    <label for="<?php echo $this->get_field_id( 'dots_active_bg_color' ); ?>" style="display:block;"><?php _e( 'Dots Active BG Color', 'advanced-classifieds-and-directory-pro' ); ?></label> 
    <input class="widefat acadp-slider-color-picker" id="<?php echo $this->get_field_id( 'dots_active_bg_color' ); ?>" name="<?php echo $this->get_field_name( 'dots_active_bg_color' ); ?>" type="text" value="<?php echo esc_attr( $instance['dots_active_bg_color'] ); ?>" />
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['arrows'] ); ?> id="<?php echo $this->get_field_id( 'arrows' ); ?>" name="<?php echo $this->get_field_name( 'arrows' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'arrows' ); ?>"><?php _e( 'Arrows', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
    <label for="<?php echo $this->get_field_id( 'arrows_bg_color' ); ?>" style="display:block;"><?php _e( 'Arrows BG Color', 'advanced-classifieds-and-directory-pro' ); ?></label> 
    <input class="widefat acadp-slider-color-picker" id="<?php echo $this->get_field_id( 'arrows_bg_color' ); ?>" name="<?php echo $this->get_field_name( 'arrows_bg_color' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_bg_color'] ); ?>" />
</p>

<p>
    <label for="<?php echo $this->get_field_id( 'arrows_icon_color' ); ?>" style="display:block;"><?php _e( 'Arrows Icon Color', 'advanced-classifieds-and-directory-pro' ); ?></label> 
    <input class="widefat acadp-slider-color-picker" id="<?php echo $this->get_field_id( 'arrows_icon_color' ); ?>" name="<?php echo $this->get_field_name( 'arrows_icon_color' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_icon_color'] ); ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'arrows_top_offset' ); ?>"><?php _e( 'Arrows Top Offset', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'arrows_top_offset' ); ?>" name="<?php echo $this->get_field_name( 'arrows_top_offset' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_top_offset'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'arrows_left_offset' ); ?>"><?php _e( 'Arrows Left Offset', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'arrows_left_offset' ); ?>" name="<?php echo $this->get_field_name( 'arrows_left_offset' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_left_offset'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'arrows_right_offset' ); ?>"><?php _e( 'Arrows Right Offset', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'arrows_right_offset' ); ?>" name="<?php echo $this->get_field_name( 'arrows_right_offset' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_right_offset'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'arrows_padding' ); ?>"><?php _e( 'Arrows Padding', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'arrows_padding' ); ?>" name="<?php echo $this->get_field_name( 'arrows_padding' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_padding'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'arrows_icon_size' ); ?>"><?php _e( 'Arrows Icon Size', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'arrows_icon_size' ); ?>" name="<?php echo $this->get_field_name( 'arrows_icon_size' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_icon_size'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'arrows_border_radius' ); ?>"><?php _e( 'Arrows Border Radius', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'arrows_border_radius' ); ?>" name="<?php echo $this->get_field_name( 'arrows_border_radius' ); ?>" type="text" value="<?php echo esc_attr( $instance['arrows_border_radius'] ); ?>">
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['autoplay'] ); ?> id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'autoplay' ); ?>"><?php _e( 'Autoplay', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'autoplay_interval' ); ?>"><?php _e( 'Autoplay Interval', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'autoplay_interval' ); ?>" name="<?php echo $this->get_field_name( 'autoplay_interval' ); ?>" type="text" value="<?php echo esc_attr( $instance['autoplay_interval'] ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'speed' ); ?>"><?php _e( 'Speed', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'speed' ); ?>" name="<?php echo $this->get_field_name( 'speed' ); ?>" type="text" value="<?php echo esc_attr( $instance['speed'] ); ?>">
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['fade'] ); ?> id="<?php echo $this->get_field_id( 'fade' ); ?>" name="<?php echo $this->get_field_name( 'fade' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'fade' ); ?>"><?php _e( 'Fade', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>