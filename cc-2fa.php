<?php

/**
 * Plugin Name: CC 2FA
 * Plugin URI: https://caterhamcomputing.co.uk/
 * Description: A plugin to add two-factor authentication via email for WordPress logins.
 * Version: 1.0.0
 * Author: Caterham Computing
 * Author URI: https://caterhamcomputing.co.uk/
 * Text Domain: cc-2fa
 * Domain Path: /languages
 */

defined('ABSPATH') || exit; // Prevent direct access to the file.

if (! defined('CC2FA_PLUGIN_DIR')) {
    define('CC2FA_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (! defined('CC2FA_PLUGIN_URL')) {
    define('CC2FA_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once CC2FA_PLUGIN_DIR . 'includes/class-cc2fa.php';

use CC2FA\CC2FA;

function cc2fa_run_plugin()
{
    $plugin = new CC2FA();
    $plugin->run();
}
add_action('plugins_loaded', 'cc2fa_run_plugin');
