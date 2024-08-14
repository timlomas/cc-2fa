<?php

namespace CC2FA;

defined('ABSPATH') || exit;

class CC2FA_2FA_Handler
{

    private $user_code;

    public function display_2fa_form()
    {
        if (isset($_POST['wp-submit'])) {
            $user = wp_authenticate($_POST['log'], $_POST['pwd']);
            if (is_wp_error($user)) {
                return;
            }

            $code = $this->generate_code();
            $this->user_code = $code;

            update_user_meta($user->ID, '_cc2fa_code', $code);
            update_user_meta($user->ID, '_cc2fa_expiration', time() + $this->get_code_timeout() * 60);

            $this->send_2fa_email($user->user_email, $code);

            wp_redirect(add_query_arg('2fa', '1', wp_login_url()));
            exit;
        }
    }

    public function check_2fa_code($user, $username, $password)
    {
        if (isset($_POST['2fa_code'])) {
            $user = wp_authenticate($username, $password);
            $saved_code = get_user_meta($user->ID, '_cc2fa_code', true);
            $expiration = get_user_meta($user->ID, '_cc2fa_expiration', true);

            if (time() > $expiration) {
                return new \WP_Error('authentication_failed', __('<strong>ERROR</strong>: 2FA code expired.', 'cc-2fa'));
            }

            if ($_POST['2fa_code'] === $saved_code) {
                delete_user_meta($user->ID, '_cc2fa_code');
                delete_user_meta($user->ID, '_cc2fa_expiration');
                return $user;
            } else {
                return new \WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid 2FA code.', 'cc-2fa'));
            }
        }

        return $user;
    }

    private function generate_code()
    {
        $settings = get_option('cc2fa_options');
        $length = isset($settings['code_length']) ? $settings['code_length'] : 6;
        $type = isset($settings['code_type']) ? $settings['code_type'] : 'numeric';

        if ($type === 'alphanumeric') {
            return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
        }

        return substr(str_shuffle('0123456789'), 0, $length);
    }

    private function send_2fa_email($email, $code)
    {
        $subject = __('Your 2FA Code', 'cc-2fa');
        $message = sprintf(__('Your 2FA code is: %s', 'cc-2fa'), $code);
        wp_mail($email, $subject, $message);
    }

    private function get_code_timeout()
    {
        $settings = get_option('cc2fa_options');
        return isset($settings['timeout']) ? $settings['timeout'] : 5;
    }
}
