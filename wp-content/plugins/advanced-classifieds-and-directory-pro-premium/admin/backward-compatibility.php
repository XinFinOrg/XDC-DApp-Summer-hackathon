<?php

/**
 * Backward Compatibility.
 *
 * @link    https://pluginsware.com
 * @since   1.7.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Admin_Backward_Compatibility Class.
 *
 * @since 1.7.3
 */
class ACADP_Admin_Backward_Compatibility {

	/**
	 * Redirect removed plugin admin pages to the alternate new pages.
	 *
	 * @since 1.7.3
	 */
	public function admin_init() {
		if ( isset( $_GET['post_type'] ) && 'acadp_listings' == $_GET['post_type'] && isset( $_GET['page'] ) && 'acadp_settings' == $_GET['page'] ) {
			wp_redirect( admin_url( 'admin.php?page=advanced-classifieds-and-directory-pro' ), 301 );
			exit;
		}
	}
	
	/**
	 * Add "Fee Plans" add-on menu.
	 *
	 * @since 1.7.3
	 */
	public function admin_menu() {
		if ( acadp_fs()->can_use_premium_code() ) {
			return false;
		}

		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if ( ! in_array( 'acadp-fee-plans/acadp-fee-plans.php', $active_plugins ) ) {
			return false;
		}

		global $submenu;
		
		add_submenu_page(
			'advanced-classifieds-and-directory-pro',
			__( 'Advanced Classifieds and Directory Pro - Fee Plans', 'advanced-classifieds-and-directory-pro' ),
			__( 'Fee Plans', 'advanced-classifieds-and-directory-pro' ),
			'manage_acadp_options',
			'edit.php?post_type=acadp_fee_plans'
		);

		if ( array_key_exists( 'advanced-classifieds-and-directory-pro', $submenu ) ) {		
			$before = $after = array();
			
			$payments_slug = 'acadp_payments';
			$settings_slug = 'acadp_settings';
	
			foreach ( $submenu['advanced-classifieds-and-directory-pro'] as $item ) {
				if ( strpos( $item[2], $payments_slug ) !== false || $item[2] == $settings_slug ) {
					$after[]  = $item;
				} else {
					$before[] = $item;
				}
			}
			
			$submenu['advanced-classifieds-and-directory-pro'] = array_values( array_merge( $before, $after ) );		
		}
	}	

}
