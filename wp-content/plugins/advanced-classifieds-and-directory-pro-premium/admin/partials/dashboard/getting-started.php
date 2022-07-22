<?php

/**
 * Dashboard: Getting Started.
 *
 * @link    http://pluginsware.com
 * @since   1.7.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */
?>

<div id="acadp-getting-started">
    <p class="about-description">
        <strong><?php printf( esc_html__( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 0 ); ?></strong> 
        &rarr;
        <?php _e( 'Install and Activate <strong>Advanced Classifieds & Directory Pro</strong>', 'advanced-classifieds-and-directory-pro' ); ?>
    </p>

    <p class="about-description">
        <strong><?php printf( esc_html__( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 1 ); ?></strong> 
        &rarr;
        <code><?php esc_html_e( 'Optional', 'advanced-classifieds-and-directory-pro' ); ?></code>
        <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=acadp_locations&post_type=acadp_listings' ) ); ?>">
            <?php esc_html_e( 'Add Locations', 'advanced-classifieds-and-directory-pro' ); ?>
        </a>
    </p>

    <p class="about-description">
        <strong><?php printf( esc_html__( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 2 ); ?></strong> 
        &rarr;
        <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=acadp_categories&post_type=acadp_listings' ) ); ?>">
            <?php esc_html_e( 'Add Categories', 'advanced-classifieds-and-directory-pro' ); ?>
        </a>
    </p>

    <p class="about-description">
        <strong><?php printf( esc_html__( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 3 ); ?></strong> 
        &rarr;
        <code><?php esc_html_e( 'Optional', 'advanced-classifieds-and-directory-pro' ); ?></code>
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=acadp_fields' ) ); ?>">
            <?php esc_html_e( 'Add Custom Fields', 'advanced-classifieds-and-directory-pro' ); ?>
        </a>
    </p>

    <p class="about-description">
        <strong><?php printf( esc_html__( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 4 ); ?></strong> 
        &rarr;
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=acadp_listings' ) ); ?>">
            <?php esc_html_e( 'Add Listings', 'advanced-classifieds-and-directory-pro' ); ?>
        </a>
    </p>

    <p class="about-description">
        <strong><?php printf( esc_html__( 'Step #%d:', 'advanced-classifieds-and-directory-pro' ), 5 ); ?></strong> 
        &rarr;
        <code><?php esc_html_e( 'Setup Pages', 'advanced-classifieds-and-directory-pro' ); ?></code>
        <?php 
            printf( 
                __( 'During activation, the plugin has added several pages dynamically on your website those display locations, categories, listings, listing submission form, etc. in your site front-end. You can find these pages under the <a href="%s">Pages</a> menu. Simply find and add them as menu items under <a href="%s">Appearance &rarr; menus</a>. That\'s it.', 'advanced-classifieds-and-directory-pro' ),
                esc_url( admin_url( 'edit.php?post_type=page' ) ),
                esc_url( admin_url( 'nav-menus.php' ) )
            ); 
        ?>
    </p>

    <p>
        <?php 
            printf( 
                __( 'These are just the basic steps to getting started with the plugin. The plugin has a lot more features, 100+ settings, widget options, etc. Please <a href="%s" target="_blank">refer</a> for more advanced tutorials.', 'advanced-classifieds-and-directory-pro' ), 
                'https://pluginsware.com/documentation/getting-started/'
            ); 
        ?>
    </p>
</div>
