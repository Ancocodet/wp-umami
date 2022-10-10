<?php

namespace Ancozockt\Umami;

/**
 * Class Settings
 * @since 0.1.0
 */
class Settings {

	/**
	 * @since 0.1.0
     * @return void
	 */
	public function __construct() {
		if( is_admin() ){
			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
			add_action( 'admin_init', array( __CLASS__, 'load_textdomain' ) );
			add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		}
	}

	/**
	 * @since 0.1.0
	 * @return void
	 */
    public function load_textdomain() {
        load_plugin_textdomain( 'integrate-umami' );
    }


	/**
	 * @since 0.1.0
	 * @return void
	 */
	public function register_settings(){
		register_setting(
                'integration_umami',
                'umami_options',
                array( __CLASS__, 'validate_options' )
        );
	}

	/**
	 * @since 0.1.0
	 * @return void
	 */
	public function add_page(){
		add_options_page(
			__('WP-Umami', 'integrate-umami'),
			__('WP-Umami', 'integrate-umami'),
			'manage_options',
			'integration_umami',
			array( __CLASS__, 'render_options_page' )
		);
	}

    /**
     * @since 0.1.0
     * @change 0.1.2
     * @param $data
     * @return array
     */
	public function validate_options( $data ): array {
		if ( empty( $data ) ) {
			return array();
		}

		return array(
            'enabled' => $data['enabled'] ?? false,
			'script_url' => esc_url_raw( $data['script_url'] ),
			'host_url' => esc_url_raw( $data['script_url'] ),
			'website_id' => sanitize_text_field( $data['website_id'] ),
            'ignore_admin' => $data['ignore_admin'] ?? true,
			'auto_track' => $data['auto_track'] ?? false,
            'do_not_track' => $data['do_not_track'] ?? true,
            'cache' => $data['cache'] ?? false,
		);
	}

	/**
     * @since 0.1.0
	 * @return void
	 */
	public function render_options_page() {
        $options = Options::get_options();
		?>
		<div class="wrap" id="integration_umami">
			<h1><?php echo __('WP-Umami Settings', 'integrate-umami'); ?></h1>
            <?php include 'templates/settings-page.php'; ?>
		</div>
		<?php
	}

}