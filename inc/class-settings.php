<?php
namespace Ancozockt\Umami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 *
 * @since 0.1.0
 */
class Settings {

	/**
	 * Settings constructor.
	 *
	 * @since 0.1.0
     * @change 0.3.3 Fix an issue with hook calls.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_init', array( $this, 'load_textdomain' ) );
			add_action( 'admin_menu', array( $this, 'add_page' ) );
		}
	}

	/**
	 * Load the plugin textdomain.
	 *
	 * @since 0.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'integrate-umami' );
	}


	/**
	 * Register settings
	 *
	 * @since 0.1.0
	 */
	public function register_settings() {
		register_setting(
			'integration_umami',
			'umami_options',
			array( $this, 'validate_options' )
		);
	}

	/**
	 * Add umami settings page.
	 *
	 * @since 0.1.0
	 */
	public function add_page() {
		add_options_page(
			__( 'Integrate Umami', 'integrate-umami' ),
			__( 'Integrate Umami', 'integrate-umami' ),
			'manage_options',
			'integration_umami',
			array( $this, 'render_options_page' )
		);
	}

	/**
	 * Option validation and sanitization.
	 *
	 * @param array $data The data to validate.
	 *
	 * @since 0.1.0
	 * @change 0.2.1
	 *
	 * @return array The validated data.
	 */
	public function validate_options( array $data ): array {
		if ( empty( $data ) ) {
			return array();
		}

		return array(
			'enabled'       => (int) ( $data['enabled'] ?? false ),
			'script_url'    => esc_url_raw( $data['script_url'] ),
			'host_url'      => esc_url_raw( $data['script_url'] ),
			'website_id'    => sanitize_text_field( $data['website_id'] ),
			'ignore_admins' => (int) $data['ignore_admins'],
			'auto_track'    => (int) $data['auto_track'],
			'do_not_track'  => (int) $data['do_not_track'],
			'cache'         => (int) $data['cache'],
		);
	}

	/**
	 * Render settings page.
	 *
	 * @since 0.1.0
	 */
	public function render_options_page() {
		$options = Options::get_options();
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
			<div class="wrap" id="integration_umami">
				<h1><?php echo esc_html__( 'Integrate Umami Settings', 'integrate-umami' ); ?></h1>
				<?php include 'templates/settings-page.php'; ?>
			</div>
		<?php
	}

}
