<?php
/**
 * Unit tests for the ApiClient class.
 *
 * @package Integrate_Umami
 */

namespace Ancozockt\Umami\Tests;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use Ancozockt\Umami\ApiClient;

/**
 * Tests for ApiClient class — Umami API communication.
 */
class ApiClientTest extends TestCase {

	/**
	 * Test that the client can be constructed with self-hosted config.
	 */
	public function test_constructor_self_hosted() {
		$client = new ApiClient(
			'https://stats.example.com',
			'test-website-id',
			'admin',
			'password123'
		);

		$this->assertInstanceOf( ApiClient::class, $client );
	}

	/**
	 * Test that the client can be constructed with Cloud API key.
	 */
	public function test_constructor_cloud_api_key() {
		$client = new ApiClient(
			'https://api.umami.is',
			'test-website-id',
			'',
			'',
			'my-api-key-123'
		);

		$this->assertInstanceOf( ApiClient::class, $client );
	}

	/**
	 * Test get_auth_headers returns API key header for Cloud.
	 */
	public function test_get_auth_headers_cloud() {
		$client = new ApiClient(
			'https://api.umami.is',
			'test-website-id',
			'',
			'',
			'my-api-key-123'
		);

		$headers = $client->get_auth_headers();

		$this->assertArrayHasKey( 'x-umami-api-key', $headers );
		$this->assertSame( 'my-api-key-123', $headers['x-umami-api-key'] );
	}

	/**
	 * Test get_auth_headers returns empty for self-hosted without token.
	 *
	 * Before login, there's no token yet.
	 */
	public function test_get_auth_headers_self_hosted_no_token() {
		$client = new ApiClient(
			'https://stats.example.com',
			'test-website-id',
			'admin',
			'password123'
		);

		$headers = $client->get_auth_headers();

		// No token yet — should be empty (login not called).
		$this->assertEmpty( $headers );
	}

	/**
	 * Test login calls the correct API endpoint.
	 */
	public function test_login_makes_correct_request() {
		WP_Mock::userFunction( 'wp_json_encode' )
			->andReturnUsing( 'json_encode' );

		WP_Mock::userFunction( 'wp_remote_post' )
			->once()
			->with(
				'https://stats.example.com/api/auth/login',
				\WP_Mock\Functions::type( 'array' )
			)
			->andReturn(
				array(
					'response' => array( 'code' => 200 ),
					'body'     => json_encode( array( 'token' => 'test-bearer-token' ) ),
				)
			);

		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		WP_Mock::userFunction( 'wp_remote_retrieve_body' )
			->andReturn( json_encode( array( 'token' => 'test-bearer-token' ) ) );

		WP_Mock::userFunction( 'is_wp_error' )
			->andReturn( false );

		$client = new ApiClient(
			'https://stats.example.com',
			'test-website-id',
			'admin',
			'password123'
		);

		$result = $client->login();

		$this->assertTrue( $result );

		// After login, auth headers should contain Bearer token.
		$headers = $client->get_auth_headers();
		$this->assertArrayHasKey( 'Authorization', $headers );
		$this->assertSame( 'Bearer test-bearer-token', $headers['Authorization'] );
	}

	/**
	 * Test login returns false on failure.
	 */
	public function test_login_returns_false_on_failure() {
		WP_Mock::userFunction( 'wp_json_encode' )
			->andReturnUsing( 'json_encode' );

		WP_Mock::userFunction( 'wp_remote_post' )
			->andReturn(
				array(
					'response' => array( 'code' => 401 ),
					'body'     => 'Unauthorized',
				)
			);

		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )
			->andReturn( 401 );

		WP_Mock::userFunction( 'is_wp_error' )
			->andReturn( false );

		$client = new ApiClient(
			'https://stats.example.com',
			'test-website-id',
			'admin',
			'wrongpassword'
		);

		$result = $client->login();

		$this->assertFalse( $result );
	}

	/**
	 * Test get_stats builds correct URL and returns parsed data.
	 */
	public function test_get_stats_returns_parsed_data() {
		$stats_response = array(
			'pageviews' => array( 'value' => 1000, 'prev' => 800 ),
			'visitors'  => array( 'value' => 250, 'prev' => 200 ),
			'visits'    => array( 'value' => 400, 'prev' => 350 ),
			'bounces'   => array( 'value' => 150, 'prev' => 120 ),
		);

		WP_Mock::userFunction( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'response' => array( 'code' => 200 ),
					'body'     => json_encode( $stats_response ),
				)
			);

		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		WP_Mock::userFunction( 'wp_remote_retrieve_body' )
			->andReturn( json_encode( $stats_response ) );

		WP_Mock::userFunction( 'is_wp_error' )
			->andReturn( false );

		$client = new ApiClient(
			'https://api.umami.is',
			'test-website-id',
			'',
			'',
			'my-api-key'
		);

		$result = $client->get_stats( '2026-01-01', '2026-01-29' );

		$this->assertIsArray( $result );
		$this->assertSame( 1000, $result['pageviews']['value'] );
		$this->assertSame( 250, $result['visitors']['value'] );
	}

	/**
	 * Test get_stats returns null on error.
	 */
	public function test_get_stats_returns_null_on_error() {
		$wp_error = new \stdClass();
		$wp_error->errors = array( 'http_request_failed' => array( 'Connection timed out' ) );

		WP_Mock::userFunction( 'wp_remote_get' )
			->andReturn( $wp_error );

		WP_Mock::userFunction( 'is_wp_error' )
			->andReturn( true );

		$client = new ApiClient(
			'https://api.umami.is',
			'test-website-id',
			'',
			'',
			'my-api-key'
		);

		$result = $client->get_stats( '2026-01-01', '2026-01-29' );

		$this->assertNull( $result );
	}

	/**
	 * Test get_active_visitors returns integer count.
	 */
	public function test_get_active_visitors_returns_count() {
		WP_Mock::userFunction( 'wp_remote_get' )
			->once()
			->andReturn(
				array(
					'response' => array( 'code' => 200 ),
					'body'     => json_encode( array( 'x' => 5 ) ),
				)
			);

		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )
			->andReturn( 200 );

		WP_Mock::userFunction( 'wp_remote_retrieve_body' )
			->andReturn( json_encode( array( 'x' => 5 ) ) );

		WP_Mock::userFunction( 'is_wp_error' )
			->andReturn( false );

		$client = new ApiClient(
			'https://stats.example.com',
			'test-website-id',
			'',
			'',
			'my-api-key'
		);

		$result = $client->get_active_visitors();

		$this->assertSame( 5, $result );
	}

	/**
	 * Test is_cloud returns true for cloud URLs.
	 */
	public function test_is_cloud_detects_cloud_instances() {
		$client = new ApiClient(
			'https://api.umami.is',
			'test-id',
			'',
			'',
			'key-123'
		);

		$this->assertTrue( $client->is_cloud() );
	}

	/**
	 * Test is_cloud returns false for self-hosted.
	 */
	public function test_is_cloud_detects_self_hosted() {
		$client = new ApiClient(
			'https://stats.example.com',
			'test-id',
			'admin',
			'pass'
		);

		$this->assertFalse( $client->is_cloud() );
	}

	/**
	 * Test from_options factory creates client from WP options.
	 */
	public function test_from_options_factory() {
		$test_options = array(
			'enabled'    => 1,
			'host_url'   => 'https://stats.example.com',
			'website_id' => 'abc-123',
			'script_url' => 'https://stats.example.com/script.js',
		);

		WP_Mock::userFunction( 'get_option' )
			->andReturn( $test_options );

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);

		$client = ApiClient::from_options();

		$this->assertInstanceOf( ApiClient::class, $client );
	}
}
