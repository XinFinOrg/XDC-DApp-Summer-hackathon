<?php

/**Mel: 15/11/21
 * Meta box to upload the profile files and metadata.json to ipfs.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<?php

	//To store the metadata of the deceased profile.
	$metadata = [];
	
	//Get the full name and biography from the post title and the content/body
	$metadata["fullName"] = get_the_title();
	$metadata["bio"] = get_the_content(); 
	
	//To get the selected category (country) from acadp category list
	$country = wp_get_object_terms( $post->ID, 'acadp_categories' );
	$metadata["country"] = $country[0]->name;

	/**
	 * Write the export.json file to the wp-content directory.
	 * File will be created if it does not exist.
	 * Content will be replaced if the file already exists.
	 * 
	 * @param string $json The JSON formatted string to write into the file.
	 * 
	 * @return void
	 */
	function write_to_json_file( $json ) {
		$myfile = fopen(WP_CONTENT_DIR . "/metadata.json", "w+");
			
		fwrite($myfile, $json);
		fclose($myfile);
	}
?>

<?php if ( $general_settings['has_images'] ) : ?>
	<table id="acadp-images" class="acadp-images acadp-no-border widefat">
		<tbody>
			<?php
			if ( isset( $post_meta['images'] ) ) {
				$images = unserialize( $post_meta['images'][0] );
			
				for ( $i = 0; $i < count( $images ); $i++ ) {	
					$image_attributes = wp_get_attachment_image_src( $images[ $i ], 'full' );
					
					//Get image IPFS CID
					//$metadata['imageInIpfs'] = "ipfs://" . $post_meta['cid'][0];
					
					//Add image into metadata
					//$metadata['Image URL'] = $image_attributes[0]; //save image in metadata as URL
					$metadata['imageFilename'] = wp_basename($image_attributes[0]); //save image in metadata as filename only
					
					$image_original_path = wp_get_original_image_path( $images[ $i ] );
					//$image_original_path = ltrim($image_original_path, '/'); //remove first slash from path
					$image_content_type = get_post_mime_type( $images[ $i ] );
					
/* 					if ( isset( $image_attributes[0] ) )  {				
						echo '<tr class="acadp-image-row">' . 
							'<td class="acadp-handle"><span class="dashicons dashicons-screenoptions"></span></td>' .         	
							'<td class="acadp-image">' . 
								'<img src="' . esc_url( $image_attributes[0] ) . '" />' . 
								'<input type="hidden" name="images[]" value="' . esc_attr( $images[ $i ] ) . '" />' . 
							'</td>' . 
							'<td>' . '<div id="image_path">' . $image_original_path . '</div>' . 
								esc_html( $image_attributes[0] ) . '<br />' . 
								'<a href="post.php?post=' . (int) $images[ $i ] . '&action=edit" target="_blank">' . esc_html__( 'Edit', 'advanced-classifieds-and-directory-pro' ) . '</a> | ' . 
								'<a href="javascript:void(0)" class="acadp-delete-image" data-attachment_id="' . esc_attr( $images[ $i ] ) . '">' . 
									esc_html__( 'Delete Permanently', 'advanced-classifieds-and-directory-pro' ) .
								'</a>' . 
							'</td>' .              
						'</tr>';						
					} // endif	 */		
				} // endfor		
			} // endif
			?>
		</tbody>
	</table>
	
	<?php
	
	// Get custom fields
	$custom_field_ids = acadp_get_custom_field_ids( $terms );
	
	if ( ! empty( $custom_field_ids ) ) {
		$args = array(
			'post_type' => 'acadp_fields',
			'posts_per_page' => 500,	
			'post__in' => $custom_field_ids,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
			'meta_key' => 'order',
			'orderby' => 'meta_value_num',			
			'order' => 'ASC',
		);		
		
		$posts = get_posts( $args );
	}
	
	?>
	
	<?php if ( count( $posts ) ) : ?>
	<table class="acadp-input widefat">
		<tbody>
			<?php foreach ( $posts as $post ) : $field_meta = get_post_meta( $post->ID ); ?>
				<tr>
					<!--<td class="label">
						<label>
							<?php //echo esc_html( $post->post_title ); ?>
							<?php //if ( 1 == $field_meta['required'][0] ) echo '<i>*</i>'; ?>
						</label>
						<?php //if ( isset( $field_meta['instructions'] ) ) : ?>
							<p class="description"><?php //echo esc_textarea( $field_meta['instructions'][0] ); ?></p>
						<?php //endif; ?>
					</td>-->
					
					<td>
						<?php
						$value = $field_meta['default_value'][0];
						if ( isset( $post_meta[ $post->ID ] ) ) {
							$value = $post_meta[ $post->ID ][0];
						}
						
						switch ( $field_meta['type'][0] ) {
							case 'text' :	
								/* echo '<div class="acadp-input-wrap">';
								printf( '<input type="text" name="acadp_fields[%d]" class="text" placeholder="%s" value="%s"/>', $post->ID, esc_attr( $field_meta['placeholder'][0] ), esc_attr( $value ) );
								echo '</div>'; */
								
								//Add custom field key and value into metadata
								//$metadata[$post->post_title] = $value;
								$custom_field = str_replace(' ', '', $post->post_title); //remove all whitespaces
								$custom_field = lcfirst($custom_field); //lower the caps of the first letter
								$metadata[$custom_field] = $value;
								
								break;
							case 'textarea' :
								printf( '<textarea name="acadp_fields[%d]" class="textarea" rows="%d" placeholder="%s">%s</textarea>', $post->ID, (int) $field_meta['rows'][0], esc_attr( $field_meta['placeholder'][0] ), esc_textarea( $value ) );
								break;
							case 'select' :
								$choices = $field_meta['choices'][0];
								$choices = explode( "\n", trim( $choices ) );
					
								printf( '<select name="acadp_fields[%d]" class="select">', $post->ID );
								if ( ! empty( $field_meta['allow_null'][0] ) ) {
									printf( '<option value="">%s</option>', '- ' . esc_html__( 'Select an Option', 'advanced-classifieds-and-directory-pro' ) . ' -' );
								}

								foreach ( $choices as $choice ) {
									if ( strpos( $choice, ':' ) !== false ) {
										$_choice = explode( ':', $choice );
										$_choice = array_map( 'trim', $_choice );
								
										$_value  = $_choice[0];
										$_label  = $_choice[1];
									} else {
										$_value  = trim( $choice );
										$_label  = $_value;
									}
							
									$_selected = '';
									if ( trim( $value ) == $_value ) {
										$_selected = ' selected="selected"';
									}
						
									printf( '<option value="%s"%s>%s</option>', esc_attr( $_value ), $_selected, esc_html( $_label ) );
								} 
								echo '</select>';
								break;
							case 'checkbox' :
								$choices = $field_meta['choices'][0];
								$choices = explode( "\n", trim( $choices ) );
					
								$values = explode( "\n", $value );
								$values = array_map( 'trim', $values );
					
								echo '<ul class="acadp-checkbox-list checkbox vertical">';
								foreach ( $choices as $choice ) {
									if ( strpos( $choice, ':' ) !== false ) {
										$_choice = explode( ':', $choice );
										$_choice = array_map( 'trim', $_choice );
								
										$_value  = $_choice[0];
										$_label  = $_choice[1];
									} else {
										$_value  = trim( $choice );
										$_label  = $_value;
									}
							
									$_checked = '';
									if ( in_array( $_value, $values ) ) {
										$_checked = ' checked="checked"';
									}
						
									printf( '<li><label><input type="hidden" name="acadp_fields[%s][]" value="" /><input type="checkbox" name="acadp_fields[%d][]" value="%s"%s>%s</label></li>', $post->ID, $post->ID, esc_attr( $_value ), $_checked, esc_html( $_label ) );
								}
								echo '</ul>';
								break;
							case 'radio' :
								$choices = $field_meta['choices'][0];
								$choices = explode( "\n", trim( $choices ) );
					
								echo '<ul class="acadp-radio-list radio vertical">';
								foreach ( $choices as $choice ) {
									if ( strpos( $choice, ':' ) !== false ) {
										$_choice = explode( ':', $choice );
										$_choice = array_map( 'trim', $_choice );
								
										$_value  = $_choice[0];
										$_label  = $_choice[1];
									} else {
										$_value  = trim( $choice );
										$_label  = $_value;
									}
						
									$_checked = '';
									if ( trim( $value ) == $_value ) {
										$_checked = ' checked="checked"';
									}
						
									printf( '<li><label><input type="radio" name="acadp_fields[%d]" value="%s"%s>%s</label></li>', $post->ID, esc_attr( $_value ), $_checked, esc_html( $_label ) );
								}
								echo '</ul>';
								break;
							case 'url'  :				
								echo '<div class="acadp-input-wrap">';
								printf( '<input type="text" name="acadp_fields[%d]" class="text" placeholder="%s" value="%s"/>', $post->ID, esc_attr( $field_meta['placeholder'][0] ), esc_url( $value ) );
								echo '</div>';
								break;
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif;?>
	
	<?php 
	//-- Convert the metadata array to JSON string.
	$json = json_encode($metadata, JSON_PRETTY_PRINT);

	//-- Write the metadata JSON string to /wp-content/metadata.json
	write_to_json_file($json);
	
	$walletGenUrl = 'https://wallet.avax.network';
	
	//Get wallet of NFT recipient
	if ( isset( $post_meta[ 'user_wallet' ] ) ) {
		$wallet_address = $post_meta['user_wallet'][0];
	}

	?>
	
	<p class="hide-if-no-js">
    	<!--<a class="button" href="javascript:;" id="acadp-upload-file-ipfs"><?php //esc_html_e( 'Upload Image and Metadata to IPFS', 'advanced-classifieds-and-directory-pro' ); ?></a><div class="loader" id="loading"></div>
		<!--<a class="button" href="javascript:;" id="acadp-upload-metadata-ipfs"><?php //esc_html_e( 'Upload Metadata to IPFS', 'advanced-classifieds-and-directory-pro' ); ?></a><div class="loader" id="loading2"></div>-->
		<!--<a class="button" href="javascript:;" id="mintToken"><?php //esc_html_e( 'Mint NFT', 'advanced-classifieds-and-directory-pro' ); ?></a><div class="loader" id="loading2"></div>-->
		<!--<a class="button" href="javascript:;" id="sendToken"><?php //esc_html_e( 'Send NFT to User', 'advanced-classifieds-and-directory-pro' ); ?></a><div class="loader" id="loading3"></div>-->	
	</p>
	
<?php endif; ?>

	<table class="acadp-input widefat">
	  <tbody>
		<tr>
			<td class="label" style="border-top: 1px solid #f0f0f0;">
				<label><?php esc_html_e( 'Step 1: Upload Image and Metadata to IPFS', 'advanced-classifieds-and-directory-pro' ); ?></label> 
			</td>
			<td style="border-top: 1px solid #f0f0f0;">
				<div class="acadp-input-wrap">
				 <a class="button" href="javascript:;" id="acadp-upload-file-ipfs"><?php esc_html_e( 'Upload Image and Metadata', 'advanced-classifieds-and-directory-pro' ); ?></a>
				</div>
				<div class="loader" id="loading"></div>
		  </td>
		</tr>
		<tr>
			<td class="label" style="border-top: 1px solid #f0f0f0;">
				<label><?php esc_html_e( 'Step 2: Mint NFT Token', 'advanced-classifieds-and-directory-pro' ); ?></label> 
			</td>
			<td style="border-top: 1px solid #f0f0f0;">
				<div class="acadp-input-wrap">
				<?php esc_html_e('Ensure Metamask is already connected to this site via the main network and using the correct owner wallet of the contract.', 'advanced-classifieds-and-directory-pro'); ?><br />
				 <a class="button" href="javascript:;" id="mintToken"><?php esc_html_e( 'Mint NFT', 'advanced-classifieds-and-directory-pro' ); ?></a>
				</div>
				<div class="loader" id="loading2"></div>
		  </td>
		</tr>
		<tr>
		  <td class="label" style="border-top: 1px solid #f0f0f0;">
			<label><?php esc_html_e( 'Step 3: Send NFT To Wallet', 'advanced-classifieds-and-directory-pro' ); ?></label>
		  </td>
		  <td style="border-top: 1px solid #f0f0f0;">
			<div class="acadp-input-wrap">
				<?php printf( esc_html('If the address field below is empty, visit %s to generate a new wallet for user. Then, save the address, private key and seed phrase in order to email to user later. You may then enter the new wallet address below.', 'advanced-classifieds-and-directory-pro'), $walletGenUrl); ?><br />
			  <input type="text" id="wallet" name="user_wallet" class="text" oninput="getWalletAddress(this.value)" placeholder="<?php echo esc_attr( 'Enter user wallet address', 'advanced-classifieds-and-directory-pro' ); ?>" value="<?php if ( !empty($wallet_address ) ) echo esc_html($wallet_address); ?>" />
			</div>
			<div style="padding-top: 5px;">
				<a class="button" href="javascript:;" id="sendToken"><?php esc_html_e( 'Send NFT', 'advanced-classifieds-and-directory-pro' ); ?></a><div class="loader" id="loading3"></div>
			</div>
		  </td>
		</tr>   
	  </tbody>
	</table>

<?php
	
	// image to string conversion
	$imagelink = file_get_contents($image_original_path); 
  
	// image string data into base64 
	$encdata = base64_encode($imagelink);

?>

	<!--To upload the files to a folder in IPFS-->
	<script>
	
	var imageCid = "";
	
	//Mel: 06/01/22. Commented to fix a bug where we can't click the button to upload to IPFS
	//var e = document.getElementById("acadp_category");
	//var country = e.options[e.selectedIndex].text;
	
	//To hide this button to ensure the image file is uploaded first
	//document.getElementById("acadp-upload-metadata-ipfs").style.display = "none";
	
	document.getElementById("acadp-upload-file-ipfs").addEventListener("click", function(event) {
		event.preventDefault()
		
		document.getElementById("loading").innerHTML = '<div class="acadp-spinner"></div>';
		
		fetch('<?php echo esc_url( $image_attributes[0] ); ?>')
			.then(res => res.blob())
			.then(blob => {
				const file = new File([blob], "<?php echo esc_html( wp_basename($image_attributes[0]) ); ?>", {
					type: '<?php echo esc_html( $image_content_type ); ?>'
				});
				
				fetch('<?php echo content_url() . "/metadata.json" ?>')
					.then(res => res.blob())
					.then(blob => {
				
					const file2 = new File([blob], "metadata.json", {
						type: 'application/json'
					});
					
					var fd = new FormData();
					fd.append("file", file);
					fd.append("file", file2);
					
					jQuery.ajax({
						type: "POST",
						enctype: 'multipart/form-data',
						url: "https://api.nft.storage/upload",
						beforeSend: function(xhr) {
							xhr.setRequestHeader("Authorization", "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweGM5RTM5RDM4RDA0NjI0MTIzMTA2MzgyMjUzMjE2M0EwODM1ZjA5MUIiLCJpc3MiOiJuZnQtc3RvcmFnZSIsImlhdCI6MTYzNjcxNTg3OTMxOCwibmFtZSI6ImV0ZXJuaWFsc19oYWNrIn0.IxRDv78NEch7JRw49k_5Ww5wydnzKsYjDJk56iDeJG4")
						},
						data: fd,
						processData: false,
						contentType: false,
						cache: false,
						success: (data) => {
							
							imageCid = data.value.cid;
							console.log("Image IPFS CID: " + data.value.cid);
							
							document.getElementById("loading").innerHTML = 'Success. <a id="ipfsUri" href="https://ipfs.io/ipfs/' + data.value.cid + '">View on IPFS</a><input type="hidden" name="ipfs_cid" value="' + data.value.cid + '">';
							
							//To display this button once image is uploaded
							//document.getElementById("acadp-upload-metadata-ipfs").style.display = "";
						},
						error: function(xhr, status, error) {
							console.log(xhr.responseText);
							document.getElementById("loading").innerHTML = '';
						}
					});
				});
		});

	});
	
	//Mel: 29/12/21. The following is unused as it is a backup method to upload metadata separately from the image. Now we are uploading image and metadate at once into a folder in IPFS. If you wish to enable the below, you need to uncomment them aand uncomment the two "...("acadp-upload-metadata-ipfs").style.display..."; lines
/* 	document.getElementById("acadp-upload-metadata-ipfs").addEventListener("click", function(event) {
		event.preventDefault()
		
		document.getElementById("loading2").innerHTML = '<div class="acadp-spinner"></div>';
		
		//To add the IPFS CID of the image to the JSON metadata
		if ( imageCid != "" ) {
			
			var json = <?php echo $json; ?>;
			
			//Append JSON with IPFS CID of the image (key is imageInIpfs)
			json.imageInIpfs = "ipfs://" + imageCid;
			
		} 
		
		jQuery.ajax({
			type: 'POST',
			url: 'https://api.nft.storage/upload',
			beforeSend: function(xhr) {
				xhr.setRequestHeader("Authorization", "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweGM5RTM5RDM4RDA0NjI0MTIzMTA2MzgyMjUzMjE2M0EwODM1ZjA5MUIiLCJpc3MiOiJuZnQtc3RvcmFnZSIsImlhdCI6MTYzNjcxNTg3OTMxOCwibmFtZSI6ImV0ZXJuaWFsc19oYWNrIn0.IxRDv78NEch7JRw49k_5Ww5wydnzKsYjDJk56iDeJG4")
			},
			contentType: "application/json",
			cache: false,
			data: JSON.stringify(json, null, 2), //save metadata as pretty print JSON
			success: function(data) {
			  console.log("Metadata IPFS CID: " + data.value.cid);
			  document.getElementById("loading2").innerHTML = 'Success! <a href="https://ipfs.io/ipfs/' + data.value.cid + '">View on IPFS</a>';
			},
			error: function(data) {
				console.log(data);
				document.getElementById("loading2").innerHTML = 'Error! View console log.';
		   } 
		});
	}); */

	</script>

	<script src="https://cdn.jsdelivr.net/npm/web3@latest/dist/web3.min.js"></script>
	<script>
	var tokenBalance = 0;
	var contractAddress = '';
	var abi = '';
	var smartContract = null;
	var web3EndPoint = '';
	var walletAddress = '';
	var privateKey = '';
	var masterAccount = '';
	var recipientAddress = '';
	var tokenId = '';
	var paymentMethod = '';
	var explorerUrl = '';
	var ExplorerName = '';
	var address = '';

	abi = [{"constant":false,"inputs":[{"name":"_tokenId","type":"uint256"},{"name":"_uri","type":"string"}],"name":"_setTokenURI","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_interfaceId","type":"bytes4"}],"name":"supportsInterface","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_tokenId","type":"uint256"}],"name":"getApproved","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_tokenId","type":"uint256"}],"name":"approve","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newPrice","type":"uint256"}],"name":"setCurrentPrice","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"InterfaceId_ERC165","outputs":[{"name":"","type":"bytes4"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_type","type":"uint256"},{"name":"_title","type":"string"},{"name":"_description","type":"string"},{"name":"_uri","type":"string"}],"name":"mintNftToken","outputs":[],"payable":true,"stateMutability":"payable","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_tokenId","type":"uint256"}],"name":"transferFrom","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_index","type":"uint256"}],"name":"tokenOfOwnerByIndex","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"kill","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_tokenId","type":"uint256"}],"name":"safeTransferFrom","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_tokenId","type":"uint256"}],"name":"exists","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_index","type":"uint256"}],"name":"tokenByIndex","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_tokenId","type":"uint256"}],"name":"ownerOf","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[],"name":"renounceOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_tokenId","type":"uint256"}],"name":"viewToken","outputs":[{"name":"tokenType_","type":"uint256"},{"name":"tokenTitle_","type":"string"},{"name":"tokenDescription_","type":"string"},{"name":"tokenUri_","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"myTokens","outputs":[{"name":"","type":"uint256[]"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_approved","type":"bool"}],"name":"setApprovalForAll","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_tokenId","type":"uint256"},{"name":"_data","type":"bytes"}],"name":"safeTransferFrom","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_tokenId","type":"uint256"}],"name":"tokenURI","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_type","type":"uint256"},{"name":"_title","type":"string"},{"name":"_description","type":"string"},{"name":"_to","type":"address"}],"name":"buyTransferToken","outputs":[],"payable":true,"stateMutability":"payable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_operator","type":"address"}],"name":"isApprovedForAll","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"getCurrentPrice","outputs":[{"name":"price","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"buyer","type":"address"},{"indexed":false,"name":"tokenId","type":"uint256"}],"name":"BoughtToken","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"}],"name":"OwnershipRenounced","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_from","type":"address"},{"indexed":true,"name":"_to","type":"address"},{"indexed":true,"name":"_tokenId","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_owner","type":"address"},{"indexed":true,"name":"_approved","type":"address"},{"indexed":true,"name":"_tokenId","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_owner","type":"address"},{"indexed":true,"name":"_operator","type":"address"},{"indexed":false,"name":"_approved","type":"bool"}],"name":"ApprovalForAll","type":"event"}];
	
	
	//To fill the user's wallet address text field entered by admin. This is required when user did not provide a wallet
	//function getWalletAddress(value) {
		//document.getElementById('wallet').setAttribute("value", value);
	//} 
	
	walletAddress = '0xCA6c272710698bbF3358c8131035CbD2AFDFea67'; //Account on Metamask. Owner of smart contract
	
	//Mel: 29/12/21. These variables are currently unused but here for safekeeping if we decided to use signTransaction with priv key
	privateKey = 'f150328450b492ab'; //Private key of wallet address
	masterAccount = '0xCA6c272710698bbF3358c8131035CbD2AFDFea67';	//Should be same as wallet address

	//Avalanche smart contract on Fuji testnet
	contractAddress = '0xcac0c5295c40698a2ee699871489227dce9bee26'; 
	
	//Endpoint is like https://kovan.infura.io/v3/f2e537e744a14d3a9981ddec2ae859c9 but leave empty if wanna use Metamask
	web3EndPoint = '';
	
	explorerUrl = 'https://testnet.snowtrace.io/tx/';
	explorerName = 'SnowTrace (Fuji Testnet)';

	function startApp(web3) {

		smartContract = new web3.eth.Contract(abi, contractAddress);
		
	}

	async function doesTokenIdExist(tokenId, contract, walletAddress) {
		
		var tokenExists = false;
		
		try {
			
			await contract.methods.ownerOf(tokenId).call(function(err, res) {
		  
				if (!err){
					
					console.log("Owner of token with tokenId " + tokenId + " is " + res);
					console.log("Current wallet address is " + walletAddress);
					
					tokenAddress = res.toLowerCase();
					walletAddress = walletAddress.toLowerCase();
					
					if (tokenAddress.localeCompare(walletAddress) == 0){
						tokenExists = true;
					} 
					
				} else {
					console.log(err);
				}
			});
				
		} catch (error) {
			
			console.log(error);
			tokenExists = false;
		
		}
		
		return tokenExists;	
	}

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

	//To read the transaction hash and send it back to vendor-order-details page to be marked as shipped
	function processTransactionHash(txHash){
		var data = {
			hash: txHash,
			tracking_url: explorerUrl + txHash,
			tracking_id: txHash,
		};
	}

	window.addEventListener('load', async () => {
	
		// To gain access to modern dapp browsers like MetaMask. Yes, MetaMask is a dapp browser and also a wallet! User needs to accept.
		//if (window.ethereum) {
			
			if (web3EndPoint != '') {
				
				//Use web3 endpoint such as from Infura
				web3 = new Web3( new Web3.providers.HttpProvider(web3EndPoint) );
				
			} else {
				
				//Use Metamask
				web3 = new Web3(ethereum);
			}
			
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
					
					//If not locked, continue to run the app
					startApp(web3);
						
					var button = document.getElementById('mintToken');
							
					button.addEventListener('click', async function (event) {
								
						var ipfsData = document.getElementById("ipfsUri");

						if( ipfsData != '' && ipfsData != null ) {
							
							console.log("ipfsUri: " + ipfsData);
							
							event.preventDefault();
							
							document.getElementById("loading2").innerHTML = '<div class="acadp-spinner"></div>';
							
							smartContract.methods.mintNftToken("1", "<?php echo esc_html( get_the_title() ); ?>", "<?php esc_html_e('Biography of a Departed', 'advanced-classifieds-and-directory-pro'); ?>", ipfsData.toString()).send({
								from: walletAddress,
								gasLimit: 4700000,
							  // if payable, specify value
							  // value: web3js.toWei(value, 'ether')
							}, function (err, transactionHash) {
								
								if (!err) {
									
									console.log("Minting transaction: " + transactionHash); 
									document.getElementById("loading2").innerHTML = 'Processed. <a href="' + explorerUrl + transactionHash + '">View on ' + explorerName + '</a><input type="hidden" id ="mint_transaction_hash" name="mint_transaction_hash" value="' + explorerUrl + transactionHash + '">';
									setTimeout(processTransactionHash(transactionHash), 5000);
									waitForTxToBeMined(transactionHash);

									//After minted, we can then send the token to buyer
									var buttonSendToken = document.getElementById('sendToken');

									buttonSendToken.addEventListener('click', function (event) {
										
										event.preventDefault();
										
										//Get wallet address from filled wallet text field
										recipientAddress = document.getElementById('wallet').value;
										
										var txHash = transactionHash;
										console.log("recipientAddress: " + recipientAddress);
										
										web3.eth.getTransactionReceipt(txHash).then( function(data) {
											let logs = data.logs;
											console.log(logs);
											var tokenId = web3.utils.hexToNumber(logs[0].topics[3]);
											console.log("tokenID: " + tokenId);
											
											if( txHash != '' && txHash != null ) {
											
												if ( recipientAddress != '' && recipientAddress != null ) {
												
													document.getElementById("loading3").innerHTML = '<div class="acadp-spinner"></div>';
													
													console.log("Send from: " + walletAddress);
													console.log("Send to: " + recipientAddress);
													
													smartContract.methods.transferFrom(walletAddress, recipientAddress, tokenId).send({
														from: walletAddress,
														gasLimit: 4700000,
													  // if payable, specify value
													  // value: web3js.toWei(value, 'ether')
													}, function (err, transferTransactionHash) {
														
														if (!err) {
															console.log("Transfer transaction: " + transferTransactionHash); 
															document.getElementById("loading3").innerHTML = 'Processed. <a href="' + explorerUrl + transferTransactionHash + '">View on ' + explorerName + '</a><input type="hidden" name="transfer_transaction_hash" value="' + explorerUrl + transferTransactionHash + '">';
															setTimeout(processTransactionHash(transferTransactionHash), 5000);
															waitForTxToBeMined(transferTransactionHash);
															
														} else {
															alert("<?php esc_html_e('Error. Please read console log.', 'advanced-classifieds-and-directory-pro'); ?>");
															console.log(err);
															document.getElementById("loading3").innerHTML = '';
														}
														
													});
												
												} else {
													alert("<?php esc_html_e('Ensure you entered a recipient wallet address.', 'advanced-classifieds-and-directory-pro'); ?>");
												}
												
											} else {
												alert("<?php esc_html_e('Please mint token before sending.', 'advanced-classifieds-and-directory-pro'); ?>");
											} 
											
										});
									
									});
									//Mel: 19/11/21 End
										
								} else {
									alert("<?php esc_html_e('Error. Please read console log.', 'advanced-classifieds-and-directory-pro'); ?>");
									console.log(err);
									document.getElementById("loading2").innerHTML = '';
								}
								
							});	
							
						} else {
							alert("<?php esc_html_e('Please upload files to IPFS before minting token.', 'advanced-classifieds-and-directory-pro'); ?>");
						}      
								
					});				
		
				  } else {
					  
					  //Mel: 29/12/21. Had the problem where the page keeps reloading cos we can't call a wallet address from Metamask 
					 //window.location.reload(); 
				  
				  }
				
				
			} catch (error) {
				alert("<?php esc_html_e('Error. Please read console log.', 'advanced-classifieds-and-directory-pro'); ?>");
				console.log(error);

			}
	/* 	}
		// Non-dapp browsers...
		else {
			
			if (alert("<?php esc_html_e( 'No MetaMask plugin detected. Please install MetaMask digital wallet at www.metamask.io', 'advanced-classifieds-and-directory-pro' ); ?>")){
			} else {
				window.location.reload(); 
			}
		} */
		

	});
														
</script>

