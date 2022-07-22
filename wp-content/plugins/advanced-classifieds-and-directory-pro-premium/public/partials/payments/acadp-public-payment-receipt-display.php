<?php

/**Mel: 27/01/22
 * This template displays the minting receipt.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-user acadp-payment-receipt">
	<p><?php esc_html_e( 'Thank you for your order!', 'advanced-classifieds-and-directory-pro' ); ?></p>
    
    <?php
	//Mel: 27/01/22.
	// $token_uri = isset( $_POST['token_uri'] ) ? sanitize_text_field($_POST['token_uri']) : '';
	// $tx_hash = isset( $_POST['tx_hash'] ) ? sanitize_text_field($_POST['tx_hash']) : '';
	// $gas_used = isset( $_POST['gas_used'] ) ? sanitize_text_field($_POST['gas_used']) : '';
	// $block_number = isset( $_POST['block_number'] ) ? sanitize_text_field($_POST['block_number']) : '';
	// $user_wallet = isset( $_POST['user_wallet'] ) ? sanitize_text_field($_POST['user_wallet']) : '';
	
	// acadp_order_completed( array( 'id' => $order->ID, 'transaction_id' => $tx_hash, 'user_wallet' => $user_wallet, 'token_uri' => $token_uri, 'gas_used' => $gas_used, 'block_number' => $block_number ) );
	
	?>
    
    <div class="row">
    	<div class="col-md-6">
   			<table class="table table-bordered">
    			<tr>
    				<td><?php esc_html_e( 'Mint ID', 'advanced-classifieds-and-directory-pro' ); ?> #</td>
            		<td><?php echo esc_html( $order->ID ); ?></td>
        		</tr>
        
        		<tr>
					<td><?php esc_html_e( 'Date', 'advanced-classifieds-and-directory-pro' ); ?></td>
            		<td>
                    	<?php
						$date = strtotime( $order->post_date );
						echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $date );
						?>
					</td>
        		</tr>
                
        		<tr>
					<td><?php esc_html_e( 'Gas Used', 'advanced-classifieds-and-directory-pro' ); ?></td>
            		<td><?php echo esc_html( $post_meta['gas_used'][0] ); ?></td>
        		</tr>
				<tr>
    				<td><?php esc_html_e( 'Wallet Address', 'advanced-classifieds-and-directory-pro' ); ?></td>
            		<td><?php echo esc_html( $post_meta['user_wallet'][0] );  ?></td>
        		</tr>
				<tr>
    				<td>
					<?php
						if ( !empty( $post_meta['token_uri'][0] ) ) {
							esc_html_e( 'File URL', 'advanced-classifieds-and-directory-pro' );
						} else if (!empty( $post_meta['file_hash'][0] ) ) {
							esc_html_e( 'File Hash', 'advanced-classifieds-and-directory-pro' );
						}
					?>
					</td>
					<td style="word-break: break-all;">
					<?php if ( !empty( $post_meta['token_uri'][0] ) ) { ?>
						<a href="<?php esc_html_e( $post_meta['token_uri'][0] ); ?>"><?php esc_html_e( $post_meta['token_uri'][0] ); ?></a>
					<?php } else if ( !empty( $post_meta['file_hash'][0] ) ) {
						esc_html_e( $post_meta['file_hash'][0] ); ?>
					<?php } ?>
					</td>
        		</tr>
    		</table>
		</div>
    	
        <div class="col-md-6">
			<table class="table table-bordered">
        		<tr>
    				<td><?php esc_html_e( 'Payment Method', 'advanced-classifieds-and-directory-pro' ); ?></td>
            		<td>
                    	<?php 
						$gateway = esc_html( $post_meta['payment_gateway'][0] );
						if ( 'free' == $gateway ) {
							esc_html_e( 'Free Submission', 'advanced-classifieds-and-directory-pro' );
						} else {
							$gateway_settings = get_option( 'acadp_gateway_' . $gateway . '_settings' );				
							echo ! empty( $gateway_settings['label'] ) ? esc_html( $gateway_settings['label'] ) : $gateway;
						}
						?>
                    </td>
        		</tr>
                
                <tr>
    				<td><?php esc_html_e( 'Payment Status', 'advanced-classifieds-and-directory-pro' ); ?></td>
            		<td>
						<?php 
						$status = isset( $post_meta['payment_status'] ) ? $post_meta['payment_status'][0] : 'created';
						echo esc_html( acadp_get_payment_status_i18n( $status ) );
						?>
                  	</td>
        		</tr
                
                ><tr>
    				<td><?php esc_html_e( 'Transaction Key', 'advanced-classifieds-and-directory-pro' ); ?></td>
            		<td><?php echo esc_html( $post_meta['transaction_id'][0] ); ?>
					</td>
        		</tr>
				<tr>
    				<td><?php esc_html_e( 'Contract Address', 'advanced-classifieds-and-directory-pro' ); ?></td>
            		<td><?php echo esc_html( $post_meta['contract_address'][0] ); ?></td>
        		</tr>
    		</table>
			<a class="btn btn-default" href="https://explorer.xinfin.network/txs/<?php echo esc_html( $post_meta['transaction_id'][0] ); ?>"><?php esc_html_e( 'View on Block Explorer', 'advanced-classifieds-and-directory-pro' ); ?></a>
    	</div>
    </div>
	

    
	<input type="button" class="btn btn-primary" value="<?php esc_html_e( 'Create Another Token', 'advanced-classifieds-and-directory-pro' ); ?>" onclick="location.href='<?php echo esc_url( acadp_get_listing_form_page_link() ); ?>'"/>
	<input type="button" class="btn btn-primary" value="<?php esc_html_e( 'View all my listings', 'advanced-classifieds-and-directory-pro' ); ?>" onclick="location.href='<?php echo esc_url( acadp_get_manage_listings_page_link() ); ?>'"/>
    
	<!--Mel: 27/01/22. Later-->
	<!--<a href="<?php //echo esc_url( acadp_get_manage_listings_page_link() ); ?>" class="btn btn-success"><?php //esc_html_e( 'View all my listings', 'advanced-classifieds-and-directory-pro' ); ?></a>-->

</div>