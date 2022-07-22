<?php

/**
 * Fired during plugin deactivation.
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
 * ACADP_Deactivator Class
 *
 * @since 1.0.0
 */
class ACADP_Deactivator {

	/**
	 * Called when the plugin is deactivated.
	 *
	 * @since  1.0.0
	 * @static	 
	 */
	public static function deactivate() {	
		delete_option( 'rewrite_rules' );
		
		// Un-schedules all previously-scheduled cron jobs
		wp_clear_scheduled_hook( 'acadp_hourly_scheduled_events' );
	}

}
