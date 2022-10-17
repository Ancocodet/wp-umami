<?php

namespace Ancozockt\Umami;

class Options {

	/**
	 * @since 0.1.0
	 * @change 0.2.0 - Added default for ignore_admin.
	 *
	 * @return array
	 */
	public static function get_options() : array {
		return wp_parse_args(
			get_option( 'umami_options'),
			array(
				'enabled' => false,
				'script_url' => '',
				'host_url' => '',
				'website_id' => '',
				'ignore_admins' => true,
				'auto_track' => true,
				'do_not_track' => true,
				'cache' => false,
			)
		);
	}

	/**
	 * @since 0.1.2
	 */
	public static function delete_options() {
		delete_option( 'umami_options' );
	}

}