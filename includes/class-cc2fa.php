<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit;

class CC2FA
{

    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init()
    {
        CC2FA_Auth::init();
        CC2FA_Settings::init();
        add_action('init', array($this, 'load_textdomain'));
        add_action('init', array($this, 'rewrite_rules'));
    }

    public static function activate()
    {
        self::rewrite_rules(); // Call the static method
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

    public static function rewrite_rules()
    {
        add_rewrite_rule('cc-2fa-form/?$', 'index.php?cc_2fa_form=1', 'top');
        add_filter('query_vars', function ($query_vars) {
            $query_vars[] = 'cc_2fa_form';
            return $query_vars;
        });
        add_action('template_redirect', function () {
            if (get_query_var('cc_2fa_form')) {
                include plugin_dir_path(__FILE__) . '../templates/form-page.php';
                exit;
            }
        });
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('cc-2fa', false, basename(dirname(__FILE__)) . '/languages');
    }
}
