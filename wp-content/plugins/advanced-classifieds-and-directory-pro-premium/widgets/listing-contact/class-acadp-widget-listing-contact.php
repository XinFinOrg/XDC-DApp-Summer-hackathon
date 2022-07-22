<?php

/**
 * ACADP Listing Contact Widget.
 *
 * @link    https://pluginsware.com
 * @since   1.5.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Widget_Listing_Contact Class.
 *
 * @since 1.5.0
 */
class ACADP_Widget_Listing_Contact extends WP_Widget {

	/**
     * Unique identifier for the widget.
     *
     * @since  1.5.0
	 * @access protected
     * @var    string
     */
    protected $widget_slug;
	
	/**
	 * Get things going.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {	
		$this->widget_slug = ACADP_PLUGIN_NAME . '-widget-listing-contact';
	
		parent::__construct(
			$this->widget_slug,
			__( 'ACADP Listing Contact', 'advanced-classifieds-and-directory-pro' ),
			array(
				'classname'   => $this->widget_slug.'-class',
				'description' => __( 'Contact us form to contact "Advanced Classifieds & Directory Pro" listing owners.', 'advanced-classifieds-and-directory-pro' )
			)
		);		
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @since 1.5.0
	 * @param array	$args	  The array of form elements.
	 * @param array $instance The current instance of the widget.
	 */
	public function widget( $args, $instance ) {		
		if ( is_singular('acadp_listings') ) {
			$general_settings      = get_option( 'acadp_general_settings' );
			$registration_settings = get_option( 'acadp_registration_settings' );
			
			$can_show_contact_form = empty( $general_settings['has_contact_form'] ) ? false : true;	
		
			if ( $can_show_contact_form ) {
				$this->enqueue_styles_scripts();

				$current_page_url = get_permalink();
				$login_url        = acadp_get_user_login_page_link( $current_page_url );
			
				echo $args['before_widget'];
		
				if ( ! empty( $instance['title'] ) ) {
					echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
				}
		
				include( acadp_get_template( 'acadp-widget-public-listing-contact-display.php', 'listing-contact' ) );
		
				echo $args['after_widget'];				
			}			
		}
	}
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @since 1.5.0
	 * @param array	$new_instance The new instance of values to be generated via the update.
	 * @param array $old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';		
		return $instance;
	}
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @since 1.5.0
	 * @param array $instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {		
 		// Define the array of defaults
		$defaults = array(
			'title' =>  __( 'Contact this listing owner', 'advanced-classifieds-and-directory-pro' ),
		);

		// Parse incoming $instance into an array and merge it with $defaults
		$instance = wp_parse_args(
			(array) $instance,
			$defaults
		);

		// Display the admin form
		include( ACADP_PLUGIN_DIR . 'widgets/listing-contact/views/acadp-widget-admin-listing-contact-display.php' );
	}
	
	/**
	 * Enqueues widget-specific styles & scripts.
	 *
	 * @since 1.5.8
	 */
	public function enqueue_styles_scripts() {	
		$recaptcha_settings = get_option( 'acadp_recaptcha_settings' );
		
		if ( isset( $recaptcha_settings['forms'] ) && in_array( 'contact', $recaptcha_settings['forms'] ) ) {
			wp_enqueue_script( ACADP_PLUGIN_NAME . "-recaptcha" );
		}			
	}
	
}
