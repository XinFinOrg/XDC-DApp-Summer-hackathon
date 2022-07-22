<?php

/**
 * Dashboard: FAQ.
 *
 * @link    http://pluginsware.com
 * @since   1.7.5
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div id="acadp-faq">
    <p class="about-description">
        <strong>1. <?php esc_html_e( 'Will the plugin work with my theme?', 'advanced-classifieds-and-directory-pro' ); ?></strong>
    </p>
    <p>
        <?php 
        printf( 
            __( '"Advanced Classifieds and Directory Pro" has been designed to work with any theme. In case you find any conflict issues, kindly write to us <a href="%s">here</a> explaining the issue. You should receive a reply within 24 hours (except Sunday).', 'advanced-classifieds-and-directory-pro' ),
            admin_url( 'admin.php?page=advanced-classifieds-and-directory-pro-contact' )
        ); 
        ?>
    </p>

    <p class="about-description">
        <strong>2. <?php esc_html_e( 'Does the plugin support third-party page builder like "Elementor", "WPBakery", "Divi", etc.?', 'advanced-classifieds-and-directory-pro' ); ?></strong>
    </p>
    <p>        
        <?php 
        printf( 
            __( 'Yes, this is the main reason we developed the shortcode builder. Simply generate your shortcode using the <a href="%s">Shortcode Builder</a> and add it to your page builder.', 'advanced-classifieds-and-directory-pro' ),
            admin_url( 'admin.php?page=advanced-classifieds-and-directory-pro&tab=shortcode-builder' )
        ); 
        ?>
    </p>

    <p class="about-description">
        <strong>3. <?php esc_html_e( 'Can the plugin be translated into my language?', 'advanced-classifieds-and-directory-pro' ); ?></strong>
    </p>
    <p>
        <?php 
        printf( 
            __( 'Yes, the plugin is translation-ready and you can translate it to your own language easy. Kindly follow the instructions <a href="%s" target="_blank">here</a>.', 'advanced-classifieds-and-directory-pro' ),
            'https://pluginsware.com/documentation/creating-translation-files/'
        ); 
        ?>
    </p>  

    <p class="about-description">
        <strong>4. <?php esc_html_e( 'Is the plugin compatible with WordPress Multisite?', 'advanced-classifieds-and-directory-pro' ); ?></strong>
    </p>
    <p>
        <?php esc_html_e( 'Yes, it is. However, do not "network-activate" the plugin. Activate it on only the subsites on which you need a directory. This can be done under "Plugins -> Add New" as the Administrator user.', 'advanced-classifieds-and-directory-pro' ); ?>
    </p>

    <p class="about-description">
        <strong>5. <?php esc_html_e( "The plugin is not working for me. What should I do now?", 'advanced-classifieds-and-directory-pro' ); ?></strong>
    </p>
    <p>
    <?php 
        printf( 
            __( 'Kindly write to us <a href="%s">here</a> explaining the issue along with your site link. You should receive a reply within 24 hours (except Sunday).', 'advanced-classifieds-and-directory-pro' ),
            admin_url( 'admin.php?page=advanced-classifieds-and-directory-pro-contact' )
        ); 
        ?>
    </p>
</div>
