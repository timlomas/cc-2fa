<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit; // Prevents direct access to the file.

/**
 * Utility class for the CC 2FA plugin.
 *
 * This class provides helper functions used throughout the plugin,
 * such as generating verification codes and sending verification emails.
 */
class CC2FA_Utils
{
    /**
     * Generates a verification code.
     *
     * This method generates a random verification code of a specified length and complexity.
     *
     * @param int    $length The length of the verification code. Default is 6.
     * @param string $complexity The complexity of the code ('numeric' or 'alphanumeric'). Default is 'numeric'.
     * @return string The generated verification code.
     */
    public static function generate_verification_code($length = 6, $complexity = 'numeric')
    {
        $characters = $complexity === 'alphanumeric' ? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' : '0123456789';
        $characters_length = strlen($characters);
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, $characters_length - 1)];
        }
        return $code;
    }

    /**
     * Sends a verification email to the user.
     *
     * This method sends an email containing the verification code to the specified email address.
     *
     * @param string $email The email address to send the verification code to.
     * @param string $code The verification code to include in the email.
     * @return void
     */
    public static function send_verification_email($email, $code)
    {
        $subject = __('Your Verification Code', 'cc-2fa');
        $message = sprintf(__('Your verification code is: %s', 'cc-2fa'), $code);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($email, $subject, $message, $headers);
    }
}
