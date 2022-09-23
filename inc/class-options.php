<?php

namespace Ancozockt\Umami;

class Options {

	/**
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
				'auto_track' => true,
				'do_not_track' => true,
				'cache' => false,
			)
		);
	}

}