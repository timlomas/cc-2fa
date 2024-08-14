<?php

namespace CC2FA;

defined('ABSPATH') || exit;

class CC2FA_Settings
{

    private $options;

    public function __construct()
    {
        $this->options = get_option('cc2fa_options');
    }

    public function add_plugin_page()
    {
        add_options_page(
            'CC 2FA Settings',
            'CC 2FA',
            'manage_options',
            'cc2fa-settings',
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page()
    {
?>
        <div class="wrap">
            <h1><?php _e('CC 2FA Settings', 'cc-2fa'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('cc2fa_option_group');
                do_settings_sections('cc2fa-settings');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function page_init()
    {
        register_setting(
            'cc2fa_option_group',
            'cc2fa_options',
            [$this, 'sanitize']
        );

        add_settings_section(
            'cc2fa_setting_section',
            __('2FA Settings', 'cc-2fa'),
            [$this, 'print_section_info'],
            'cc2fa-settings'
        );

        add_settings_field(
            'code_length',
            __('Code Length', 'cc-2fa'),
            [$this, 'code_length_callback'],
            'cc2fa-settings',
            'cc2fa_setting_section'
        );

        add_settings_field(
            'code_type',
            __('Code Type', 'cc-2fa'),
            [$this, 'code_type_callback'],
            'cc2fa-settings',
            'cc2fa_setting_section'
        );

        add_settings_field(
            'timeout',
            __('Code Timeout (minutes)', 'cc-2fa'),
            [$this, 'timeout_callback'],
            'cc2fa-settings',
            'cc2fa_setting_section'
        );
    }

    public function sanitize($input)
    {
        $sanitized_input = [];
        $sanitized_input['code_length'] = absint($input['code_length']);
        $sanitized_input['code_type'] = sanitize_text_field($input['code_type']);
        $sanitized_input['timeout'] = absint($input['timeout']);

        return $sanitized_input;
    }

    public function print_section_info()
    {
        echo __('Configure the settings for two-factor authentication.', 'cc-2fa');
    }

    public function code_length_callback()
    {
        printf(
            '<input type="text" id="code_length" name="cc2fa_options[code_length]" value="%s" />',
            isset($this->options['code_length']) ? esc_attr($this->options['code_length']) : '6'
        );
    }

    public function code_type_callback()
    {
        $type = isset($this->options['code_type']) ? esc_attr($this->options['code_type']) : 'numeric';
    ?>
        <select id="code_type" name="cc2fa_options[code_type]">
            <option value="numeric" <?php selected($type, 'numeric'); ?>><?php _e('Numeric', 'cc-2fa'); ?></option>
            <option value="alphanumeric" <?php selected($type, 'alphanumeric'); ?>><?php _e('Alphanumeric', 'cc-2fa'); ?></option>
        </select>
<?php
    }

    public function timeout_callback()
    {
        printf(
            '<input type="text" id="timeout" name="cc2fa_options[timeout]" value="%s" />',
            isset($this->options['timeout']) ? esc_attr($this->options['timeout']) : '5'
        );
    }
}
