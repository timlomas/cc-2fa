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
wp_enqueue_script('cc2fa-script', plugin_dir_url(__FILE__) . '../assets/js/cc2fa-script.js', array(), false, true);

// Retrieve and delete the error message
$error_message = get_transient('cc_2fa_error');
if ($error_message) {
    delete_transient('cc_2fa_error');
}
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
            <p>
                <input type="submit" class="button button-primary" value="<?php esc_attr_e('Submit', 'cc-2fa'); ?>">
            </p>
        </form>
    </div>
    <?php wp_footer(); ?>
</body>

</html>