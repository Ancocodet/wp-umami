<?php

/*
Plugin Name: Integrate Umami
Description: Integration for Umami Analytics
Version: 0.1
Author: Ancocodet
Author URI: https://ancozockt.de
License: GPL
Text Domain: integration_umami
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