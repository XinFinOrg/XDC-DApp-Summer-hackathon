<?php

/**
 * This template displays the logout button.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-logout-btn">
	<a href="<?php echo esc_url( wp_logout_url() ); ?>" class="btn btn-primary"><?php esc_html_e( 'Logout', 'advanced-classifieds-and-directory-pro' ); ?></a>
</div>