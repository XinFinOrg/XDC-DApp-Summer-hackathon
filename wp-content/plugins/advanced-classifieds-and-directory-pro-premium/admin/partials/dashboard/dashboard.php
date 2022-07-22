<?php

/**
 * Plugin Dashboard.
 *
 * @link    http://pluginsware.com
 * @since   1.7.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div id="acadp-dashboard" class="wrap about-wrap full-width-layout acadp-dashboard">
	<h1><?php esc_html_e( 'Advanced Classifieds & Directory Pro', 'advanced-classifieds-and-directory-pro' ); ?></h1>
    
    <p class="about-text">
		<?php esc_html_e( 'Build any kind of directory site: classifieds, cars, bikes & other vehicles dealers site, pets, real estate portal, yellow pages, etc...', 'advanced-classifieds-and-directory-pro' ); ?>
    </p>
        
    <?php if ( acadp_fs()->is__premium_only() ) : ?>
        <p class="about-text">
            <a href="https://pluginsware.com/documentation/using-premium-features/" class="button button-primary button-hero" target="_blank"><?php esc_html_e( 'Documentation (Premium Version)', 'advanced-classifieds-and-directory-pro' ); ?></a>
        </p>
    <?php endif; ?>

	<div class="wp-badge"><?php printf( esc_html__( 'Version %s', 'advanced-classifieds-and-directory-pro' ), ACADP_VERSION_NUM ); ?></div>
    
    <h2 class="nav-tab-wrapper wp-clearfix">
		<?php		
        foreach ( $tabs as $tab => $title ) {
            $class = ( $tab == $active_tab ) ? 'nav-tab nav-tab-active' : 'nav-tab';

            $title = esc_html( $title );
            if ( 'issues' == $tab ) {
                $class .= ' acadp-text-error';
                $title .= sprintf( ' <span class="count">(%d)</span>', count( $issues['found'] ) );
            }

            $url = admin_url( add_query_arg( 'tab', $tab, 'admin.php?page=advanced-classifieds-and-directory-pro' ) );

            printf( 
                '<a href="%s" class="%s">%s</a>', 
                esc_url( $url ), 
                $class, 
                $title 
            );
        }
        ?>
    </h2>

    <?php require_once ACADP_PLUGIN_DIR . "admin/partials/dashboard/{$active_tab}.php"; ?>    
</div>