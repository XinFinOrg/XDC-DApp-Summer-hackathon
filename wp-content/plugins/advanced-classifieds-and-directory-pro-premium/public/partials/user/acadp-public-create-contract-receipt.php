<?php

/**
 * This template displays the status of smart contract creation.
 *
 * @author  Mel Wong
 * @date    25/01/22
 *
 */
?>

<div class="acadp acadp-user acadp-payment-receipt">
	<p><?php esc_html_e( 'Your smart contract is created', 'advanced-classifieds-and-directory-pro' ); ?></p>

    <p><input type="button" class="btn btn-primary" value="<?php esc_html_e( 'Create First NFT', 'advanced-classifieds-and-directory-pro' ); ?>" onclick="location.href='<?php echo esc_url( acadp_get_listing_form_page_link() ); ?>'"/></p>

    <?php
        $wallet_address = $_GET['wallet_address'] ? sanitize_text_field( $_GET['wallet_address'] ) : '';
        $contract_address = $_GET['contract_address'] ? sanitize_text_field( $_GET['contract_address'] ) : '';
        $contract_tx_hash = $_GET['contract_tx_hash'] ? sanitize_text_field( $_GET['contract_tx_hash'] ) : '';	
	?>

    <div class="row d-flex justify-content-center">
    	<div class="col-md-6">
   			<table class="table table-bordered table-striped">
               <tr>
    				<td><?php esc_html_e( 'Your Smart Contract Address', 'advanced-classifieds-and-directory-pro' ); ?></td>
        		</tr>
        		<tr>
    				<td><?php echo esc_html($contract_address); ?></td>
        		</tr>
        		<tr>
    				<td><?php esc_html_e( 'Smart Contract Transaction Hash', 'advanced-classifieds-and-directory-pro' ); ?></td>
        		</tr>
                <tr>
    				<td style="word-wrap: break-word;min-width: 160px;max-width: 160px;"><?php echo esc_html($contract_tx_hash); ?></td>
        		</tr>
                <tr>
    				<td><?php esc_html_e( 'Wallet Address', 'advanced-classifieds-and-directory-pro' ); ?></td>
        		</tr>
                <tr>
    				<td><?php echo esc_html($wallet_address); ?></td>
        		</tr>
    		</table>
                <a class="btn btn-default" href="https://explorer.xinfin.network/txs/<?php echo $contract_tx_hash; ?>"><?php esc_html_e( 'View on Block Explorer', 'advanced-classifieds-and-directory-pro' ); ?></a>
		</div>
    </div>
</div>