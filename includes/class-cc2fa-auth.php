<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit;

class CC2FA_Auth
{
    /**
     * The verification methods array.
     *
     * @var array of CC2FA_Verification_Interface
     */
    private static $verification_methods = [];

    /**
     * Initializes the authentication hooks and handlers.
     *
     * @return void
     */
    public static function init()
    {
        // Load all selected verification methods
        $selected_methods = get_option('cc_2fa_verification_methods', ['email']);
        foreach ($selected_methods as $method) {
            self::$verification_methods[] = self::get_verification_method($method);
        }

        add_action('login_redirect', array(__CLASS__, 'redirect_after_login'), 10, 3);
        add_action('wp_logout', array(__CLASS__, 'clear_session'));
        add_action('admin_init', array(__CLASS__, 'prevent_dashboard_access'));

        add_action('template_redirect', array(__CLASS__, 'handle_form_submission'));

        add_action('wp_ajax_cc2fa_resend_code', array(__CLASS__, 'resend_code'));
        add_action('wp_ajax_nopriv_cc2fa_resend_code', array(__CLASS__, 'resend_code'));
    }

    /**
     * Gets the appropriate verification method based on the user selection.
     *
     * @param string $method The method selected by the user (e.g., 'email', 'sms').
     * @return CC2FA_Verification_Interface The selected verification method.
     */
    private static function get_verification_method($method)
    {
        switch ($method) {
            case 'sms':
                return new CC2FA_SMS_Verification();
            case 'email':
            default:
                return new CC2FA_Email_Verification();
        }
    }

    /**
     * Redirects the user after login to the 2FA verification form.
     *
     * @param string $redirect_to The default redirect URL.
     * @param string $request The requested redirect URL.
     * @param WP_User $user The logged-in user object.
     * @return string The URL to redirect to.
     */
    public static function redirect_after_login($redirect_to, $request, $user)
    {
        if (is_wp_error($user) || !$user) {
            return $redirect_to;
        }

        self::clear_verification($user->ID);

        // Send the code via all selected methods
        foreach (self::$verification_methods as $method) {
            $method->send_code($user);
        }

        return site_url('/cc-2fa-form');
    }

    /**
     * Prevents unauthorized users from accessing the WordPress dashboard.
     *
     * @return void
     */
    public static function prevent_dashboard_access()
    {
        if (wp_doing_ajax()) {
            return;
        }

        if (!get_transient('cc_2fa_passed_' . get_current_user_id()) && !current_user_can('manage_options')) {
            wp_redirect(site_url('/cc-2fa-form'));
            exit;
        }
    }

    /**
     * Clears the session data for the current user upon logout.
     *
     * @return void
     */
    public static function clear_session()
    {
        self::clear_verification(get_current_user_id());
    }

    /**
     * Clears the verification data for the given user.
     *
     * @param int $user_id The ID of the user.
     * @return void
     */
    private static function clear_verification($user_id)
    {
        delete_transient('cc_2fa_passed_' . $user_id);
        delete_transient('cc_2fa_code_' . $user_id);
        delete_option('cc_2fa_code_' . $user_id . '_timestamp');
        delete_transient('cc_2fa_attempts_' . $user_id);
    }

    /**
     * Handles the 2FA form submission.
     *
     * @return void
     */
    public static function handle_form_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cc_2fa_code'])) {
            check_admin_referer('cc_2fa_form_nonce', 'cc_2fa_form_nonce_field');

            $user_id = get_current_user_id();
            $input_code = $_POST['cc_2fa_code'];

            // Validate the code using all methods
            foreach (self::$verification_methods as $method) {
                if ($method->validate_code($user_id, $input_code)) {
                    wp_redirect(admin_url());
                    exit;
                }
            }

            // If none of the methods validated the code, set an error
            set_transient('cc_2fa_error', __('Incorrect code. Please try again.', 'cc-2fa'), 30);
            wp_redirect(site_url('/cc-2fa-form'));
            exit;
        }
    }

    /**
     * Resends the verification code via all selected methods.
     *
     * @return void
     */
    public static function resend_code()
    {
        check_ajax_referer('cc2fa_resend_code', 'security');
        $user = wp_get_current_user();

        // Resend the code via all selected methods
        foreach (self::$verification_methods as $method) {
            $method->send_code($user);
        }

        wp_send_json_success(__('A new verification code has been sent.', 'cc-2fa'));
    }
}
