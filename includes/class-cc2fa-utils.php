<?php

namespace CaterhamComputing\CC2FA;

defined('ABSPATH') || exit;

class CC2FA_Utils
{

    public static function generate_verification_code()
    {
        $length = get_option('cc_2fa_code_length', 6);
        $complexity = get_option('cc_2fa_code_complexity', 'numeric');

        $characters = $complexity === 'alphanumeric' ?
            '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' :
            '0123456789';

        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[wp_rand(0, strlen($characters) - 1)];
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
