<?php
/**
 * Site menu class.
 *
 * @package User Menus
 */

namespace JP\UM\Site;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JP\UM\Site\Menus
 */
class Menus {

	/**
	 * Init
	 */
	public static function init() {
		add_filter( 'wp_get_nav_menu_items', [ __CLASS__, 'exclude_menu_items' ] );
	}

	/**
	 * Exclude menu items via wp_get_nav_menu_items filter.
	 *
	 * Guarantees compatibility with nearly any theme.
	 *
	 * @param array $items Menu items.
	 */
	public static function exclude_menu_items( $items = [] ) {
		if ( empty( $items ) ) {
			return $items;
		}

		$logged_in = is_user_logged_in();

		$excluded = [];

		foreach ( $items as $key => $item ) {
			// Exclude menu items that are children of excluded items.
			$exclude = in_array( (int) $item->menu_item_parent, $excluded, true );

			if ( 'logout' === $item->object ) {
				$exclude = ! $logged_in;
			} elseif ( 'login' === $item->object || 'register' === $item->object ) {
				$exclude = $logged_in;
			} else {
				if ( is_object( $item ) && isset( $item->which_users ) ) {
					switch ( $item->which_users ) {
						case 'logged_in':
							if ( ! $logged_in ) {
								$exclude = true;
							} elseif ( ! empty( $item->roles ) ) {

								/**
								 * If yes
								 * - this value will be true
								 * - $allowed_by_role will be set to false by default, allowing only matched roles to see it.
								 * - if any matching role is found, $allowed_by_role will be set to true.
								 *
								 * If no
								 * - this value will be false.
								 * - $allowed_by_role will be set to true by default, allowing all not-matched roles to see it.
								 * - if any matching role is found, $allowed_by_role will be set to false.
								 */
								$can_see         = 'yes' === $item->can_see;
								$allowed_by_role = ! $can_see;

								foreach ( $item->roles as $role ) {
									if ( current_user_can( $role ) ) {
										$allowed_by_role = $can_see;
										break;
									}
								}

								if ( ! $allowed_by_role ) {
									$exclude = true;
								}
							}
							break;

						case 'logged_out':
							$exclude = $logged_in;
							break;
					}
				}
			}

			$exclude = apply_filters( 'jpum_should_exclude_item', $exclude, $item );

			// unset non-visible item.
			if ( $exclude ) {
				$excluded[] = $item->ID; // store ID of item.
				unset( $items[ $key ] );
			}
		}

		return $items;
	}

}

Menus::init();
