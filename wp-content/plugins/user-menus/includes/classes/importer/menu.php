<?php
/**
 * Nav menu custom importer class.
 *
 * @package User Menus
 */

namespace JP\UM\Importer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
	return;
}

/** Display verbose errors */
if ( ! defined( 'IMPORT_DEBUG' ) ) {
	define( 'IMPORT_DEBUG', false );
}

// Load Importer API.
if ( ! function_exists( 'get_importers' ) ) {
	require_once ABSPATH . 'wp-admin/includes/import.php';
}

if ( ! class_exists( 'WP_Importer' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-importer.php';
}

/* phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment, WordPress.Security.EscapeOutput.OutputNotEscaped */

/**
 * Class JP\UM\Importer\Menu
 */
class Menu extends \WP_Importer {

	/**
	 * Max supported WXR version
	 *
	 * @var float
	 */
	public $max_wxr_version = 1.2;

	/**
	 * WXR attachment ID
	 *
	 * @var integer
	 */
	public $id;

	/**
	 * WXR File Version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Array of posts.
	 *
	 * @var array
	 */
	public $posts = [];

	/**
	 * Base URL.
	 *
	 * @var string
	 */
	public $base_url = '';

	/**
	 * Invalid meta keys.
	 *
	 * @var array
	 */
	public $invalid_meta_keys = [
		'_wp_attached_file',
		'_wp_attachment_metadata',
		'_edit_lock',
	];

	/**
	 * Registered callback function for the WordPress Importer
	 *
	 * Manages the three separate stages of the WXR import process
	 */
	public function dispatch() {
		$this->header();

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		switch ( $step ) {
			case 0:
				$this->greet();
				break;
			case 1:
				check_admin_referer( 'import-upload' );
				if ( $this->handle_upload() ) {
					$file = get_attached_file( $this->id );
					set_time_limit( 0 );
					$this->import( $file );
				}
				break;
		}

		$this->footer();
	}

	/**
	 * Page header.
	 */
	public function header() {
		echo '<div class="wrap">';
		echo '<h2>' . __( 'Import Nav Menus', 'user-menus' ) . '</h2>';

		$updates  = get_plugin_updates();
		$basename = plugin_basename( __FILE__ );
		if ( isset( $updates[ $basename ] ) ) {
			$update = $updates[ $basename ];
			echo '<div class="error"><p><strong>';
			printf( __( 'A new version of this importer is available. Please update to version %s to ensure compatibility with newer export files.', 'user-menus' ), $update->update->new_version );
			echo '</strong></p></div>';
		}
	}

	/**
	 * Display introductory text and file upload form
	 */
	public function greet() {
		echo '<div class="narrow">';
		echo '<p>' . __( 'Upload your WordPress export (WXR) file and import the Nav Menus and any meta for the Nav Menu items.', 'user-menus' ) . '</p>';
		echo '<p>' . __( 'Choose a WXR (.xml) file to upload, then click Upload file and import.', 'user-menus' ) . '</p>';
		wp_import_upload_form( 'admin.php?import=jpum_nav_menu_importer&amp;step=1' );
		echo '</div>';
	}

	/**
	 * Handles the WXR upload and initial parsing of the file to prepare for
	 * displaying author import options
	 *
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	public function handle_upload() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'user-menus' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';

			return false;
		} elseif ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'user-menus' ) . '</strong><br />';
			printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'user-menus' ), esc_html( $file['file'] ) );
			echo '</p>';

			return false;
		}

		$this->id    = (int) $file['id'];
		$import_data = $this->parse( $file['file'] );
		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'user-menus' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';

			return false;
		}

		$this->version = $import_data['version'];
		if ( $this->version > $this->max_wxr_version ) {
			echo '<div class="error"><p><strong>';
			printf( __( 'This WXR file (version %s) may not be supported by this version of the importer. Please consider updating.', 'user-menus' ), esc_html( $import_data['version'] ) );
			echo '</strong></p></div>';
		}

		return true;
	}

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing.
	 */
	public function import( $file ) {
		add_filter( 'import_post_meta_key', [ $this, 'is_valid_meta_key' ] );
		add_filter( 'http_request_timeout', [ $this, 'bump_request_timeout' ] );

		$this->import_start( $file );

		wp_suspend_cache_invalidation( true );
		$this->process_nav_menu_meta();
		wp_suspend_cache_invalidation( false );

		$this->import_end();
	}

	/**
	 * Render the page footer.
	 */
	public function footer() {
		echo '</div>';
	}

	/**
	 * Parse a WXR file
	 *
	 * @param string $file Path to WXR file for parsing.
	 *
	 * @return array Information gathered from the WXR file.
	 */
	public function parse( $file ) {
		$parser = new \WXR_Parser();

		return $parser->parse( $file );
	}

	/**
	 * Parses the WXR file and prepares us for the task of processing parsed data.
	 *
	 * @param string $file Path to the WXR file for importing.
	 */
	public function import_start( $file ) {
		if ( ! is_file( $file ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'user-menus' ) . '</strong><br />';
			echo __( 'The file does not exist, please try again.', 'user-menus' );
			echo '</p>';

			$this->footer();
			die();
		}

		$import_data = $this->parse( $file );

		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'user-menus' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() );

			echo '</p>';

			$this->footer();
			die();
		}

		$this->version  = $import_data['version'];
		$this->posts    = $import_data['posts'];
		$this->base_url = esc_url( $import_data['base_url'] );

		do_action( 'import_start' );
	}

	/**
	 * Create new menu items based on import information
	 */
	public function process_nav_menu_meta() {
		foreach ( $this->posts as $post ) {

			// Exclude other post types.
			if ( 'nav_menu_item' !== $post['post_type'] || ! empty( $post['post_id'] ) ) {
				continue;
			}

			$post_id = (int) $post['post_id'];

			if ( isset( $post['postmeta'] ) ) {
				foreach ( $post['postmeta'] as $meta ) {
					$key   = apply_filters( 'import_post_meta_key', $meta['key'] );
					$value = false;

					if ( $key ) {
						// export gets meta straight from the DB so could have a serialized string.
						if ( ! $value ) {
							$value = maybe_unserialize( $meta['value'] );
						}

						update_post_meta( $post_id, $key, $value );
						do_action( 'import_post_meta', $post_id, $key, $value );
					}
				}
			}
		}

		unset( $this->posts );
	}

	/**
	 * Performs post-import cleanup of files and the cache.
	 */
	public function import_end() {
		wp_import_cleanup( $this->id );

		wp_cache_flush();

		echo '<p>' . __( 'All done.', 'user-menus' ) . ' <a href="' . admin_url() . '">' . __( 'Have fun!', 'user-menus' ) . '</a></p>';

		do_action( 'import_end' );
	}

	/**
	 * Decide if the given meta key maps to information we will want to import.
	 *
	 * @param string $key The meta key to check.
	 *
	 * @return string|bool The key if we do want to import, false if not.
	 */
	public function is_valid_meta_key( $key ) {
		if ( in_array( $key, $this->invalid_meta_keys, true ) ) {
			return false;
		}

		return $key;
	}

}
