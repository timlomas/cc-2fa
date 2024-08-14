<?php
// cc-2fa/templates/verification-page.php

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

show_admin_bar(false);

// Enqueue styles and scripts
wp_enqueue_style('cc2fa-style', plugin_dir_url(__FILE__) . '../assets/css/cc2fa-style.css');
wp_enqueue_script('cc2fa-script', plugin_dir_url(__FILE__) . '../assets/js/cc2fa-script.js', array('jquery'), false, true);

// Localize the AJAX URL, expiration time, and other messages for use in JavaScript
$expiration_time = get_option('cc_2fa_code_expiration', 120);
$time_passed = time() - (int) get_option('cc_2fa_code_' . get_current_user_id() . '_timestamp');
$time_left = max(0, $expiration_time - $time_passed);

wp_localize_script('cc2fa-script', 'cc2fa_vars', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'time_left' => $time_left, // Pass remaining time to JavaScript
    'resend_code_text' => __('Resend code', 'cc-2fa'),
    'send_new_code_text' => __('Send new code', 'cc-2fa'),
    'code_expired_message' => __('Your verification code has expired.', 'cc-2fa'),
    'resend_code_message' => __('Verification code resent successfully.', 'cc-2fa'),
    'new_code_sent_message' => __('A new verification code has been sent.', 'cc-2fa'),
));
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e('Verification Required', 'cc-2fa'); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div class="wrap">
        <h1><?php esc_html_e('Enter Your Verification Code', 'cc-2fa'); ?></h1>
        <?php if ($error_message): ?>
            <div class="error">
                <p><?php echo esc_html($error_message); ?></p>
            </div>
        <?php endif; ?>
        <form method="post" action="" id="cc2fa-form">
            <?php wp_nonce_field('cc_2fa_form_nonce', 'cc_2fa_form_nonce_field'); ?>
            <input type="hidden" id="cc_2fa_user_id" name="cc_2fa_user_id" value="<?php echo esc_attr(get_current_user_id()); ?>">
            <p>
                <label for="cc_2fa_code"><?php esc_html_e('Verification Code:', 'cc-2fa'); ?></label>
                <input type="text" id="cc_2fa_code" name="cc_2fa_code" required autofocus>
            </p>
            <p id="countdown-timer"></p> <!-- Placeholder for countdown timer -->
            <p>
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Submit', 'cc-2fa'); ?>">
            </p>
        </form>
        <p id="resend-container"></p> <!-- Placeholder for the resend link -->
    </div>
    <?php wp_footer(); ?>
</body>

</html>