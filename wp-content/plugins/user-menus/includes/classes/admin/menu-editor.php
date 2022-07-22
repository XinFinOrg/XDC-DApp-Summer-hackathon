<?php
/**
 * Admin menu editor class
 *
 * @package User Menus
 */

namespace JP\UM\Admin;

use JP\UM\User\Codes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JP\UM\Admin\Menu_Editor
 */
class Menu_Editor {

	/**
	 * Init
	 */
	public static function init() {
		add_filter( 'wp_edit_nav_menu_walker', [ __CLASS__, 'nav_menu_walker' ], 999999999 );
		add_action( 'admin_head-nav-menus.php', [ __CLASS__, 'register_metaboxes' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
	}

	/**
	 * Override the Admin Menu Walker
	 *
	 * @param string $walker Current walker name.
	 *
	 * @return string
	 */
	public static function nav_menu_walker( $walker ) {
		global $wp_version;

		$bail_early = [
			// WP 5.4 adds support for custom fields, no need to do this hack at all.
			version_compare( $wp_version, '5.4', '>=' ),
			// not sure about this one, was part of the original solution.
			doing_filter( 'plugins_loaded' ),
			// No need if its already loaded by another plugin.
			'Walker_Nav_Menu_Edit_Custom_Fields' === $walker,
		];

		if ( in_array( true, $bail_early, true ) ) {
			return $walker;
		}

		// Load custom nav menu walker class for custom field compatibility.
		if ( ! class_exists( 'Walker_Nav_Menu_Edit_Custom_Fields' ) ) {
			if ( version_compare( $wp_version, '3.6', '>=' ) ) {
				require_once \JP_User_Menus::$DIR . 'includes/classes/walker/nav-menu-edit-custom-fields.php';
			} else {
				require_once \JP_User_Menus::$DIR . 'includes/classes/walker/nav-menu-edit-custom-fields-deprecated.php';
			}
		}

		return 'Walker_Nav_Menu_Edit_Custom_Fields';
	}


	/**
	 * Register metaboxes.
	 */
	public static function register_metaboxes() {
		add_meta_box( 'jp_user_menus', __( 'User Links', 'user-menus' ), [ __CLASS__, 'nav_menu_metabox' ], 'nav-menus', 'side', 'default' );
	}

	/**
	 * Render nav menu metabox.
	 *
	 * @param mixed $object Nav menu object.
	 */
	public static function nav_menu_metabox( $object ) {
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$link_types = [
			[
				'object' => 'login',
				'title'  => __( 'Login', 'user-menus' ),
			],
			[
				'object' => 'register',
				'title'  => __( 'Register', 'user-menus' ),
			],
			[
				'object' => 'logout',
				'title'  => __( 'Logout', 'user-menus' ),
			],
		];

		foreach ( $link_types as $key => $link ) {
			$i = isset( $i ) ? $i + 1 : 1;

			$link_types[ $key ] = (object) array_replace_recursive( [
				'type'             => '',
				'object'           => '',
				'title'            => '',
				'ID'               => $i,
				'object_id'        => $i,
				'db_id'            => 0,
				'post_parent'      => 0,
				'menu_item_parent' => 0,
				'url'              => '',
				'target'           => '',
				'attr_title'       => '',
				'description'      => '',
				'classes'          => [],
				'xfn'              => '',
			], $link );
		}

		$walker = new \Walker_Nav_Menu_Checklist();

		$removed_args = [
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		];

		?>

		<div id="user-menus-div" class="user-menus">
			<div id="tabs-panel-user-menus-all" class="tabs-panel tabs-panel-active">

				<?php $registration_disabled = '1' !== get_option( 'users_can_register' ); ?>

				<?php if ( $registration_disabled ) : ?>
				<small>
					<span class="dashicons dashicons-info"></span>
					<?php
					// translators: 1: link to registration page, 2: closing link tag.
					echo esc_html( sprintf( __( 'Registration is %1$scurrently disabled%2$s on your site.', 'user-menus' ), '<a href="' . admin_url( 'options-general.php' ) . '">', '</a>' ) );
					?>
				</small>
				<?php endif; ?>

				<ul id="user-menus-checklist-all" class="categorychecklist form-no-clear <?php echo $registration_disabled ? 'user-menus-registration-disabled' : ''; ?>">
					<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $link_types ), 0, (object) [ 'walker' => $walker ] ); ?>
				</ul>

				<p class="button-controls">
					<span class="list-controls">
						<a href="
						<?php
						echo esc_url( add_query_arg( [
							'user-menus-all' => 'all',
							'selectall'      => 1,
						], remove_query_arg( $removed_args ) ) );
						?>
						#user-menus-div" class="select-all">
						<?php
						 /* phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction */
						_e( 'Select All', 'user-menus' );
						?>
						</a>
					</span>

					<span class="add-to-menu">
						<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'user-menus' ); ?>" name="add-user-menus-menu-item" id="submit-user-menus-div" />
						<span class="spinner"></span>
					</span> </p>
			</div>
		</div>

		<?php
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook Hook name.
	 */
	public static function enqueue_scripts( $hook ) {
		if ( 'nav-menus.php' !== $hook ) {
			return;
		}

		add_action( 'admin_footer', [ __CLASS__, 'media_templates' ] );

		// Use minified libraries if SCRIPT_DEBUG is turned off.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jpum-scripts', \JP_User_Menus::$URL . 'assets/js/admin-general' . $suffix . '.js', [ 'jquery', 'underscore' ], \JP_User_Menus::$VER, true );
		wp_enqueue_style( 'jpum-styles', \JP_User_Menus::$URL . 'assets/css/admin-general' . $suffix . '.css', [ 'dashicons' ], \JP_User_Menus::$VER );
	}

	/**
	 * Render media templates.
	 */
	public static function media_templates() {
		/* phpcs:disable WordPress.Security.EscapeOutput.UnsafePrintingFunction */
		?>
		<script type="text/html" id="tmpl-jpum-user-codes">
			<div class="jpum-user-codes">
				<button type="button" title="<?php _e( 'Insert User Menu Codes', 'user-menus' ); ?>">
					<i class="dashicons dashicons-arrow-left"></i>
				</button>
				<ul>
					<?php foreach ( Codes::valid_codes() as $code => $label ) : ?>
						<li>
							<a title="<?php echo esc_attr( $label ); ?>" href="#" data-code="<?php echo esc_attr( $code ); ?>">
								<?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</script>
		<?php
	}
}

Menu_Editor::init();
