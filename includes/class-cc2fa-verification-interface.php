<?php

namespace CaterhamComputing\CC2FA;

/**
 * Interface for different 2FA verification methods.
 */
interface CC2FA_Verification_Interface
{
    /**
     * Send the verification code to the user.
     *
     * @param WP_User $user The user object.
     * @return void
     */
    public function send_code($user);

    /**
     * Validate the verification code submitted by the user.
     *
     * @param int $user_id The user ID.
     * @param string $code The code submitted by the user.
     * @return bool True if the code is valid, false otherwise.
     */
    public function validate_code($user_id, $code);
}
