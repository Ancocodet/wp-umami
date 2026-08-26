<?php
/**
 * Unit tests for the Manager class.
 *
 * @package Integrate_Umami
 */

namespace Ancozockt\Umami\Tests;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use Ancozockt\Umami\Manager;
use Ancozockt\Umami\Options;

/**
 * Tests for Manager class — script rendering and comment tracking.
 */
class ManagerTest extends TestCase {

	/**
	 * Test render_script outputs correct script tag with basic options.
	 *
	 * This validates the esc_attr_e bug fix — the output must contain
	 * the raw website ID value, not a translated string.
	 */
	public function test_render_script_outputs_correct_attributes() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'abc-123-def-456',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )
			->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		// Must contain the script URL.
		$this->assertStringContainsString( 'src="https://stats.example.com/script.js"', $output );

		// Must contain the website ID as a data attribute value.
		$this->assertStringContainsString( 'data-website-id="abc-123-def-456"', $output );

		// Must NOT contain data-do-not-track (disabled).
		$this->assertStringNotContainsString( 'data-do-not-track', $output );

		// Must NOT contain data-auto-track=false (auto_track is enabled).
		$this->assertStringNotContainsString( 'data-auto-track=false', $output );
	}

	/**
	 * Test render_script includes do-not-track when enabled.
	 */
	public function test_render_script_includes_do_not_track() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 1,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-do-not-track', $output );
	}

	/**
	 * Test render_script outputs data-tag when tag option is set.
	 *
	 * This test WILL FAIL until we implement the tag attribute.
	 */
	public function test_render_script_includes_data_tag() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => 'my-ab-test',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-tag="my-ab-test"', $output );
	}

	/**
	 * Test render_script outputs data-domains when domains option is set.
	 *
	 * This test WILL FAIL until we implement the domains attribute.
	 */
	public function test_render_script_includes_data_domains() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => 'example.com,www.example.com',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-domains="example.com,www.example.com"', $output );
	}

	/**
	 * Test render_script outputs data-exclude-search when option enabled.
	 */
	public function test_render_script_includes_exclude_search() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 1,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-exclude-search="true"', $output );
	}

	/**
	 * Test render_script outputs data-exclude-hash when option enabled.
	 */
	public function test_render_script_includes_exclude_hash() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 1,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-exclude-hash="true"', $output );
	}

	/**
	 * Test render_script outputs data-before-send when option is set.
	 */
	public function test_render_script_includes_before_send() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => 'myFilterFunction',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-before-send="myFilterFunction"', $output );
	}

	/**
	 * Test render_script skips tracking for admin users when ignore_admins enabled.
	 */
	public function test_render_script_skips_admin_users() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 1,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )
			->with( 'manage_options' )
			->andReturn( true );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		// Admin user — should output nothing.
		$this->assertEmpty( trim( $output ) );
	}

	/**
	 * Test render_script trims whitespace from script_url and website_id.
	 *
	 * This addresses issue #28 — copy-paste whitespace causing tracking failure.
	 */
	public function test_render_script_trims_whitespace_from_values() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => '  https://stats.example.com/script.js  ',
			'website_id'     => "  abc-123  \n",
			'host_url'       => '',
			'use_host_url'   => 0,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		// Values should be trimmed — no leading/trailing whitespace in attributes.
		$this->assertStringContainsString( 'src="https://stats.example.com/script.js"', $output );
		$this->assertStringContainsString( 'data-website-id="abc-123"', $output );
		$this->assertStringNotContainsString( '  https://', $output );
	}

	/**
	 * Test render_comment_tracking_script outputs JS snippet for comment forms.
	 *
	 * The new JS-based approach replaces the fragile PHP regex method that
	 * broke with custom themes and comment plugins (#39).
	 */
	public function test_render_comment_tracking_script_outputs_js() {
		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_comment_tracking_script();
		$output = ob_get_clean();

		// Must contain a script tag.
		$this->assertStringContainsString( '<script', $output );

		// Must target the comment form by standard WP ID.
		$this->assertStringContainsString( 'commentform', $output );

		// Must set data-umami-event attribute.
		$this->assertStringContainsString( 'data-umami-event', $output );
		$this->assertStringContainsString( 'comment', $output );
	}

	/**
	 * Test render_script includes host_url when use_host_url enabled.
	 */
	public function test_render_script_includes_host_url() {
		$test_options = array(
			'enabled'        => 1,
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'test-id',
			'host_url'       => 'https://proxy.example.com',
			'use_host_url'   => 1,
			'ignore_admins'  => 0,
			'auto_track'     => 1,
			'do_not_track'   => 0,
			'cache'          => 0,
			'track_comments' => 0,
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => 0,
			'exclude_hash'   => 0,
			'before_send'    => '',
		);

		WP_Mock::userFunction( 'get_option' )->andReturn( $test_options );
		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);
		WP_Mock::userFunction( 'current_user_can' )->andReturn( false );
		WP_Mock::passthruFunction( 'esc_url' );
		WP_Mock::passthruFunction( 'esc_attr' );

		$manager = $this->getMockBuilder( Manager::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		ob_start();
		$manager->render_script();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'data-host-url="https://proxy.example.com"', $output );
	}
}
