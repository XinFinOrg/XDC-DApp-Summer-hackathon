<?php

/**
 * ACADP Search Widget.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Widget_Search Class.
 *
 * @since 1.0.0
 */
class ACADP_Widget_Search extends WP_Widget {

	/**
     * Unique identifier for the widget.
     *
     * @since  1.0.0
	 * @access protected
     * @var    string
     */
    protected $widget_slug;
	
	/**
	 * Get things going.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {	
		$this->widget_slug = ACADP_PLUGIN_NAME . '-widget-search';
	
		parent::__construct(
			$this->widget_slug,
			__( 'ACADP Search', 'advanced-classifieds-and-directory-pro' ),
			array(
				'classname'   => $this->widget_slug . '-class',
				'description' => __( '"Advanced Classifieds & Directory Pro" Search Form.', 'advanced-classifieds-and-directory-pro' )
			)
		);		
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @since 1.0.0
	 * @param array	$args	  The array of form elements.
	 * @param array $instance The current instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$this->enqueue_styles_scripts();

		$general_settings    = get_option( 'acadp_general_settings' );
		$locations_settings  = get_option( 'acadp_locations_settings' );
		$categories_settings = get_option( 'acadp_categories_settings' );
		$page_settings       = get_option( 'acadp_page_settings' );
		
		$id = wp_rand();
		
		$style = 'vertical';
		if ( ! empty( $instance['style'] ) && 'inline' == $instance['style'] ) {
			$style = 'inline';
		}
		
		$has_location = empty( $general_settings['has_location'] ) ? 0 : 1;
		$has_price    = empty( $general_settings['has_price'] )    ? 0 : 1;
		
		$can_search_by_category      = ! empty( $instance['search_by_category'] )      ? 1             : 0;
		$can_search_by_location      = ! empty( $instance['search_by_location'] )      ? $has_location : 0;
		$can_search_by_custom_fields = ! empty( $instance['search_by_custom_fields'] ) ? 1             : 0;
		$can_search_by_price         = ! empty( $instance['search_by_price'] )         ? $has_price    : 0;
		
		$span_top    = 12 / ( 1 + $can_search_by_category  + $can_search_by_location );
		$span_bottom = 12 / ( $can_search_by_price + 1 );
		
		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		include( acadp_get_template( "search/acadp-public-search-form-$style-display.php" ) );
		
		echo $args['after_widget'];
	}
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @since 1.0.0
	 * @param array $new_instance The new instance of values to be generated via the update.
	 * @param array $old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']                   = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['style']                   = ! empty( $new_instance['style'] ) ? strip_tags( $new_instance['style'] ) : 'vertical';
		$instance['search_by_category']      = isset( $new_instance['search_by_category'] ) ? (int) $new_instance['search_by_category'] : 0;
		$instance['search_by_location']      = isset( $new_instance['search_by_location'] ) ? (int) $new_instance['search_by_location'] : 0;
		$instance['search_by_custom_fields'] = isset( $new_instance['search_by_custom_fields'] ) ? (int) $new_instance['search_by_custom_fields'] : 0;
		$instance['search_by_price']         = isset( $new_instance['search_by_price'] ) ? (int) $new_instance['search_by_price'] : 0;
		
		return $instance;
	}
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @since 1.0.0
	 * @param array $instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$general_settings = get_option( 'acadp_general_settings' );
		
		$has_location = empty( $general_settings['has_location'] ) ? 0 : 1;
		$has_price    = empty( $general_settings['has_price'] )    ? 0 : 1;
		
 		// Define the array of defaults
		$defaults = array(
			'title'                   =>  __( 'Search Listings', 'advanced-classifieds-and-directory-pro' ),
			'style'                   => 'vertical',
			'search_by_category'      => 1,
			'search_by_location'      => $has_location,
			'search_by_custom_fields' => 1,
			'search_by_price'         => $has_price
		);

		// Parse incoming $instance into an array and merge it with $defaults
		$instance = wp_parse_args(
			(array) $instance,
			$defaults
		);

		// Display the admin form
		include( ACADP_PLUGIN_DIR . 'widgets/search/views/acadp-widget-admin-search-display.php' );
	}
	
	/**
	 * Enqueues widget-specific styles & scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles_scripts() {	
		wp_enqueue_style( ACADP_PLUGIN_NAME );
		wp_enqueue_script( ACADP_PLUGIN_NAME );			
	}
	
}
