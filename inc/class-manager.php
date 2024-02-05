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
	 * @change 0.6.0 - Add filter for comment form submit button.
	 */
	public function __construct() {
		$options = Options::get_options();
		if ( $options['enabled'] && isset( $options['script_url'] ) && isset( $options['website_id'] ) && ! is_admin() ) {
			if ( ! empty( $options['website_id'] ) && ! empty( $options['script_url'] ) ) {
				add_action( 'wp_footer', array( $this, 'render_script' ) );

				if ( isset( $options['track_comments'] ) && $options['track_comments'] === 1 ) {
					// Add filters to add event data attributes.
					add_filter( 'comment_form_submit_button', array( $this, 'filter_comment_form_submit_button' ), 10, 2 );
				}
			}
		}

		register_deactivation_hook( INTEGRATE_UMAMI_BASE_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Deactivation callback.
	 *
	 * @since 0.2.0 - Delete options on deactivation.
	 */
	public static function deactivate() {
		Options::delete_options();
	}

	/**
	 * Filter comment submit button to add data attribute.
	 *
	 * @param string $submit_button The submit button.
	 * @param array  $args          The arguments.
	 *
	 * @since 0.6.0
	 */
	public function filter_comment_form_submit_button( string $submit_button, array $args ) {
		return str_replace( '<button', '<button data-umami-event="comment"', $submit_button );
	}

	/**
	 * Callback for script rendering.
	 *
	 * @since 0.1.0
	 * @change 0.2.0 - Added option for ignoring admins.
	 * @change 0.3.0 - Fixed bug with ignore admins option.
	 * @change 0.4.1 - Fix bug with host url option.
	 * @change 0.5.0 - Fix problem with option escaping.
	 */
	public function render_script() {
		$options = Options::get_options();

		if ( $options['ignore_admins'] === 1 && current_user_can( 'manage_options' ) ) {
			return;
		}

		$umami_options = '';
		if ( isset( $options['do_not_track'] ) && $options['do_not_track'] === 1 ) {
			$umami_options .= 'data-do-not-track=true ';
		}
		if ( isset( $options['auto_track'] ) && $options['auto_track'] === 0 ) {
			$umami_options .= 'data-auto-track=false ';
		}
		if ( isset( $options['cache'] ) && $options['cache'] === 1 ) {
			$umami_options .= 'data-cache=true ';
		}
		if ( ! empty( $options['host_url'] ) && isset( $options['use_host_url'] ) && $options['use_host_url'] === 1 ) {
			$umami_options .= 'data-host-url=' . esc_url( $options['host_url'] );
		}

		?>
		<!-- Integrate Umami -->
		<script async defer
				src="<?php echo esc_url( $options['script_url'] ); ?>"
				data-website-id="<?php esc_attr_e( $options['website_id'] ); ?>"
				<?php esc_attr_e( $umami_options ); ?>>
		</script>
		<!-- /Integrate Umami -->
		<?php
	}

}
