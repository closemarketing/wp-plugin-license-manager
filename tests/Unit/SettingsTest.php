<?php
/**
 * Class SettingsTest
 *
 * @package Closemarketing\WPLicenseManager
 */

use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

/**
 * Settings test case.
 */
class SettingsTest extends WP_UnitTestCase {

	/**
	 * Test Settings class instantiation
	 */
	public function test_settings_instantiation() {
		$license_options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $license_options );
		
		$settings_options = array(
			'title'        => 'Test License Settings',
			'description'  => 'Test description',
			'plugin_name'  => 'Test Plugin',
		);

		$settings = new Settings( $license, $settings_options );
		
		$this->assertInstanceOf( Settings::class, $settings );
	}

	/**
	 * Test Settings with default options
	 */
	public function test_settings_with_default_options() {
		$license_options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $license_options );
		
		// No settings options provided, should use defaults.
		$settings = new Settings( $license );
		
		$this->assertInstanceOf( Settings::class, $settings );
	}

	/**
	 * Test that admin_init hooks are registered
	 */
	public function test_admin_init_hooks_registered() {
		$license_options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license  = new License( $license_options );
		$settings = new Settings( $license );

		// Check if admin_init actions are registered.
		$this->assertNotFalse( has_action( 'admin_init', array( $settings, 'register_settings' ) ) );
		$this->assertNotFalse( has_action( 'admin_init', array( $settings, 'handle_form_submission' ) ) );
	}

	/**
	 * Test render method exists
	 */
	public function test_render_method_exists() {
		$license_options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license  = new License( $license_options );
		$settings = new Settings( $license );

		$this->assertTrue( method_exists( $settings, 'render' ) );
	}

	/**
	 * Test Settings with custom benefits
	 */
	public function test_settings_with_custom_benefits() {
		$license_options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $license_options );
		
		$settings_options = array(
			'benefits' => array(
				'Custom benefit 1',
				'Custom benefit 2',
			),
		);

		$settings = new Settings( $license, $settings_options );
		
		$this->assertInstanceOf( Settings::class, $settings );
	}

	/**
	 * Test Settings with custom purchase and renew URLs
	 */
	public function test_settings_with_custom_urls() {
		$license_options = array(
			'api_url'         => 'https://example.com',
			'rest_api_key'    => 'test_key',
			'rest_api_secret' => 'test_secret',
			'product_uuid'    => 'test-uuid',
			'file'            => __FILE__,
			'version'         => '1.0.0',
			'slug'            => 'test-plugin',
			'name'            => 'Test Plugin',
		);

		$license = new License( $license_options );
		
		$settings_options = array(
			'purchase_url' => 'https://example.com/purchase',
			'renew_url'    => 'https://example.com/renew',
		);

		$settings = new Settings( $license, $settings_options );
		
		$this->assertInstanceOf( Settings::class, $settings );
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
