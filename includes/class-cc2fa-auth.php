<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit; // Prevent direct access to the file.

/**
 * Class CC2FA_Auth
 * 
 * Handles authentication-related functionality for the CC 2FA plugin.
 */
class CC2FA_Auth
{

    /**
     * Initializes the authentication hooks and handlers.
     * 
     * Sets up actions for login redirection, session clearing, dashboard access prevention,
     * form submission handling, and AJAX code resending.
     *
     * @return void
     */
    public static function init()
    {
        add_action('login_redirect', array(__CLASS__, 'redirect_after_login'), 10, 3);
        add_action('wp_logout', array(__CLASS__, 'clear_session'));
        add_action('admin_init', array(__CLASS__, 'prevent_dashboard_access'));

        // Add the custom form submission handler
        add_action('template_redirect', array(__CLASS__, 'handle_form_submission'));

        // Add AJAX handler for resending the code
        add_action('wp_ajax_cc2fa_resend_code', array(__CLASS__, 'resend_code'));
        add_action('wp_ajax_nopriv_cc2fa_resend_code', array(__CLASS__, 'resend_code'));
    }

    /**
     * Redirects the user after login to the 2FA verification form.
     * 
     * Clears any previous verification attempts, sends a new verification code,
     * and redirects the user to the 2FA form.
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
        self::send_verification_code($user);

        return site_url('/cc-2fa-form');
    }

    /**
     * Prevents unauthorized users from accessing the WordPress dashboard.
     * 
     * Redirects users who haven't passed 2FA to the verification form,
     * unless they have admin capabilities or are making AJAX requests.
     *
     * @return void
     */
    public static function prevent_dashboard_access()
    {
        // Avoid redirecting AJAX requests
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
     * This ensures that the 2FA process will be required again on the next login.
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
     * Removes the stored verification code and any transient data associated with the user.
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
     * Validates the 2FA code submitted by the user.
     * 
     * Compares the input code with the stored code and checks for expiration and attempt limits.
     * 
     * @param string $input_code The code entered by the user.
     * @return bool True if the code is valid, false otherwise.
     */
    public static function validate_form_submission($input_code)
    {
        $user_id = get_current_user_id();
        $stored_code = get_transient('cc_2fa_code_' . $user_id);

        // Check if the limit attempts option is enabled
        $limit_attempts = get_option('cc_2fa_limit_attempts', 0);
        if ($limit_attempts) {
            $attempts_allowed = get_option('cc_2fa_attempts_allowed', 4);
            $attempts = get_transient('cc_2fa_attempts_' . $user_id);
            $attempts = $attempts ? $attempts + 1 : 1;

            if ($stored_code !== $input_code) {
                // Increment the number of attempts only if the code is incorrect
                set_transient('cc_2fa_attempts_' . $user_id, $attempts, get_option('cc_2fa_code_expiration', 120));

                // If the number of attempts equals the allowed attempts, log out and redirect
                if ($attempts >= $attempts_allowed) {
                    wp_logout();
                    wp_redirect(wp_login_url() . '?message=too_many_attempts');
                    exit;
                }

                self::handle_incorrect_code();
                return false;
            }
        }

        if (!$stored_code) {
            self::handle_expired_code();
            return false;
        }

        if ($stored_code === $input_code) {
            set_transient('cc_2fa_passed_' . $user_id, true, 60 * 10);
            return true;
        }

        self::handle_incorrect_code();
        return false;
    }

    /**
     * Handles the case where the verification code has expired.
     * 
     * Redirects the user to the login page with an expiration message and logs them out.
     *
     * @return void
     */
    private static function handle_expired_code()
    {
        add_action('template_redirect', function () {
            wp_redirect(wp_login_url());
            wp_logout();
            exit;
        });

        add_action('wp_footer', function () {
            echo '<script type="text/javascript">
                setTimeout(function() {
                    alert("' . esc_js(__('Your verification code has expired. You will be logged out and redirected to the login page.', 'cc-2fa')) . '");
                    window.location.href = "' . esc_js(wp_login_url()) . '";
                }, 2000); // 2 second delay before logout
            </script>';
        });
    }

    /**
     * Handles the case where the user entered an incorrect verification code.
     * 
     * Sets an error message and redirects the user back to the 2FA form.
     *
     * @return void
     */
    private static function handle_incorrect_code()
    {
        set_transient('cc_2fa_error', __('Incorrect code. Please try again.', 'cc-2fa'), 30);
        wp_redirect(site_url('/cc-2fa-form'));
        exit;
    }

    /**
     * Sends the 2FA verification code to the user's email address.
     * 
     * Generates a new verification code and emails it to the user.
     *
     * @param WP_User $user The user object.
     * @return void
     */
    public static function send_verification_code($user)
    {
        $expiration_time = get_option('cc_2fa_code_expiration', 120); // Get expiration time in seconds
        $code_length = get_option('cc_2fa_code_length', 6);
        $code_complexity = get_option('cc_2fa_code_complexity', 'numeric');

        $code = CC2FA_Utils::generate_verification_code($code_length, $code_complexity);
        set_transient('cc_2fa_code_' . $user->ID, $code, $expiration_time); // Set expiration
        update_option('cc_2fa_code_' . $user->ID . '_timestamp', time());

        CC2FA_Utils::send_verification_email($user->user_email, $code);
    }

    /**
     * Resends the verification code to the user via AJAX.
     * 
     * Generates a new code or resends the existing one, and emails it to the user.
     *
     * @return void
     */
    public static function resend_code()
    {
        // Ensure the user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('User is not logged in', 'cc-2fa'));
            wp_die();
        }

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        if (!$user) {
            wp_send_json_error(__('User not found', 'cc-2fa'));
            wp_die();
        }

        // Retrieve the expiration time from settings
        $expiration_time = get_option('cc_2fa_code_expiration', 120); // Get expiration time in seconds

        // Retrieve the existing code, if any
        $code = get_transient('cc_2fa_code_' . $user_id);

        if (!$code) {
            // If no code exists, generate a new one
            $code_length = get_option('cc_2fa_code_length', 6);
            $code_complexity = get_option('cc_2fa_code_complexity', 'numeric');
            $code = CC2FA_Utils::generate_verification_code($code_length, $code_complexity);
        }

        // Set or reset the code with a fresh expiration time
        set_transient('cc_2fa_code_' . $user_id, $code, $expiration_time);
        update_option('cc_2fa_code_' . $user_id . '_timestamp', time());

        // Resend the verification email with the existing code
        CC2FA_Utils::send_verification_email($user->user_email, $code);

        wp_send_json_success(__('Verification code resent successfully', 'cc-2fa'));

        wp_die();
    }

    /**
     * Handles the form submission for the 2FA verification.
     * 
     * Validates the submitted verification code and either redirects to the admin dashboard
     * or back to the 2FA form with an error message.
     *
     * @return void
     */
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

                // Redirect back to the verification page
                wp_redirect(site_url('/cc-2fa-form'));
                exit;
            }
        }
    }
}
