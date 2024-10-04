<?php

namespace Ancozockt\Umami;

/**
 * Class Widget
 *
 * @since 0.8.0
 */
class DashboardWidget {

	/**
	 * Widget constructor.
	 *
	 * @since 0.8.0
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$options = Options::get_options();
		if ( ! $options['enabled'] ) {
			return;
		}

		add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
	}

	/**
	 * Add the widget to the dashboard.
	 *
	 * @since 0.8.0
	 */
	public function add_widget() {
		wp_add_dashboard_widget(
			'umami_widget',
			'Umami Analytics',
			array( $this, 'render_widget' )
		);
	}

	/**
	 * Render the widget.
	 *
	 * @since 0.8.0
	 */
	public function render_widget() {
		$options    = Options::get_options();
		$website_id = $options['website_id'];
		$host_url   = $options['host_url'];
		$script_url = $options['script_url'];

		// Check if the settings are configured.
		if ( ! empty( $website_id ) && ! empty( $script_url ) ) {
			if ( empty( $host_url ) ) {
				$host_url = wp_parse_url( $script_url, PHP_URL_SCHEME ) . '://' . wp_parse_url( $script_url, PHP_URL_HOST );
			}
			$url = $host_url . '/admin/websites/' . $website_id;
			echo wp_kses(
				// translators: %s => Umami Admin page URL.
				sprintf( __( 'View you analytics on <a href="%s" target="_blank">Umami Analytics</a>', 'integrate-umami' ), esc_url( $url ) ),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				)
			);
			return;
		}

		echo wp_kses(
			__( 'Please configure the Umami Analytics settings.', 'integrate-umami' ),
			array(
				'p' => array(),
			)
		);
	}
}
