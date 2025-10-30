<?php
/**
 * Custom Integration Example
 *
 * This example shows how to integrate license settings into your
 * existing settings page with custom tabs, while still using the
 * Settings class to handle the fields automatically.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

/*
Plugin Name: My Plugin with Custom Integration
Plugin URI:  https://example.com/my-plugin
Description: Example plugin with custom settings integration
Version:     1.0.0
Author:      Your Name
Text Domain: my-plugin-custom
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'MY_CUSTOM_PLUGIN_VERSION', '1.0.0' );
define( 'MY_CUSTOM_PLUGIN_FILE', __FILE__ );

// Include Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

/**
 * Main Plugin Class
 */
class My_Custom_Plugin {
	/**
	 * License instance
	 *
	 * @var License
	 */
	private $license;

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_license();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_general_settings' ) );
	}

	/**
	 * Initialize license with Settings class
	 */
	private function init_license() {
		try {
			// Create license instance.
			$this->license = new License(
				array(
					'api_url'     => 'https://yourstore.com/',
					'file'        => MY_CUSTOM_PLUGIN_FILE,
					'version'     => MY_CUSTOM_PLUGIN_VERSION,
					'slug'        => 'my_custom_plugin',
					'name'        => 'My Custom Plugin',
					'text_domain' => 'my-plugin-custom',
				)
			);

			// DON'T create Settings page - we'll integrate manually.
			// But we can still use Settings class for rendering fields!

		} catch ( \Exception $e ) {
			add_action(
				'admin_notices',
				function() use ( $e ) {
					?>
					<div class="notice notice-error">
						<p><?php echo esc_html( $e->getMessage() ); ?></p>
					</div>
					<?php
				}
			);
		}
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'My Plugin Settings', 'my-plugin-custom' ),
			__( 'My Plugin', 'my-plugin-custom' ),
			'manage_options',
			'my-custom-plugin',
			array( $this, 'render_settings_page' ),
			'dashicons-admin-generic',
			30
		);
	}

	/**
	 * Register general settings
	 */
	public function register_general_settings() {
		register_setting( 'my_custom_plugin_general', 'my_custom_plugin_options' );

		add_settings_section(
			'my_custom_plugin_main',
			__( 'General Settings', 'my-plugin-custom' ),
			array( $this, 'section_callback' ),
			'my_custom_plugin_general'
		);

		add_settings_field(
			'enable_feature',
			__( 'Enable Feature', 'my-plugin-custom' ),
			array( $this, 'enable_feature_callback' ),
			'my_custom_plugin_general',
			'my_custom_plugin_main'
		);
	}

	/**
	 * Section callback
	 */
	public function section_callback() {
		echo '<p>' . esc_html__( 'Configure the main plugin settings.', 'my-plugin-custom' ) . '</p>';
	}

	/**
	 * Enable feature field callback
	 */
	public function enable_feature_callback() {
		$options = get_option( 'my_custom_plugin_options', array() );
		$checked = isset( $options['enable_feature'] ) ? $options['enable_feature'] : 0;
		?>
		<input type="checkbox" name="my_custom_plugin_options[enable_feature]" value="1" <?php checked( 1, $checked ); ?>>
		<span class="description"><?php esc_html_e( 'Check to enable this feature.', 'my-plugin-custom' ); ?></span>
		<?php
	}

	/**
	 * Render settings page with tabs
	 */
	public function render_settings_page() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<a href="?page=my-custom-plugin&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'General', 'my-plugin-custom' ); ?>
				</a>
				<a href="?page=my-custom-plugin&tab=license" class="nav-tab <?php echo 'license' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'License', 'my-plugin-custom' ); ?>
				</a>
			</h2>

			<div class="tab-content">
				<?php
				if ( 'general' === $active_tab ) {
					$this->render_general_tab();
				} elseif ( 'license' === $active_tab ) {
					$this->render_license_tab();
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render general tab
	 */
	private function render_general_tab() {
		?>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'my_custom_plugin_general' );
			do_settings_sections( 'my_custom_plugin_general' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Render license tab using the built-in fields
	 */
	private function render_license_tab() {
		if ( ! $this->license ) {
			echo '<p>' . esc_html__( 'License manager not initialized.', 'my-plugin-custom' ) . '</p>';
			return;
		}

		// Create a temporary Settings instance just for rendering.
		$settings_renderer = new Settings(
			$this->license,
			array(
				'show_header' => false, // We already have the page header.
			)
		);

		// Render just the license form section.
		?>
		<div class="license-settings-container">
			<div class="license-settings-main">
				<form method="post" action="options.php">
					<?php
					settings_fields( $this->license->get_option_group() );
					do_settings_sections( $this->license->get_settings_section() );
					wp_nonce_field( 'Update_License_Options', 'license_nonce' );
					submit_button( __( 'Save License Settings', 'my-plugin-custom' ) );
					?>
				</form>
			</div>

			<div class="license-settings-sidebar">
				<div class="license-info-box">
					<h3><?php esc_html_e( 'License Status', 'my-plugin-custom' ); ?></h3>
					<?php if ( $this->license->is_license_active() ) : ?>
						<p class="license-status-active">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Active', 'my-plugin-custom' ); ?>
						</p>
					<?php else : ?>
						<p class="license-status-inactive">
							<span class="dashicons dashicons-dismiss"></span>
							<?php esc_html_e( 'Inactive', 'my-plugin-custom' ); ?>
						</p>
					<?php endif; ?>
				</div>

				<div class="license-info-box">
					<h3><?php esc_html_e( 'Instance ID', 'my-plugin-custom' ); ?></h3>
					<p><code><?php echo esc_html( $this->license->get_option_value( 'instance' ) ); ?></code></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if license is active
	 *
	 * @return bool
	 */
	public function is_licensed() {
		if ( ! $this->license ) {
			return false;
		}

		return $this->license->is_license_active();
	}
}

// Initialize plugin.
function my_custom_plugin_init() {
	return new My_Custom_Plugin();
}
add_action( 'plugins_loaded', 'my_custom_plugin_init' );



