<?php
/**
 * Menu item class.
 *
 * @package User Menus
 */

namespace JP\UM\Menu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JP\UM\Menu\Item
 */
class Item {

	/**
	 * Get item options.
	 *
	 * @param int $item_id Item ID.
	 *
	 * @return array
	 */
	public static function get_options( $item_id = 0 ) {

		// Fetch all rules for this menu item.
		$item_options = get_post_meta( $item_id, '_jp_nav_item_options', true );

		return static::parse_options( $item_options );
	}

	/**
	 * Parse options.
	 *
	 * @param array $options Array of options to parse.
	 *
	 * @return array
	 */
	public static function parse_options( $options = [] ) {
		if ( ! is_array( $options ) ) {
			$options = [];
		}

		return wp_parse_args( $options, [
			'avatar_size'   => 24,
			'redirect_type' => 'current',
			'redirect_url'  => '',
			'which_users'   => '',
			'can_see'       => 'yes',
			'roles'         => [],
		] );
	}

}
