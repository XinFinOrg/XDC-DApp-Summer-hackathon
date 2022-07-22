<?php
/**
 * Adds [ethpress_login_button] shortcode.
 *
 * Since version 0.7.0 you should use the EthPress widget instead.
 *
 * @since 1.4.0
 * @package ethpress
 */

namespace losnappas\Ethpress\Shortcodes;

defined( 'ABSPATH' ) || die;

use losnappas\Ethpress\Address;
use losnappas\Ethpress\Plugin;
use losnappas\Ethpress\Logger;

/**
 * Contains LoggedAccount's internals.
 *
 * @since 1.4.0
 */
class LoggedAccount {
	/**
	 * Name of the shortcode.
	 *
	 * @var String shortcode name
	 *
	 * @since 1.4.0
	 */
	public static $shortcode_name = 'ethpress_account';

	/**
	 * Creates shortcode content. Runs on `\add_shortcode`.
	 *
	 * Outputs nothing when user is logged in. Button otherwise.
	 *
	 * @since 1.4.0
	 */
	public static function add_shortcode() {
		if ( !\is_user_logged_in() ) {
			return '';
		}
		$address = Address::find_by_user(get_current_user_id());
        if ( is_wp_error( $address ) ) {
            Logger::log("LoggedAccount::add_shortcode: err = " . $address->get_error_message());
            return '';
		}
        if (!$address) {
            return '';
        }
		return $address->get_address();
	}
}
