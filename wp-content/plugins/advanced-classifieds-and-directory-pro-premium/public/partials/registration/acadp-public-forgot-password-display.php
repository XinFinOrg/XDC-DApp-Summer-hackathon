<?php

/**
 * This template displays the forgot password form.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-forgot-password-form">
	<!-- Show errors if there are any -->
	<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
    	<div class="alert alert-danger" role="alert">
			<?php foreach ( $attributes['errors'] as $error ) : ?>
                <span class="acadp-error"><?php echo wp_kses_post( $error ); ?></span>
            <?php endforeach; ?>
        </div>
	<?php endif; ?>

	<div class="alert alert-info">
		<?php esc_html_e( "Enter your Username or E-mail Address. We'll send you a link you can use to pick a new password.", 'advanced-classifieds-and-directory-pro' );	?>
	</div>

	<form id="acadp-forgot-password-form" class="form-horizontal" action="<?php echo esc_url( wp_lostpassword_url() ); ?>" method="post" role="form">
		<div class="form-group">
			<label for="acadp-user-login" class="col-sm-3 control-label"><?php esc_html_e( 'Username or E-mail', 'advanced-classifieds-and-directory-pro' ); ?></label>
            <div class="col-sm-9">
				<input type="text" name="user_login" id="acadp-user-login" class="form-control" required />
            </div>
		</div>

		<div class="form-group">
			<div class="col-sm-offset-3 col-sm-9">
            	<?php if ( $attributes['redirect'] ) : ?>
            		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $attributes['redirect'] ); ?>" />
                <?php endif; ?>
                
				<input type="submit" name="submit" class="btn btn-primary" value="<?php esc_attr_e( 'Reset Password', 'advanced-classifieds-and-directory-pro' ); ?>" />
           </div>
      	</div>
	</form>
</div>