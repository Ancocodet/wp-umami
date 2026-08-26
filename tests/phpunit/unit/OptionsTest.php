<?php
/**
 * Unit tests for the Options class.
 *
 * @package Integrate_Umami
 */

namespace Ancozockt\Umami\Tests;

use WP_Mock;
use WP_Mock\Tools\TestCase;
use Ancozockt\Umami\Options;

/**
 * Tests for Options class.
 */
class OptionsTest extends TestCase {

	/**
	 * Test that get_options returns correct defaults for a fresh install.
	 */
	public function test_get_options_returns_defaults_for_new_install() {
		// Stub migration check: no old options exist.
		WP_Mock::userFunction( 'get_option' )
			->with( 'integrate_umami_options' )
			->andReturn( false );
		WP_Mock::userFunction( 'get_option' )
			->with( 'umami_options' )
			->andReturn( false );

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);

		$options = Options::get_options();

		$this->assertIsArray( $options );
		$this->assertSame( 0, $options['enabled'] );
		$this->assertSame( '', $options['script_url'] );
		$this->assertSame( '', $options['website_id'] );
		$this->assertSame( '', $options['host_url'] );
		$this->assertSame( 0, $options['use_host_url'] );
		$this->assertSame( 1, $options['ignore_admins'] );
		$this->assertSame( 1, $options['auto_track'] );
		$this->assertSame( 1, $options['do_not_track'] );
		$this->assertSame( 0, $options['cache'] );
		$this->assertSame( 0, $options['track_comments'] );
	}

	/**
	 * Test that get_options includes new v3 option defaults.
	 *
	 * This test WILL FAIL until we add the new option keys.
	 */
	public function test_get_options_includes_v3_defaults() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'integrate_umami_options' )
			->andReturn( false );
		WP_Mock::userFunction( 'get_option' )
			->with( 'umami_options' )
			->andReturn( false );

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);

		$options = Options::get_options();

		// v3 tracker attributes.
		$this->assertArrayHasKey( 'tag', $options );
		$this->assertSame( '', $options['tag'] );
		$this->assertArrayHasKey( 'domains', $options );
		$this->assertSame( '', $options['domains'] );
		$this->assertArrayHasKey( 'exclude_search', $options );
		$this->assertSame( 0, $options['exclude_search'] );
		$this->assertArrayHasKey( 'exclude_hash', $options );
		$this->assertSame( 0, $options['exclude_hash'] );
		$this->assertArrayHasKey( 'before_send', $options );
		$this->assertSame( '', $options['before_send'] );
	}

	/**
	 * Test that migrate_options moves old umami_options to new key.
	 *
	 * The migration flow:
	 * 1. maybe_migrate_options() checks get_option('integrate_umami_options') → empty
	 * 2. Checks get_option('umami_options') → has old data
	 * 3. Calls update_option('integrate_umami_options', old_data)
	 * 4. Calls delete_option('umami_options')
	 * 5. get_options() then calls get_option('integrate_umami_options') for wp_parse_args
	 *    — after migration, this should return the migrated data
	 */
	public function test_migration_from_old_options() {
		$old_options = array(
			'enabled'    => 1,
			'script_url' => 'https://stats.example.com/umami.js',
			'website_id' => 'test-id-123',
		);

		// Track state: after update_option, get_option should return the migrated data.
		$migrated = false;

		WP_Mock::userFunction( 'get_option' )
			->andReturnUsing(
				function ( $key ) use ( $old_options, &$migrated ) {
					if ( 'integrate_umami_options' === $key ) {
						// Before migration: empty. After: returns migrated data.
						return $migrated ? $old_options : false;
					}
					if ( 'umami_options' === $key ) {
						return $old_options;
					}
					return false;
				}
			);

		WP_Mock::userFunction( 'update_option' )
			->andReturnUsing(
				function () use ( &$migrated ) {
					$migrated = true;
					return true;
				}
			);

		WP_Mock::userFunction( 'delete_option' )->andReturn( true );

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, is_array( $args ) ? $args : array() );
				}
			);

		$options = Options::get_options();

		$this->assertTrue( $migrated, 'Migration should have been triggered' );
		$this->assertSame( 1, $options['enabled'] );
		$this->assertSame( 'https://stats.example.com/umami.js', $options['script_url'] );
	}

	/**
	 * Test that delete_options removes both old and new option keys.
	 */
	public function test_delete_options_removes_both_keys() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'umami_options' )
			->andReturn( array( 'enabled' => 1 ) );

		WP_Mock::userFunction( 'delete_option' )
			->with( 'umami_options' )
			->once();

		WP_Mock::userFunction( 'delete_option' )
			->with( 'integrate_umami_options' )
			->once();

		Options::delete_options();

		// Assertions are in the WP_Mock expectation counts.
		$this->assertConditionsMet();
	}
}
