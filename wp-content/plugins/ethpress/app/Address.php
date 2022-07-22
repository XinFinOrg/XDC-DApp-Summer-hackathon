<?php
/**
 * Handles addresses.
 *
 * @since 0.1.0
 * @package ethpress
 */

namespace losnappas\Ethpress;

defined( 'ABSPATH' ) || die;

use losnappas\Ethpress\Plugin;
use losnappas\Ethpress\Logger;

/**
 * Address object.
 *
 * @since 0.1.0
 */
class Address {

	/**
	 * User associated with this address.
	 *
	 * @since 0.1.0
	 *
	 * @var (WP_User|null) $user
	 */
	private $user;

	/**
	 * Address or public key.
	 *
	 * @since 0.1.0
	 *
	 * @var string $coinbase Address.
	 */
	private $coinbase;

	/**
	 * ID. Don't rely on this too much for now.
	 *
	 * @since 0.1.0
	 *
	 * @var int $ID Id.
	 */
	// private $ID;

	/**
	 * Gets variable.
	 *
	 * @since 0.1.0
	 */
	public function get_user() {
        if ( $this->user ) {
            return $this->user;
        }
        return self::find_by_address( $this->coinbase );
	}

	/**
	 * Gets variable.
	 *
	 * @since 0.1.0
	 */
	public function get_coinbase() {
		return $this->coinbase;
	}

	/**
	 * Gets variable.
	 *
	 * @since 0.1.0
	 */
	public function get_address() {
		return $this->get_coinbase();
	}

	/**
	 * Gets variable.
	 *
	 * @since 0.1.0
	 */
	// public function get_id() {
	// 	return $this->ID;
	// }

	/**
	 * Constructs.
	 *
	 * @since 0.1.0
	 *
	 * @param string $coinbase An address.
	 * @param array  $args {
	 *      Optional. Array with user, and address id.
	 *
	 *      @type WP_User $user The User to be associated with this address.
	 *      @type int $id ID for this address. NOT user ID!
	 * }
	 * @throws \WP_Error If coinbase is invalid.
	 */
	public function __construct( $coinbase, $args = [] ) {
		// Better safe than sorry.
		$this->coinbase = self::sanitize( $coinbase );

		if ( ! $this->coinbase ) {
			throw new \WP_Error( 'ethpress', __( 'Bad address.', 'ethpress' ) );
		}
		if ( empty( $args['user'] ) ) {
			$this->attach_owner();
		} else {
			$this->user = $args['user'];
		}

		// if ( isset( $args['id'] ) ) {
		// 	$this->ID = $args['id'];
		// }
	}

    public static function have_db_users() {
        // check table count and write to options.
        $options = get_site_option( 'ethpress' );
        // Logger::log("Address::have_db_users: options0 = " . print_r($options, true));
		if ( isset( $options['have_db_users'] ) ) {
            return boolval($options['have_db_users']);
		}

        global $wpdb;
        $table = $wpdb->base_prefix . Plugin::$tables['addresses'];

		// phpcs:ignore -- cache.
		$res = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore -- table name
				"SELECT COUNT(*) FROM $table"
			)
		);
        Logger::log("Address::have_db_users: res = " . print_r($res, true));
        $res = intval($res) > 0;
        $options['have_db_users'] = intval($res);
        update_site_option( 'ethpress', $options );
        // Logger::log("Address::have_db_users: options1 = " . print_r($options, true));

        return $res;
    }

	/**
	 * Sets owner of address.
	 *
	 * @since 0.1.0
	 */
	private function attach_owner() {
        $this->user = $this->get_user();
	}

	/**
	 * Creates an address row for this user.
	 *
	 * @since 0.1.0
	 * @since 0.7.0 Returns false or insert_id.
	 *
	 * @return (int|boolean) insert_id or false.
	 */
	public function create() {
		if ( ! $this->user ) {
			$this->attach_owner();
		}
		if ( ! $this->user ) {
			return new \WP_Error( 'ethpress', __( 'No user for this address exists.', 'ethpress' ) );
		}
        update_user_meta($this->user->ID, "ethpress", $this->coinbase);
	}

	/**
	 * Deletes this address from the database.
	 *
	 * @since 0.1.0
	 *
	 * @return (int|false) # of rows, false on error.
	 */
	public function delete() {
        $user = $this->get_user();
        if (self::have_db_users()) {
            // backwards compatibility
            $where  = [];
    		$format = [];
            if ( ! empty( $user ) ) {
    			$where['user_id'] = $user->ID;
    			$format[]         = '%d';
    		}
            $coinbase = $this->coinbase;
    		if ( isset( $coinbase ) ) {
    			$where['name'] = $coinbase;
    			$format[]      = '%s';
    		}

    		// $id = $this->ID;
    		// if ( isset( $id ) ) {
    		// 	$where['id'] = $id;
    		// 	$format[]    = '%d';
    		// }

    		if ( !empty( $where ) ) {
                global $wpdb;

        		// phpcs:ignore -- ok.
        		$wpdb->delete(
        			$wpdb->base_prefix . Plugin::$tables['addresses'],
        			$where,
        			$format
        		);
    		}
        }

		if ( ! empty( $user ) ) {
            delete_user_meta($user->ID, "ethpress");
		}
	}

    public static function find_by_address( $address ) {
        global $wpdb;
        $user = null;
        if (self::have_db_users()) {
            // backwards compatibility
            $table = $wpdb->base_prefix . Plugin::$tables['addresses'];

            // phpcs:ignore -- ok.
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    // phpcs:ignore -- table name.
                    "SELECT id, user_id FROM {$table}
                    WHERE name = %s
                    LIMIT 1",
                    $address
                )
            );

            // When this function is called before user is created, row is null.
            if ( null !== $row ) {
                $user0 = get_user_by( 'ID', $row->user_id );
                if ( ! is_wp_error( $user0 ) ) {
                    $user = $user0;
                }
                // $id = $row->id;
                // if ( isset( $id ) ) {
                // 	$this->ID = $id;
                // }
            }
        }
        if (is_null($user)) {
            // @see https://stackoverflow.com/a/16039508/4256005
            $user0 = reset(
                get_users(
                    [
                        'meta_key' => "ethpress",
                        'meta_value' => $address,
                        'number' => 1,
                    ]
                )
            );
            if (false === $user0) {
                // backwards compatibility 2
                $user0 = get_user_by( 'login', $address );
            }
            if ( $user0 ) {
                $user = $user0;
            }
        }
        return $user;
    }

	/**
	 * Finds user's address.
	 *
	 * @since 0.1.0
	 *
	 * @param int $user_id User id.
	 * @return (Address|\WP_Error) Address if found, \WP_Error otherwise.
	 */
	public static function find_by_user( $user_id ) {
        $addr = get_user_meta($user_id, "ethpress", true);
        if (empty($addr) && self::have_db_users()) {
            // backwards compatibility
    		global $wpdb;

    		$table = $wpdb->base_prefix . Plugin::$tables['addresses'];
    		// phpcs:ignore -- cache.
    		$row   = $wpdb->get_row(
    			$wpdb->prepare(
    				// phpcs:ignore -- table name.
    				"SELECT id, name FROM {$table}
    				WHERE user_id = %d
    				LIMIT 1",
    				$user_id
    			)
    		);
    		if ( empty( $row ) ) {
    			return new \WP_Error( 'ethpress', __( 'No matching address.', 'ethpress' ) );
    		}

    		// $id   = $row->id;
    		$addr = $row->name;
        }
        if ( empty( $addr ) ) {
            return new \WP_Error( 'ethpress', __( 'No matching address.', 'ethpress' ) );
        }
        try {
            $user = get_user_by( 'ID', $user_id );
            $addr = new Address(
                $addr,
                compact( /*'id', */'user' )
            );
        } catch ( \WP_Error $error ) {
            $addr = $error;
        }
        return $addr;
	}

	/**
	 * Logs in user associated with the address.
	 *
	 * @since 0.1.0
	 *
	 * @return (WP_User|\WP_Error) WP_User on success, \WP_Error on failure.
	 */
	public function log_in() {
		if ( ! $this->user ) {
			return new \WP_Error( 'ethpress', __( 'You have not registered on this site; we cannot log you in', 'ethpress' ) );
		}

		clean_user_cache( $this->user->ID );
		wp_clear_auth_cookie();

		wp_set_current_user( $this->user->ID );
		wp_set_auth_cookie( $this->user->ID, false );
		update_user_caches( $this->user );

		// phpcs:ignore -- WordPress action.
		do_action( 'wp_login', $this->user->data->user_login, $this->user );
		return $this->user;
	}

	/**
	 * Registers address user.
	 *
	 * @since 0.1.0
	 *
	 * @return (WP_User|\WP_Error) WP_User on success, \WP_Error or false on failure.
	 */
	public function register() {
		$userlogin_max_length = 60;

		$user_login = substr( $this->coinbase, 0, $userlogin_max_length );
		$user_login = trim( $user_login );
		if ( empty( $user_login ) ) {
			return new \WP_Error( 'ethpress', __( 'Empty username.', 'ethpress' ) );
		}
        $existing_user = self::find_by_address( $user_login );

		if ( !is_null($existing_user) ) {
			return new \WP_Error( 'ethpress', __( 'Username already exists.', 'ethpress' ) );
		}

		if ( is_multisite() ) {
			// Is this obsolete or not???
			// https://codex.wordpress.org/WPMU_Functions says it is?
			// But then, the new REST api uses it. What is going on?
			$user_id = wpmu_create_user( $user_login, wp_generate_password(), '' );
			if ( ! $user_id ) {
				return new \WP_Error( 'ethpress', __( 'Error during creation', 'ethpress' ) );
			}
		} else {
			$user_id = wp_create_user( $user_login, wp_generate_password() );
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}
		}
		$this->user = get_user_by( 'ID', $user_id );

		$this->create();

		return $this->user;
	}

	/**
	 * Registers, if address doesn't exist, and logs in.
	 *
	 * @since 0.1.0
	 *
	 * @return (WP_User|WP_Error) WP_User on success, WP_Error on error.
	 */
	public function register_and_log_in() {
		if ( ! $this->user ) {
			$user = $this->register();
		}
		if ( ! is_wp_error( $user ) ) {
			$user = $this->log_in();
		}
		return $user;
	}

	/**
	 * Helps remain consistent in sanitizing addresses.
	 *
	 * TODO: need to make sure this doesn't go around changing the addresses. also need to make a validate() function that can take eth and bch and etc. addresses.
	 *
	 * @since 0.1.0
	 *
	 * @param string $coinbase Address.
	 * @return string Sanitized address.
	 */
	public static function sanitize( $coinbase ) {
		$coinbase = sanitize_text_field( (string) $coinbase );
		return $coinbase;
	}

}
