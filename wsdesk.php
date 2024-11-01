<?php
/**
 * Plugin Name: WSDesk - Wordpress HelpDesk & Support Ticket System (BASIC)
 * Plugin URI: https://wsdesk.com
 * Description: Enhances your customer service and enables efficient handling of customer issues.
 * Version: 1.7.0
 * Author: AdaptXY
 * Text Domain: wsdesk
 * Domain Path: /i18n/languages/
 * Author URI: https://wsdesk.com
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!defined('EH_CRM_VERSION')) {
    define('EH_CRM_VERSION', '1.7.0');
}
if (!defined('EH_CRM_MAIN_URL')) {
    define('EH_CRM_MAIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('EH_CRM_MAIN_PATH')) {
    define('EH_CRM_MAIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('EH_CRM_MAIN_IMG')) {
    define('EH_CRM_MAIN_IMG', EH_CRM_MAIN_URL . "assets/img/");
}
if (!defined('EH_CRM_MAIN_CSS')) {
    define('EH_CRM_MAIN_CSS', EH_CRM_MAIN_URL . "assets/css/");
}
if (!defined('EH_CRM_MAIN_JS')) {
    define('EH_CRM_MAIN_JS', EH_CRM_MAIN_URL . "assets/js/");
}
if (!defined('EH_CRM_MAIN_VENDOR')) {
    define('EH_CRM_MAIN_VENDOR', EH_CRM_MAIN_PATH . "vendor/");
}
if (!defined('EH_CRM_MAIN_VIEWS')) {
    define('EH_CRM_MAIN_VIEWS', EH_CRM_MAIN_PATH . "views/");
}
if (!defined('EH_CRM_WOO_STATUS')) {
    if (in_array('woocommerce/woocommerce.php',get_option('active_plugins')))
    {
        define('EH_CRM_WOO_STATUS', TRUE);
    }
    else
    {
        define('EH_CRM_WOO_STATUS', FALSE);
    }
}
require_once(ABSPATH . "wp-admin/includes/plugin.php");
// Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM')
switch ('BASIC') {
    case 'PREMIUM':
        $conflict = 'basic';
        $base = 'premium';
        break;
    case 'BASIC':
        $conflict = 'premium';
        $base = 'basic';
        break;
}
// Enter your plugin unique option name below $option_name variable
$option_name = 'wsdesk_pack';
if (get_option($option_name) == $conflict) {
    add_action('admin_notices', 'wsdesk_admin_notices', 99);
    deactivate_plugins(plugin_basename(__FILE__));
    function wsdesk_admin_notices() {
        is_admin() && add_filter('gettext', function($translated_text, $untranslated_text, $domain) {
                    $old = array(
                        "Plugin <strong>activated</strong>.",
                        "Selected plugins <strong>activated</strong>."
                    );
                    $error_text = '';
                    // Change the Pack IF BASIC  mention switch('BASIC') ELSE mention switch('PREMIUM')
                    switch ('BASIC') {
                        case 'PREMIUM':
                            $error_text = "BASIC Version of this Plugin Installed. Please uninstall the BASIC Version before activating PREMIUM.";
                            break;
                        case 'BASIC':
                            $error_text = "PREMIUM Version of this Plugin Installed. Please uninstall the PREMIUM Version before activating BASIC.";
                            break;
                    }
                    $new = "<span style='color:red'>" . $error_text . "</span>";
                    if (in_array($untranslated_text, $old, true)) {
                        $translated_text = $new;
                    }
                    return $translated_text;
                }, 99, 3);
    }

    return;
} else {
    add_action('admin_init', 'wsdesk_welcome');
    register_activation_hook(__FILE__, 'eh_crm_install');
    register_deactivation_hook(__FILE__, 'wsdesk_deactivate_work');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wsdesk_action_link');
    add_filter('plugin_row_meta', 'wsdesk_plugin_row_meta', 10, 2);
    add_action( 'plugins_loaded', 'eh_crm_update_function' );
    
    add_action( 'init', 'wsdesk_lang_loader' );
    function wsdesk_lang_loader() {
        load_plugin_textdomain( 'wsdesk', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages');
    }

    function eh_crm_update_function() {
        global $base;
        if(get_option('wsdesk_version_'.$base) != EH_CRM_VERSION)
        {
            require_once (EH_CRM_MAIN_PATH . "includes/class-crm-install-functions.php");
            EH_CRM_Install::update_tables($base);
        }
    }
    
    function wsdesk_deactivate_work() {
        $cron = new EH_CRM_Cron_Setup();
        $cron->crawler_schedule_terminate();
        update_option('wsdesk_pack', '');
    }

    function eh_crm_install() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        require_once (EH_CRM_MAIN_PATH . "includes/class-crm-install-functions.php");global $wpdb;
        if ( is_multisite()) {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                EH_CRM_Install::install_tables();
                restore_current_blog();
            }
        } else {
            EH_CRM_Install::install_tables();
        }
        $shown = get_option("wsdesk_setup_wizard",'not');
        if($shown === 'not')
        {
            set_transient('_wsdesk_welcome_screen_activation_redirect', true, 30);
        }
    }

    function wsdesk_welcome() {
        if (!get_transient('_wsdesk_welcome_screen_activation_redirect') && get_option("wsdesk_setup_wizard",'not')=='shown') {
            return;
        }
        delete_transient('_wsdesk_welcome_screen_activation_redirect');
        wp_safe_redirect(add_query_arg(array('page' => 'wsdesk-setup'), admin_url('index.php')));
    }

    function wsdesk_wizard_includes()
    {
        if ( ! empty( $_GET['page'] ) ) {
            switch ( $_GET['page'] ) {
                case 'wsdesk-setup' :
                    include_once EH_CRM_MAIN_VIEWS . 'welcome/welcome.php';
                break;
            }
        }
    }
    
    function wsdesk_action_link($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wsdesk_tickets') . '">'.__('Tickets', 'wsdesk').'</a>',
            '<a href="' . admin_url('admin.php?page=wsdesk_settings') . '">'.__('Settings', 'wsdesk').'</a>',
            '<a href="https://wsdesk.com/pricing/" target="_blank">'.__('Premium Upgrade', 'wsdesk').'</a>'
        );
        array_push($plugin_links,'<a onclick="return confirm('."'You are about to perform factory reset of WSDesk. This will delete all data permanently. This action is irreversible. This action can only be performed if you have \'DROP\' privilege for your database. Proceed?'".');" href="' . admin_url('admin.php?page=wsdesk_factory_reset') . '">'.__('Factory Reset', 'wsdesk').'</a>');
        if ( array_key_exists( 'deactivate', $links ) ) {
            $links['deactivate'] = str_replace( '<a', '<a class="wsdesk-deactivate-link"', $links['deactivate'] );
        }
        return array_merge($plugin_links, $links);
    }

    function wsdesk_plugin_row_meta($links, $file) {
        if ($file == plugin_basename(__FILE__)) {
            $row_meta = array(
                '<a href="https://www.wsdesk.com/documentation" target="_blank">'.__('Documentation', 'wsdesk').'</a>',
                '<a href="https://wordpress.org/support/plugin/wsdesk" target="_blank">'.__('Support', 'wsdesk').'</a>'
            );
            return array_merge($links, $row_meta);
        }
        return (array) $links;
    }

    function eh_crm_run() {
        if (!class_exists('CRM_Init_Handler')) {
            require_once (EH_CRM_MAIN_PATH . "includes/class-crm-public-functions.php");
            require_once (EH_CRM_MAIN_PATH . "includes/class-crm-init-handler.php");
            require_once (EH_CRM_MAIN_PATH . "includes/class-crm-js-translation.php");  
            require_once (EH_CRM_MAIN_PATH . "includes/class-crm-ajax-functions.php");
            require_once (EH_CRM_MAIN_PATH . "includes/class-crm-settings-handler.php");
            require_once (EH_CRM_MAIN_PATH . "includes/Mailbox.php");
            require_once (EH_CRM_MAIN_PATH . "includes/IncomingMail.php");
            new EH_CRM_Init_Handler();
        }
    }
    
    add_action('init','eh_crm_run',99);
    add_action( 'init', 'wsdesk_wizard_includes',100);
    require_once (EH_CRM_MAIN_PATH . "includes/class-crm-email-oauth.php");
    require_once (EH_CRM_MAIN_PATH . "includes/class-crm-cron-setup.php");
    new EH_CRM_Cron_Setup();
    update_option($option_name, $base);
}
if(isset($_GET['page'])) //code for factory reset
{
    if(is_admin())
    {
        if($_GET['page']==='wsdesk_factory_reset')
        {

            require_once (EH_CRM_MAIN_PATH . "includes/class-crm-init-handler.php");

            EH_CRM_Init_Handler::factory_reset();
            
            deactivate_plugins( plugin_basename( __FILE__ ) );
            
            header("Location: ".admin_url('plugins.php'));
            exit;
        }

    }
}