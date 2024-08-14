<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit;

class CC2FA
{

    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    public function init()
    {
        add_action('login_redirect', array($this, 'redirect_after_login'), 10, 3);
        add_action('wp_logout', array($this, 'clear_session'));
        add_action('admin_init', array($this, 'prevent_dashboard_access'));
        add_action('template_redirect', array($this, 'intercept_custom_page'));
    }

    public function redirect_after_login($redirect_to, $request, $user)
    {
        if (is_wp_error($user)) {
            return $redirect_to;
        }

        if (!$user) {
            return $redirect_to;
        }

        delete_transient('cc_2fa_passed_' . $user->ID);
        delete_transient('cc_2fa_code_' . $user->ID);

        $this->send_verification_code($user);

        return site_url('/cc-2fa-form');
    }

    public function intercept_custom_page()
    {
        if (strpos($_SERVER['REQUEST_URI'], '/cc-2fa-form') !== false) {
            include plugin_dir_path(__FILE__) . '../templates/form-page.php';
            exit;
        }
    }

    public function clear_session()
    {
        delete_transient('cc_2fa_passed_' . get_current_user_id());
        delete_transient('cc_2fa_code_' . get_current_user_id());
    }

    public function prevent_dashboard_access()
    {
        if (!get_transient('cc_2fa_passed_' . get_current_user_id()) && !current_user_can('manage_options')) {
            wp_redirect(site_url('/cc-2fa-form'));
            exit;
        }
    }

    private function generate_verification_code()
    {
        $length = get_option('cc_2fa_code_length', 6);
        $complexity = get_option('cc_2fa_code_complexity', 'numeric');

        if ($complexity === 'alphanumeric') {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        } else {
            $characters = '0123456789';
        }

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[wp_rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    private function send_verification_code($user)
    {
        if (is_wp_error($user)) {
            return;
        }

        if (empty($user->user_email)) {
            return;
        }

        $code = $this->generate_verification_code();

        if (!set_transient('cc_2fa_code_' . $user->ID, $code, 60 * 10)) {
            return;
        }

        $subject = __('Your Verification Code', 'cc-2fa');
        $message = sprintf(__('Your verification code is: %s', 'cc-2fa'), $code);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($user->user_email, $subject, $message, $headers);
    }

    public function validate_form_submission($input_code)
    {
        $stored_code = get_transient('cc_2fa_code_' . get_current_user_id());

        if ($stored_code && $stored_code === $input_code) {
            return true;
        }

        return false;
    }
}
