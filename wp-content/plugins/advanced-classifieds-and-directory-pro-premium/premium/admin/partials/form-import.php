<?php

/**
 * CSV Import Form.
 *
 * @link    https://pluginsware.com
 * @since   1.7.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

require_once ACADP_PLUGIN_DIR . "premium/admin/partials/form-import-step1.php";
?>

<p id="acadp-import-actions" class="submit">
	<input type="button" id="acadp-import-button" class="button button-primary" value="<?php esc_attr_e( 'Proceed Next', 'advanced-classifieds-and-directory-pro' ); ?>" />
	<span id="acadp-import-status"></span>
</p>

<h3><?php esc_html_e( 'Import Log', 'advanced-classifieds-and-directory-pro' ); ?></h3>
<textarea id="acadp-import-logs" class="widefat" rows="10"></textarea>
