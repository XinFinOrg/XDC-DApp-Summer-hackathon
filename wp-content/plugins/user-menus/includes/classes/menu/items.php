<?php
/**
 * Menu items class.
 *
 * @package User Menus
 */

namespace JP\UM\Menu;

use JP\UM\User\Codes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JP\UM\Menu\Items
 */
class Items {

	/**
	 * Current item.
	 *
	 * @var object
	 */
	private static $current_item;

	/**
	 * Init
	 */
	public static function init() {
		add_filter( 'wp_setup_nav_menu_item', [ __CLASS__, 'merge_item_data' ] );
	}

	/**
	 * Merge Item data into the $item object.
	 *
	 * @param object $item Item object.
	 *
	 * @return mixed
	 */
	public static function merge_item_data( $item ) {
		self::$current_item = $item;

		// Merge Rules.
		foreach ( Item::get_options( $item->ID ) as $key => $value ) {
			$item->$key = $value;
		}

		if ( in_array( $item->object, [ 'login', 'register', 'logout' ], true ) ) {
			$item->type_label = __( 'User Link', 'user-menus' );

			switch ( $item->redirect_type ) {
				case 'current':
					$redirect = static::current_url();
					break;

				case 'home':
					$redirect = home_url();
					break;

				case 'custom':
					$redirect = $item->redirect_url;
					break;

				default:
					$redirect = '';
					break;
			}

			switch ( $item->object ) {
				case 'login':
					$item->url = wp_login_url( $redirect );
					break;

				case 'register':
					$item->url = add_query_arg( [ 'redirect_to' => $redirect ], wp_registration_url() );
					break;

				case 'logout':
					$item->url = wp_logout_url( $redirect );
					break;
			}
		}

		// User text replacement.
		if ( ! is_admin() ) {
			$item->title = static::user_titles( $item->title );
		}

		return $item;
	}

	/**
	 * Get the current url.
	 *
	 * @return string
	 */
	public static function current_url() {
		/* phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated */
		$protocol = ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) || 443 === $_SERVER['SERVER_PORT'] ? 'https://' : 'http://';

		return $protocol . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		/* phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotValidated */
	}

	/**
	 * Get replacement titles.
	 *
	 * @param string $title Menu item title.
	 *
	 * @return mixed|string
	 */
	public static function user_titles( $title = '' ) {
		preg_match_all( '/{(.*?)}/', $title, $found );

		if ( count( $found[1] ) ) {
			foreach ( $found[1] as $key => $match ) {
				$title = static::text_replace( $title, $match );
			}
		}

		return $title;
	}

	/**
	 * Replace text.
	 *
	 * @param string $title Text to search.
	 * @param string $match Strings to match.
	 *
	 * @return mixed|string
	 */
	public static function text_replace( $title = '', $match = '' ) {
		if ( empty( $match ) ) {
			return $title;
		}

		if ( strpos( $match, '||' ) !== false ) {
			$matches = explode( '||', $match );
		} else {
			$matches = [ $match ];
		}

		$current_user = wp_get_current_user();

		$replace = '';

		foreach ( $matches as $string ) {
			if ( ! array_key_exists( $string, Codes::valid_codes() ) ) {

				// If its not a valid code it is likely a fallback.
				$replace = $string;
			} elseif ( 0 === $current_user->ID && array_key_exists( $string, Codes::valid_codes() ) ) {

				// If the code exists & user is not logged in, return nothing.
				$replace = '';
			} else {
				switch ( $string ) {
					case 'avatar':
						$replace = get_avatar( $current_user->ID, self::$current_item->avatar_size );
						break;

					case 'first_name':
						$replace = $current_user->user_firstname;
						break;

					case 'last_name':
						$replace = $current_user->user_lastname;
						break;

					case 'username':
						$replace = $current_user->user_login;
						break;

					case 'display_name':
						$replace = $current_user->display_name;
						break;

					case 'nickname':
						$replace = $current_user->nickname;
						break;

					case 'email':
						$replace = $current_user->user_email;
						break;

					default:
						$replace = $string;
						break;
				}
			}

			// If we found a replacement stop the loop.
			if ( ! empty( $replace ) ) {
				break;
			}
		}

		return str_replace( '{' . $match . '}', $replace, $title );
	}

}

Items::init();
