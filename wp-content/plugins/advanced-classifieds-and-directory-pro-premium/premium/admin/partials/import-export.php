<?php

/**
 * CSV Import/Export.
 *
 * @link    https://pluginsware.com
 * @since   1.7.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div id="acadp-import-export" class="wrap acadp-import-export">
	<h1><?php esc_html_e( 'CSV Import/Export', 'advanced-classifieds-and-directory-pro' ); ?></h1>
	    
    <h2 class="nav-tab-wrapper">
    	<?php
		foreach ( $tabs as $tab => $title ) {
			$class = "nav-tab";
			if ( $active_tab == $tab ) {
				$class .= ' nav-tab-active';			
			}	

			$url = add_query_arg( 
				'tab',
				$tab, 
				admin_url( 'admin.php?page=acadp_import_export' ) 
			);
			
			printf( 
				'<a href="%s" class="%s">%s</a>',
				esc_url( $url ),
				$class,
				esc_html( $title ) 
			);
		}
		?>
	</h2>

	<br />

	<?php require_once ACADP_PLUGIN_DIR . "premium/admin/partials/form-{$active_tab}.php"; ?>
</div>