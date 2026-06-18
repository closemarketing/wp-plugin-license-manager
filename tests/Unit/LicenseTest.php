<?php
/**
 * Class LicenseTest
 *
 * @package Closemarketing\WPLicenseManager
 */

use Closemarketing\WPLicenseManager\License;

/**
 * License test case.
 */
class LicenseTest extends WP_UnitTestCase {

	/**
	 * Test License class instantiation
	 */
	public function test_license_instantiation() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $options );
		$this->assertInstanceOf( License::class, $license );
	}

	/**
	 * Test missing required options throw exception
	 */
	public function test_missing_required_options_throws_exception() {
		$this->expectException( Exception::class );
		$this->expectExceptionMessageMatches( '/Required option/' );

		$options = array(
			'api_url' => 'https://example.com',
			// Missing other required fields.
		);

		new License( $options );
	}

	/**
	 * Test get_option_key method
	 */
	public function test_get_option_key() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $options );
		$key     = $license->get_option_key( 'apikey' );

		$this->assertEquals( 'test-plugin_license_apikey', $key );
	}

	/**
	 * Test is_license_active returns false when not activated
	 */
	public function test_is_license_active_returns_false_when_not_activated() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $options );
		
		// Clean up any existing options.
		delete_option( 'test-plugin_license_activated' );
		
		$this->assertFalse( $license->is_license_active() );
	}

	/**
	 * Test is_license_active returns true when activated
	 */
	public function test_is_license_active_returns_true_when_activated() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $options );
		
		// Set license as activated.
		update_option( 'test-plugin_license_activated', 'Activated' );
		
		$this->assertTrue( $license->is_license_active() );
		
		// Clean up.
		delete_option( 'test-plugin_license_activated' );
	}

	/**
	 * Test get_plugin_name returns correct name
	 */
	public function test_get_plugin_name() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $options );
		
		$this->assertEquals( 'Test Plugin', $license->get_plugin_name() );
	}

	/**
	 * Test get_text_domain returns correct text domain
	 */
	public function test_get_text_domain() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
			'text_domain'     => 'test-plugin-domain',
		);

		$license = new License( $options );
		
		$this->assertEquals( 'test-plugin-domain', $license->get_text_domain() );
	}

	/**
	 * Test get_option_value
	 */
	public function test_get_option_value() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $options );
		
		// Set a test value.
		update_option( 'test-plugin_license_apikey', 'test-api-key-value' );
		
		$value = $license->get_option_value( 'apikey' );
		
		$this->assertEquals( 'test-api-key-value', $value );
		
		// Clean up.
		delete_option( 'test-plugin_license_apikey' );
	}

	/**
	 * Test get_env_var_name returns correct env var name from slug.
	 */
	public function test_get_env_var_name() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $options );
		$this->assertEquals( 'CTECH_LICENSE_TEST_PLUGIN', $license->get_env_var_name() );
	}

	/**
	 * Test get_env_var_name handles slugs with underscores.
	 */
	public function test_get_env_var_name_with_underscores() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'my_awesome_plugin',
			'name'            => 'My Awesome Plugin',
		);

		$license = new License( $options );
		$this->assertEquals( 'CTECH_LICENSE_MY_AWESOME_PLUGIN', $license->get_env_var_name() );
	}

	/**
	 * Test is_license_key_from_env returns false when env var is not set.
	 */
	public function test_is_license_key_from_env_returns_false_when_not_set() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		putenv( 'CTECH_LICENSE_TEST_PLUGIN' );
		$license = new License( $options );
		$this->assertFalse( $license->is_license_key_from_env() );
	}

	/**
	 * Test is_license_key_from_env returns true when env var is set.
	 */
	public function test_is_license_key_from_env_returns_true_when_set() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		putenv( 'CTECH_LICENSE_TEST_PLUGIN=env-license-key-123' );
		$license = new License( $options );
		$this->assertTrue( $license->is_license_key_from_env() );
		putenv( 'CTECH_LICENSE_TEST_PLUGIN' );
	}

	/**
	 * Test get_option_value('apikey') returns env var value when set (takes priority over DB).
	 */
	public function test_get_option_value_apikey_prefers_env_var() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		update_option( 'test-plugin_license_apikey', 'db-license-key' );
		putenv( 'CTECH_LICENSE_TEST_PLUGIN=env-license-key-456' );

		$license = new License( $options );
		$this->assertEquals( 'env-license-key-456', $license->get_option_value( 'apikey' ) );

		putenv( 'CTECH_LICENSE_TEST_PLUGIN' );
		delete_option( 'test-plugin_license_apikey' );
	}

	/**
	 * Test get_option_value('apikey') falls back to DB when env var is not set.
	 */
	public function test_get_option_value_apikey_falls_back_to_db() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		putenv( 'CTECH_LICENSE_TEST_PLUGIN' );
		update_option( 'test-plugin_license_apikey', 'db-only-key' );

		$license = new License( $options );
		$this->assertEquals( 'db-only-key', $license->get_option_value( 'apikey' ) );

		delete_option( 'test-plugin_license_apikey' );
	}

	/**
	 * Test that two plugins with different slugs use independent env vars.
	 */
	public function test_multiple_plugins_use_independent_env_vars() {
		$make_options = function ( $slug, $name ) {
			return array(
				'api_url'         => 'https://example.com',
				'rest_api_key'    => 'test_key',
				'rest_api_secret' => 'test_secret',
				'product_uuid'    => 'test-uuid',
				'file'            => __FILE__,
				'version'         => '1.0.0',
				'slug'            => $slug,
				'name'            => $name,
			);
		};

		putenv( 'CTECH_LICENSE_PLUGIN_ONE=key-for-one' );
		putenv( 'CTECH_LICENSE_PLUGIN_TWO' );

		$plugin_one = new License( $make_options( 'plugin_one', 'Plugin One' ) );
		$plugin_two = new License( $make_options( 'plugin_two', 'Plugin Two' ) );

		$this->assertEquals( 'CTECH_LICENSE_PLUGIN_ONE', $plugin_one->get_env_var_name() );
		$this->assertEquals( 'CTECH_LICENSE_PLUGIN_TWO', $plugin_two->get_env_var_name() );

		$this->assertTrue( $plugin_one->is_license_key_from_env() );
		$this->assertFalse( $plugin_two->is_license_key_from_env() );

		$this->assertEquals( 'key-for-one', $plugin_one->get_option_value( 'apikey' ) );

		putenv( 'CTECH_LICENSE_PLUGIN_ONE' );
	}

	/**
	 * Test that rotating the env var key triggers re-activation even when status is Activated.
	 *
	 * Verifies that the env_key_hash comparison detects a key change and does not
	 * short-circuit when the DB still shows 'Activated' for a different key.
	 */
	public function test_key_rotation_detected_when_hash_differs() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		// Simulate a previously activated state with an old key hash.
		update_option( 'test-plugin_license_activated', 'Activated' );
		update_option( 'test-plugin_license_env_key_hash', md5( 'old-env-key' ) );

		// Now a new env key is set.
		putenv( 'CTECH_LICENSE_TEST_PLUGIN=new-env-key' );

		$license          = new License( $options );
		$new_hash         = md5( 'new-env-key' );
		$stored_hash      = get_option( 'test-plugin_license_env_key_hash' );
		$current_env_hash = md5( $license->get_option_value( 'apikey' ) );

		// The stored hash must differ from the current key hash → re-activation needed.
		$this->assertNotEquals( $stored_hash, $current_env_hash );

		putenv( 'CTECH_LICENSE_TEST_PLUGIN' );
		delete_option( 'test-plugin_license_env_key_hash' );
	}

	/**
	 * Test that no re-activation is needed when hash matches current env key.
	 */
	public function test_no_reactivation_when_hash_matches() {
		$options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		putenv( 'CTECH_LICENSE_TEST_PLUGIN=stable-env-key' );

		// Simulate previously activated with the same key.
		update_option( 'test-plugin_license_activated', 'Activated' );
		update_option( 'test-plugin_license_env_key_hash', md5( 'stable-env-key' ) );

		$license     = new License( $options );
		$stored_hash = get_option( 'test-plugin_license_env_key_hash' );
		$env_hash    = md5( $license->get_option_value( 'apikey' ) );

		// Hashes match → no re-activation needed.
		$this->assertEquals( $stored_hash, $env_hash );

		putenv( 'CTECH_LICENSE_TEST_PLUGIN' );
		delete_option( 'test-plugin_license_env_key_hash' );
	}

	/**
	 * Cleanup after all tests
	 */
	public function tearDown(): void {
		parent::tearDown();

		// Clean up all test options.
		delete_option( 'test-plugin_license_activated' );
		delete_option( 'test-plugin_license_apikey' );
		delete_option( 'test-plugin_license_deactivate_checkbox' );
		delete_option( 'test-plugin_license_env_key_hash' );

		// Ensure env var is unset after each test.
		putenv( 'CTECH_LICENSE_TEST_PLUGIN' );
	}
}
