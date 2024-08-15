<?php

/**
 * Plugin Name: CC 2FA
 * Description: A plugin that requires users to enter a verification code sent via email or other methods before accessing the WordPress dashboard.
 * Version: 1.1.0
 * Author: Caterham Computing
 * Author URI: https://caterhamcomputing.co.uk/
 * Text Domain: cc-2fa
 * Domain Path: /languages
 */

defined('ABSPATH') || exit; // Prevents direct access to the file.

/**
 * Autoloader function to load plugin classes.
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
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

/**
 * Initializes the main plugin instance.
 *
 * Hooks into the 'plugins_loaded' action to ensure all plugins are fully loaded before initialization.
 *
 * @return void
 */
function cc_2fa_init()
{
    \CaterhamComputing\CC2FA\CC2FA::instance();
}
add_action('plugins_loaded', 'cc_2fa_init');

/**
 * Activation hook callback.
 *
 * Executes actions required upon plugin activation, such as setting up initial settings.
 *
 * @return void
 */
function cc_2fa_activate()
{
    \CaterhamComputing\CC2FA\CC2FA::activate();
}
register_activation_hook(__FILE__, 'cc_2fa_activate');

/**
 * Deactivation hook callback.
 *
 * Executes actions required upon plugin deactivation, such as cleaning up settings.
 *
 * @return void
 */
function cc_2fa_deactivate()
{
    \CaterhamComputing\CC2FA\CC2FA::deactivate();
}
register_deactivation_hook(__FILE__, 'cc_2fa_deactivate');
