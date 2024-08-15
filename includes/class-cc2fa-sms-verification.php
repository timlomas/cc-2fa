<?php

namespace CaterhamComputing\CC2FA;

/**
 * SMS-based verification method (placeholder).
 */
class CC2FA_SMS_Verification implements CC2FA_Verification_Interface
{
    /**
     * Send the verification code to the user via SMS (placeholder).
     *
     * @param WP_User $user The user object.
     * @return void
     */
    public function send_code($user)
    {
        // Placeholder logic for SMS sending
        // In a real implementation, integrate with an SMS API
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
        // Placeholder validation logic for SMS
        // Return false since this is a placeholder
        return false;
    }
}
