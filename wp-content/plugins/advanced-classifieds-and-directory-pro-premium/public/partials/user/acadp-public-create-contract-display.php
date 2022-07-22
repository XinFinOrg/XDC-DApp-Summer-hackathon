<?php

/**
 * This template displays the form to create smart contract.
 *
 * @author  Mel
 * @date   24/01/22
 *  
 * @package Advanced_Classifieds_And_Directory_Pro
 */

?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.5.3/ethers.umd.min.js"></script>
<script src="<?php echo ACADP_PLUGIN_URL; ?>public/js/web3-connector.js"></script>
<div class="acadp acadp-user acadp-post-form">
	<form action="<?php echo esc_url( get_site_url() . "/contract-form" ); ?>" method="post" id="acadp-post-form-contract" class="form-vertical" role="form">
		<?php acadp_status_messages(); ?>

        <div id="acadp-post-errors" class="alert alert-danger" role="alert" style="display: none;">
            <?php esc_html_e( 'Please fill in all required fields.', 'advanced-classifieds-and-directory-pro' ); ?>
        </div>
           
        <!-- Listing details -->
        <div class="panel panel-default">
        	<div class="panel-heading"><?php esc_html_e( 'Smart Contract Details', 'advanced-classifieds-and-directory-pro' ); ?></div>
        
        	<div class="panel-body">
            	<div class="form-group">
      				<label class="control-label" for="acadp-title">
                          <?php esc_html_e( 'Contract Name', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span><span data-bs-toggle="tooltip" class="glyphicon glyphicon-question-sign" title="<?php esc_html_e( 'Contract Name Tooltip', 'advanced-classifieds-and-directory-pro' ); ?>"></span>
                    </label>  
      				<input type="text" name="contract_name" id="acadp-contract-name" class="form-control" value="<?php if ( $post_id > 0 ) echo esc_attr( $post->post_title ); ?>" size="50" maxlength="45" required/>
    			</div>
                <div class="form-group">
      				<label class="control-label" for="acadp-symbol"><?php esc_html_e( 'Contract Symbol', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span><span data-bs-toggle="tooltip" class="glyphicon glyphicon-question-sign" title="<?php esc_html_e( 'Contract Symbol Tooltip', 'advanced-classifieds-and-directory-pro' ); ?>"></span>
                    </label>
      				<input type="text" name="contract_symbol" id="acadp-contract-symbol" class="form-control" value="<?php if ( $post_id > 0 ) echo esc_attr( $post->post_content ); ?>" size="50" maxlength="5" required/>
    			</div>     
            </div>
        </div>        
        
        <!-- Complete listing -->
        <div class="panel panel-default">
        	<div class="panel-heading"><?php esc_html_e( 'Begin Minting', 'advanced-classifieds-and-directory-pro' ); ?></div>
            
            <div class="panel-body">

            	<?php echo the_acadp_terms_of_agreement(); ?>
                
                <?php if ( $post_id == 0 ) : ?>
                	<div id="acadp-listing-g-recaptcha"></div>
                    <div id="acadp-listing-g-recaptcha-message" class="help-block text-danger"></div>
				<?php endif; ?>
                
                <?php wp_nonce_field( 'acadp_save_contract', 'acadp_contract_nonce' ); ?>
                <input type="hidden" name="post_type" value="acadp_listings" />
                <input type="hidden" name="contract_address" id="contract-address"/>
                <input type="hidden" name="contract_tx_hash" id="contract-tx-hash"/>
                <input type="hidden" name="wallet_address" id="wallet-address"/>
                <span id="loading"></span>
        
                <input type="submit" name="action" id="create-contract" class="btn btn-primary pull-right acadp-listing-form-submit-btn" value="<?php esc_html_e( 'Create Contract', 'advanced-classifieds-and-directory-pro' ); ?>" />
               	
                <div class="clearfix"></div>                
             </div>
        </div>
    </form>
    
    <form id="acadp-form-upload" class="hidden" method="post" action="#" enctype="multipart/form-data">
	</form>
	
</div>
