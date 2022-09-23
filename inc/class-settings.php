<?php

namespace Ancozockt\Umami;

class Settings {

	public function __construct() {
		if( is_admin() ){
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
			add_action( 'admin_init', array( __CLASS__, 'load_textdomain' ) );
			add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		}
	}

    public function load_textdomain() {
        load_plugin_textdomain('umami_integration' );
    }

	public function register_settings(){
		register_setting(
                'umami_integration',
                'umami_options',
                array( __CLASS__, 'validate_options' )
        );
	}

	public function add_page(){
		add_options_page(
			__('WP-Umami', 'umami_integration'),
			__('WP-Umami', 'umami_integration'),
			'manage_options',
			'umami_integration',
			array( __CLASS__, 'render_options_page' )
		);
	}

	public function validate_options( $data ): array {
		if ( empty( $data ) ) {
			return array();
		}

		return array(
            'enabled' => $data['enabled'] ?? false,
			'script_url' => esc_url_raw( $data['script_url'] ),
			'host_url' => esc_url_raw( $data['script_url'] ),
			'website_id' => sanitize_text_field( $data['website_id'] ),
			'auto_track' => $data['auto_track'] ?? false,
            'do_not_track' => $data['do_not_track'] ?? true,
            'cache' => $data['cache'] ?? false,
		);
	}

	public function render_options_page() {
        $options = Options::get_options();
		?>
		<div class="wrap" id="umami_integration">
			<h1><?php echo __('WP-Umami Settings', 'umami_integration'); ?></h1>
            <?php include 'templates/settings-page.php'; ?>
		</div>
		<?php
	}

}