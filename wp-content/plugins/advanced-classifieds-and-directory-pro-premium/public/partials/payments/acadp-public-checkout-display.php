<?php

/**Mel: 27/01/22
 * This template displays the token minting confirmation page.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.5.3/ethers.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
<!--<script src="https://unpkg.com/moralis/dist/moralis.js"></script>-->
<!-- <script src="https://unpkg.com/@metamask/legacy-web3@latest/dist/metamask.web3.min.js"></script> -->
<script src="<?php echo ACADP_PLUGIN_URL; ?>public/js/web3-connector.js"></script>
<!--<script src="<?php echo ACADP_PLUGIN_URL; ?>public/js/web3-connector-moralis.js"></script>-->

<!-- Mel: 29/01/22. Modal to show you must use matching wallet -->
<div class="modal fade" id="walletMismatchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php esc_html_e( 'Wrong Wallet', 'advanced-classifieds-and-directory-pro' ); ?></h5>
      </div>
      <div class="modal-body">
        <?php esc_html_e( 'Please use the same wallet you used to create the contract.', 'advanced-classifieds-and-directory-pro' ); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!--Mel: End-->

<div class="acadp acadp-user acadp-checkout">
	<?php //acadp_status_messages(); //Mel: 29/01/22. Suppress listing published msg ?>

	<p><?php esc_html_e( 'Please review your order, and click Purchase once you are ready to proceed.', 'advanced-classifieds-and-directory-pro' ); ?></p>

    <form id="acadp-checkout-form" class="form-vertical" method="post" action="" role="form">
		<table id="acadp-checkout-form-data" class="table table-stripped table-bordered">
			<tr>
				<td><strong><?php echo get_the_title( $post_id ); ?></strong>
				</td>
			</tr>
			<tr>
				<td><?php echo get_the_content(null, false, $post_id); ?>
				</td>
			</tr>
			<tr>
				<td>
				
				<?php 
				if ( !empty($token_uri) ) {
					echo esc_html__( 'File :', 'advanced-classifieds-and-directory-pro' ) . ' <a href="' . $token_uri . '">' . esc_html__( 'View File', 'advanced-classifieds-and-directory-pro' ) . '</a>';
				} elseif ( !empty($hash) ) {
					echo esc_html__( 'File Hash :', 'advanced-classifieds-and-directory-pro' ) . ' ' . $hash;
				}
				?>
				
				<?php //esc_html_e( 'File URL :', 'advanced-classifieds-and-directory-pro' ); ?> <a href="<?php //echo esc_attr( $token_uri ); ?>"><?php //esc_html_e( 'View File', 'advanced-classifieds-and-directory-pro' ); ?></a>
				</td>
			</tr>
    	</table>
		
        <?php wp_nonce_field( 'acadp_process_payment', 'acadp_checkout_nonce' ); ?>
        <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>" />
		<input type="hidden" id="token-uri" name="token_uri" value="<?php echo esc_attr( $token_uri ); ?>" />
		<input type="hidden" id="file-hash" name="file_hash" value="<?php echo esc_attr( $hash ); ?>" />
		<input type="hidden" id="contract-address" name="contract_address" value="<?php echo esc_attr( $contract_address ); ?>" />
		<input type="hidden" id="user-wallet" name="user_wallet" value="<?php echo esc_attr( $wallet_address ); ?>"/>
		<input type="hidden" id="tx-hash" name="tx_hash" />
		<input type="hidden" id="block-number" name="block_number" />
		<input type="hidden" id="gas-used" name="gas_used" />
		<input type="hidden" id="amount" name="amount" />
		<input type="hidden" name="payment_gateway" value="XDC" />
		<input type="hidden" name="featured" value="1" />
		<span id="loading"></span>
		
        <div class="pull-right">
			<input type="submit" id="go-back-btn" class="btn btn-default" onclick="javascript:history.back()" value="<?php esc_attr_e( 'Not now', 'advanced-classifieds-and-directory-pro' ); ?>" />
        	<input type="submit" id="acadp-checkout-submit-btn" class="btn btn-primary" value="<?php esc_attr_e( 'Proceed to payment', 'advanced-classifieds-and-directory-pro' ); ?>" />
        </div>
    </form>
</div>
