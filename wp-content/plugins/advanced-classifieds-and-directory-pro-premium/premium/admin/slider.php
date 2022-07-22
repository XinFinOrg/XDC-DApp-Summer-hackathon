<?php

/**
 * Listings Slider.
 *
 * @link    https://pluginsware.com
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Admin_Slider class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Admin_Slider {

	/**
	 * Register "Slider" shortcode fields.
	 *
	 * @since  1.7.3
	 * @param  array $fields Core shortcode fields array.
	 * @return array $fields Updated fields array.
	 */
	public function register_shortcode_fields( $fields ) {
		$general_settings  = get_option( 'acadp_general_settings' );		
		$listings_settings = get_option( 'acadp_listings_settings' ); 

		$fields['carousel_slider'] = array(
			'title'    => __( 'Listings Slider', 'advanced-classifieds-and-directory-pro' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'location',
							'label'       => __( 'Select location', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'locations',
							'options'     => array(),
							'value'       => max( 0, $general_settings['base_location'] )
						),
						array(
							'name'        => 'follow_locations',
							'label'       => __( 'Use current page location when in the single location page', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'category',
							'label'       => __( 'Select category', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'categories',
							'options'     => array(),
							'value'       => 0
						),						
						array(
							'name'        => 'follow_categories',
							'label'       => __( 'Use current page category when in the single category page', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'filterby',
							'label'       => __( 'Filter by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								''         => __( 'None', 'advanced-classifieds-and-directory-pro' ),
								'featured' => __( 'Featured', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => ''
						),
						array(
							'name'        => 'limit',
							'label'       => __( 'Limit', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 0,
							'max'         => 500,
							'step'        => 1,	
							'value'       => 50
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'title' => __( 'Title', 'advanced-classifieds-and-directory-pro' ),
								'date'  => __( 'Date posted', 'advanced-classifieds-and-directory-pro' ),
								'price' => __( 'Price', 'advanced-classifieds-and-directory-pro' ),
								'views' => __( 'Views count', 'advanced-classifieds-and-directory-pro' ),
								'rand'  => __( 'Random sort', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $listings_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
								'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $listings_settings['order']
						)						
					)					
				),
				'display' => array(
					'title'  => __( 'Display options', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'slides_to_show',
							'label'       => __( 'Slides to show', 'advanced-classifieds-and-directory-pro' ),
							'description' => __( '# of slides to show', 'advanced-classifieds-and-directory-pro' ),
							'type'        => 'number',
							'min'         => 1,
							'max'         => 12,
							'step'        => 1,
							'value'       => 4
						),					
						array(
							'name'        => 'images_size',
							'label'       => __( 'Image size', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'thumbnail' => __( 'Thumbnail', 'advanced-classifieds-and-directory-pro' ),
								'medium'    => __( 'Medium', 'advanced-classifieds-and-directory-pro' ),
								'large'     => __( 'Large', 'advanced-classifieds-and-directory-pro' ),
								'full'      => __( 'Full', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => 'medium'
						),	
						array(
							'name'        => 'images_ratio',
							'label'       => __( 'Image scaling ratio [%]', 'advanced-classifieds-and-directory-pro' ),
							'description' => __( 'A decimal value. Accepted values are 0 to 1.', 'advanced-classifieds-and-directory-pro' ),
							'type'        => 'number',
							'value'       => 0.5625
						),
						array(
							'name'        => 'images_scale_type',
							'label'       => __( 'Image stretch type', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'uniform' => __( 'Uniform', 'advanced-classifieds-and-directory-pro' ),
								'fill'    => __( 'Fill', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => 'uniform'
						),				
						array(
							'name'        => 'arrows',
							'label'       => __( 'Show arrows', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'arrows_bg_color',
							'label'       => __( 'Arrows background color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#008BCF'
						),
						array(
							'name'        => 'arrows_icon_color',
							'label'       => __( 'Arrows icon color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#FFFFFF'
						),
						array(
							'name'        => 'arrows_top_offset',
							'label'       => __( 'Arrows top offset [%]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 35
						), 
						array(
							'name'        => 'arrows_left_offset',
							'label'       => __( 'Arrows left offset [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => -30
						), 
						array(
							'name'        => 'arrows_right_offset',
							'label'       => __( 'Arrows right offset [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => -30
						),
						array(
							'name'        => 'arrows_padding',
							'label'       => __( 'Arrows padding [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 5
						), 
						array(
							'name'        => 'arrows_icon_size',
							'label'       => __( 'Arrows icon size [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 14
						),
						array(
							'name'        => 'arrows_border_radius',
							'label'       => __( 'Arrows border radius [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 12
						), 
						array(
							'name'        => 'dots',
							'label'       => __( 'Show dots', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						), 
						array(
							'name'        => 'dots_bg_color',
							'label'       => __( 'Dots background color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#DDDDDD'
						),
						array(
							'name'        => 'dots_active_bg_color',
							'label'       => __( 'Dots active background color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#008BCF'
						),
						array(
							'name'        => 'autoplay',
							'label'       => __( 'Autoplay', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						), 
						array(
							'name'        => 'autoplay_interval',
							'label'       => __( 'Autoplay interval [milliseconds]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 5000
						), 
						array(
							'name'        => 'speed',
							'label'       => __( 'Animation Speed', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 500
						)
					)
				)
			)
		);

		$fields['banner_rotator'] = array(
			'title'    => __( 'Listings Banner', 'advanced-classifieds-and-directory-pro' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'location',
							'label'       => __( 'Select location', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'locations',
							'options'     => array(),
							'value'       => max( 0, $general_settings['base_location'] )
						),
						array(
							'name'        => 'follow_locations',
							'label'       => __( 'Use current page location when in the single location page', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'category',
							'label'       => __( 'Select category', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'categories',
							'options'     => array(),
							'value'       => 0
						),						
						array(
							'name'        => 'follow_categories',
							'label'       => __( 'Use current page category when in the single category page', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'filterby',
							'label'       => __( 'Filter by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								''         => __( 'None', 'advanced-classifieds-and-directory-pro' ),
								'featured' => __( 'Featured', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => ''
						),
						array(
							'name'        => 'limit',
							'label'       => __( 'Limit', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 0,
							'max'         => 500,
							'step'        => 1,	
							'value'       => 50
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order by', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'title' => __( 'Title', 'advanced-classifieds-and-directory-pro' ),
								'date'  => __( 'Date posted', 'advanced-classifieds-and-directory-pro' ),
								'price' => __( 'Price', 'advanced-classifieds-and-directory-pro' ),
								'views' => __( 'Views count', 'advanced-classifieds-and-directory-pro' ),
								'rand'  => __( 'Random sort', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $listings_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'Ascending', 'advanced-classifieds-and-directory-pro' ),
								'desc' => __( 'Descending', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => $listings_settings['order']
						)						
					)
				),
				'display' => array(
					'title'  => __( 'Display options', 'advanced-classifieds-and-directory-pro' ),
					'fields' => array(
						array(
							'name'        => 'images_ratio',
							'label'       => __( 'Image scaling ratio [%]', 'advanced-classifieds-and-directory-pro' ),
							'description' => __( 'A decimal value. Accepted values are 0 to 1.', 'advanced-classifieds-and-directory-pro' ),
							'type'        => 'number',
							'value'       => 0.5625
						),
						array(
							'name'        => 'images_scale_type',
							'label'       => __( 'Image stretch type', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'uniform' => __( 'Uniform', 'advanced-classifieds-and-directory-pro' ),
								'fill'    => __( 'Fill', 'advanced-classifieds-and-directory-pro' )
							),
							'value'       => 'fill'
						),	
						array(
							'name'        => 'show_content',
							'label'       => __( 'Show excerpt', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),			
						array(
							'name'        => 'arrows',
							'label'       => __( 'Show arrows', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'arrows_bg_color',
							'label'       => __( 'Arrows background color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#008BCF'
						),
						array(
							'name'        => 'arrows_icon_color',
							'label'       => __( 'Arrows icon color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#FFFFFF'
						),
						array(
							'name'        => 'arrows_top_offset',
							'label'       => __( 'Arrows top offset [%]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 50
						), 
						array(
							'name'        => 'arrows_left_offset',
							'label'       => __( 'Arrows left offset [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 30
						), 
						array(
							'name'        => 'arrows_right_offset',
							'label'       => __( 'Arrows right offset [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 30
						),
						array(
							'name'        => 'arrows_padding',
							'label'       => __( 'Arrows padding [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 10
						), 
						array(
							'name'        => 'arrows_icon_size',
							'label'       => __( 'Arrows icon size [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 28
						),
						array(
							'name'        => 'arrows_border_radius',
							'label'       => __( 'Arrows border radius [px]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 24
						), 
						array(
							'name'        => 'dots',
							'label'       => __( 'Show dots', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						), 
						array(
							'name'        => 'dots_bg_color',
							'label'       => __( 'Dots background color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#DDDDDD'
						),
						array(
							'name'        => 'dots_active_bg_color',
							'label'       => __( 'Dots active background color', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'color',
							'value'       => '#008BCF'
						),
						array(
							'name'        => 'autoplay',
							'label'       => __( 'Autoplay', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						), 
						array(
							'name'        => 'autoplay_interval',
							'label'       => __( 'Autoplay interval [milliseconds]', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 5000
						), 
						array(
							'name'        => 'speed',
							'label'       => __( 'Animation Speed', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'number',
							'value'       => 500
						),
						array(
							'name'        => 'fade',
							'label'       => __( 'Enable Fade', 'advanced-classifieds-and-directory-pro' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						)
					)
				)
			)
		);

		return $fields;
	}

}
