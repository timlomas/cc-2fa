<?php

/**
 * Plugin Name: CC 2FA
 * Description: A plugin that requires users to enter a verification code sent via email before accessing the WordPress dashboard.
 * Version: 1.0.5
 * Author: Caterham Computing
 * Author URI: https://caterhamcomputing.co.uk/
 * Text Domain: cc-2fa
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

function cc_2fa_autoloader($class)
{
    $namespace = 'CaterhamComputing\\CC2FA\\';
    if (strpos($class, $namespace) === 0) {
        $class_name = str_replace($namespace, '', $class);
        $class_name = strtolower(str_replace('_', '-', $class_name));
        $class_file = plugin_dir_path(__FILE__) . 'includes/class-' . $class_name . '.php';
        if (file_exists($class_file)) {
            require_once $class_file;
        }
    }
}
spl_autoload_register('cc_2fa_autoloader');

function cc_2fa_init()
{
    \CaterhamComputing\CC2FA\CC2FA::instance();
}
add_action('plugins_loaded', 'cc_2fa_init');

function cc_2fa_activate()
{
    \CaterhamComputing\CC2FA\CC2FA::activate();
}
register_activation_hook(__FILE__, 'cc_2fa_activate');

function cc_2fa_deactivate()
{
    \CaterhamComputing\CC2FA\CC2FA::deactivate();
}
register_deactivation_hook(__FILE__, 'cc_2fa_deactivate');
