<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit;

class CC2FA_Settings
{

    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_settings_page'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    public static function add_settings_page()
    {
        add_options_page(
            __('CC 2FA Settings', 'cc-2fa'),
            __('CC 2FA', 'cc-2fa'),
            'manage_options',
            'cc-2fa-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function render_settings_page()
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

    public static function register_settings()
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
            array(__CLASS__, 'code_length_field_callback'),
            'cc-2fa-settings',
            'cc_2fa_settings_section'
        );

        add_settings_field(
            'cc_2fa_code_complexity',
            __('Verification Code Complexity', 'cc-2fa'),
            array(__CLASS__, 'code_complexity_field_callback'),
            'cc-2fa-settings',
            'cc_2fa_settings_section'
        );
    }

    public static function code_length_field_callback()
    {
        $length = get_option('cc_2fa_code_length', 6);
    ?>
        <input type="range" id="cc_2fa_code_length" name="cc_2fa_code_length" min="4" max="12" value="<?php echo esc_attr($length); ?>" oninput="this.nextElementSibling.value = this.value">
        <output><?php echo esc_attr($length); ?></output>
    <?php
    }

    public static function code_complexity_field_callback()
    {
        $complexity = get_option('cc_2fa_code_complexity', 'numeric');
    ?>
        <label><input type="radio" name="cc_2fa_code_complexity" value="numeric" <?php checked($complexity, 'numeric'); ?>> <?php _e('Numeric', 'cc-2fa'); ?></label><br>
        <label><input type="radio" name="cc_2fa_code_complexity" value="alphanumeric" <?php checked($complexity, 'alphanumeric'); ?>> <?php _e('Alphanumeric', 'cc-2fa'); ?></label>
<?php
    }
}
