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
	 * Cleanup after all tests
	 */
	public function tearDown(): void {
		parent::tearDown();
		
		// Clean up all test options.
		delete_option( 'test-plugin_license_activated' );
		delete_option( 'test-plugin_license_apikey' );
		delete_option( 'test-plugin_license_deactivate_checkbox' );
	}
}
