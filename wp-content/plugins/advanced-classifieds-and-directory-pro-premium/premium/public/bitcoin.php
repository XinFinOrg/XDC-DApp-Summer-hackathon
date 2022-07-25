<?php

/**
 * Bitcoin Payment Gateway. Based on advanced-classifieds-and-directory-pro-premium\premium\public\paypal.php
 *
 * @author	Mel
 * @date	27/12/21
 * @link    
 * @since   1.6.4
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ACADP_Premium_Public_Bitcoin class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Public_Bitcoin {
	
	/**
	 * Get things started.
	 *
	 * @since 1.6.4
	 */
	public function __construct() {		
		$gateway_settings = get_option( 'acadp_gateway_settings' );
	}

	/**
	 * Process Bitcoin Payment.
	 *
	 * @since 1.6.4
	 * @param int   $order_id Order ID.
	 */
	public function process_payment( $order_id ) {
		$bitcoin_settings = get_option( 'acadp_gateway_bitcoin_settings' );
		$page_settings   = get_option( 'acadp_page_settings' );
		
		$currency   = 'BTC';		
		$listing_id = get_post_meta( $order_id, 'listing_id', true );
		$amount     = get_post_meta( $order_id, 'amount', true );
		
		?>		
        <br />
		<p class="text-center"><?php printf( esc_html__( 'Please send BTC %s to the wallet address below.', 'advanced-classifieds-and-directory-pro' ), $amount); ?></p>
        <br />
		<div class="text-center">
			<img alt="BTC address QR code" src="https://api.qrserver.com/v1/create-qr-code/?data=<?php echo esc_url( $bitcoin_settings['payment_address'] ); ?>&amp;size=200x200" width="200">
		</div>
		<div id="address" class="text-center">
			<strong><?php echo esc_html( $bitcoin_settings['payment_address'] ); ?></strong>
			<br />
			<button class="btn btn-outline-info btn-sm" onClick="copyText('address')">
				<?php esc_html_e( 'Copy Address', 'advanced-classifieds-and-directory-pro' ); ?>
			</button> <button class="btn btn-outline-info btn-sm text-center" onclick="window.open('https://blockchain.com/btc/address/<?php echo esc_html( $bitcoin_settings['payment_address'] ); ?>','_blank');" type="button">
				<?php esc_html_e( 'Check Explorer', 'advanced-classifieds-and-directory-pro' ); ?>
			</button>
		</div>
		<br />
		<div class="text-center">
			<form id="acadp-bitcoin-form" name="acadp-bitcoin-form" action="<?php echo acadp_get_payment_receipt_page_link( $order_id ); ?>" method="post">
				<div class="form-group row">
					<label><?php esc_html_e( 'Transaction Hash', 'advanced-classifieds-and-directory-pro' ); ?></label>
					<input type="text" name="txn_hash" placeholder="<?php esc_attr_e( 'Paste the hash here', 'advanced-classifieds-and-directory-pro' ); ?>" size="45" required>
				</div>
				<div class="form-group row">
					<label><?php esc_html_e( 'Your Wallet Address (optional)', 'advanced-classifieds-and-directory-pro' ); ?></label>
					<input type="text" name="user_wallet" placeholder="<?php esc_attr_e( 'Ethereum/Avalanche address to receive the NFT', 'advanced-classifieds-and-directory-pro' ); ?>" size="45">
				</div>
				<input type="hidden" name="listing_id" value="<?php echo esc_attr( $listing_id ); ?>">
				<input type="hidden" name="wallet_address" value="<?php echo esc_attr( $bitcoin_settings['payment_address'] ); ?>">
				<input type="hidden" name="currency_code" value="<?php echo esc_attr( $currency ); ?>">
				<input type="hidden" name="item_name" value="<?php echo wp_strip_all_tags( get_the_title( $listing_id ) ); ?>">
				<input type="hidden" name="item_number" value="<?php echo esc_attr( $order_id ); ?>">
				<input type="hidden" name="amount" value="<?php echo esc_attr( $amount ); ?>">	
				<br /><br />
				<div class="text-center">
					<button name="submit" type="submit" >
						<?php esc_html_e( 'Complete Process', 'advanced-classifieds-and-directory-pro' ); ?>
					</button>
				</div>
			</form>
		</div>
		<br /><br /><br />
        
		<script type="text/javascript">
			//To copy the wallet address to clipboard
			function copyText(element) {
				var range, selection, worked;
				element = document.getElementById(element); 

				if (document.body.createTextRange) {
					range = document.body.createTextRange();
					range.moveToElementText(element);
					range.select();
				} else if (window.getSelection) {
					selection = window.getSelection();        
					range = document.createRange();
					range.selectNodeContents(element);
					selection.removeAllRanges();
					selection.addRange(range);
				}
				
				try {
					document.execCommand('copy');
					alert('Wallet address copied');
				} catch (err) {
					alert('Error. Unable to copy wallet address');
				}
			}
        </script>
		<?php		
	}

}
