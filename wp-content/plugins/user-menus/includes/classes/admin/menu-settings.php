<?php
/**
 * Menu settings class.
 *
 * @package User Menus
 */

namespace JP\UM\Admin;

use JP\UM\Menu\Item;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JP\UM\Admin\Menu_Settings
 */
class Menu_Settings {

	/**
	 * Init
	 */
	public static function init() {
		add_action( 'wp_nav_menu_item_custom_fields', [ __CLASS__, 'fields' ], 10, 4 );
		add_action( 'wp_update_nav_menu_item', [ __CLASS__, 'save' ], 10, 2 );
	}

	/**
	 * Render fields for each menu item.
	 *
	 * @param int    $item_id Item ID.
	 * @param object $item Item object.
	 * @param int    $depth Current menu item depth.
	 * @param array  $args Additional array of arguments.
	 */
	public static function fields( $item_id, $item, $depth, $args ) {
		$allowed_user_roles = static::allowed_user_roles();

		wp_nonce_field( 'jpum-menu-editor-nonce', 'jpum-menu-editor-nonce' ); ?>

		<p class="nav_item_options-avatar_size  description  description-wide">

			<label for="jp_nav_item_options-avatar_size-<?php echo esc_attr( $item->ID ); ?>">

					<?php echo esc_html( __( 'Avatar Size', 'user-menus' ) ); ?><br />

				<input type="number" min="0" step="1" name="jp_nav_item_options[<?php echo esc_attr( $item->ID ); ?>][avatar_size]" id="jp_nav_item_options-avatar_size-<?php echo esc_attr( $item->ID ); ?>" value="<?php echo esc_attr( $item->avatar_size ); ?>" class="widefat  code" />

			</label>

		</p>


		<?php

		$which_users_options = [
			''           => __( 'Everyone', 'user-menus' ),
			'logged_out' => __( 'Logged Out Users', 'user-menus' ),
			'logged_in'  => __( 'Logged In Users', 'user-menus' ),
		];

		if ( in_array( $item->object, [ 'login', 'register', 'logout' ], true ) ) :
			$redirect_types = [
				'current' => __( 'Current Page', 'user-menus' ),
				'home'    => __( 'Home Page', 'user-menus' ),
				'custom'  => __( 'Custom URL', 'user-menus' ),
			];
			?>

			<p class="nav_item_options-redirect_type  description  description-wide">

				<label for="jp_nav_item_options-redirect_type-<?php echo esc_attr( $item->ID ); ?>">

					<?php echo esc_html( __( 'Where should users be taken afterwards?', 'user-menus' ) ); ?><br />

					<select name="jp_nav_item_options[<?php echo esc_attr( $item->ID ); ?>][redirect_type]" id="jp_nav_item_options-redirect_type-<?php echo esc_attr( $item->ID ); ?>" class="widefat">
						<?php foreach ( $redirect_types as $option => $label ) : ?>
							<option value="<?php echo $option; ?>" <?php /*phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ selected( $option, $item->redirect_type ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>

				</label>

			</p>

			<p class="nav_item_options-redirect_url  description  description-wide">

				<label for="jp_nav_item_options-redirect_url-<?php echo esc_attr( $item->ID ); ?>">

					<?php echo esc_html( __( 'Enter a url user should be redirected to', 'user-menus' ) ); ?><br />

					<input type="text" name="jp_nav_item_options[<?php echo esc_attr( $item->ID ); ?>][redirect_url]" id="jp_nav_item_options-redirect_url-<?php echo esc_attr( $item->ID ); ?>" value="<?php echo esc_attr( $item->redirect_url ); ?>" class="widefat  code" />

				</label>

			</p>

			<p class="nav_item_options-which_users  description  description-wide">

				<label for="jp_nav_item_options-which_users-<?php echo esc_attr( $item->ID ); ?>">

					<?php echo esc_html( __( 'Who can see this link?', 'user-menus' ) ); ?>

				</label>

				<select n id="jp_nav_item_options-which_users-<?php echo esc_attr( $item->ID ); ?>" class="widefat" disabled="disabled">
					<option>
					<?php
					if ( 'logout' === $item->object ) {
						echo esc_html( $which_users_options['logged_in'] );
					} else {
						echo esc_html( $which_users_options['logged_out'] );
					}
					?>
					</option>
				</select>

			</p>

		<?php else : ?>

			<p class="nav_item_options-which_users  description  description-wide">

				<label for="jp_nav_item_options-which_users-<?php echo esc_attr( $item->ID ); ?>">

					<?php echo esc_html( __( 'Who can see this link?', 'user-menus' ) ); ?><br />

					<select name="jp_nav_item_options[<?php echo esc_attr( $item->ID ); ?>][which_users]" id="jp_nav_item_options-which_users-<?php echo esc_attr( $item->ID ); ?>" class="widefat">
							<?php foreach ( $which_users_options as $option => $label ) : ?>
							<option value="<?php echo $option; ?>" <?php /*phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ selected( $option, $item->which_users ); ?>>
									<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>

				</label>

			</p>

			<p class="nav_item_options-can_see  description  description-wide">

				<label>
					<input type="radio" name="jp_nav_item_options[<?php echo esc_attr( $item->ID ); ?>][can_see]" value="yes" <?php checked( $item->can_see, 'yes' ); ?>/>
								<?php echo esc_html( __( 'Choose which roles can see this link', 'user-menus' ) ); ?>
				</label>

				<br />

				<label>
					<input type="radio" name="jp_nav_item_options[<?php echo esc_attr( $item->ID ); ?>][can_see]" value="no" <?php checked( $item->can_see, 'no' ); ?>/>
								<?php echo esc_html( __( 'Choose which roles won\'t see this link', 'user-menus' ) ); ?>
				</label>

			</p>

			<p class="nav_item_options-roles  description  description-wide">

				<?php foreach ( $allowed_user_roles as $option => $label ) : ?>
					<label> <input type="checkbox" name="jp_nav_item_options[<?php echo esc_attr( $item->ID ); ?>][roles][]" value="<?php echo $option; ?>" <?php /*phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ checked( in_array( esc_attr( $option ), $item->roles, true ), true ); ?>/>
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>

			</p>

			<?php
		endif;
	}

	/**
	 * Get array of allowed user roles.
	 *
	 * @return array
	 */
	public static function allowed_user_roles() {
		global $wp_roles;

		static $roles;

		if ( ! isset( $roles ) ) {
			$roles = apply_filters( 'jpum_user_roles', $wp_roles->role_names );

			if ( ! is_array( $roles ) || empty( $roles ) ) {
				$roles = [];
			}
		}

		return $roles;
	}

	/**
	 * Save menu item data.
	 *
	 * @param int $menu_id Menu ID.
	 * @param int $item_id Item ID.
	 */
	public static function save( $menu_id, $item_id ) {
		$allowed_roles = static::allowed_user_roles();

		if ( empty( $_POST['jp_nav_item_options'][ $item_id ] ) || ! isset( $_POST['jpum-menu-editor-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jpum-menu-editor-nonce'] ) ), 'jpum-menu-editor-nonce' ) ) {
			return;
		}

		/* phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
		$item_options = Item::parse_options( wp_unslash( $_POST['jp_nav_item_options'][ $item_id ] ) );

		if ( 'logged_in' === $item_options['which_users'] ) {
			// Validate chosen roles and remove non-allowed roles.
			foreach ( (array) $item_options['roles'] as $key => $role ) {
				if ( ! array_key_exists( $role, $allowed_roles ) ) {
					unset( $item_options['roles'][ $key ] );
				}
			}
		} else {
			unset( $item_options['roles'] );
		}

		// Remove empty options to save space.
		$item_options = array_filter( $item_options );

		if ( ! empty( $item_options ) ) {
			update_post_meta( $item_id, '_jp_nav_item_options', $item_options );
		} else {
			delete_post_meta( $item_id, '_jp_nav_item_options' );
		}
	}
}

Menu_Settings::init();
