<?php

/**
 * This template displays the public-facing aspects of the widget.
 *
 * @link    https://pluginsware.com
 * @since   1.5.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-widget-listing-contact">
	<?php if ( ! empty( $general_settings['contact_form_require_login'] ) && ! is_user_logged_in() ) { ?> 
    	<p class="text-muted">
        	<?php 
			if ( 'acadp' == $registration_settings['engine'] ) {
				printf( __( 'Please, <a href="%s">login</a> to contact this listing owner.', 'advanced-classifieds-and-directory-pro' ), esc_url( $login_url ) );
			} else {
				esc_html_e( 'Please, login to contact this listing owner.', 'advanced-classifieds-and-directory-pro' );
			}
			?>
        </p>
	<?php } else {
		$current_user = wp_get_current_user();
		?>
		<form id="acadp-contact-form" class="form-vertical" role="form">
        	<div class="form-group">
    			<label for="acadp-contact-name"><?php esc_html_e( 'Your Name', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span></label>
    			<input type="text" class="form-control" id="acadp-contact-name" value="<?php echo esc_attr( $current_user->display_name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'advanced-classifieds-and-directory-pro' ); ?>" required />
  			</div>
      		
            <div class="form-group">
    			<label for="acadp-contact-email"><?php esc_html_e( 'Your E-mail Address', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span></label>
    			<input type="email" class="form-control" id="acadp-contact-email" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php esc_attr_e( 'Email', 'advanced-classifieds-and-directory-pro' ); ?>" required />
  			</div>  						
			
			<div class="form-group">
				<label for="acadp-contact-phone"><?php esc_html_e( 'Your Phone Number', 'advanced-classifieds-and-directory-pro' ); ?></label>
				<input type="text" class="form-control" id="acadp-contact-phone" placeholder="<?php esc_attr_e( 'Phone', 'advanced-classifieds-and-directory-pro' ); ?>" />
			</div>
			  
            <div class="form-group">
    			<label for="acadp-contact-message"><?php esc_html_e( 'Your Message', 'advanced-classifieds-and-directory-pro' ); ?><span class="acadp-star">*</span></label>
    			<textarea class="form-control" id="acadp-contact-message" rows="3" placeholder="<?php esc_attr_e( 'Message', 'advanced-classifieds-and-directory-pro' ); ?>..." required ></textarea>
  			</div>
            
            <div id="acadp-contact-g-recaptcha"></div>
            <div id="acadp-contact-message-display"></div>
      		
            <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Submit', 'advanced-classifieds-and-directory-pro' ); ?></button>
     	</form> 
	<?php } ?>
</div>