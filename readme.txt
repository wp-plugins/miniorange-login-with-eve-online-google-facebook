=== miniOrange - Login with EVE Online, Google, Facebook ===
Contributors: miniOrange
Tags: eveonline, Login with EVE Online, EVE Online Login, EVE Online SSO, EVE Online Single Sign on, EVE Online OAUTH, OAUTH, miniorange, miniorange oauth, oauth google, oauth eveonline, oauth facebook, miniorange sso, google sso, eveonline sso, facebook sso, google login, eveonline login, eve online, eve online sso, eve online login, oauth eve online, EVE Online API Authentication
Requires at least: 3.0.1
Tested up to: 4.2.2
Stable tag: 4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

OAuth login through your favorite apps like EVE Online, Google, Facebook for user authentication.

== Description ==

Login with famous applications such as EVE Online and Google. If you require any other application, please free to email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.

= Features =
*	Login to your Wordpress site using EVE Online and Google.
*	Valid user registrations verified by applications such as EVE Online, Google.
*	You can disable the applications which you don't require.
*	Easily integrate plugin with your Wordpress website using widgets. Just drop it in a desirable place in your website.
*	Automatic user registration after login if the user is not already registered with your site.

= EVE Online specific features =
*	One-click login to Wordpress through EVE Online application.
*	Restrict who logs in - administrators can restrict users which can log in through Eve Online. This can be on the basis of user's Corporation Name, Alliance Name and Character Name.
*	Save the Corporation Name, Alliance Name, Character Name of characters who login to their profiles.
*	Also takes care of updating the Corporation or Alliance name of the user if it changes.

== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`
2. Search for `EVE Online Login`. Find and Install `miniOrange - Login with EVE Online`
3. Activate the plugin from your Plugins page

= From WordPress.org =
1. Download miniOrange - Login with EVE Online.
2. Unzip and upload the `miniorange-oauth-login` directory to your `/wp-content/plugins/` directory.
3. Activate miniOrange OAuth from your Plugins page.

= Once Activated =
1. Go to `Settings-> miniOrange OAuth -> Configure OAuth`, and follow the instructions
2. Go to `Appearance->Widgets` ,in available widgets you will find `miniOrange OAuth` widget, drag it to chosen widget area where you want it to appear.
3. Now visit your site and you will see login with widget.

= For Viewing Corporation, Alliance, Character Name in user profile =
To view Corporation, Alliance and Character Name in edit user profile, copy the following code in the end of your theme's `Theme Functions(functions.php)`. You can find `Theme Functions(functions.php)` in `Appearance->Editor`.
<code>
add_action( 'show_user_profile', 'mo_oauth_my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'mo_oauth_my_show_extra_profile_fields' );
</code>

== Frequently Asked Questions ==
= I need to customize the plugin or I need support and help? =
Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>. You can also submit your query from plugin's configuration page.

= I don't see any applications to configure. I only see Register to miniOrange? =
Our very simple and easy registration lets you register to miniOrange. OAuth login works if you are connected to miniOrange. Once you have registered with a valid email-address and phone number, you will be able to configure applications for OAuth.

= How to configure the applications? =
When you want to configure a particular application, you will see a Save Settings button, and beside that a Help button. Click on the Help button to see configuration instructions.

= How do I see Corporation, Alliance and Character Name from EVE Online? =
You can view your Corporation, Alliance and Character Name in your Edit Profile. Copy the following code at the end of your theme's `Theme Functions(functions.php)` page. You can find `Theme Functions(functions.php)` in `Appearance->Editor`.

<code>
add_action( 'show_user_profile', 'mo_oauth_my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'mo_oauth_my_show_extra_profile_fields' );
</code>

If you still can't see any values in the textbox. You need to login through EVE Online to Wordpress site to get those values.

= I already have existing user in my wordpress site. I want this plugin to use the user's existing profile and do not create another profile? =
We will help you in this migration process. Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>. You can also submit your query from plugin's configuration page.

= I need integration of this plugin with my other installed plugins like BuddyPress, etc.? =
We will help you in integrating this plugin with your other installed plugins. Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>. You can also submit your query from plugin's configuration page.

= I verified the OTP received over my email and entering the same password that I registered with, but I am still getting the error message - "Invalid password." =
Please write to us at info@miniorange.com and we will get back to you very soon.

= For any other query/problem/request =
Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>. You can also submit your query from plugin's configuration page.

== Screenshots ==

1. Configure OAuth Applications
2. Advanced Settings for EVE Online

== Changelog ==

= 1.8 =
* Sets last_name as EVE Online Character Name when user logs in for the first time

= 1.7 =
* Bug fixes for some users facing problem after sign in

= 1.6 =
* Bug fixes.

= 1.5 =
* Fixed bug where user was not redirecting to EVE Online in some php versions.

= 1.4 =
* Bug fixes

= 1.3 =
* Bug fixes

= 1.2 =
* Bug fixes

= 1.1 =
* Added email ID verification during registration.

= 1.0.5 =
* Added Login with Facebook

= 1.0.4 =
* Updates user's profile picture with his EVE Online charcater image.
* Submit your query (Contact Us) from within the plugin.

= 1.0.3 =
* Bug fix

= 1.0.2 =
* Resolved EVE Online login flow bug in some cases

= 1.0.1 =
* Resolved some bug fixes.

= 1.0 =
* First version with supported applications as EVE Online and Google.

== Upgrade Notice ==

= 1.8 =
* Sets last_name as EVE Online Character Name when user logs in for the first time

= 1.7 =
* Bug fixes for some users facing problem after sign in

= 1.6 =
* Bug fixes.

= 1.5 =
* Fixed bug where user was not redirecting to EVE Online in some php versions.

= 1.4 =
* Bug fixes

= 1.3 =
* Bug fixes

= 1.2 =
* Bug fixes

= 1.1 =
* Added email ID verification during registration.

= 1.0.5 =
* Added Login with Facebook

= 1.0.4 =
* Updates user's profile picture with his EVE Online charcater image.

= 1.0.3 =
* Bug fix

= 1.0.2 =
* Updated version

= 1.0.1 =
* Updated version

= 1.0 =
First version of plugin.