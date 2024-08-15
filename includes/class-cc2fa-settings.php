<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit; // Prevent direct access to the file.

/**
 * Class CC2FA_Settings
 * 
 * Manages the settings and options for the CC 2FA plugin.
 */
class CC2FA_Settings
{

    /**
     * Initializes the settings hooks and handlers.
     * 
     * Adds actions to register the settings page and settings options.
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'add_settings_page'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
    }

    /**
     * Adds the settings page to the WordPress admin menu.
     * 
     * Creates a new options page under the "Settings" menu for the plugin's settings.
     *
     * @return void
     */
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

    /**
     * Registers the plugin settings with WordPress.
     * 
     * Registers the settings, sections, and fields used in the plugin's settings page.
     *
     * @return void
     */
    public static function register_settings()
    {
        // Register settings
        register_setting('cc_2fa_settings', 'cc_2fa_code_length');
        register_setting('cc_2fa_settings', 'cc_2fa_code_complexity');
        register_setting('cc_2fa_settings', 'cc_2fa_code_expiration');
        register_setting('cc_2fa_settings', 'cc_2fa_limit_attempts');
        register_setting('cc_2fa_settings', 'cc_2fa_attempts_allowed');

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

        add_settings_field(
            'cc_2fa_limit_attempts',
            __('Limit Verification Attempts', 'cc-2fa'),
            array(__CLASS__, 'render_limit_attempts_checkbox'),
            'cc-2fa-settings',
            'cc_2fa_main_settings'
        );

        add_settings_field(
            'cc_2fa_attempts_allowed',
            __('Verification Attempts Allowed', 'cc-2fa'),
            array(__CLASS__, 'render_attempts_allowed_slider'),
            'cc-2fa-settings',
            'cc_2fa_main_settings'
        );
    }

    /**
     * Renders the settings page content.
     * 
     * Outputs the HTML for the settings page in the WordPress admin.
     *
     * @return void
     */
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

    /**
     * Renders the verification code length slider.
     * 
     * Outputs the HTML for the code length setting slider.
     *
     * @return void
     */
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

    /**
     * Renders the verification code complexity field.
     * 
     * Outputs the HTML for the code complexity setting field.
     *
     * @return void
     */
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

    /**
     * Renders the expiration time slider.
     * 
     * Outputs the HTML for the expiration time setting slider.
     *
     * @return void
     */
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

    /**
     * Renders the limit attempts checkbox.
     * 
     * Outputs the HTML for the setting to limit verification attempts.
     *
     * @return void
     */
    public static function render_limit_attempts_checkbox()
    {
        $limit_attempts = get_option('cc_2fa_limit_attempts', 0);
    ?>
        <label>
            <input type="checkbox" id="cc_2fa_limit_attempts" name="cc_2fa_limit_attempts" value="1" <?php checked($limit_attempts, 1); ?>>
            <?php esc_html_e('Limit verification attempts', 'cc-2fa'); ?>
        </label>
        <script type="text/javascript">
            document.getElementById('cc_2fa_limit_attempts').addEventListener('change', function() {
                const attemptsSlider = document.getElementById('cc_2fa_attempts_allowed_container');
                attemptsSlider.style.display = this.checked ? 'block' : 'none';
            });

            // Initially hide/show the slider based on the checkbox state
            document.addEventListener('DOMContentLoaded', function() {
                const attemptsSlider = document.getElementById('cc_2fa_attempts_allowed_container');
                const checkbox = document.getElementById('cc_2fa_limit_attempts');
                attemptsSlider.style.display = checkbox.checked ? 'block' : 'none';
            });
        </script>
    <?php
    }

    /**
     * Renders the attempts allowed slider.
     * 
     * Outputs the HTML for the setting that defines the number of allowed verification attempts.
     *
     * @return void
     */
    public static function render_attempts_allowed_slider()
    {
        $attempts_allowed = get_option('cc_2fa_attempts_allowed', 4);
    ?>
        <div id="cc_2fa_attempts_allowed_container" style="margin-top: 10px;">
            <label for="cc_2fa_attempts_allowed"><?php esc_html_e('Verification Attempts Allowed', 'cc-2fa'); ?></label>
            <input type="range" id="cc_2fa_attempts_allowed" name="cc_2fa_attempts_allowed" min="1" max="20" value="<?php echo esc_attr($attempts_allowed); ?>">
            <span id="cc_2fa_attempts_allowed_value"><?php echo esc_html($attempts_allowed); ?></span>
            <script type="text/javascript">
                document.getElementById('cc_2fa_attempts_allowed').addEventListener('input', function() {
                    document.getElementById('cc_2fa_attempts_allowed_value').textContent = this.value;
                });
            </script>
        </div>
<?php
    }
}
