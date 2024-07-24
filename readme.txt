=== Integrate Umami ===
Contributors: ancocodet
Tags: analytics,umami
Stable tag: 0.7.0
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://github.com/Ancocodet/wp-umami/blob/main/LICENSE.md

Integrate Umami Analytics into your WordPress site.

== Description ==

This plugin integrates [Umami Analytics](https://umami.is/) into your WordPress site.
Umami is a simple, fast, website analytics tool for those who care about privacy.

== Installation ==

* If you don’t know how to install a plugin for WordPress, here’s how.

1. Upload the plugin files to the `/wp-content/plugins/integrate-umami` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress
3. Follow the Setup Tracking instructions

=== Setup Tracking ===
1. [Add your WordPress-Site to umami](https://umami.is/docs/add-a-website)
2. Go to the Plugin Settings
3. Fill in the websiteId and ScriptUrl
   * websiteId can be found in the website settings
   * scriptUrl is normally %link_to_umami%/script.js or you can found in the website settings under tracking code
4. Enable umami analytics and save your settings

=== Requirements ===
* PHP 7.4 or greater
* WordPress 5.0 or greater

== Contribute ==

* Active Development of this plugin is handled [on Github](https://github.com/Ancocodet/wp-umami).
* Pull requests for documented [issues](https://github.com/Ancocodet/wp-umami/issues) are highly appreciated.

== Upgrade Notice ==

= 0.7.0 =
This release increases the minimum php version to 7.4 as a preperation for the 1.0.

= 0.6.1 =
This release fixes an issue with the update from 0.5.0 to 0.6.0. The update process should now work as expected.

= 0.4.1 =
Issues with the settings page were fixed and the overall feeling of the page was improved as well.

== Changelog ==

= 0.7.0 =
* Increased the minimum required PHP version to 7.4
* Add deprecation information for do_not_track option

= 0.6.1 =
* Fixed an issue with the update from 0.5.0 to 0.6.0
* Fixed an issue with the settings page
* Thanks to [@markim](https://github.com/markim) for reporting the issue

= 0.6.0 =
* Added tracking data-attribute for comment submits (disabled by default)
* Tested for newer WordPress versions

= 0.5.0 =
* Added link to Settings Page to Plugin actions
* Change Settings page slug to plugin slug
* Updated and expanded documentation
* Fixed issue with escaping in script arguments

= 0.4.1 =
* Fixed an issue with the host URL which could cause issues with the tracking.
* Moved the host URL option to the advanced section
* Thanks to [@gioxx](https://github.com/gioxx) for reporting the issue

= 0.4.0 =
* Improved options page to be more user friendly
* Fixed an issue with the options validation

= 0.3.2 =
* Updated the autoloading to use plugin_dir_path

= 0.3.1 =
* Fixed an issue with the building mechanism which resulted in an unusable version
* Cleaned some code
* Replaced the placeholder logo with a better one

= 0.2.1 =
* Fixed an issue with the option validation that caused the plugin settings to not work.

= 0.2.0 =
* Add an option to ignore admin users.

= 0.1.1 =
* The deployment does not include the built zip anymore.

== Screenshots ==
1. Settings page
2. Advanced options
