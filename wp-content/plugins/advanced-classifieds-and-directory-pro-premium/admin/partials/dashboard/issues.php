<?php

/**
 * Dashboard: Issues.
 *
 * @link    http://pluginsware.com
 * @since   1.7.3
 *
 * @package Advanced_Classifieds_And_Directory_Pro
 */

$sections = array(
    'found'   => __( 'Issues', 'advanced-classifieds-and-directory-pro' ),
    'ignored' => __( 'Ignored', 'advanced-classifieds-and-directory-pro' )
);

$active_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'found';
?>

<div id="acadp-issues">
    <?php
    // Notices 
    if ( isset( $_GET['success'] ) && 1 == $_GET['success'] ) {
        printf( 
            '<div class="acadp-notice acadp-notice-success">%s</div>',
            ( 'found' == $active_section ? __( 'Congrats! Issues solved.', 'advanced-classifieds-and-directory-pro' ) : __( 'Issues ignored.', 'advanced-classifieds-and-directory-pro' ) )
        );
    }

    // Section Links
    $section_links = array();

    foreach ( $sections as $key => $title ) {
        $count = count( $issues[ $key ] );

        $section_links[] = sprintf( 
            '<a class="%s" href="?page=advanced-classifieds-and-directory-pro&tab=issues&section=%s">%s <span class="count">(%d)</span></a>',
            ( $key == $active_section ? 'current' : '' ),
            esc_attr( $key ),
            esc_html( $title ),
            $count
        );
    }
    ?>
    <ul class="subsubsub"><li><?php echo implode( ' | </li><li>', $section_links ); ?></li></ul>
    <div class="clear"></div>
    
    <!-- Issues List -->
    <form action="<?php echo esc_url( admin_url( '?page=advanced-classifieds-and-directory-pro&tab=issues&section=' . $active_section ) ); ?>" method="post">
        <table class="widefat striped">
            <thead>
                <tr>
                    <td><input type="checkbox" id="acadp-check-all" /></td>
                    <td><?php esc_html_e( 'Issue', 'advanced-classifieds-and-directory-pro' ); ?></td>
                    <td><?php esc_html_e( 'Description', 'advanced-classifieds-and-directory-pro' ); ?></td>
                </tr>
            </thead>
            <?php if ( count( $issues[ $active_section ] ) > 0 ) : ?>
                <tbody>
                    <?php foreach ( $issues[ $active_section ] as $key ) : 
                        $issue = $this->get_issue_details( $key );
                        ?>
                        <tr>
                            <td><input type="checkbox" name="issues[]" class="acadp-checkbox" value="<?php echo esc_attr( $key ); ?>" /></td>
                            <td><?php echo esc_html( $issue['title'] ); ?></td>
                            <td><?php echo wp_kses_post( $issue['description'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <?php if ( 'found' == $active_section ) : ?>
                                <input type="submit" name="action" class="button" value="<?php esc_attr_e( 'Ignore', 'advanced-classifieds-and-directory-pro' ); ?>" />
                            <?php endif; ?>

                            <input type="submit" name="action" class="button button-primary" value="<?php esc_attr_e( 'Apply Fix', 'advanced-classifieds-and-directory-pro' ); ?>" />
                        </td>
                    </tr>
                </tfoot>
            <?php else : ?>
                <tr>
                    <td colspan="3">
                        <?php
                        if ( 'ignored' == $active_section ) {
                           esc_html_e( 'You have no ignored issues.', 'advanced-classifieds-and-directory-pro' );
                        } else {
                            esc_html_e( 'You have no issues.', 'advanced-classifieds-and-directory-pro' );
                        }
                        ?>
                    </td>
                </tr>  
            <?php endif; ?>
        </table> 

        <!-- Nonce -->
        <?php wp_nonce_field( 'acadp_fix_issues', 'acadp_fix_issues_nonce' ); ?>
    </form>   
</div>
