<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit; // Prevents direct access to the file.

/**
 * Main class for the CC 2FA plugin.
 *
 * This class handles the core functionality of the plugin, including initialization,
 * activation, deactivation, and handling custom rewrite rules.
 */
class CC2FA
{
    /**
     * Holds the single instance of the class.
     *
     * @var CC2FA|null
     */
    private static $instance = null;

    /**
     * Returns the single instance of the class.
     *
     * If the instance doesn't exist yet, it is created and initialized.
     *
     * @return CC2FA The single instance of the class.
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     * Initializes the plugin's functionality.
     *
     * This method sets up authentication, settings, and custom rewrite rules.
     *
     * @return void
     */
    private function init()
    {
        CC2FA_Auth::init();
        CC2FA_Settings::init();
        add_action('init', array($this, 'load_textdomain'));
        add_action('init', array($this, 'rewrite_rules'));
    }

    /**
     * Executes actions needed when the plugin is activated.
     *
     * This includes setting up custom rewrite rules and flushing them.
     *
     * @return void
     */
    public static function activate()
    {
        self::rewrite_rules(); // Call the static method to add rewrite rules.
        flush_rewrite_rules(); // Flushes the rewrite rules to make the new rules effective.
    }

    /**
     * Executes actions needed when the plugin is deactivated.
     *
     * This typically involves flushing the rewrite rules.
     *
     * @return void
     */
    public static function deactivate()
    {
        flush_rewrite_rules(); // Flushes the rewrite rules to remove custom rules.
    }

    /**
     * Adds custom rewrite rules for the plugin's custom pages.
     *
     * This method also sets up query variables and handles the display of the verification page.
     *
     * @return void
     */
    public static function rewrite_rules()
    {
        add_rewrite_rule('cc-2fa-form/?$', 'index.php?cc_2fa_form=1', 'top');
        add_filter('query_vars', function ($query_vars) {
            $query_vars[] = 'cc_2fa_form';
            return $query_vars;
        });
        add_action('template_redirect', function () {
            if (get_query_var('cc_2fa_form')) {
                include plugin_dir_path(__FILE__) . '../templates/verification-page.php';
                exit;
            }
        });
    }

    /**
     * Loads the plugin's text domain for translations.
     *
     * This method makes the plugin ready for internationalization by loading the text domain.
     *
     * @return void
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('cc-2fa', false, basename(dirname(__FILE__)) . '/languages');
    }
}
