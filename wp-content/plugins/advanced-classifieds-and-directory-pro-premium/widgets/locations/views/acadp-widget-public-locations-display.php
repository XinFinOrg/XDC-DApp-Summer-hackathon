<?php

/**
 * This template displays the public-facing aspects of the widget.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div class="acadp acadp-widget-locations">
	<?php if ( 'dropdown' == $query_args['template'] ) : ?>
    	<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
        	<select class="form-control" name="acadp_locations" onchange="if ( '' != this.options[ this.selectedIndex ].value ) { this.form.submit() };">
				<option value="">-- <?php esc_html_e( 'Select location', 'advanced-classifieds-and-directory-pro' ); ?> --</option>
            	<?php echo $locations; ?>
            </select>
        </form>
    <?php else :
		echo $locations;
	endif; ?>
</div>