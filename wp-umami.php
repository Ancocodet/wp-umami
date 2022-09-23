<?php

/*
Plugin Name: Umami Integration
Description: Integration for Umami Analytics
Version: 0.1
Author: Ancocodet
Author URI: https://ancozockt.de
License: GPL
Text Domain: umami_integration
*/

namespace Ancozockt\Umami;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Init plugin.
 * @return void
 */
function init() {
	new Manager();
	new Settings();
}

\add_action( 'plugins_loaded', 'Ancozockt\Umami\init' );