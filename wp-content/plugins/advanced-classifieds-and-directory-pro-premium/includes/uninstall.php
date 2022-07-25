<?php

/**
 * Fired during plugin uninstallation.
 *
 * @link    https://pluginsware.com
 * @since   1.6.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Uninstall class.
 *
 * @since 1.6.3
 */
class ACADP_Uninstall {

	/**
	 * Called when the plugin is uninstalled.
	 *
	 * @since 1.6.3
	 */
	public static function uninstall() {
		$misc_settings = get_option( 'acadp_misc_settings' );

		if ( empty( $misc_settings['delete_plugin_data'] ) ) {
			return;
		}

		global $wpdb;

		// Delete All the Custom Post Types
		$acadp_post_types = array( 'acadp_listings', 'acadp_fields', 'acadp_payments' );

		foreach ( $acadp_post_types as $post_type ) {
			$args = array( 
				'post_type' => $post_type, 
				'post_status' => 'any', 
				'posts_per_page' => -1, 
				'fields' => 'ids',
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'cache_results' => false
			);

			$acadp_query = new WP_Query( $args );
			
			if ( $acadp_query->have_posts() ) {
				foreach ( $acadp_query->posts as $item ) {
					// Delete attachments (only if applicable)
					if ( 'acadp_listings' == $post_type ) {
						$images = get_post_meta( $item, 'images', true );
						
						if ( ! empty( $images ) ) {						
							foreach ( $images as $image ) {
								wp_delete_attachment( $image, true );
							}						
						}
					}
					
					// Delete the actual post
					wp_delete_post( $item, true );
				}
			}					
		}

		// Delete All the Terms & Taxonomies
		$acadp_taxonomies = array( 'acadp_categories', 'acadp_locations' );

		foreach ( $acadp_taxonomies as $taxonomy ) {
			$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );
			
			// Delete Terms
			if ( $terms ) {
				foreach ( $terms as $term ) {
					$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
					$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
				}
			}
			
			// Delete Taxonomies
			$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
		}

		// Delete the Plugin Pages
		if ( $acadp_created_pages = get_option( 'acadp_page_settings' ) ) {
			foreach ( $acadp_created_pages as $page => $id ) {
				if ( $id > 0 ) {
					wp_delete_post( $id, true );
				}			
			}
		}

		// Delete all the Plugin Options
		$acadp_settings = array(
			'acadp_general_settings',
			'acadp_badges_settings',
			'acadp_registration_settings',
			'acadp_currency_settings',
			'acadp_map_settings',
			'acadp_listings_settings',
			'acadp_listing_settings',
			'acadp_locations_settings',
			'acadp_categories_settings',
			'acadp_socialshare_settings',
			'acadp_recaptcha_settings',
			'acadp_terms_of_agreement',						
			'acadp_featured_listing_settings',						
			'acadp_gateway_settings',
			'acadp_gateway_offline_settings',
			'acadp_email_settings',
			'acadp_email_template_listing_submitted',
			'acadp_email_template_listing_published',
			'acadp_email_template_listing_renewal',
			'acadp_email_template_listing_expired',	
			'acadp_email_template_renewal_reminder',
			'acadp_email_template_order_created',
			'acadp_email_template_order_created_offline',
			'acadp_email_template_order_completed',
			'acadp_email_template_listing_contact',
			'acadp_misc_settings',
			'acadp_permalink_settings',			
			'acadp_page_settings'
		);

		foreach ( $acadp_settings as $settings ) {
			delete_option( $settings );
		}

		delete_option( 'acadp_categories_children' );
		delete_option( 'acadp_locations_children' );
		delete_option( 'acadp_issues' );
		delete_option( 'acadp_version' );

		// Delete Capabilities
		$roles = new ACADP_Roles;
		$roles->remove_caps();
		
		if ( acadp_fs()->is__premium_only() ) {
			// Delete premium settings
			delete_option( 'acadp_wc_plans_settings' );
			delete_option( 'acadp_fee_plans_settings' );
			delete_option( 'acadp_gateway_paypal_settings' );
			delete_option( 'acadp_gateway_stripe_settings' );
			delete_option( 'acadp_multi_categories_settings' );

			// Delete Capabilities
			global $wp_roles;
					
			if ( class_exists( 'WP_Roles' ) ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}
					
			if ( is_object( $wp_roles ) ) {
				$wp_roles->remove_cap( 'administrator', "edit_acadp_fee_plan" );
				$wp_roles->remove_cap( 'administrator', "read_acadp_fee_plan" );
				$wp_roles->remove_cap( 'administrator', "delete_acadp_fee_plan" );
				$wp_roles->remove_cap( 'administrator', "edit_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "edit_others_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "publish_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "read_private_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "delete_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "delete_private_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "delete_published_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "delete_others_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "edit_private_acadp_fee_plans" );
				$wp_roles->remove_cap( 'administrator', "edit_published_acadp_fee_plans" );					
			}
		}
	}

}
