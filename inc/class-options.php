<?php
namespace Ancozockt\Umami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for managing the options.
 *
 * @since 0.1.0
 */
class Options {
	/**
	 * Get the options.
	 *
	 * @since 0.1.0
	 * @change 0.2.0 - Added default for ignore_admin.
	 * @change 0.6.0 - Added default for track_comments.
	 * @change 0.8.0 - Add migration for old options.
	 *
	 * @return array
	 */
	public static function get_options(): array {
		self::maybe_migrate_options();
		return wp_parse_args(
			get_option( 'integrate_umami_options' ),
			array(
				'enabled'        => 0,
				'script_url'     => '',
				'host_url'       => '',
				'website_id'     => '',
				'use_host_url'   => 0,
				'ignore_admins'  => 1,
				'auto_track'     => 1,
				'do_not_track'   => 1,
				'cache'          => 0,
				'track_comments' => 0,
			)
		);
	}

	/**
	 * Delete the options.
	 *
	 * @since 0.2.0 - Delete umami_options.
	 * @since 0.8.0 - Delete integrate_umami_options.
	 */
	public static function delete_options() {
		if ( get_option( 'umami_options' ) ) {
			delete_option( 'umami_options' );
		}
		delete_option( 'integrate_umami_options' );
	}

	/**
	 *  Migrate options from old version.
	 *
	 * @since 0.8.0 - Migrate options from old version.
	 */
	private static function maybe_migrate_options() {
		if ( empty( get_option( 'integrate_umami_options' ) ) ) {
			if ( ! empty( get_option( 'umami_options' ) ) ) {
				update_option( 'integrate_umami_options', get_option( 'umami_options' ) );
				delete_option( 'umami_options' );
			}
		}
	}
}
