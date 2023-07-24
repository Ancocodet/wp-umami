=== Integrate Umami ===
Contributors: ancocodet
Tags: analytics,umami
Stable tag: 0.5.0
Requires at least: 5.0
Tested up to: 6.2
Requires PHP: 7.0
License: GPLv3 or later
License URI: https://github.com/Ancocodet/wp-umami/blob/main/LICENSE.md

Integrate Umami Analytics into your WordPress site.

== Description ==

This plugin integrates [Umami Analytics](https://umami.is/) into your WordPress site.
Umami is a simple, fast, website analytics tool for those who care about privacy.

== Installation ==

* If you don’t know how to install a plugin for WordPress, here’s how.

=== Setup Tracking ===
1. [Add your wordpress to umami](https://umami.is/docs/add-a-website)
2. Go to the Plugin Settings
3. Fill in the websiteId and ScriptUrl
   * websiteId can be found in the website settings
   * scriptUrl is normally %link_to_umami%/script.js or you can found in the website settings under tracking code
4. Enable umami analytics and save your settings

=== Requirements ===
* PHP 7.0 or greater
* WordPress 5.0 or greater

== Contribute ==

* Active Development of this plugin is handled [on Github](https://github.com/Ancocodet/wp-umami).
* Pull requests for documented [issues](https://github.com/Ancocodet/wp-umami/issues) are highly appreciated.

== Upgrade Notice ==

= 0.4.1 =
Issues with the settings page were fixed and the overall feeling of the page was improved as well.

== Changelog ==

= 0.5.0 =
* Added link to Settings Page to Plugin actions
* Updated and expanded documentation

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