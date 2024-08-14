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

    public static function register_settings()
    {
        // Register settings
        register_setting('cc_2fa_settings', 'cc_2fa_code_length');
        register_setting('cc_2fa_settings', 'cc_2fa_code_complexity');
        register_setting('cc_2fa_settings', 'cc_2fa_code_expiration');

        // Add settings section
        add_settings_section(
            'cc_2fa_main_settings',
            __('Main Settings', 'cc-2fa'),
            null,
            'cc-2fa-settings'
        );

        // Add settings fields
        add_settings_field(
            'cc_2fa_code_length',
            __('Verification Code Length', 'cc-2fa'),
            array(__CLASS__, 'render_code_length_slider'),
            'cc-2fa-settings',
            'cc_2fa_main_settings'
        );

        add_settings_field(
            'cc_2fa_code_complexity',
            __('Verification Code Complexity', 'cc-2fa'),
            array(__CLASS__, 'render_code_complexity_field'),
            'cc-2fa-settings',
            'cc_2fa_main_settings'
        );

        add_settings_field(
            'cc_2fa_code_expiration',
            __('Code Expiration Time', 'cc-2fa'),
            array(__CLASS__, 'render_expiration_time_slider'),
            'cc-2fa-settings',
            'cc_2fa_main_settings'
        );
    }

    public static function render_settings_page()
    {
?>
        <div class="wrap">
            <h1><?php esc_html_e('CC 2FA Settings', 'cc-2fa'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('cc_2fa_settings');
                do_settings_sections('cc-2fa-settings');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public static function render_code_length_slider()
    {
        $code_length = get_option('cc_2fa_code_length', 6);
    ?>
        <input type="range" id="cc_2fa_code_length" name="cc_2fa_code_length" min="4" max="12" value="<?php echo esc_attr($code_length); ?>">
        <span id="cc_2fa_code_length_value"><?php echo esc_html($code_length); ?></span>
        <script type="text/javascript">
            document.getElementById('cc_2fa_code_length').addEventListener('input', function() {
                document.getElementById('cc_2fa_code_length_value').textContent = this.value;
            });
        </script>
    <?php
    }

    public static function render_code_complexity_field()
    {
        $code_complexity = get_option('cc_2fa_code_complexity', 'numeric');
    ?>
        <label>
            <input type="radio" name="cc_2fa_code_complexity" value="numeric" <?php checked($code_complexity, 'numeric'); ?>>
            <?php esc_html_e('Numeric', 'cc-2fa'); ?>
        </label><br>
        <label>
            <input type="radio" name="cc_2fa_code_complexity" value="alphanumeric" <?php checked($code_complexity, 'alphanumeric'); ?>>
            <?php esc_html_e('Alphanumeric', 'cc-2fa'); ?>
        </label>
    <?php
    }

    public static function render_expiration_time_slider()
    {
        $expiration_time = get_option('cc_2fa_code_expiration', 120);
    ?>
        <input type="range" id="cc_2fa_code_expiration" name="cc_2fa_code_expiration" min="30" max="600" step="30" value="<?php echo esc_attr($expiration_time); ?>">
        <span id="cc_2fa_code_expiration_value"><?php echo esc_html($expiration_time); ?> <?php esc_html_e('seconds', 'cc-2fa'); ?></span>
        <script type="text/javascript">
            document.getElementById('cc_2fa_code_expiration').addEventListener('input', function() {
                document.getElementById('cc_2fa_code_expiration_value').textContent = this.value + ' <?php esc_html_e('seconds', 'cc-2fa'); ?>';
            });
        </script>
<?php
    }
}
