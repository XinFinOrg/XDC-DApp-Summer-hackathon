<?php

/**
 * This template displays the user account page.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-user-account">
	<!-- Show errors if there are any -->
	<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
    	<div class="alert alert-danger" role="alert">
			<?php foreach ( $attributes['errors'] as $error ) : ?>
                <span class="acadp-error"><?php echo wp_kses_post( $error ); ?></span>
            <?php endforeach; ?>
        </div>
	<?php endif; ?>
    
    <?php if ( $attributes['account_updated'] ) : ?>
		<div class="alert alert-info" role="alert">
			<?php esc_html_e( 'Your account has been updated!', 'advanced-classifieds-and-directory-pro' ); ?>
		</div>
	<?php endif; ?>

	<form id="acadp-user-account" class="form-horizontal" action="<?php echo esc_url( acadp_get_user_account_page_link() ); ?>" method="post" role="form">
    	<div class="form-group">
			<label for="acadp-username" class="col-sm-3 control-label"><?php esc_html_e( 'Username', 'advanced-classifieds-and-directory-pro' ); ?></label>
            <div class="col-sm-9">
				<p class="form-control-static"><strong><?php echo esc_html( $attributes['username'] ); ?></strong></p>
            </div>
		</div>

		<div class="form-group">
			<label for="acadp-first-name" class="col-sm-3 control-label"><?php esc_html_e( 'First Name', 'advanced-classifieds-and-directory-pro' ); ?></label>
            <div class="col-sm-9">
				<input type="text" name="first_name" id="acadp-first-name" value="<?php echo esc_attr( $attributes['first_name'] ); ?>" class="form-control" />
            </div>
		</div>

		<div class="form-group">
			<label for="acadp-last-name" class="col-sm-3 control-label"><?php esc_html_e( 'Last Name', 'advanced-classifieds-and-directory-pro' ); ?></label>
            <div class="col-sm-9">
				<input type="text" name="last_name" id="acadp-last-name" value="<?php echo esc_attr( $attributes['last_name'] ); ?>" class="form-control" />
            </div>
		</div>
        
        <div class="form-group">
			<label for="acadp-email" class="col-sm-3 control-label"><?php esc_html_e( 'E-mail Address', 'advanced-classifieds-and-directory-pro' ); ?> <strong>*</strong></label>
            <div class="col-sm-9">
				<input type="text" name="email" id="acadp-email" class="form-control" value="<?php echo esc_attr( $attributes['email'] ); ?>" required />
            </div>
		</div>
        
        <div class="form-group">
    		<div class="col-sm-offset-3 col-sm-9">
        		<div class="checkbox">
    				<label>
            			<input type="checkbox" name="change_password" id="acadp-change-password" value="1"><?php esc_html_e( 'Change Password', 'advanced-classifieds-and-directory-pro' ); ?>
            		</label>
 				</div>
        	</div>
        </div>
        
        <div class="form-group acadp-password-fields" style="display: none;">
			<label for="acadp-pass1" class="col-sm-3 control-label"><?php esc_html_e( 'New Password', 'advanced-classifieds-and-directory-pro' ); ?> <strong>*</strong></label>
            <div class="col-sm-9">
				<input type="password" name="pass1" id="acadp-pass1" class="form-control" autocomplete="off" required />
            </div>
		</div>
        
        <div class="form-group acadp-password-fields" style="display: none">
			<label for="acadp-pass2" class="col-sm-3 control-label"><?php esc_html_e( 'Confirm Password', 'advanced-classifieds-and-directory-pro' ); ?> <strong>*</strong></label>
            <div class="col-sm-9">
				<input type="password" name="pass2" id="acadp-pass2" class="form-control" autocomplete="off" data-match="#acadp-pass1" required />
            </div>
		</div>

		<?php wp_nonce_field( 'acadp_update_user_account', 'acadp_user_account_nonce' ); ?>
         
		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">   
        		<input type="submit" name="submit" class="btn btn-primary" value="<?php esc_attr_e( 'Update Account', 'advanced-classifieds-and-directory-pro' ); ?>" />
            </div>
        </div>
	</form>
</div>