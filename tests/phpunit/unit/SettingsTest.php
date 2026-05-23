<?php
/**
 * Unit tests for the Settings class.
 *
 * @package Integrate_Umami
 */

namespace Ancozockt\Umami\Tests;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use Ancozockt\Umami\Settings;

/**
 * Tests for Settings class — option validation and sanitization.
 */
class SettingsTest extends TestCase {

	/**
	 * Test validate_options sanitizes all input fields correctly.
	 */
	public function test_validate_options_sanitizes_basic_fields() {
		WP_Mock::userFunction( 'esc_url_raw' )
			->andReturnUsing( function ( $url ) {
				return filter_var( trim( $url ), FILTER_SANITIZE_URL );
			} );
		WP_Mock::userFunction( 'sanitize_text_field' )
			->andReturnUsing( function ( $str ) {
				return trim( strip_tags( $str ) );
			} );

		$settings = $this->getMockBuilder( Settings::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		$input = array(
			'enabled'        => '1',
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'abc-123-def',
			'host_url'       => 'https://proxy.example.com',
			'use_host_url'   => '1',
			'ignore_admins'  => '1',
			'auto_track'     => '1',
			'do_not_track'   => '0',
			'cache'          => '0',
			'track_comments' => '1',
		);

		$result = $settings->validate_options( $input );

		$this->assertSame( 1, $result['enabled'] );
		$this->assertIsString( $result['script_url'] );
		$this->assertSame( 'abc-123-def', $result['website_id'] );
		$this->assertSame( 1, $result['use_host_url'] );
		$this->assertSame( 1, $result['track_comments'] );
	}

	/**
	 * Test validate_options returns empty array for empty input.
	 */
	public function test_validate_options_handles_empty_input() {
		$settings = $this->getMockBuilder( Settings::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		$result = $settings->validate_options( array() );
		$this->assertSame( array(), $result );
	}

	/**
	 * Test validate_options handles v3 fields (tag, domains, etc.).
	 *
	 * This test WILL FAIL until we add v3 field validation.
	 */
	public function test_validate_options_sanitizes_v3_fields() {
		WP_Mock::userFunction( 'esc_url_raw' )
			->andReturnUsing( function ( $url ) {
				return filter_var( trim( $url ), FILTER_SANITIZE_URL );
			} );
		WP_Mock::userFunction( 'sanitize_text_field' )
			->andReturnUsing( function ( $str ) {
				return trim( strip_tags( $str ) );
			} );

		$settings = $this->getMockBuilder( Settings::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		$input = array(
			'enabled'        => '1',
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'abc-123',
			'host_url'       => '',
			'use_host_url'   => '0',
			'ignore_admins'  => '1',
			'auto_track'     => '1',
			'do_not_track'   => '0',
			'cache'          => '0',
			'track_comments' => '0',
			'tag'            => 'my-tag',
			'domains'        => 'example.com,www.example.com',
			'exclude_search' => '1',
			'exclude_hash'   => '1',
			'before_send'    => 'myHandler',
		);

		$result = $settings->validate_options( $input );

		$this->assertArrayHasKey( 'tag', $result );
		$this->assertSame( 'my-tag', $result['tag'] );
		$this->assertArrayHasKey( 'domains', $result );
		$this->assertSame( 'example.com,www.example.com', $result['domains'] );
		$this->assertArrayHasKey( 'exclude_search', $result );
		$this->assertSame( 1, $result['exclude_search'] );
		$this->assertArrayHasKey( 'exclude_hash', $result );
		$this->assertSame( 1, $result['exclude_hash'] );
		$this->assertArrayHasKey( 'before_send', $result );
		$this->assertSame( 'myHandler', $result['before_send'] );
	}

	/**
	 * Test validate_options rejects invalid before_send function names.
	 *
	 * Only valid JS identifiers should be allowed.
	 */
	public function test_validate_options_rejects_invalid_before_send() {
		WP_Mock::userFunction( 'esc_url_raw' )
			->andReturnUsing( function ( $url ) {
				return filter_var( trim( $url ), FILTER_SANITIZE_URL );
			} );
		WP_Mock::userFunction( 'sanitize_text_field' )
			->andReturnUsing( function ( $str ) {
				return trim( strip_tags( $str ) );
			} );

		$settings = $this->getMockBuilder( Settings::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		$input = array(
			'enabled'        => '1',
			'script_url'     => 'https://stats.example.com/script.js',
			'website_id'     => 'abc-123',
			'host_url'       => '',
			'use_host_url'   => '0',
			'ignore_admins'  => '1',
			'auto_track'     => '1',
			'do_not_track'   => '0',
			'cache'          => '0',
			'track_comments' => '0',
			'tag'            => '',
			'domains'        => '',
			'exclude_search' => '0',
			'exclude_hash'   => '0',
			'before_send'    => 'alert("xss")',
		);

		$result = $settings->validate_options( $input );

		// Invalid JS function name — should be sanitized to empty.
		$this->assertSame( '', $result['before_send'] );
	}

	/**
	 * Test validate_options trims whitespace from script_url and website_id.
	 *
	 * Addresses issue #28 — copy-paste whitespace breaking tracking.
	 */
	public function test_validate_options_trims_whitespace() {
		WP_Mock::userFunction( 'esc_url_raw' )
			->andReturnUsing( function ( $url ) {
				return filter_var( trim( $url ), FILTER_SANITIZE_URL );
			} );
		WP_Mock::userFunction( 'sanitize_text_field' )
			->andReturnUsing( function ( $str ) {
				return trim( strip_tags( $str ) );
			} );

		$settings = $this->getMockBuilder( Settings::class )
			->disableOriginalConstructor()
			->onlyMethods( array() )
			->getMock();

		$input = array(
			'enabled'        => '1',
			'script_url'     => "  https://stats.example.com/script.js \n",
			'website_id'     => "  abc-123  \t",
			'host_url'       => '',
			'use_host_url'   => '0',
			'ignore_admins'  => '1',
			'auto_track'     => '1',
			'do_not_track'   => '0',
			'cache'          => '0',
			'track_comments' => '0',
		);

		$result = $settings->validate_options( $input );

		// esc_url_raw and sanitize_text_field both trim, but validate_options
		// should also explicitly trim before passing to sanitizers.
		$this->assertStringNotContainsString( "\n", $result['script_url'] );
		$this->assertStringNotContainsString( "\t", $result['website_id'] );
		$this->assertStringNotContainsString( '  ', $result['script_url'] );
	}
}
