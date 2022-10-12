<?php

namespace Ancozockt\Umami;

/**
 * Class Manager
 * @since 0.1.0
 */
class Manager {

	/**
	 * @since 0.1.0
	 */
	public function __construct() {
		$options = Options::get_options();
		if ( $options['enabled'] && isset( $options['script_url'] ) && isset( $options['website_id'] ) && ! is_admin() ) {
			if( strlen( $options['website_id'] ) > 0 && strlen( $options['script_url'] ) > 0 ){
				add_action('wp_footer', array( __CLASS__, 'render_script' ) );
			}
		}
	}

    /**
     * @since 0.1.0
     * @change 0.1.2 - Added option for ignoring admins.
     */
	public function render_script() {
		$options = Options::get_options();

        if( $options['ignore_admins'] && current_user_can( 'manage_options' ) ){
            return;
        }

		$umami_options = "";
		if ( isset( $options['auto_track'] ) && ! $options['auto_track'] ) {
			$umami_options .= "data-auto-track=\"false\" ";
		}
		if( isset( $options['do_not_track'] ) && $options['do_not_track'] ) {
			$umami_options .= "data-do-not-track=\"true\" ";
		}
		if( isset( $options['cache'] ) && $options['cache'] ) {
			$umami_options .= "data-cache=\"true\" ";
		}
		if( isset( $options['host_url'] ) && strlen( $options['host_url'] ) > 0 ) {
			$umami_options .= "data-host=\"" . esc_url($options['host_url']) . "\" ";
		}

		?>
        <!-- WP-Umami -->
		<script async defer
		        src="<?php echo esc_url($options['script_url']); ?>"
		        data-website-id="<?php esc_attr_e($options['website_id']); ?>"
		        <?php esc_attr_e($umami_options); ?>>
		</script>
        <!-- /WP-Umami -->
		<?php
	}

}
