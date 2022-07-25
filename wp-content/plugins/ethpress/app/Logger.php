<?php
/**
 * Has functions related to logging.
 *
 * @package ethpress
 */

namespace losnappas\Ethpress;

defined( 'ABSPATH' ) || die;

class _EthPress_Logger {

    /**
     * Add a log entry.
     *
     * This is not the preferred method for adding log messages. Please use log() or any one of
     * the level methods (debug(), info(), etc.). This method may be deprecated in the future.
     *
     * @param string $handle
     * @param string $message
     * @param string $level
     *
     * @see https://docs.woocommerce.com/wc-apidocs/source-class-WC_Logger.html#105
     *
     * @return bool
     */
    public function add( $handle, $message, $level = 'unused' ) {
        error_log($handle . ': ' . $message);
        return true;
    }
}

/**
 * Output logs
 *
 * @since 1.4.0
 *
 * Output logs to WooCommerce log file, or to a standard error.log file if the WooCommerce is not installed.
 */
class Logger
{
    private static $logger = false;

    public static function log($error) {
        // Create a logger instance if we don't already have one.
        if ( false === self::$logger ) {
            /**
             * Check if WooCommerce is active
             * https://wordpress.stackexchange.com/a/193908/137915
             **/
            if (
              in_array(
                'woocommerce/woocommerce.php',
                apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
              ) && class_exists("\WC_Logger", false)
            ) {
                self::$logger = new \WC_Logger();
            } else {
                self::$logger = new _EthPress_Logger();
            }
        }
        self::$logger->add( 'ethpress', $error );
    }
}
