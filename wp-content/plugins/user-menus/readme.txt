=== User Menus - Nav Menu Visibility ===
Contributors: codeatlantic, danieliser
Author URI:  https://code-atlantic.com/
Plugin URI:  https://wordpress.org/plugins/user-menus/
Donate link: https://code-atlantic.com/donate/
Tags: menu, menus, user-menu, logout, nav-menu, nav-menus, user, user-role, user-roles
Requires at least: 4.6
Tested up to: 5.8
Stable tag: 1.2.9
Requires PHP: 5.6
Freemius: 2.5.0
License: GPLv3 or Any Later Version

Show/hide menu items to logged in users, logged out users or specific user roles. Display logged in user details in menu. Add a logout link to menu.


== Description ==

User Menus is the perfect plugin for websites that have logged in users.

The plugin gives you more control over your nav menu by allowing you to apply visibility controls to menu items e.g., who can see each menu item (everyone, logged out users, logged in users, specific user roles).

It also enables you to display logged in user information in the navigation menu e.g., “Hello, John Doe”.

Lastly, the plugin allows you to add login, register, and logout links to your menu.

= Full Feature List =

User Menus allows you to do the following:

* Display menu items to everyone
* Display menu items to only logged out users
* Display menu items to only logged in users
* Display menu items to users with or without a specific user role.
* Show a logged in user’s {avatar} in a menu item with a custom size option.
* Show a logged in user’s {username} in a menu item
* Show a logged in user’s {first_name} in a menu item
* Show a logged in user’s {last_name} in a menu item
* Show a logged in user’s {display_name} in a menu item
* Show a logged in user’s nickname} in a menu item
* Show a logged in user’s {email} in a menu item
* Add a logout link to the menu (optional redirect settings)
* Add a register link to the menu (optional redirect settings)
* Add a login link to the menu (optional redirect settings)

** Includes a custom Menu Importer that will allow migrating User Menus data with the normal menu export/import.

= Created by Code Atlantic =

User Menus is built by the [Code Atlantic][codeatlantic] team. We create high-quality WordPress plugins that help you grow your WordPress sites.

Check out some of our most popular plugins:

* [Popup Maker][popupmaker] - #1 Popup & Marketing Plugin for WordPress
* [Content Control][contentcontrol] - Restrict Access to Pages and Posts

**Requires WordPress 4.6 and PHP 5.6**

[codeatlantic]: https://code-atlantic.com "Code Atlantic - High Quality WordPress Plugins"

[popupmaker]: https://wppopupmaker.com "#1 Popup & Marketing Plugin for WordPress"

[contentcontrol]: https://wordpress.org/plugins/content-control/ "Control Who Can Access Content"

== Installation ==

= Minimum Requirements =

* WordPress 4.6 or greater
* PHP version 5.6 or greater

= Installation =

* Install User Menus either via the WordPress.org plugin repository or by uploading the files to your server.
* Activate User Menus.
* Go to wp-admin > Appearance > Menus and edit your menu.

If you need help getting started with User Menus, please see the [FAQs][faq page] that explain how to use the plugin.


[faq page]: https://wordpress.org/plugins/user-menus/faq/ "User Menus FAQ"


== Frequently Asked Questions ==

= How do I set up this plugin? =

* To setup the plugin, go to /wp-admin/ > **Appearance** > **Menus**.
* Add a **menu item** or choose an existing one to edit the User Menus settings.
* To see the User Menus settings, _expand_ the **menu item** that you chose in the **Menu structure** panel.
* Select **Everyone**, **Logged Out Users**, or **Logged In Users** from the **Who can see this link?** dropdown.
* **Logged In Users**: The **Choose which roles can see this link** radio button is selected by default. If no roles are selected, all roles can see the menu item by default. Once a role is checked, then only checked roles can see the menu item.
* **Logged In Users**: The **Choose which roles won't see this link** radio button is **not** selected by default. If no roles are selected, all roles still have visibility to the menu item by default. Once a role is checked, then only checked roles won't see the menu item.
* To show a logged in user’s information in a **menu item**, make a **menu item** only visible to logged in users. Click the grey arrow button to add a user tag (username, first_name, last_name, nickname, display_name, email) to the **menu item** label.
* To add a login or logout link to your menu, expand the **User Links** under the **Add menu items** panel, check **Login** or **Logout**, then click **Add to Menu**.

= Where can I get support? =

If you get stuck, you can ask for help in the [User Menu Plugin Forum](https://wordpress.org/support/plugin/user-menus).

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in our support forum or preferably on the [User Menu GitHub repository](https://github.com/jungleplugins/user-menus/issues).


== Screenshots ==

1. Limit menu item visibility based on logged in status, user role etc.
2. Display user information such as username, first name etc in your menu text.
3. Quickly insert login/logout links & choose where users will be taken afterwards.


== Changelog ==

= v1.2.9 - 03/02/2022 =

* Tweak: Downgrade freemius sdk to the latest stable (previously version was Release Candidate).

= v1.2.8 - 03/02/2022 =

* Tweak: Update freemius sdk to the latest version.

= v1.2.7 - 07/21/2021 =

* Fix: Bug due to variable type mismatch which caused children of protected items to be rendered.

= v1.2.6 - 07/20/2021 =

* Improvement: Update Freemius to 2.4.2
* Improvement: Code styling clean up.
* Improvement: Compatibility with jQuery v3.

= v1.2.5 - 12/31/2020 =

* Improvement:Update Freemius to 2.4.1

= v1.2.4 - 08/20/2020 =

* Improvement: Removed class that could cause links to be disabled with some themes.
* Tweak: Update Freemius sdk to v2.4.0.1.
* Fix: Compatibility issue with some sites where duplicate fields were shown in the menu editor.

= v1.2.3 - 3/23/2020 =

* Tweak: Add compatibility fix for WP 5.4 menu walker

= v1.2.2 - 12/17/2019 =

* Improvement: Login, Register & Logout menu links now hint at who they will be visible for.
* Fix: Deprecation notice for sites using PHP 7.4

= v1.2.1 - 10/20/2019 =

* Fix: Bug in some sites where Menu Editor Description field was not shown.

= v1.2.0 - 10/10/2019 =

* Feature: Added option to *show* or *hide* the menu item for chosen roles.
* Feature: Added Register user link navigation menu type with optional redirect.
* Improvement: Added Freemius integration to allow for future premium offerings
* Tweak: Updates brand from Jungle Plugins to Code Atlantic (nothing has changed, just the name).
* Tweak: Minor text and design changes.
* Fix: Bug where missing data in menu items caused an error to be thrown in edge cases.

= v1.1.3 =

* Improvement: Corrected usage of get_avatar to ensure compatibility with 3rd party avatar plugins.

= v1.1.2 =

* Improvement: Made changes to the nav menu editor to make it more compatible with other plugins.

= v1.1.1 =

* Fix: Forgot to add new files during commit. Correcting this issue.

= v1.1.0 =

* Feature: Added ability to insert user avatar in menu items with size option to match your needs.
* Improvement: Added accessibility enhancements to menu editor. Includes keyboard support, proper focus, tabbing & titles.
* Improvement: Added proper labeling to the user code dropdown.
* Tweak: Restyled user code insert elements to better resemble default WP admin.

= v1.0.0 =

* Initial Release
