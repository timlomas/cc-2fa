<?php

namespace CaterhamComputing\CC2FA;

/**
 * Email-based verification method.
 */
class CC2FA_Email_Verification implements CC2FA_Verification_Interface
{
    /**
     * Send the verification code to the user via email.
     *
     * @param WP_User $user The user object.
     * @return void
     */
    public function send_code($user)
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
     * Validate the verification code submitted by the user.
     *
     * @param int $user_id The user ID.
     * @param string $code The code submitted by the user.
     * @return bool True if the code is valid, false otherwise.
     */
    public function validate_code($user_id, $code)
    {
        $stored_code = get_transient('cc_2fa_code_' . $user_id);

        if (!$stored_code || $stored_code !== $code) {
            return false;
        }

        set_transient('cc_2fa_passed_' . $user_id, true, 10 * MINUTE_IN_SECONDS);
        return true;
    }
}
