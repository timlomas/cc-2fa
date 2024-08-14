<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit;

class CC2FA_Auth
{

    public static function init()
    {
        add_action('login_redirect', array(__CLASS__, 'redirect_after_login'), 10, 3);
        add_action('wp_logout', array(__CLASS__, 'clear_session'));
        add_action('admin_init', array(__CLASS__, 'prevent_dashboard_access'));

        // Add the custom form submission handler
        add_action('template_redirect', array(__CLASS__, 'handle_form_submission'));
    }

    public static function redirect_after_login($redirect_to, $request, $user)
    {
        if (is_wp_error($user) || !$user) {
            return $redirect_to;
        }

        self::clear_verification($user->ID);
        self::send_verification_code($user);

        return site_url('/cc-2fa-form');
    }

    public static function prevent_dashboard_access()
    {
        if (!get_transient('cc_2fa_passed_' . get_current_user_id()) && !current_user_can('manage_options')) {
            wp_redirect(site_url('/cc-2fa-form'));
            exit;
        }
    }

    public static function clear_session()
    {
        self::clear_verification(get_current_user_id());
    }

    private static function clear_verification($user_id)
    {
        delete_transient('cc_2fa_passed_' . $user_id);
        delete_transient('cc_2fa_code_' . $user_id);
    }

    public static function validate_form_submission($input_code)
    {
        $stored_code = get_transient('cc_2fa_code_' . get_current_user_id());

        if ($stored_code && $stored_code === $input_code) {
            set_transient('cc_2fa_passed_' . get_current_user_id(), true, 60 * 10);
            return true;
        }

        return false;
    }

    public static function send_verification_code($user)
    {
        $code = CC2FA_Utils::generate_verification_code();
        set_transient('cc_2fa_code_' . $user->ID, $code, 60 * 10);
        CC2FA_Utils::send_verification_email($user->user_email, $code);
    }

    public static function handle_form_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cc_2fa_code'])) {
            check_admin_referer('cc_2fa_form_nonce', 'cc_2fa_form_nonce_field');

            if (self::validate_form_submission($_POST['cc_2fa_code'])) {
                wp_redirect(admin_url());
                exit;
            } else {
                // Store the error message in a transient or session
                set_transient('cc_2fa_error', __('Incorrect code. Please try again.', 'cc-2fa'), 30);

                // Redirect back to the form page
                wp_redirect(site_url('/cc-2fa-form'));
                exit;
            }
        }
    }
}
