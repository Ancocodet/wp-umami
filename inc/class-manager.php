<?php

namespace Ancozockt\Umami;

class Manager {

	public function __construct() {
		$options = Options::get_options();
		if ( $options['enabled'] && isset( $options['script_url'] ) && isset( $options['website_id'] ) && ! is_admin() ) {
			if( strlen( $options['website_id'] ) > 0 && strlen( $options['script_url'] ) > 0 ){
				add_action('wp_footer', array( __CLASS__, 'render_script' ) );
			}
		}
	}

	public function render_script() {
		$options = Options::get_options();

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
			$umami_options .= "data-host=\"" . $options['host_url'] . "\" ";
		}

		?>
        <!-- WP-Umami -->
		<script async defer
		        src="<?php echo $options['script_url']; ?>"
		        data-website-id="<?php echo $options['website_id']; ?>"
		        <?php echo $umami_options; ?>>
		</script>
        <!-- /WP-Umami -->
		<?php
	}

}
