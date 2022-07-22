<?php

/**
 * Listing detail Page.
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
 * ACADP_Public_Listing Class.
 *
 * @since 1.0.0
 */
class ACADP_Public_Listing {
	
	/**
	 * Filter the post content.
	 *
	 * @since  1.5.4
	 * @param  string $html The post thumbnail HTML.
	 * @return string $html Filtered thumbnail HTML.
	 */
	public function post_thumbnail_html( $html ) {	
		if ( is_singular( 'acadp_listings' ) ) {
			return '';
		}
		
		return $html;		
	}
	
	/**
	 * Filter the post content.
	 *
	 * @since  1.0.0
	 * @param  string $content Content of the current post.
	 * @return string $content Modified Content.
	 */
	public function the_content( $content ) {		
		if ( is_singular( 'acadp_listings' ) && in_the_loop() && is_main_query() ) {		
			global $post, $wp_query;
			
			if ( $post->ID != $wp_query->get_queried_object_id() ) {
				return $content;
			}
			
			if ( post_password_required( $post->ID ) ) {
				return $content;
			}
			
			acadp_update_listing_views_count( $post->ID );
			
			$general_settings      = get_option( 'acadp_general_settings' );
			$recaptcha_settings    = get_option( 'acadp_recaptcha_settings' );	
			$registration_settings = get_option( 'acadp_registration_settings' );

			$post_meta = get_post_meta( $post->ID );
			
			$description = $content;
					
			$can_show_date         = isset( $general_settings['display_options'] ) && in_array( 'date', $general_settings['display_options'] )  ? true : false;
			$can_show_user         = isset( $general_settings['display_options'] ) && in_array( 'user', $general_settings['display_options'] )  ? true : false;
			$can_show_views        = isset( $general_settings['display_options'] ) && in_array( 'views', $general_settings['display_options'] ) ? true : false;
			$can_show_contact_form = empty( $general_settings['has_contact_form'] ) ? false : true;	
			$can_add_favourites    = empty( $general_settings['has_favourites'] )   ? false : true;
			$can_report_abuse      = empty( $general_settings['has_report_abuse'] ) ? false : true;
			$has_widgets           = acadp_has_active_listing_widgets();
			$has_sidebar           = ! $has_widgets;
			
			// $login_url
			$current_page_url = get_permalink();
			$login_url        = acadp_get_user_login_page_link( $current_page_url );
			
			// $location
			$has_location = empty( $general_settings['has_location'] ) ? false : true;
			$can_show_location = false;
			
			if ( $has_location ) {
				$location = wp_get_object_terms( $post->ID, 'acadp_locations' );
			
				if ( ! empty( $location ) ) {
					$location = $location[0];
					$can_show_location = true;
				}
			}
			
			// $category
			$has_category = false;
			$can_show_category = isset( $general_settings['display_options'] ) && in_array( 'category', $general_settings['display_options'] ) ? true : false;
			$can_show_category_desc = isset( $general_settings['display_options'] ) && in_array( 'category_desc', $general_settings['display_options'] ) ? true : false;
			$categories = wp_get_object_terms( $post->ID, 'acadp_categories' );
			
			if ( empty( $categories ) ) {
				$can_show_category = false;
			} else {
				$category = $categories[0];
				$has_category = true;
			}
			
			// $can_show_images
			$has_images = empty( $general_settings['has_images'] ) ? false : true;
			$can_show_images = false;
			
			if ( $has_images ) {
				$can_show_images = isset( $post_meta['images'] ) ? true : false;
			}
			
			// $can_show_video
			$has_video = empty( $general_settings['has_video'] ) ? false : true;
			$can_show_video = false;
			$video_url = '';
			
			if ( $has_video ) {
				if ( ! empty( $post_meta['video'][0] ) ) {
					$video_url = acadp_parse_videos( $post_meta['video'][0] );	
					$can_show_video = empty( $video_url ) ? false : true;				
				}				
			}
			
			// $can_show_map
			$has_map = ! empty( $general_settings['has_map'] ) && empty( $post_meta['hide_map'][0] ) ? true : false;
			$can_show_map = false;
		
			if ( $can_show_location && $has_map ) {			
				$can_show_map = ! empty( $post_meta['latitude'][0] ) && ! empty( $post_meta['longitude'][0] ) ? true : false;
			}

			// $can_show_price
			$has_price = empty( $general_settings['has_price'] ) ? false : true;
			$can_show_price = false;
		
			if ( $has_price && isset( $post_meta['price'] ) && $post_meta['price'][0] > 0 ) {
				$can_show_price = true;
			}
			
			// Get custom fields
			$fields = array();
			$category_ids = array();

			foreach ( $categories as $category ) {
				$category_ids[] = $category->term_id;
			}

			$custom_field_ids = acadp_get_custom_field_ids( $category_ids );	
					
			if ( ! empty( $custom_field_ids ) ) {
				$args = array(
					'post_type' => 'acadp_fields',
					'post_status' => 'publish',
					'posts_per_page' => 500,	
					'post__in' => $custom_field_ids,
					'no_found_rows' => true,
					'update_post_term_cache' => false,	
					'meta_key' => 'order',
					'orderby' => 'meta_value_num',				
					'order' => 'ASC'
				);
				
				$acadp_query = new WP_Query( $args );

				if ( $acadp_query->have_posts() ) {
					$fields = $acadp_query->posts;
				}
			}

			// Process output
			ob_start();
			do_action( 'acadp_before_listing_content' );
			include( acadp_get_template( "listing/acadp-public-listing-display.php" ) );
			do_action( 'acadp_after_listing_content' );
			$content = ob_get_clean();		
		}
		
		return $content;		
	}
	
	/**
	 * Add or Remove favourites.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_add_remove_favorites() {	
		check_ajax_referer( 'acadp_ajax_nonce', 'security' );

		$post_id = (int) $_POST['post_id'];
		
		$favourites = (array) get_user_meta( get_current_user_id(), 'acadp_favourites', true );
		
		if ( in_array( $post_id, $favourites ) ) {			
			if ( ( $key = array_search( $post_id, $favourites ) ) !== false ) {
    			unset( $favourites[ $key ] );
			}
		} else {
			$favourites[] = $post_id;
		}
		
		$favourites = array_filter( $favourites );
		$favourites = array_values( $favourites );
		
		delete_user_meta( get_current_user_id(), 'acadp_favourites' );
		update_user_meta( get_current_user_id(), 'acadp_favourites', $favourites );
		
		the_acadp_favourites_link( $post_id );
		
		wp_die();	
	}
	
	/**
	 * Report Abuse.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_report_abuse() {	
		check_ajax_referer( 'acadp_ajax_nonce', 'security' );

		$data = array( 'error' => 0 );
		
		if ( acadp_is_human('report_abuse') ) {		
			if ( acadp_email_admin_report_abuse() ) {			
				$data['message'] = __( 'Your message sent successfully.', 'advanced-classifieds-and-directory-pro' );				
			} else {				
				$data['error']   = 1;
				$data['message'] = __( 'Sorry! Please try again.', 'advanced-classifieds-and-directory-pro' );				
			}			
		} else {		
			$data['error']   = 1;
			$data['message'] = __( 'Invalid Captcha: Please try again.', 'advanced-classifieds-and-directory-pro' );			
		}
				
		echo wp_json_encode( $data );
		wp_die();	
	}
	
	/**
	 * Send contact email.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_send_contact_email() {	
		check_ajax_referer( 'acadp_ajax_nonce', 'security' );
		
		$data = array( 'error' => 0 );
		
		if ( acadp_is_human('contact') ) {		
			if ( acadp_email_listing_owner_listing_contact() ) {			
				// Send a copy to admin( only if applicable ).
				acadp_email_admin_listing_contact();
				
				$data['message'] = __( 'Your message sent successfully.', 'advanced-classifieds-and-directory-pro' );				
			} else {				
				$data['error']   = 1;
				$data['message'] = __( 'Sorry! Please try again.', 'advanced-classifieds-and-directory-pro' );				
			}			
		} else {		
			$data['error']   = 1;
			$data['message'] = __( 'Invalid Captcha: Please try again.', 'advanced-classifieds-and-directory-pro' );			
		}
			
		echo wp_json_encode( $data );	
		wp_die();	
	}

}
