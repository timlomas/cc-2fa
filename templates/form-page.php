<?php
// cc-2fa/templates/form-page.php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

show_admin_bar(false);

$verification_error = false;

$user_id = get_current_user_id();

if (isset($_POST['cc_2fa_code'])) {
    check_admin_referer('cc_2fa_form_nonce', 'cc_2fa_form_nonce_field');

    $cc_2fa_instance = \CaterhamComputing\CC2FA\CC2FA::instance();
    if ($cc_2fa_instance->validate_form_submission($_POST['cc_2fa_code'])) {
        set_transient('cc_2fa_passed_' . $user_id, true, 60 * 10);
        wp_redirect(admin_url());
        exit;
    } else {
        $verification_error = true;
    }
}

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('Verification Required', 'cc-2fa'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .wrap {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            box-sizing: border-box;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
            color: #333;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background-color: #0073aa;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #005d8a;
        }
    </style>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div class="wrap">
        <h1><?php esc_html_e('Enter Your Verification Code', 'cc-2fa'); ?></h1>
        <?php if ($verification_error): ?>
            <div class="error">
                <p><?php esc_html_e('Incorrect code. Please try again.', 'cc-2fa'); ?></p>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <?php wp_nonce_field('cc_2fa_form_nonce', 'cc_2fa_form_nonce_field'); ?>
            <input type="hidden" id="cc_2fa_user_id" name="cc_2fa_user_id" value="<?php echo esc_attr($user_id); ?>"> <!-- Hidden field for user ID -->
            <p>
                <label for="cc_2fa_code"><?php esc_html_e('Verification Code:', 'cc-2fa'); ?></label>
                <input type="text" id="cc_2fa_code" name="cc_2fa_code" required autofocus>
            </p>
            <p>
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Submit', 'cc-2fa'); ?>">
            </p>
        </form>
    </div>
    <?php wp_footer(); ?>
</body>

</html>