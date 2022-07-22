<?php

/**
 * Ethereum Payment Gateway. Based on advanced-classifieds-and-directory-pro-premium\premium\public\paypal.php
 *
 * @author	Mel
 * @date	03/01/22
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
 * ACADP_Premium_Public_Ethereum class.
 *
 * @since 1.6.4
 */
class ACADP_Premium_Public_Ethereum {
	
	/**
	 * Get things started.
	 *
	 * @since 1.6.4
	 */
	public function __construct() {		
		$gateway_settings = get_option( 'acadp_gateway_settings' );
	}

	/**
	 * Process Ethereum Payment.
	 *
	 * @since 1.6.4
	 * @param int   $order_id Order ID.
	 */
	public function process_payment( $order_id ) {
		$ethereum_settings = get_option( 'acadp_gateway_ethereum_settings' );
		$page_settings   = get_option( 'acadp_page_settings' );
		
		$currency   = 'ETH';		
		$listing_id = get_post_meta( $order_id, 'listing_id', true );
		$amount     = get_post_meta( $order_id, 'amount', true );
		
		?>
		<br />
		<div class="container">
			<div class="row">
				<div id="loader" class="text-center"><img src="<?php echo ACADP_PLUGIN_URL . 'premium/public/assets/images/ajax-loader.gif'; ?>"></div>
			</div>
			<div class="row">
				<div class="text-center">
					<br />
					<?php esc_html_e('Please access your wallet for instruction and then wait for confirmation', 'advanced-classifieds-and-directory-pro'); ?>
				</div>
			</div>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
		<script>
			
			async function waitForTxToBeMined(txHash) {
				let txReceipt;
				
				while (!txReceipt) {
					try {
						txReceipt = await web3.eth.getTransactionReceipt(txHash);
						
					} catch (err) {
						return indicateFailure(err);
					}
				}
				indicateSuccess(txReceipt);
			}

			function indicateFailure(error){
				alert("<?php esc_html_e('Error. Please try to switch your wallet from main network to test network and back.', 'advanced-classifieds-and-directory-pro'); ?>");
				console.log(error);
			}

			function indicateSuccess(txReceipt){
				alert("<?php esc_html_e('Transaction completed.', 'advanced-classifieds-and-directory-pro'); ?>");
				console.log(txReceipt);
				
			}

			//To check if an object (or something) is empty
			function empty(n){
				return !(!!n ? typeof n === 'object' ? Array.isArray(n) ? !!n.length : !!Object.keys(n).length : true : false);
			}

			window.addEventListener('load', async () => {
			
				// To gain access to modern dapp browsers like MetaMask. Yes, MetaMask is a dapp browser and also a wallet! User needs to accept.
				if (window.ethereum) {	
					
					//Use Metamask
					web3 = new Web3(ethereum);
					
					try {
						// Request account access if needed
						await ethereum.enable();
						
						//Accounts now exposed
						
						var version = web3.version;
						
						console.log("Using web3js version " + version );
						
						//This is another way to retrieve the current wallet address on MetaMask
						/*var accounts = web3.eth.getAccounts(function(error, result) {
							if (error) {
								console.log(error);
							} else {
								console.log(result + " is current account");
							}       
						});*/
						
						//The other recommended way to get wallet address 
						//walletAddress = web3.eth.defaultAccount;
						
						//Get wallet info in the form of Javascript object
						var account = web3.eth.accounts;
						
						//Get the current MetaMask selected/active wallet
						walletAddress = account.givenProvider.selectedAddress;
						
						//Check if Metamask is locked
						if (!empty(walletAddress)) {
							
							//Detect if the user changes the account on MetaMask
							window.ethereum.on('accountsChanged', function (accounts) {
								console.log("MetaMask account changed. Reloading...");
								window.location.reload(); 
							})
													
							//Send ETH to this wallet.
							var toAddress = '<?php echo esc_html( $ethereum_settings['payment_address'] ); ?>'; 

							var account = web3.eth.accounts;
							
							//Get the current MetaMask selected/active wallet
							walletAddress = account.givenProvider.selectedAddress;

							console.log('Send from: ' + walletAddress);
							console.log('Send to: ' + toAddress);								
							
							web3.eth.sendTransaction({
								from: walletAddress,
								to: toAddress,
								value: web3.utils.toWei('<?php echo $amount; ?>', 'ether')
							}, async function (error, result) {
								if (error) {
									console.log(error);
									window.location.hef ='<?php echo acadp_get_failure_page_link(); ?>';
									
								} else {
									console.log("Transaction hash: " + result); 
									await waitForTxToBeMined(result);
									
									//Send POST request back to server with query string containing symbol and user's wallet and transaction hash
								/* 	var http = new XMLHttpRequest();
									var url = '<?php echo acadp_get_payment_receipt_page_link( $order_id ); ?>';
									var params = '?currency_code=' + '<?php echo urlencode($currency); ?>' + '&user_wallet=' + encodeURIComponent(walletAddress) + '&txn_hash=' + encodeURIComponent(result);
									http.open('POST', url, true);

									//Send the proper header information along with the request
									http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

									http.onreadystatechange = function() {//Call a function when the state changes.
										if ( http.readyState == 4 && http.status == 200 ) {
											console.log(http.responseText);
										}
									}
									http.send(params); */
									
									window.location.href = '<?php echo acadp_get_payment_receipt_page_link( $order_id ) . '?currency_code='. urlencode($currency); ?>' + '&user_wallet=' + encodeURIComponent(walletAddress) + '&txn_hash=' + encodeURIComponent(result);

								}
								
							});

												
						  } else { //if (!empty(walletAddress)) {
							  
							 alert("Could not read your wallet's address");
							 //window.location.reload(); 
						  
						  }
						
						
					} catch (error) {
						console.log(error);
						window.location.hef ='<?php echo acadp_get_failure_page_link(); ?>';
						
					}

				}
			});
	
		</script>

		<?php		
	}

}
