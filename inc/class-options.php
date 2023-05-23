<?php
/**
 * Class for managing the options.
 *
 * @package Integrate Umami
 */

namespace Ancozockt\Umami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Options
 *
 * @since 0.1.0
 */
class Options {
	/**
	 * Get the options.
	 *
	 * @since 0.1.0
	 * @change 0.2.0 - Added default for ignore_admin.
	 *
	 * @return array
	 */
	public static function get_options(): array {
		return wp_parse_args(
			get_option( 'umami_options' ),
			array(
				'enabled'       => 0,
				'script_url'    => '',
				'host_url'      => '',
				'website_id'    => '',
				'use_host_url'  => 0,
				'ignore_admins' => 1,
				'auto_track'    => 1,
				'do_not_track'  => 1,
				'cache'         => 0,
			)
		);
	}

	/**
	 * Delete the options.
	 *
	 * @since 0.2.0 - Delete umami_options.
	 */
	public static function delete_options() {
		delete_option( 'umami_options' );
	}
}
