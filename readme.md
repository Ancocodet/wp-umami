# Integrate Umami #
**Contributors:** [ancocodet](https://github.com/Ancocodet) <br>
**Tags:** analytics,umami <br>
**Requires at least:** 5.0 <br>
**Tested up to:** 6.6 <br>
**Stable tag:** 0.7.0 <br>
**Requires PHP:** 7.4 <br>
**License:** GPLv3 or later <br>
**License URI:** https://github.com/Ancocodet/wp-umami/blob/main/LICENSE.md <br>

Integrate Umami Analytics into your WordPress site.

## Description ##

This plugin integrates [Umami Analytics](https://umami.is/) into your WordPress site.
Umami is a simple, fast, website analytics tool for those who care about privacy.

## Installation ##

1. Upload the plugin files to the `/wp-content/plugins/integrate-umami` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress
3. Follow the Setup Tracking instructions

### Setup Tracking ###
1. [Add your WordPress-Site to umami](https://umami.is/docs/add-a-website)
2. Go to the Plugin Settings
3. Fill in the websiteId and ScriptUrl
   * websiteId can be found in the website settings
   * scriptUrl is normally %link_to_umami%/script.js or you can found in the website settings under tracking code
4. Enable umami analytics and save your settings

### Requirements ###
* PHP 7.4 or greater
* WordPress 5.0 or greater

## Contribute ##

* Active Development of this plugin is handled [on Github](https://github.com/Ancocodet/wp-umami).
* Pull requests for documented [issues](https://github.com/Ancocodet/wp-umami/issues) are highly appreciated.

## Roadmap ##

* Add easier setup using the umami API
* Add dashboard widget for analytics
* Add filter for adding custom tracking data/events

## Upgrade Notice ##

- **0.7.0** - Preparation for 1.0<br>
This release increases the minimum required PHP version to 7.4.
<br><br>

- **0.6.1** - Update Issue<br>
This release fixes an issue with the update from 0.5.0 to 0.6.0. The update process should now work as expected.
<br><br>

- **0.4.1** - Host URL Issue <br> 
Issues with the settings page were fixed and the overall feeling of the page was improved as well.

## Changelog ##

- **0.7.0** - Preperation for 1.0<br>
<br>Increased the minimum required PHP version to 7.4
<br>Add deprecation information for do_not_track option
<br><br>

- **0.6.1** - Update Issue<br>
<br>Fixed an issue with the update from 0.5.0 to 0.6.0
<br>Fixed an issue with the settings page
<br>Thanks to @markim for reporting the issue
<br><br>

- **0.6.0** - Event Tracking<br>
<br>Added tracking data-attribute for comments
<br><br>

- **0.5.0** - Documentation and plugin action<br>
<br>Added link to Settings Page to Plugin actions
<br>Change Settings page slug to plugin slug (**integrate-umami**)
<br>Updated and expanded documentation
<br>Fixed issue with escaping in script arguments
<br><br>

- **0.4.1** - Host URL Issue <br>
<br>Fixed an issue with the host URL which could cause issues with the tracking.
<br>Moved the host URL option to the advanced section
<br>Thanks to @gioxx for reporting the issue
<br><br>

- **0.4.0** - Improve Settings Page <br> The settings page has been improved to be more user friendly.
<br>Fixed an issue with the options validation
<br><br> 
- **0.3.2 - Update Autoloading** <br> Updated the autoloading to use `plugin_dir_path`
<br><br>
- **0.3.1 - Fix Build Process** <br> Fixed an issue with the building mechanism which resulted in an unusable version
<br>Cleaned some code
<br>Replaced the placeholder logo with a better one
<br><br>
- **0.2.1 - Fix Option Validation** <br> Fixed an issue with the option validation that caused the plugin settings to not work.
<br><br>
- **0.2.0 - Ignore Admin Option** <br> Add an option to ignore admin users.
<br><br>
- **0.1.1 - Fix Deployment** <br> The Deployment does not include the built zip anymore.
