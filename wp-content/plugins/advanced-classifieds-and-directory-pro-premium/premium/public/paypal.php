<?php

/**
 * PayPal Payment Gateway.
 *
 * @link    https://pluginsware.com
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Public_PayPal class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Public_PayPal {

	/**
     * If true, the paypal sandbox URI www.sandbox.paypal.com is used for the
     * post back. If false, the live URI www.paypal.com is used. Default false.
     *
	 * @since  1.6.4
	 * @access private
	 * @var    bool
     */
	private $use_sandbox = false;
	
	/**
	 * Get things started.
	 *
	 * @since 1.6.4
	 */
	public function __construct() {		
		$gateway_settings = get_option( 'acadp_gateway_settings' );
		$this->use_sandbox = empty( $gateway_settings['test_mode'] ) ? false : true;	
	}
	
	/**
	 * Parse request to find correct WordPress query.
	 *
	 * @since 1.6.4
	 * @param WP_Query $wp WordPress Query object.
	 */
	public function parse_request( $wp ) {	
		if ( array_key_exists( 'acadp_action', $wp->query_vars ) && 'paypal-ipn' == $wp->query_vars['acadp_action'] && array_key_exists( 'acadp_order', $wp->query_vars ) ) {
			$this->process_payment_ipn( $wp->query_vars['acadp_order'] );
			exit();
    	}		
	}

	/**
	 * Process PayPal Payment.
	 *
	 * @since 1.6.4
	 * @param int   $order_id Order ID.
	 */
	public function process_payment( $order_id ) {
		$paypal_settings = get_option( 'acadp_gateway_paypal_settings' );
		$page_settings   = get_option( 'acadp_page_settings' );
		
		$currency   = acadp_get_payment_currency();		
		$listing_id = get_post_meta( $order_id, 'listing_id', true );
		$amount     = get_post_meta( $order_id, 'amount', true );
		
		$paypal_host = $this->use_sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';
		?>		
        <p><?php _e( 'Redirecting to paypal.com, please wait...', 'advanced-classifieds-and-directory-pro' ); ?></p>
        
		<form id="acadp-paypal-form" name="acadp-paypal-form" action="https://<?php echo $paypal_host; ?>/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="custom" value="<?php echo esc_attr( $listing_id ); ?>">
			<input type="hidden" name="business" value="<?php echo esc_attr( $paypal_settings['email'] ); ?>">
			<input type="hidden" name="currency_code" value="<?php echo esc_attr( $currency ); ?>">
			<input type="hidden" name="item_name" value="<?php echo wp_strip_all_tags( get_the_title( $listing_id ) ); ?>">
			<input type="hidden" name="item_number" value="<?php echo esc_attr( $order_id ); ?>">
			<input type="hidden" name="amount" value="<?php echo esc_attr( $amount ); ?>">	
			<input type="hidden" name="cancel_return" value="<?php echo acadp_get_failure_page_link(); ?>">
			<input type="hidden" name="notify_url" value="<?php echo $this->get_notify_page_link( $order_id ); ?>">
			<input type="hidden" name="return" value="<?php echo acadp_get_payment_receipt_page_link( $order_id ); ?>">
		</form>
        
		<script type="text/javascript">
			document.getElementById( 'acadp-paypal-form' ).submit();
        </script>
		<?php		
	}

	/**
 	 * Generate a permalink for IPN notification.
 	 *
 	 * @since  1.6.4
 	 * @param  int    $order_id Order ID.
 	 * @return string           Notify page link.
 	*/
	 public function get_notify_page_link( $order_id ) {
		$page_settings = get_option( 'acadp_page_settings' );
	
		$link = '';
	
		if ( $page_settings['checkout'] > 0 ) {
			$link = esc_url_raw( get_permalink( $page_settings['checkout'] ) );	
		
			if ( '' != get_option( 'permalink_structure' ) ) {
    			$link = user_trailingslashit( trailingslashit( $link ) . 'paypal-ipn/' . $order_id );
  			} else {
    			$link = add_query_arg( array( 'acadp_action' => 'paypal-ipn', 'acadp_order' => $order_id ), $link );
  			}
		}
  
		return $link;
	}

	/**
	 * Process PayPal IPN.
	 *
	 * @since 1.6.4
	 * @param int   $order_id Order ID.
	 */
	private function process_payment_ipn( $order_id ) {	
		if ( ! class_exists( 'IpnListener' ) ) {
			require_once ACADP_PLUGIN_DIR . 'premium/vendor/paypal/ipnlistener.php';
		}

		$listener = new IpnListener();
		$listener->use_sandbox = $this->use_sandbox;
		
		$error = 0;	
		
		try {
    		$listener->requirePostMethod();
    		$verified = $listener->processIpn();
		} catch ( Exception $e ) {
			$this->write_error_log( $e->getMessage() );
    		exit( 0 );
		}
		
		if ( $verified ) {		
			$paypal_settings = get_option( 'acadp_gateway_paypal_settings' );
			
			$currency       = acadp_get_payment_currency();
			$transaction_id = get_post_meta( $order_id, 'transaction_id', true );
			$amount         = get_post_meta( $order_id, 'amount', true );

			if ( $_POST['receiver_email'] != $paypal_settings['email'] ) {			
				$this->write_error_log( 'Email mismatch : ' . $_POST['receiver_email'] );
				++$error;
    		}
			
			if ( $_POST['mc_gross'] != $amount ) {	
				$this->write_error_log( 'Amount mismatch : '.$_POST['mc_gross'] );
				++$error;
    		}
			
			if ( $_POST['mc_currency'] != $currency ) {
				$this->write_error_log( 'Currency mismatch : ' . $_POST['mc_currency'] );
				++$error;
    		}
			
			if ( $_POST['txn_id'] == $transaction_id ) {
				$this->write_error_log( 'Duplicate Transaction : '.$_POST['txn_id'] );
				++$error;
    		}
			
			if ( ! $error ) {			
				$status = strtolower( $_POST['payment_status'] );

				if ( 'completed' == $status || ( $this->use_sandbox && 'pending' == $status ) ) {
					acadp_order_completed( array( 'id' => $order_id, 'transaction_id' => $_POST['txn_id'] ) );
				}				
			}		
		}		
	}

	/**
	 * Write error log.
	 *
	 * @since 1.6.4
	 * @param string $message Error message to log.
	 */
	private function write_error_log( $message ) {
		if ( $this->use_sandbox ) error_log( $message );		
	}

}
