<?php
namespace Ancozockt\Umami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ApiClient
 *
 * Communicates with the Umami Analytics API.
 * Supports both self-hosted (token auth) and Umami Cloud (API key auth).
 *
 * @since 0.9.0
 */
class ApiClient {

	/**
	 * Base URL of the Umami instance (e.g. https://stats.example.com).
	 *
	 * @var string
	 */
	private string $base_url;

	/**
	 * The Umami website ID.
	 *
	 * @var string
	 */
	private string $website_id;

	/**
	 * Username for self-hosted authentication.
	 *
	 * @var string
	 */
	private string $username;

	/**
	 * Password for self-hosted authentication.
	 *
	 * @var string
	 */
	private string $password;

	/**
	 * API key for Umami Cloud authentication.
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Bearer token obtained from self-hosted login.
	 *
	 * @var string
	 */
	private string $token = '';

	/**
	 * Constructor.
	 *
	 * @param string $base_url   The Umami instance base URL.
	 * @param string $website_id The website ID.
	 * @param string $username   Username for self-hosted auth (optional).
	 * @param string $password   Password for self-hosted auth (optional).
	 * @param string $api_key    API key for Cloud auth (optional).
	 */
	public function __construct(
		string $base_url,
		string $website_id,
		string $username = '',
		string $password = '',
		string $api_key = ''
	) {
		$this->base_url   = rtrim( $base_url, '/' );
		$this->website_id = $website_id;
		$this->username   = $username;
		$this->password   = $password;
		$this->api_key    = $api_key;
	}

	/**
	 * Create an ApiClient from the saved WordPress options.
	 *
	 * @return self|null The client instance, or null if not configured.
	 */
	public static function from_options(): ?self {
		$options    = Options::get_options();
		$website_id = trim( $options['website_id'] ?? '' );
		$host_url   = trim( $options['host_url'] ?? '' );
		$script_url = trim( $options['script_url'] ?? '' );

		if ( empty( $website_id ) ) {
			return null;
		}

		// Derive the base URL from host_url or script_url.
		if ( ! empty( $host_url ) ) {
			$base_url = $host_url;
		} elseif ( ! empty( $script_url ) ) {
			$parsed   = wp_parse_url( $script_url );
			$base_url = ( $parsed['scheme'] ?? 'https' ) . '://' . ( $parsed['host'] ?? '' );
		} else {
			return null;
		}

		$api_key  = trim( $options['api_key'] ?? '' );
		$username = trim( $options['api_username'] ?? '' );
		$password = trim( $options['api_password'] ?? '' );

		return new self( $base_url, $website_id, $username, $password, $api_key );
	}

	/**
	 * Whether this client targets Umami Cloud.
	 *
	 * @return bool
	 */
	public function is_cloud(): bool {
		return str_contains( $this->base_url, 'umami.is' );
	}

	/**
	 * Get authentication headers for API requests.
	 *
	 * @return array Associative array of HTTP headers.
	 */
	public function get_auth_headers(): array {
		// Cloud: use API key header.
		if ( ! empty( $this->api_key ) ) {
			return array( 'x-umami-api-key' => $this->api_key );
		}

		// Self-hosted: use Bearer token (must call login() first).
		if ( ! empty( $this->token ) ) {
			return array( 'Authorization' => 'Bearer ' . $this->token );
		}

		return array();
	}

	/**
	 * Authenticate with a self-hosted Umami instance.
	 *
	 * @return bool True if login succeeded.
	 */
	public function login(): bool {
		$response = wp_remote_post(
			$this->base_url . '/api/auth/login',
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'username' => $this->username,
						'password' => $this->password,
					)
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['token'] ) ) {
			return false;
		}

		$this->token = $body['token'];
		return true;
	}

	/**
	 * Fetch website statistics for a date range.
	 *
	 * @param string $start_date Start date (ISO 8601 or timestamp).
	 * @param string $end_date   End date (ISO 8601 or timestamp).
	 *
	 * @return array|null Stats array or null on failure.
	 */
	public function get_stats( string $start_date, string $end_date ): ?array {
		$url = $this->base_url . '/api/websites/' . $this->website_id . '/stats'
			. '?startAt=' . $this->to_timestamp( $start_date )
			. '&endAt=' . $this->to_timestamp( $end_date );

		return $this->api_get( $url );
	}

	/**
	 * Fetch the number of active visitors.
	 *
	 * @return int|null Active visitor count or null on failure.
	 */
	public function get_active_visitors(): ?int {
		$url    = $this->base_url . '/api/websites/' . $this->website_id . '/active';
		$result = $this->api_get( $url );

		if ( null === $result ) {
			return null;
		}

		return (int) ( $result['x'] ?? 0 );
	}

	/**
	 * Make an authenticated GET request.
	 *
	 * @param string $url The full URL.
	 *
	 * @return array|null Decoded JSON response or null on failure.
	 */
	private function api_get( string $url ): ?array {
		$response = wp_remote_get(
			$url,
			array(
				'headers' => array_merge(
					array( 'Accept' => 'application/json' ),
					$this->get_auth_headers()
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $body ) ? $body : null;
	}

	/**
	 * Convert a date string to a Unix timestamp in milliseconds.
	 *
	 * @param string $date Date string or numeric timestamp.
	 *
	 * @return int Timestamp in milliseconds.
	 */
	private function to_timestamp( string $date ): int {
		if ( is_numeric( $date ) ) {
			return (int) $date;
		}

		return (int) ( strtotime( $date ) * 1000 );
	}
}
