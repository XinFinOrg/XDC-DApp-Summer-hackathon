<?php

/**
 * Has extras for signatures (message is signed -> signature).
 *
 * @since 0.1.0
 * @package ethpress
 */
namespace losnappas\Ethpress;

use  losnappas\Ethpress\Logger ;
use  losnappas\Ethpress\Dependencies\kornrunner\Keccak ;
use  losnappas\Ethpress\Dependencies\Elliptic\EC ;
defined( 'ABSPATH' ) || die;
/**
 * Contains utility functions for signed messages.
 *
 * @since 0.1.0
 */
class Signature
{
    /**
     * Extracts address with PHP. Requires one of php-gmp or php-bcmath extensions.
     *
     * From here: https://github.com/digitaldonkey/ecverify/blob/master/src/EcRecover.php .
     * Only difference is '0x' not prepended to `$message_hash`.
     * That might be a bug on that side..?
     *
     * @since 0.6.0
     *
     * @param string $message   Message that was signed.
     * @param string $signature Matching signature.
     * @return (string|false)   Address or false.
     */
    public static function extract_address_with_php( $message, $signature )
    {
        $message = self::personal_sign_add_header( $message );
        $message_hash = Keccak::hash( $message, 256 );
        $ec = new EC( 'secp256k1' );
        $sign = [
            'r' => substr( $signature, 2, 64 ),
            's' => substr( $signature, 66, 64 ),
        ];
        // fix for the coincircle wallet
        $v = ord( hex2bin( substr( $signature, 130, 2 ) ) );
        $offset = 0;
        if ( $v >= 27 ) {
            $offset = 27;
        }
        $recid = $v - $offset;
        $pub_key = $ec->recoverPubKey( $message_hash, $sign, $recid );
        $recovered_address = '0x' . substr( Keccak::hash( substr( hex2bin( $pub_key->encode( 'hex' ) ), 1 ), 256 ), 24 );
        return $recovered_address;
    }
    
    /**
     * Gets public address from signature. TODO check for failure.
     *
     * @since 0.1.0
     *
     * @param string $message   Message that was signed.
     * @param string $signature Matching signature.
     * @return (string|false)   The response data or false.
     */
    public static function extract_address_with_api( $message, $signature )
    {
        $defopts = [
            'api_url' => '',
        ];
        $options = \get_site_option( 'ethpress', $defopts );
        $api_url = null;
        
        if ( is_null( $api_url ) ) {
            
            if ( 'https://api.ethereumico.io/ethpress' === $options['api_url'] ) {
                Logger::log( "extract_address_with_api failed for bad api_url" );
                return false;
            }
            
            $api_url = \esc_url_raw( $options['api_url'] );
        }
        
        Logger::log( "extract_address_with_api for {$message}, {$signature}: api_url = {$api_url}" );
        $data = [
            'message'   => $message,
            'signature' => $signature,
        ];
        $args = [
            'body'    => json_encode( $data ),
            'timeout' => 45,
            'method'  => 'POST',
            'headers' => [
            'Content-type' => 'application/json',
        ],
        ];
        // $response = \wp_safe_remote_post(
        $response = \wp_remote_post( $api_url, $args );
        $body = \wp_remote_retrieve_body( $response );
        
        if ( !empty($body) ) {
            Logger::log( "extract_address_with_api for {$message}, {$signature}: res = {$body}" );
            $body = json_decode( $body );
            return $body->data;
        } else {
            Logger::log( "extract_address_with_api failed for {$message}, {$signature}: res is empty for {$api_url} and args=" . print_r( $args, true ) );
            return false;
        }
    
    }
    
    /**
     * Verifies a signature.
     *
     * @since 0.1.0
     *
     * @param string $message   Message that was signed.
     * @param string $signature Matching signature.
     * @param string $coinbase  Public address used to sign the signature.
     * @return bool
     */
    public static function verify( $message, $signature, $coinbase )
    {
        $address = null;
        
        if ( empty($signature) || empty($coinbase) || !is_string( $signature ) || !is_string( $coinbase ) ) {
            Logger::log( "verify failed for: {$message}, {$signature}, {$coinbase}" );
            return false;
        }
        
        
        if ( self::_check_ecrecovers_with_php() ) {
            $address = self::extract_address_with_php( $message, $signature );
        } else {
            $address = self::extract_address_with_api( $message, $signature );
        }
        
        
        if ( !is_string( $address ) ) {
            Logger::log( "verify failed for: {$message}, {$signature}, {$coinbase}: address is not a string" );
            return false;
        }
        
        if ( strtolower( $coinbase ) !== strtolower( $address ) ) {
            Logger::log( "verify failed for: {$message}, {$signature}, {$coinbase}, {$address}: address !== coinbase" );
        }
        return strtolower( $coinbase ) === strtolower( $address );
    }
    
    protected static function _check_ecrecovers_with_php()
    {
        $ecrecovers_with_php = extension_loaded( 'gmp' ) || extension_loaded( 'bcmath' );
        return $ecrecovers_with_php;
    }
    
    /**
     * Ethereum personal_sign message header.
     *
     * @since 0.6.0
     *
     * @param string $message Message to be prefixed.
     *
     * @return string Prefixed message.
     */
    public static function personal_sign_add_header( $message )
    {
        return "\31Ethereum Signed Message:\n" . strlen( $message ) . $message;
    }

}