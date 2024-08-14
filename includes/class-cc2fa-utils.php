<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit;

class CC2FA_Utils
{

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

    public static function send_verification_email($email, $code)
    {
        $subject = __('Your Verification Code', 'cc-2fa');
        $message = sprintf(__('Your verification code is: %s', 'cc-2fa'), $code);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($email, $subject, $message, $headers);
    }
}
