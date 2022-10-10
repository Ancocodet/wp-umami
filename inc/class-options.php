<?php

namespace Ancozockt\Umami;

class Options {

	/**
	 * @since 0.1.0
	 * @change 0.1.2
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
				'ignore_admin' => true,
				'auto_track' => true,
				'do_not_track' => true,
				'cache' => false,
			)
		);
	}

}