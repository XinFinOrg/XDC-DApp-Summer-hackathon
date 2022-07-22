<?php

/**
 * Displays and acts on the plugin's options page.
 *
 * @since 0.3.0
 * @package ethpress
 */
namespace losnappas\Ethpress\Admin;

defined( 'ABSPATH' ) || die;
use  losnappas\Ethpress\Logger ;
/**
 * Static.
 *
 * @since 0.3.0
 */
class Options
{
    /**
     * Adds options page.
     *
     * @since 0.3.0
     */
    public static function admin_menu()
    {
        $page = esc_html__( 'EthPress', 'ethpress' );
        
        if ( is_multisite() ) {
            add_submenu_page(
                'settings.php',
                $page,
                $page,
                'manage_network_options',
                'ethpress',
                [ __CLASS__, 'create_page' ]
            );
        } else {
            add_options_page(
                $page,
                $page,
                'manage_options',
                'ethpress',
                [ __CLASS__, 'create_page' ]
            );
        }
    
    }
    
    /**
     * Creates options page.
     *
     * @since 0.3.0
     */
    public static function create_page()
    {
        // Man that html looks bad!
        ?>
		<div class="wrap">
			<h1><?php 
        esc_html_e( 'EthPress Options Page', 'ethpress' );
        ?></h1>
			<p><?php 
        esc_html_e( 'The MetaMask login plugin.', 'ethpress' );
        ?></p>
      <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            ?>
        <p><a
          aria-label="<?php 
            esc_attr_e( 'Opens in new tab', 'ethpress' );
            ?>"
          href="https://etherscan.io/address/0x106417f7265e15c1aae52f76809f171578e982a9"
          target="_blank"
          title="<?php 
            esc_attr_e( 'Developer\'s wallet, etherscan.io', 'ethpress' );
            ?>"
          rel="noopener noreferer"
        ><?php 
            esc_html_e( 'Donate to support development!', 'ethpress' );
            ?> <span style="text-decoration: none;" aria-hidden="true" class="dashicons dashicons-external"></span></a> <?php 
            esc_html_e( 'For fiat, find the charity link on wp plugin directory.', 'ethpress' );
            ?></p>
        <?php 
        }
        
        ?>
      <p><a
        href="https://wordpress.org/plugins/ethpress/"
        target="_blank"
        rel="noopener noreferer"
      ><?php 
        esc_html_e( 'Rate EthPress on wp plugin directory!', 'ethpress' );
        ?> <span style="text-decoration: none;" aria-hidden="true" class="dashicons dashicons-external"></span></a></p>

      <?php 
        
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            echo  '<section><h1>' . esc_html__( 'Awesome Premium Features', 'ethpress' ) . '</h1>' ;
            echo  esc_html__( 'Managed Verification Service and more.', 'ethpress' ) ;
            echo  ' <a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . esc_html__( 'Upgrade Now!', 'ethpress' ) . '</a>' ;
            echo  '</section>' ;
        }
        
        ?>

			<?php 
        
        if ( is_multisite() ) {
            ?>
				<form action="../options.php" method="POST">
			<?php 
        } else {
            ?>
				<form action="options.php" method="POST">
				<?php 
        }
        
        settings_fields( 'ethpress' );
        do_settings_sections( 'ethpress' );
        $ecrecovers_with_php = self::_check_ecrecovers_with_php();
        $can_save_settings = !$ecrecovers_with_php;
        if ( !$can_save_settings ) {
        }
        if ( $can_save_settings ) {
            submit_button();
        }
        ?>
			</form>
			</div>
		<?php 
    }
    
    protected static function _check_ecrecovers_with_php()
    {
        $ecrecovers_with_php = extension_loaded( 'gmp' ) || extension_loaded( 'bcmath' );
        return $ecrecovers_with_php;
    }
    
    /**
     * Adds settings for api_url to options page. admin_init hooked.
     *
     * @since 0.3.0
     */
    public static function admin_init()
    {
        register_setting( 'ethpress', 'ethpress', [ __CLASS__, 'options_validate' ] );
        add_settings_section(
            'ethpress_main',
            esc_html__( 'Main Settings', 'ethpress' ),
            [ __CLASS__, 'section_main' ],
            'ethpress'
        );
        add_settings_field(
            'ethpress_api_url',
            esc_html__( 'Verification service', 'ethpress' ),
            [ __CLASS__, 'input_api_url' ],
            'ethpress',
            'ethpress_main'
        );
        add_settings_field(
            'ethpress_use_managed_service',
            esc_html__( 'Managed Verification Service', 'ethpress' ),
            [ __CLASS__, 'input_use_managed_service' ],
            'ethpress',
            'ethpress_main'
        );
        add_settings_section(
            'ethpress_login',
            esc_html__( 'Login Display Settings', 'ethpress' ),
            [ __CLASS__, 'section_login' ],
            'ethpress'
        );
        add_settings_field(
            'ethpress_woocommerce_login_form_setting',
            esc_html__( 'WooCommerce Login Form', 'ethpress' ),
            [ __CLASS__, 'woocommerce_login_form_setting' ],
            'ethpress',
            'ethpress_login'
        );
        add_settings_field(
            'ethpress_woocommerce_register_form_setting',
            esc_html__( 'WooCommerce Register Form', 'ethpress' ),
            [ __CLASS__, 'woocommerce_register_form_setting' ],
            'ethpress',
            'ethpress_login'
        );
        add_settings_field(
            'ethpress_woocommerce_after_checkout_registration_form_setting',
            esc_html__( 'WooCommerce Checkout Page Register Form', 'ethpress' ),
            [ __CLASS__, 'woocommerce_after_checkout_registration_form_setting' ],
            'ethpress',
            'ethpress_login'
        );
    }
    
    /**
     * Outputs main section title.
     *
     * @since 0.3.0
     */
    public static function section_main()
    {
    }
    
    /**
     * Outputs login section title.
     *
     * @since 1.2.0
     */
    public static function section_login()
    {
    }
    
    /**
     * Outputs input for api url option.
     *
     * @since 0.3.0
     */
    public static function input_api_url()
    {
        $ecrecovers_with_php = self::_check_ecrecovers_with_php();
        
        if ( $ecrecovers_with_php ) {
            echo  '<p>' . esc_html__( 'Your PHP installation has the necessary PHP extension to do verifications on your server, so there is nothing to configure.', 'ethpress' ) . '</p>' ;
            return;
        }
        
        $options = get_site_option( 'ethpress' );
        // Logger::log("Options::input_api_url: options = " . print_r($options, true));
        $api_url = ( isset( $options['api_url'] ) ? esc_url( $options['api_url'] ) : '' );
        echo  '<input class="regular-text" id="ethpress_api_url" name="ethpress[api_url]" type="text" value="' . esc_attr( $api_url ) . '" />' ;
        echo  '<p class="description">' . sprintf(
            __( 'Use an API or install %1$sPHP-GMP%2$s or %3$sPHP-BCMath%2$s to verify Ethereum signatures.', 'ethpress' ),
            '<a href="https://www.php.net/manual/en/book.gmp.php" target="_blank" rel="noopener noreferrer">',
            '</a>',
            '<a href="https://www.php.net/manual/en/book.bc.php" target="_blank" rel="noopener noreferrer">'
        ) . '</p>' ;
        echo  '<p class="description">' . wp_kses( sprintf(
            /* translators: a link. */
            __( 'To deploy your own verification service, see %1$s.', 'ethpress' ),
            '<a href="https://gitlab.com/losnappas/verify-eth-signature/-/tree/master" target="_blank" rel="noopener noreferrer">https://gitlab.com/losnappas/verify-eth-signature</a>'
        ), [
            'a' => [
            'href'   => [],
            'target' => [],
            'rel'    => [],
        ],
        ] ) . '</p>' ;
    }
    
    /**
     * Outputs input for use_managed_service option.
     *
     * @since 1.2.0
     */
    public static function input_use_managed_service()
    {
        $ecrecovers_with_php = self::_check_ecrecovers_with_php();
        
        if ( $ecrecovers_with_php ) {
            echo  '<p>' . esc_html__( 'Your PHP installation has the necessary PHP extension to do verifications on your server, so there is nothing to configure.', 'ethpress' ) . '</p>' ;
            return;
        }
        
        $options = get_site_option( 'ethpress' );
        // Logger::log("Options::input_use_managed_service: options = " . print_r($options, true));
        $use_managed_service = false;
        echo  '<input ' ;
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            if ( !\losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                echo  'disabled' ;
            }
        }
        echo  ' class="regular-text" id="ethpress_use_managed_service" name="ethpress[use_managed_service]" type="checkbox" value="yes" ' . (( $use_managed_service ? 'checked' : '' )) . ' />' ;
        echo  '<label for="ethpress_use_managed_service">' . __( 'Use Managed Verification Service', 'ethpress' ) . '</label>' ;
        echo  '<p class="description">' . __( 'Check to use the Managed Verification Service.', 'ethpress' ) . '</p>' ;
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            
            if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using the Managed Verification Service API feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            } else {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use the Managed Verification Service API feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            }
        
        }
    }
    
    /**
     * Outputs input for woocommerce_login_form_show option.
     *
     * @since 1.2.0
     */
    public static function woocommerce_login_form_setting()
    {
        $options = get_site_option( 'ethpress' );
        // Logger::log("Options::woocommerce_login_form_setting: options = " . print_r($options, true));
        $woocommerce_login_form_show = false;
        /**
         * Check if WooCommerce is active
         * https://wordpress.stackexchange.com/a/193908/137915
         **/
        $woocommerce_active = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        echo  '<input ' ;
        if ( !$woocommerce_active || \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            if ( !$woocommerce_active || !\losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                echo  'disabled' ;
            }
        }
        echo  ' class="regular-text" id="ethpress_woocommerce_login_form_show" name="ethpress[woocommerce_login_form_show]" type="checkbox" value="yes" ' . (( $woocommerce_login_form_show ? 'checked' : '' )) . ' />' ;
        echo  '<label for="ethpress_woocommerce_login_form_show">' . __( 'Show on WooCommerce Login Form?', 'ethpress' ) . '</label>' ;
        echo  '<p class="description">' . __( 'Check to show EthPress login button on the WooCommerce Login Form.', 'ethpress' ) . '</p>' ;
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            
            if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using the WooCommerce Login Form feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            } else {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use the WooCommerce Login Form feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            }
        
        }
        
        if ( !$woocommerce_active ) {
            ?>
          <h2 class="description"><?php 
            echo  '<a href="https://woocommerce.com/?aff=12943&cid=17113767">' . __( 'Install WooCommerce to use this feature!', 'ethpress' ) . '</a> ' . __( 'WooCommerce is a customizable, open-source eCommerce platform built on WordPress.', 'ethpress' ) ;
            ?></h2>
          <?php 
        }
    
    }
    
    /**
     * Outputs input for woocommerce_register_form_show option.
     *
     * @since 1.3.0
     */
    public static function woocommerce_register_form_setting()
    {
        $options = get_site_option( 'ethpress' );
        // Logger::log("Options::woocommerce_register_form_setting: options = " . print_r($options, true));
        $woocommerce_register_form_show = false;
        /**
         * Check if WooCommerce is active
         * https://wordpress.stackexchange.com/a/193908/137915
         **/
        $woocommerce_active = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        echo  '<input ' ;
        if ( !get_option( 'users_can_register' ) || !$woocommerce_active || \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            if ( !get_option( 'users_can_register' ) || !$woocommerce_active || !\losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                echo  'disabled' ;
            }
        }
        echo  ' class="regular-text" id="ethpress_woocommerce_register_form_show" name="ethpress[woocommerce_register_form_show]" type="checkbox" value="yes" ' . (( $woocommerce_register_form_show ? 'checked' : '' )) . ' />' ;
        echo  '<label for="ethpress_woocommerce_register_form_show">' . __( 'Show on WooCommerce Register Form?', 'ethpress' ) . '</label>' ;
        echo  '<p class="description">' . __( 'Check to show EthPress register button on the WooCommerce Register Form.', 'ethpress' ) . '</p>' ;
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            
            if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using the WooCommerce Register Form feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            } else {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use the WooCommerce Register Form feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            }
        
        }
        
        if ( !$woocommerce_active ) {
            ?>
          <h2 class="description"><?php 
            echo  '<a href="https://woocommerce.com/?aff=12943&cid=17113767">' . __( 'Install WooCommerce to use this feature!', 'ethpress' ) . '</a> ' . __( 'WooCommerce is a customizable, open-source eCommerce platform built on WordPress.', 'ethpress' ) ;
            ?></h2>
          <?php 
        }
        
        
        if ( !get_option( 'users_can_register' ) ) {
            ?>
          <h2 class="description"><?php 
            echo  '<a href="' . get_admin_url() . 'options-general.php' . '">' . __( 'Check the Administration > Settings > General > Membership: Anyone can register box to use this feature.', 'ethpress' ) . '</a> ' ;
            ?></h2>
          <?php 
        }
    
    }
    
    /**
     * Outputs input for woocommerce_after_checkout_registration_form_show option.
     *
     * @since 1.3.0
     */
    public static function woocommerce_after_checkout_registration_form_setting()
    {
        $options = get_site_option( 'ethpress' );
        // Logger::log("Options::woocommerce_after_checkout_registration_form_setting: options = " . print_r($options, true));
        $woocommerce_after_checkout_registration_form_show = false;
        /**
         * Check if WooCommerce is active
         * https://wordpress.stackexchange.com/a/193908/137915
         **/
        $woocommerce_active = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
        echo  '<input ' ;
        if ( !get_option( 'users_can_register' ) || !$woocommerce_active || \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            if ( !get_option( 'users_can_register' ) || !$woocommerce_active || !\losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                echo  'disabled' ;
            }
        }
        echo  ' class="regular-text" id="ethpress_woocommerce_after_checkout_registration_form_show" name="ethpress[woocommerce_after_checkout_registration_form_show]" type="checkbox" value="yes" ' . (( $woocommerce_after_checkout_registration_form_show ? 'checked' : '' )) . ' />' ;
        echo  '<label for="ethpress_woocommerce_after_checkout_registration_form_show">' . __( 'Show on the WooCommerce Checkout page?', 'ethpress' ) . '</label>' ;
        echo  '<p class="description">' . __( 'Check to show EthPress register button on the WooCommerce Checkout page.', 'ethpress' ) . '</p>' ;
        if ( \losnappas\Ethpress\ethpress_fs()->is_not_paying() ) {
            
            if ( \losnappas\Ethpress\ethpress_fs()->is_trial() ) {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to keep using the WooCommerce Register on a Checkout page feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            } else {
                ?>
                <h2 class="description"><?php 
                echo  '<a href="' . \losnappas\Ethpress\ethpress_fs()->get_upgrade_url() . '">' . __( 'Upgrade to use the WooCommerce Register on a Checkout page feature!', 'ethpress' ) . '</a>' ;
                ?></h2>
                <?php 
            }
        
        }
        
        if ( !$woocommerce_active ) {
            ?>
          <h2 class="description"><?php 
            echo  '<a href="https://woocommerce.com/?aff=12943&cid=17113767">' . __( 'Install WooCommerce to use this feature!', 'ethpress' ) . '</a> ' . __( 'WooCommerce is a customizable, open-source eCommerce platform built on WordPress.', 'ethpress' ) ;
            ?></h2>
          <?php 
        }
        
        
        if ( !get_option( 'users_can_register' ) ) {
            ?>
          <h2 class="description"><?php 
            echo  '<a href="' . get_admin_url() . 'options-general.php' . '">' . __( 'Check the Administration > Settings > General > Membership: Anyone can register box to use this feature.', 'ethpress' ) . '</a> ' ;
            ?></h2>
          <?php 
        }
    
    }
    
    /**
     * Validates input for api url option.
     *
     * @param array $input New options input.
     *
     * @since 0.3.0
     */
    public static function options_validate( $input )
    {
        $options = get_site_option( 'ethpress' );
        // Logger::log("Options::options_validate: options = " . print_r($options, true));
        // Logger::log("Options::options_validate: input = " . print_r($input, true));
        $newurl = esc_url_raw( trim( $input['api_url'] ) );
        $use_managed_service = false;
        $woocommerce_login_form_show = false;
        $woocommerce_register_form_show = false;
        $woocommerce_after_checkout_registration_form_show = false;
        
        if ( empty($input['recursive']) && is_multisite() ) {
            $options['api_url'] = $newurl;
            $options['use_managed_service'] = intval( $use_managed_service );
            $options['woocommerce_login_form_show'] = intval( $woocommerce_login_form_show );
            $options['woocommerce_register_form_show'] = intval( $woocommerce_register_form_show );
            $options['woocommerce_after_checkout_registration_form_show'] = intval( $woocommerce_after_checkout_registration_form_show );
            // Mark next call as recursed.
            $options['recursive'] = true;
            if ( isset( $input['have_db_users'] ) ) {
                $options['have_db_users'] = $input['have_db_users'];
            }
            // This calls this validation function recursively.
            // Nothing happens on "return" because this is multisite.
            update_site_option( 'ethpress', $options );
            // Logger::log("Options::options_validate: update_site_option options = " . print_r($options, true));
        }
        
        $options['api_url'] = $newurl;
        $options['use_managed_service'] = intval( $use_managed_service );
        $options['woocommerce_login_form_show'] = intval( $woocommerce_login_form_show );
        $options['woocommerce_register_form_show'] = intval( $woocommerce_register_form_show );
        $options['woocommerce_after_checkout_registration_form_show'] = intval( $woocommerce_after_checkout_registration_form_show );
        if ( isset( $input['have_db_users'] ) ) {
            $options['have_db_users'] = $input['have_db_users'];
        }
        return $options;
    }
    
    /**
     * Adds settings link. Hooked to filter.
     *
     * @since 0.7.0
     *
     * @param array $links Existing links.
     */
    public static function plugin_action_links( $links )
    {
        $label = esc_html__( 'Settings', 'ethpress' );
        
        if ( is_multisite() ) {
            
            if ( current_user_can( 'manage_network_options' ) ) {
                $url = esc_attr( esc_url( add_query_arg( 'page', 'ethpress', network_admin_url() . 'settings.php' ) ) );
            } else {
                return $links;
            }
        
        } else {
            $url = esc_attr( esc_url( add_query_arg( 'page', 'ethpress', get_admin_url() . 'options-general.php' ) ) );
        }
        
        $settings_link = "<a href='{$url}'>{$label}</a>";
        array_unshift( $links, $settings_link );
        return $links;
    }

}