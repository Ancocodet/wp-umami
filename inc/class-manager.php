<?php
namespace Ancozockt\Umami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Manager
 *
 * @since 0.1.0
 */
class Manager {
	/**
	 * Manager constructor.
	 *
	 * @since 0.1.0
	 * @change 0.2.0 - Add deactivation hook.
	 */
	public function __construct() {
		$options = Options::get_options();
		if ( $options['enabled'] && isset( $options['script_url'] ) && isset( $options['website_id'] ) && ! is_admin() ) {
			if ( ! empty( $options['website_id'] ) && ! empty( $options['script_url'] ) ) {
				add_action( 'wp_footer', array( __CLASS__, 'render_script' ) );
			}
		}

		register_deactivation_hook( INTEGRATE_UMAMI_BASE_FILE, array( __CLASS__, 'deactivate' ) );
	}

	/**
	 * Deactivation callback.
	 *
	 * @since 0.2.0 - Delete options on deactivation.
	 */
	public function deactivate() {
		Options::delete_options();
	}

	/**
	 * Callback for script rendering.
	 *
	 * @since 0.1.0
	 * @change 0.2.0 - Added option for ignoring admins.
	 * @change 0.3.0 - Fixed bug with ignore admins option.
	 */
	public function render_script() {
		$options = Options::get_options();

		if ( $options['ignore_admins'] === 1 && current_user_can( 'manage_options' ) ) {
			return;
		}

		$umami_options = '';
		if ( isset( $options['auto_track'] ) && $options['auto_track'] === 0 ) {
			$umami_options .= 'data-auto-track="false" ';
		}
		if ( isset( $options['do_not_track'] ) && $options['do_not_track'] === 1 ) {
			$umami_options .= 'data-do-not-track="true" ';
		}
		if ( isset( $options['cache'] ) && $options['cache'] === 1 ) {
			$umami_options .= 'data-cache="true" ';
		}
		if ( isset( $options['host_url'] ) && ! empty( $options['host_url'] ) ) {
			$umami_options .= 'data-host="' . esc_url( $options['host_url'] ) . '" ';
		}

		?>
		<!-- WP-Umami -->
		<script async defer
				src="<?php echo esc_url( $options['script_url'] ); ?>"
				data-website-id="<?php esc_attr_e( $options['website_id'] ); ?>"
			<?php esc_attr_e( $umami_options ); ?>>
		</script>
		<!-- /WP-Umami -->
		<?php
	}

}
