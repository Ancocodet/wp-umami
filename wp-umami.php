<?php

/*
Plugin Name: Integrate Umami
Description: Integration for Umami Analytics
Version: 0.2.0
Author: Ancocodet
Author URI: https://ancozockt.de
License: GPL
Text Domain: integrate-umami
*/

namespace Ancozockt\Umami;

require_once __DIR__ . '/vendor/autoload.php';

define( 'INTEGRATE_UMAMI_VERSION', '0.2.0' );
define( 'INTEGRATE_UMAMI_BASE_FILE', __FILE__ );

/**
 * Init plugin.
 * @since 0.1.0
 */
function init() {
	new Manager();
	new Settings();
}

\add_action( 'plugins_loaded', 'Ancozockt\Umami\init' );

