<?php
/**
 * PHPUnit bootstrap file for wp-umami unit tests.
 *
 * Uses WP_Mock to stub WordPress functions so tests run
 * without a full WordPress installation.
 *
 * @package Integrate_Umami
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

WP_Mock::bootstrap();

// Define WordPress constants that the plugin expects.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}
