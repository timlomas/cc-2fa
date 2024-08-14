=== CC 2FA ===
Contributors: caterhamcomputing
Tags: two-factor authentication, 2FA, security, login, email verification
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.0.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin that adds two-factor authentication (2FA) via email verification for users logging into the WordPress dashboard.

== Description ==

**CC 2FA** is a simple and effective two-factor authentication (2FA) plugin for WordPress that adds an extra layer of security to your login process. Upon attempting to log in, users receive a 6-digit verification code via email, which they must enter to proceed to the WordPress dashboard.

This plugin is particularly useful for enhancing the security of your WordPress site by ensuring that only users with access to the registered email account can log in.

= Features =

* Sends a unique 6-digit verification code to the user's email on every login attempt.
* Requires users to enter the code before they can access the WordPress dashboard.
* Modern, responsive, and accessible verification form.
* Simple to set up and use, with no additional configuration required.
* Supports WordPress Multisite and works with existing user roles and permissions.

== Installation ==

1. Upload the `cc-2fa` folder to the `/wp-content/plugins/` directory, or install the plugin directly through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The plugin is now active, and users will be required to enter a verification code sent to their email upon logging in.

== Frequently Asked Questions ==

= How does the plugin work? =

When a user attempts to log in to WordPress, the plugin generates a unique 6-digit verification code and sends it to the user's registered email address. The user must then enter this code on a custom verification page before they can access the dashboard.

= Does the plugin work with Multisite? =

Yes, CC 2FA is compatible with WordPress Multisite.

= What happens if a user doesn't receive the email? =

If a user doesn't receive the email, they should check their spam or junk folder. Ensure that the email address associated with the user account is correct and that your WordPress site's email sending capabilities are working correctly.

= Can I customize the email sent to users? =

Currently, the plugin does not include settings for customizing the email content. However, you can modify the code in the `class-cc-2fa.php` file if you wish to change the email subject or body.

= Is the plugin translation-ready? =

Yes, the plugin is fully translation-ready. You can translate the plugin into your desired language using the `cc-2fa.pot` file located in the `languages` directory.

== Changelog ==

= 1.0.1 =
* Renamed plugin from "CC Test" to "CC 2FA".
* Removed the "Hello" field, focusing solely on the verification code.
* Updated text domain to "cc-2fa".
* Improved the form page with a modern, responsive design.

= 1.0.0 =
* Initial release of the plugin.
* Basic two-factor authentication functionality with a "Hello" field.

== Upgrade Notice ==

= 1.0.1 =
Renamed plugin and improved functionality. Ensure that your site is compatible with the changes before updating.

== License ==

This plugin is licensed under the GPLv2 or later. For more information, see https://www.gnu.org/licenses/gpl-2.0.html.
