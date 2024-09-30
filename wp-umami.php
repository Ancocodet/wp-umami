<?php
/**
 * Plugin Name: Integrate Umami
 * Description: Integration for Umami Analytics
 * Version: 0.7.0
 * Author: Ancocodet
 * Author URI: https://ancozockt.de
 * Plugin URI: https://github.com/Ancocodet/wp-umami
 * License: GPLv3 or later
 * Text Domain: integrate-umami
 *
 * @package Integrate Umami
 */

namespace Ancozockt\Umami;

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

define( 'INTEGRATE_UMAMI_VERSION', '0.7.0' );
define( 'INTEGRATE_UMAMI_BASE_FILE', __FILE__ );

/**
 * Init plugin.
 *
 * @since 0.1.0
 */
function init() {
	new Manager();
	new Settings();
}

\add_action( 'plugins_loaded', 'Ancozockt\Umami\init' );

