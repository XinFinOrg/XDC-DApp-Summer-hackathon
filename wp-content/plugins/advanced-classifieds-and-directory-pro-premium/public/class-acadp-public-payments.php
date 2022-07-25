<?php

/**
 * Payments
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Public_Payments Class.
 *
 * @since 1.0.0
 */
class ACADP_Public_Payments {
	
	/**
	 * Get things going.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Register shortcodes used by the payments page
		add_shortcode( "acadp_checkout", array( $this, "run_shortcode_checkout" ) );
		add_shortcode( "acadp_payment_errors", array( $this, "run_shortcode_payment_errors" ) );
		add_shortcode( "acadp_payment_receipt", array( $this, "run_shortcode_payment_receipt" ) );
		add_shortcode( "acadp_payment_history", array( $this, "run_shortcode_payment_history" ) );
	} 
	
	/**
	 * Process the shortcode [acadp_checkout].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_checkout() {	
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();		
		}
		
		$shortcode = 'acadp_checkout';
		
		$post_id = get_query_var( 'acadp_listing' );

		//Mel:
		$hash = get_post_meta($post_id, 'hash', true);
		$ipfs_metadata_cid = get_post_meta($post_id, 'ipfs_metadata_cid', true);
		$contract_address = get_user_meta(get_current_user_id(), 'contract_address', true);
		$wallet_address = get_user_meta(get_current_user_id(), 'wallet_address', true);

		if ( !empty($ipfs_metadata_cid) ) {
			//The IPFS CID in token URI contains a metadata.json file. We need to attach the file to the end
			$token_uri = esc_url("https://ipfs.io/ipfs/" . $ipfs_metadata_cid . "/metadata.json");
		}

		if ( empty($contract_address) ) {
			return __( 'You need a smart contract to proceed.', 'advanced-classifieds-and-directory-pro' );
		}

		//Comment out cos now we have private file that doesn't have IPFS URL
		// if ( empty($ipfs_metadata_cid) ) {
		// 	return __( 'You need to upload a file to proceed.', 'advanced-classifieds-and-directory-pro' );
		// }
		
		if ( ! empty( $post_id ) && 'acadp_listings' == get_post_type( $post_id ) ) {			
			if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['acadp_checkout_nonce'] ) && wp_verify_nonce( $_POST['acadp_checkout_nonce'], 'acadp_process_payment' ) ) {									
				$this->place_order();				
			} else {	
				$options = apply_filters( 'acadp_checkout_form_data', array(), $post_id );
				
				$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
				if ( ! empty( $featured_listing_settings['enabled'] ) ) {
					$options[] = array(
						'type'  => 'header',
						'label' => $featured_listing_settings['label']
					);
					
					$options[] = array( 
						'type'        => 'radio',	//Mel: 24/12/21. To use radio button in the switch case at acadp-public-checkout-display.php
						//'type'        => 'checkbox', 
						'name'        => 'featured',
						'value'       => 1,
						'selected'    => 1,
						'description' => $featured_listing_settings['description'],
						'price'       => $featured_listing_settings['price']
					);
				}
				
				// Enqueue style dependencies
				wp_enqueue_style( ACADP_PLUGIN_NAME );
		
				// Enqueue script dependencies
				wp_enqueue_script( ACADP_PLUGIN_NAME );
				
				// Hook for developers
				do_action( 'acadp_before_checkout_form' );
			
				// ...	
				ob_start();
				include( acadp_get_template( "payments/acadp-public-checkout-display.php" ) );
				return ob_get_clean();			
			}			
		} else {		
			return '<span>' . __( 'Sorry, something went wrong.', 'advanced-classifieds-and-directory-pro' ) . '</span>';			
		}	
	}
	
	/**
 	 * Display formatted amount.
     *
     * @since 1.0.0
     */
	public function ajax_callback_format_total_amount() {	
		check_ajax_referer( 'acadp_ajax_nonce', 'security' );
		
		if ( isset( $_POST['amount'] ) ) {	
			echo acadp_format_payment_amount( sanitize_text_field( $_POST['amount'] ) );					
		}
									
		wp_die();		
	}
	
	/**
	 * Create Orders. Send emails to site and listing owners
	 * when order placed.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function place_order() {
		$post_id = (int) $_POST['post_id'];
		
		//Mel: 28/12/21. Grab the amount from checkout form. Amount can be more than 0 or 0.001, normally in gwei
		$amount = (float) $_POST['amount'];

		$file_hash = isset( $_POST['file_hash'] ) ? sanitize_text_field($_POST['file_hash']) : '';
		$token_uri = isset( $_POST['token_uri'] ) ? sanitize_text_field($_POST['token_uri']) : '';
		$tx_hash = isset( $_POST['tx_hash'] ) ? sanitize_text_field($_POST['tx_hash']) : '';
		$gas_used = isset( $_POST['gas_used'] ) ? sanitize_text_field($_POST['gas_used']) : '';
		$block_number = isset( $_POST['block_number'] ) ? sanitize_text_field($_POST['block_number']) : '';
		$user_wallet = isset( $_POST['user_wallet'] ) ? sanitize_text_field($_POST['user_wallet']) : '';
		$contract_address = isset( $_POST['contract_address'] ) ? sanitize_text_field($_POST['contract_address']) : '';
		
		// place order
		$new_order = array(
			'post_title'   => sprintf( __( '[Order] Listing #%d' ), $post_id ),
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_type'	   => 'acadp_payments'
		);
		
		$order_id = wp_insert_post( $new_order );
		
		if ( $order_id ) {		
			// save meta fields			
			update_post_meta( $order_id, 'listing_id', $post_id );

			do_action( 'acadp_order_created', $order_id );
			
			$order_details = apply_filters( 'acadp_order_details', array(), $order_id );
			
			if ( isset( $_POST['featured'] ) ) {
				update_post_meta( $order_id, 'featured', 1 );
				$order_details[] = get_option( 'acadp_featured_listing_settings' );
			}
			
			update_post_meta( $order_id, 'amount', $amount );			
			
			$gateway = ! empty( $amount ) ? sanitize_key( $_POST['payment_gateway'] ) : 'free';
			update_post_meta( $order_id, 'payment_gateway', $gateway );
			
			update_post_meta( $order_id, 'payment_status', 'created' );

			// send email to site admin after order placed successfully
			acadp_email_admin_order_created( $post_id, $order_id );

			// process payment
			if ( $amount > 0 ) {			
				if ( 'offline' == $gateway ) {
					update_post_meta( $order_id, 'transaction_id', wp_generate_password( 12, false ) );
				
					acadp_email_listing_owner_order_created_offline( $post_id, $order_id );
			
					$redirect_url = acadp_get_payment_receipt_page_link( $order_id );
					wp_redirect( $redirect_url );
				} else {
					//Mel: 27/01/22
					//acadp_email_listing_owner_order_created( $post_id, $order_id );
				
					// executes the action hook named 'acadp_process_payment'
					//do_action( 'acadp_process_'.$gateway.'_payment', $order_id );	//Mel: 27/01/22
					
					//Mel: 27/01/22
					acadp_order_completed( array( 'id' => $order_id, 'transaction_id' => $tx_hash, 'user_wallet' => $user_wallet, 'token_uri' => $token_uri, 'gas_used' => $gas_used, 'block_number' => $block_number, 'contract_address' => $contract_address, 'file_hash' => $file_hash ) );

					//Mel: 27/01/22. To finally display minting receipt page
					$redirect_url = acadp_get_payment_receipt_page_link( $order_id );
					wp_redirect( $redirect_url );	

				}				
			} else {			
				acadp_email_listing_owner_order_created( $post_id, $order_id );
				
				acadp_order_completed( array( 'id' => $order_id, 'transaction_id' => wp_generate_password( 12, false ) ) );
				
				$redirect_url = acadp_get_payment_receipt_page_link( $order_id );
				wp_redirect( $redirect_url );					
			}
			
			exit();			
		}		
	}
	
	/**
	 * Process the shortcode [acadp_payment_errors].
	 *
	 * @since 1.4.1
	 * @param array  $atts    An associative array of attributes.
	 * @param string $content Content to display.
	 */
	public function run_shortcode_payment_errors( $atts, $content = '' ) {	
		if ( $order_id = get_query_var('acadp_order') ) {
			if ( $error = get_transient( "acadp_payment_errors_{$order_id}" ) ) {
				$content = $error;
    			delete_transient( "acadp_payment_errors_{$order_id}" );
			}			
		}
		
		return $content;	
	}
	
	/**
	 * Process the shortcode [acadp_payment_receipt].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_payment_receipt() {	
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}
		
		$shortcode = 'acadp_payment_receipt';
		
		if ( $order_id = get_query_var('acadp_order') ) {
			$featured_listing_settings = get_option( 'acadp_featured_listing_settings' );
			
			// Enqueue style dependencies
			wp_enqueue_style( ACADP_PLUGIN_NAME );
			
			// ...
			$order = get_post( $order_id );
			$post_meta = get_post_meta( $order_id );

			$order_details = apply_filters( 'acadp_order_details', array(), $order_id );

			if ( ! empty( $featured_listing_settings['enabled'] ) && isset( $post_meta['featured'] ) ) {
				$order_details[] = $featured_listing_settings;
			}
			
			ob_start();
			include( acadp_get_template( "payments/acadp-public-payment-receipt-display.php" ) );
			return ob_get_clean();		
		} else {		
			return '<span>' . __( 'Sorry, something went wrong.', 'advanced-classifieds-and-directory-pro' ) . '</span>';			
		}	
	}
	
	/**
	 * Process the shortcode [acadp_payment_history].
	 *
	 * @since 1.0.0
	 */
	public function run_shortcode_payment_history() {	
		if ( ! is_user_logged_in() ) {		
			return acadp_login_form();			
		}
		
		if ( ! acadp_current_user_can('edit_acadp_listings') ) {
			return '<span>' . __( 'You do not have sufficient permissions to access this page.', 'advanced-classifieds-and-directory-pro' ) . '</span>';
		}
		
		$shortcode = 'acadp_payment_history';
		
		$listings_settings = get_option( 'acadp_listings_settings' );

		// Enqueue style dependencies
		wp_enqueue_style( ACADP_PLUGIN_NAME );

		// Define the query
		$paged = acadp_get_page_number();
			
		$args = array(				
			'post_type'      => 'acadp_payments',
			'posts_per_page' => isset( $listings_settings['listings_per_page'] ) ? $listings_settings['listings_per_page'] : 10,
			'paged'          => $paged,
			'author'         => get_current_user_id(),
	  	);
			
		$acadp_query = new WP_Query( $args );
		
		// Start the Loop
		global $post;
			
		// Process output
		if ( $acadp_query->have_posts() ) {		
			ob_start();
			include( acadp_get_template( "payments/acadp-public-payment-history-display.php" ) );
			wp_reset_postdata(); // Use reset postdata to restore orginal query
			return ob_get_clean();		
		} else {		
			return '<span>' . __( 'No Results Found.', 'advanced-classifieds-and-directory-pro' ) . '</span>';		
		}			
	}
		
}
