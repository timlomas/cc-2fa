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

function cc_2fa_init()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-cc-2fa.php';
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

function cc_2fa_add_settings_page()
{
    add_options_page(
        __('CC 2FA Settings', 'cc-2fa'),
        __('CC 2FA', 'cc-2fa'),
        'manage_options',
        'cc-2fa-settings',
        'cc_2fa_render_settings_page'
    );
}
add_action('admin_menu', 'cc_2fa_add_settings_page');

function cc_2fa_render_settings_page()
{
?>
    <div class="wrap">
        <h1><?php esc_html_e('CC 2FA Settings', 'cc-2fa'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cc_2fa_settings_group');
            do_settings_sections('cc-2fa-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

function cc_2fa_register_settings()
{
    register_setting('cc_2fa_settings_group', 'cc_2fa_code_length', [
        'type' => 'integer',
        'description' => __('The length of the verification code', 'cc-2fa'),
        'default' => 6,
        'sanitize_callback' => 'absint',
    ]);

    register_setting('cc_2fa_settings_group', 'cc_2fa_code_complexity', [
        'type' => 'string',
        'description' => __('The complexity of the verification code', 'cc-2fa'),
        'default' => 'numeric',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    add_settings_section(
        'cc_2fa_settings_section',
        __('Verification Code Settings', 'cc-2fa'),
        '__return_false',
        'cc-2fa-settings'
    );

    add_settings_field(
        'cc_2fa_code_length',
        __('Verification Code Length', 'cc-2fa'),
        'cc_2fa_code_length_field_callback',
        'cc-2fa-settings',
        'cc_2fa_settings_section'
    );

    add_settings_field(
        'cc_2fa_code_complexity',
        __('Verification Code Complexity', 'cc-2fa'),
        'cc_2fa_code_complexity_field_callback',
        'cc-2fa-settings',
        'cc_2fa_settings_section'
    );
}
add_action('admin_init', 'cc_2fa_register_settings');

function cc_2fa_code_length_field_callback()
{
    $length = get_option('cc_2fa_code_length', 6);
?>
    <input type="range" id="cc_2fa_code_length" name="cc_2fa_code_length" min="4" max="12" value="<?php echo esc_attr($length); ?>" oninput="this.nextElementSibling.value = this.value">
    <output><?php echo esc_attr($length); ?></output>
<?php
}

function cc_2fa_code_complexity_field_callback()
{
    $complexity = get_option('cc_2fa_code_complexity', 'numeric');
?>
    <label><input type="radio" name="cc_2fa_code_complexity" value="numeric" <?php checked($complexity, 'numeric'); ?>> <?php _e('Numeric', 'cc-2fa'); ?></label><br>
    <label><input type="radio" name="cc_2fa_code_complexity" value="alphanumeric" <?php checked($complexity, 'alphanumeric'); ?>> <?php _e('Alphanumeric', 'cc-2fa'); ?></label>
<?php
}

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
