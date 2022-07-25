<?php

/**
 * Plugin Name: EthPress
 * Plugin URI: https://wordpress.org/plugins/ethpress/
 * Description: Ethereum Web3 login. Enable crypto wallet logins to WordPress.
 * Author: Lynn (lynn.mvp at tutanota dot com), ethereumicoio
 * Version: 1.5.4
 * Author URI: https://ethereumico.io
 * Text Domain: ethpress
 * Domain Path: /languages
 *
 * @package ethpress
 */
namespace losnappas\Ethpress;

defined( 'ABSPATH' ) || die;

if ( function_exists( '\\losnappas\\Ethpress\\ethpress_fs' ) ) {
    \losnappas\Ethpress\ethpress_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'ethpress_fs' ) ) {
        // Create a helper function for easy SDK access.
        function ethpress_fs()
        {
            global  $ethpress_fs ;
            
            if ( !isset( $ethpress_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_9248_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_9248_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $ethpress_fs = fs_dynamic_init( array(
                    'id'              => '9248',
                    'slug'            => 'ethpress',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_45cc0f7a099a59d2117d9fb313d01',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Professional',
                    'has_addons'      => true,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                    'has_affiliation' => 'all',
                    'menu'            => array(
                    'slug'   => 'ethpress',
                    'parent' => array(
                    'slug' => 'options-general.php',
                ),
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $ethpress_fs;
        }
        
        // Init Freemius.
        \losnappas\Ethpress\ethpress_fs();
        // Signal that SDK was initiated.
        do_action( 'ethpress_fs_loaded' );
    }
    
    // ... Your plugin's main file logic ...
    require_once 'vendor/autoload.php';
    // use losnappas\Ethpress\Plugin;
    define( 'ETHPRESS_FILE', __FILE__ );
    define( 'ETHPRESS_NS', __NAMESPACE__ );
    define( 'ETHPRESS_PHP_MIN_VER', '5.4.0' );
    define( 'ETHPRESS_WP_MIN_VER', '4.6.0' );
    
    if ( version_compare( get_bloginfo( 'version' ), ETHPRESS_WP_MIN_VER, '<' ) || version_compare( PHP_VERSION, ETHPRESS_PHP_MIN_VER, '<' ) ) {
        /**
         * Displays notification.
         */
        function ethpress_compatability_warning()
        {
            echo  '<div class="error"><p>' . esc_html( sprintf(
                /* translators: version numbers. */
                __( '“%1$s” requires PHP %2$s (or newer) and WordPress %3$s (or newer) to function properly. Your site is using PHP %4$s and WordPress %5$s. Please upgrade. The plugin has been automatically deactivated.', 'ethpress' ),
                'EthPress',
                ETHPRESS_PHP_MIN_VER,
                ETHPRESS_WP_MIN_VER,
                PHP_VERSION,
                $GLOBALS['wp_version']
            ) ) . '</p></div>' ;
            // phpcs:ignore -- no nonces here.
            if ( isset( $_GET['activate'] ) ) {
                // phpcs:ignore -- no nonces here.
                unset( $_GET['activate'] );
            }
        }
        
        add_action( 'admin_notices', ETHPRESS_NS . '\\ethpress_compatability_warning' );
        /**
         * Deactivates.
         */
        function ethpress_deactivate_self()
        {
            deactivate_plugins( plugin_basename( ETHPRESS_FILE ) );
        }
        
        add_action( 'admin_init', ETHPRESS_NS . '\\ethpress_deactivate_self' );
        return;
    } else {
        function ethpress_fs_uninstall_cleanup()
        {
            \losnappas\Ethpress\Plugin::uninstall();
        }
        
        // Not like register_uninstall_hook(), you do NOT have to use a static function.
        \losnappas\Ethpress\ethpress_fs()->add_action( 'after_uninstall', 'ethpress_fs_uninstall_cleanup' );
        register_activation_hook( __FILE__, [ ETHPRESS_NS . '\\Plugin', 'activate' ] );
        \losnappas\Ethpress\Plugin::attach_hooks();
    }

}
