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
	 * The Plugin options.
	 *
	 * @var array $options
	 */
	private array $options;

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
					add_action( 'wp_footer', array( $this, 'render_comment_tracking_script' ) );
				}
			}
		}

		new DashboardWidget();

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
	 * Render JavaScript snippet for comment form event tracking.
	 *
	 * Replaces the old PHP regex approach (filter_comment_form_submit_button)
	 * which was fragile and broke with custom themes and comment plugins (#39).
	 * This JS-based approach dynamically finds the comment form submit button
	 * at DOM ready, working with native WP comments, wpDiscuz, and most plugins.
	 *
	 * @since 0.9.0
	 */
	public function render_comment_tracking_script() {
		?>
		<!-- Integrate Umami: Comment Tracking -->
		<script>
		(function() {
			var selectors = [
				'#commentform input[type="submit"]',
				'#commentform button[type="submit"]',
				'.comment-form input[type="submit"]',
				'.comment-form button[type="submit"]'
			];
			function attachTracking() {
				for (var i = 0; i < selectors.length; i++) {
					var btn = document.querySelector(selectors[i]);
					if (btn && !btn.hasAttribute('data-umami-event')) {
						btn.setAttribute('data-umami-event', 'comment');
					}
				}
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', attachTracking);
			} else {
				attachTracking();
			}
		})();
		</script>
		<!-- /Integrate Umami: Comment Tracking -->
		<?php
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

		// Trim whitespace from critical values (fixes copy-paste issues, #28).
		$script_url = trim( $options['script_url'] ?? '' );
		$website_id = trim( $options['website_id'] ?? '' );

		$attrs = array();

		if ( isset( $options['do_not_track'] ) && 1 === $options['do_not_track'] ) {
			$attrs[] = 'data-do-not-track="true"';
		}
		if ( isset( $options['auto_track'] ) && 0 === $options['auto_track'] ) {
			$attrs[] = 'data-auto-track="false"';
		}
		if ( isset( $options['cache'] ) && 1 === $options['cache'] ) {
			$attrs[] = 'data-cache="true"';
		}
		if ( ! empty( $options['host_url'] ) && isset( $options['use_host_url'] ) && 1 === $options['use_host_url'] ) {
			$attrs[] = 'data-host-url="' . esc_url( trim( $options['host_url'] ) ) . '"';
		}

		// v3 tracker attributes.
		if ( ! empty( $options['tag'] ) ) {
			$attrs[] = 'data-tag="' . esc_attr( trim( $options['tag'] ) ) . '"';
		}
		if ( ! empty( $options['domains'] ) ) {
			$attrs[] = 'data-domains="' . esc_attr( trim( $options['domains'] ) ) . '"';
		}
		if ( isset( $options['exclude_search'] ) && 1 === $options['exclude_search'] ) {
			$attrs[] = 'data-exclude-search="true"';
		}
		if ( isset( $options['exclude_hash'] ) && 1 === $options['exclude_hash'] ) {
			$attrs[] = 'data-exclude-hash="true"';
		}
		if ( ! empty( $options['before_send'] ) ) {
			$attrs[] = 'data-before-send="' . esc_attr( trim( $options['before_send'] ) ) . '"';
		}

		$extra_attrs = ! empty( $attrs ) ? "\n\t\t\t\t" . implode( "\n\t\t\t\t", $attrs ) : '';

		?>
		<!-- Integrate Umami -->
		<script async defer
				src="<?php echo esc_url( $script_url ); ?>"
				data-website-id="<?php echo esc_attr( $website_id ); ?>"<?php echo $extra_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- all values escaped above. ?>>
		</script>
		<!-- /Integrate Umami -->
		<?php
	}
}
