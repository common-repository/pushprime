=== Plugin Name ===
Contributors: pushrime
Plugin Name: PushPrime
Plugin URI: https://pushprime.com/
Tags: push notifications, website push notifications, chrome push notifications, firefox push notifications, safari push notifications
Requires at least: 2.7
Tested up to: 4.7
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Make your website fly and increase your revenue with PushPrime push notifications.

== Description ==

Installing the PushPrime plugin will automatically insert the PushPrime Smart Code on your WordPress website. To get started, you just have to copy the PushPrime Website Id(Your Website Id can be found in the PushPrime dashboard) and paste it in this plugins’s settings.

What is PushPrime?

PushPrime lets you talk to your subscribers in an easy and delightful manner, using push notifications on browser. Push Notifications are clickable messages sent directly to your subscribers’ browsers (even when they are not on your website). These work on all devices — desktops, tablets and even mobile phones — so you don’t even have to invest in building a mobile app for your business. The opt-in and click rates are amazing!.

Let us help you get amazing returns on your communications. For any questions, please get in touch with us at info@pushprime.com

== Installation ==

Wordpress : Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Upgrade Notice ==

= 1.0 =

== Frequently Asked Questions ==

= I can't see any code added to my header or footer when I view my page source =
Your theme needs to have the header and footer actions in place before the `</head>` and before the `</body>`

= If I use this plugin, do I need to enter any other code on my website? =
No, this plugin is sufficient by itself

== Screenshots ==

1. Copy the website Id from your PushPrime Dashboard
2. Paste it into the 'Your PushPrime Website ID' field on PushPrime settings Page
3. Compose a notifications, hit send
4. Notification appears on the screens of your users
5. Setup api key to send push notification while publishing an article

== ChangeLog ==

= 1.5 =
* Native Opt-in Support Added.

= 1.4 =
* Fixed an issue which will resend notification on edit article.

= 1.1 =
* Now send Push Notification while publishing an article

= 1.0 =
* First Version

== Configuration ==

Enter your PushPrime Website Id in the field marked 'Your PushPrime Website ID'

== Adding to your template ==

header code :
`<?php wp_head();?>`

footer code :
`<?php wp_footer();?>`