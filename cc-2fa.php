<?php

/**
 * Plugin Name: CC 2FA
 * Description: A plugin that requires users to enter a verification code sent via email before accessing the WordPress dashboard.
 * Version: 1.0.1
 * Author: Caterham Computing
 * Author URI: https://caterhamcomputing.co.uk/
 * Text Domain: cc-2fa
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/class-cc-2fa.php';

function cc_2fa_init()
{
    \CaterhamComputing\CC2FA\CC2FA::instance();
}
add_action('plugins_loaded', 'cc_2fa_init');

function cc_2fa_load_textdomain()
{
    load_plugin_textdomain('cc-2fa', false, basename(dirname(__FILE__)) . '/languages');
}
add_action('init', 'cc_2fa_load_textdomain');

function cc_2fa_rewrite_rules()
{
    add_rewrite_rule('cc-2fa-form/?$', 'index.php?cc_2fa_form=1', 'top');
}
add_action('init', 'cc_2fa_rewrite_rules');

function cc_2fa_query_vars($query_vars)
{
    $query_vars[] = 'cc_2fa_form';
    return $query_vars;
}
add_filter('query_vars', 'cc_2fa_query_vars');

function cc_2fa_template_redirect()
{
    if (get_query_var('cc_2fa_form')) {
        include plugin_dir_path(__FILE__) . 'templates/form-page.php';
        exit;
    }
}
add_action('template_redirect', 'cc_2fa_template_redirect');

function cc_2fa_activate()
{
    cc_2fa_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'cc_2fa_activate');

function cc_2fa_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'cc_2fa_deactivate');
