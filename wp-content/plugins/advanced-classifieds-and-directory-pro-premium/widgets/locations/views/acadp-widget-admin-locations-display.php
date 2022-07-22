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
	<label for="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>"><?php esc_html_e( 'Select Template', 'advanced-classifieds-and-directory-pro' ); ?></label> 
	<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'template' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'template' ) ); ?>">
    	<option value="list" <?php selected( $instance['template'], 'list' ); ?>><?php esc_html_e( 'List', 'advanced-classifieds-and-directory-pro' ); ?></option>
        <option value="dropdown" <?php selected( $instance['template'], 'dropdown' ); ?>><?php esc_html_e( 'Dropdown', 'advanced-classifieds-and-directory-pro' ); ?></option>
    </select>
</p>

<p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'base_location' ) ); ?>"><?php esc_html_e( 'Select Parent', 'advanced-classifieds-and-directory-pro' ); ?></label> 
    <?php
    wp_dropdown_categories(array(
        'show_option_none'  => '-- ' . esc_html__( 'Select Parent', 'advanced-classifieds-and-directory-pro' ) . ' --',
        'option_none_value' => (int) $defaults['base_location'],
        'child_of'          => (int) $defaults['base_location'],
        'taxonomy'          => 'acadp_locations',
        'name' 			    => esc_attr( $this->get_field_name( 'base_location' ) ),
        'class'             => 'widefat',
        'orderby'           => 'name',
        'selected'          => (int) $instance['base_location'],
        'hierarchical'      => true,
        'depth'             => 10,
        'show_count'        => false,
        'hide_empty'        => false,
    ) );
    ?>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['imm_child_only'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'imm_child_only' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'imm_child_only' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'imm_child_only' ) ); ?>"><?php esc_html_e( 'Show only the immediate children of the selected location. Displays all the top level locations if no parent is selected.', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['hide_empty'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>"><?php esc_html_e( 'Hide Empty Locations', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked( $instance['show_count'] ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" />
	<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Show Listing Counts', 'advanced-classifieds-and-directory-pro' ); ?></label>
</p>
