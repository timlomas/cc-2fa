<?php

namespace CC2FA;

defined('ABSPATH') || exit;

class CC2FA
{

    public function __construct()
    {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_hooks();
    }

    private function load_dependencies()
    {
        require_once CC2FA_PLUGIN_DIR . 'includes/class-cc2fa-settings.php';
        require_once CC2FA_PLUGIN_DIR . 'includes/class-cc2fa-2fa-handler.php';
    }

    private function set_locale()
    {
        load_plugin_textdomain('cc-2fa', false, dirname(dirname(__FILE__)) . '/languages/');
    }

    private function define_hooks()
    {
        $handler = new CC2FA_2FA_Handler();

        add_action('login_form', [$handler, 'display_2fa_form']);
        add_filter('authenticate', [$handler, 'check_2fa_code'], 30, 3);

        if (is_admin()) {
            $settings = new CC2FA_Settings();
            add_action('admin_menu', [$settings, 'add_plugin_page']);
            add_action('admin_init', [$settings, 'page_init']);
        }
    }

    public function run()
    {
        // Plugin execution code
    }
}
