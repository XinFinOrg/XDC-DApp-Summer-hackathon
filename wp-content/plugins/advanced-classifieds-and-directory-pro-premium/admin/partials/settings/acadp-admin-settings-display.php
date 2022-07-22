<?php

/**
 * This file is used to markup the settings page of the plugin.
 *
 * @link    https://pluginsware.com
 * @since   1.0.0
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

$active_tab     = isset( $_GET['tab'] ) ?  sanitize_text_field( $_GET['tab'] ) : 'general';
$active_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '';

$sections = array();
foreach ( $this->sections as $section ) {
	$tab = $section['tab'];
	
	if ( ! isset( $sections[ $tab ] ) ) {
		$sections[ $tab ] = array();
	}

	$slug = $section['slug'];
	if ( ! isset( $sections[ $tab ][ $slug ] ) ) {
		$sections[ $tab ][ $slug ] = $section;
	}	
}
?>

<div id="acadp-settings" class="wrap acadp-settings">
	<h1><?php esc_html_e( 'Plugin Settings', 'advanced-classifieds-and-directory-pro' ); ?></h1>
	
	<?php settings_errors(); ?>
    
    <h2 class="nav-tab-wrapper">
    	<?php
		foreach ( $this->tabs as $slug => $title ) {
			$class = "nav-tab";
			if ( $active_tab == $slug ) {
				$class .= ' nav-tab-active';			
			}	
			
			$section = '';
			foreach ( $sections[ $slug ] as $key => $value ) {
				$section = $key;

				if ( $active_tab == $slug && empty( $active_section ) ) {
					$active_section = $section;
				}
				break;
			}

			$url = add_query_arg( 
				array( 
					'tab'     => $slug, 
					'section' => $section 
				), 
				admin_url( 'admin.php?page=acadp_settings' ) 
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

	<?php	
	$section_links = array();

	foreach ( $sections[ $active_tab ] as $section ) {
		$section_links[] = sprintf( 
			'<a class="%s" href="?page=acadp_settings&tab=%s&section=%s">%s</a>',
			( $section['slug'] == $active_section ? 'current' : '' ),
			esc_attr( $active_tab ),
			esc_attr( $section['slug'] ),
			esc_html( $section['title'] )
		);
	}

	if ( count( $section_links ) > 1 ) : ?>
		<ul class="subsubsub"><li><?php echo implode( ' | </li><li>', $section_links ); ?></li></ul>
		<div class="clear"></div>
	<?php endif; ?>

	<form method="post" action="options.php"> 
    	<?php
		$page_hook = $active_section;

		settings_fields( $page_hook );
		do_settings_sections( $page_hook );
				
		submit_button();
		?>
    </form>
</div>