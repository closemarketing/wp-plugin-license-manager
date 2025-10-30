<?php
/**
 * Advanced Implementation Example
 *
 * This example shows a more complex integration with multiple tabs,
 * custom styling, and advanced features.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

/*
Plugin Name: My Advanced Plugin
Plugin URI:  https://example.com/my-advanced-plugin
Description: Advanced example plugin with license management and custom settings
Version:     2.0.0
Author:      Your Name
Author URI:  https://example.com
Text Domain: my-advanced-plugin
License:     GPL-2.0+
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'MY_ADV_PLUGIN_VERSION', '2.0.0' );
define( 'MY_ADV_PLUGIN_FILE', __FILE__ );
define( 'MY_ADV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MY_ADV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include Composer autoloader.
require_once MY_ADV_PLUGIN_PATH . 'vendor/autoload.php';

/**
 * Main Plugin Class
 */
class My_Advanced_Plugin {
	/**
	 * License manager instance
	 *
	 * @var \Closemarketing\WPLicenseManager\License
	 */
	private $license;

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
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_notices', array( $this, 'license_notice' ) );
	}

	/**
	 * Initialize license manager
	 */
	private function init_license() {
		try {
			$this->license = new \Closemarketing\WPLicenseManager\License(
				array(
					'api_url'           => 'https://yourstore.com/',
					'file'              => MY_ADV_PLUGIN_FILE,
					'version'           => MY_ADV_PLUGIN_VERSION,
					'slug'              => 'my_advanced_plugin',
					'name'              => 'My Advanced Plugin',
					'text_domain'       => 'my-advanced-plugin',
					'settings_page'     => 'my_advanced_plugin',
					'settings_tabs'     => 'my_advanced_plugin_tabs',
					'settings_content'  => 'my_advanced_plugin_content',
				)
			);
		} catch ( \Exception $e ) {
			add_action(
				'admin_notices',
				function() use ( $e ) {
					?>
					<div class="notice notice-error is-dismissible">
						<p><strong><?php esc_html_e( 'License Manager Error:', 'my-advanced-plugin' ); ?></strong> <?php echo esc_html( $e->getMessage() ); ?></p>
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
			__( 'My Advanced Plugin', 'my-advanced-plugin' ),
			__( 'My Plugin', 'my-advanced-plugin' ),
			'manage_options',
			'my_advanced_plugin',
			array( $this, 'render_settings_page' ),
			'dashicons-admin-generic',
			30
		);
	}

	/**
	 * Enqueue admin styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_styles( $hook ) {
		if ( 'toplevel_page_my_advanced_plugin' !== $hook ) {
			return;
		}

		// Custom CSS for settings page.
		$custom_css = '
		.license-manager-settings {
			display: grid;
			grid-template-columns: 2fr 1fr;
			gap: 20px;
			margin-top: 20px;
		}
		.license-manager-settings .license {
			background: #fff;
			padding: 20px;
			border: 1px solid #ccd0d4;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
		}
		.license-manager-settings .settings,
		.license-manager-settings .help {
			background: #f0f0f1;
			padding: 20px;
			border-radius: 4px;
		}
		.license-status-active {
			color: #00a32a;
			font-weight: bold;
		}
		.license-status-inactive {
			color: #d63638;
			font-weight: bold;
		}
		';
		wp_add_inline_style( 'wp-admin', $custom_css );
	}

	/**
	 * Show license notice
	 */
	public function license_notice() {
		if ( ! $this->is_license_active() ) {
			$current_screen = get_current_screen();
			
			// Don't show on settings page.
			if ( isset( $current_screen->id ) && 'toplevel_page_my_advanced_plugin' === $current_screen->id ) {
				return;
			}

			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'My Advanced Plugin:', 'my-advanced-plugin' ); ?></strong>
					<?php
					printf(
						// translators: %s: Link to settings page.
						esc_html__( 'Please activate your license to receive updates and support. %s', 'my-advanced-plugin' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=my_advanced_plugin&tab=license' ) ) . '">' . esc_html__( 'Activate License', 'my-advanced-plugin' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		// General settings.
		register_setting( 'my_advanced_plugin_general', 'my_advanced_plugin_options' );

		add_settings_section(
			'my_advanced_plugin_main',
			__( 'Main Settings', 'my-advanced-plugin' ),
			array( $this, 'section_callback' ),
			'my_advanced_plugin_general'
		);

		add_settings_field(
			'api_endpoint',
			__( 'API Endpoint', 'my-advanced-plugin' ),
			array( $this, 'api_endpoint_callback' ),
			'my_advanced_plugin_general',
			'my_advanced_plugin_main'
		);
	}

	/**
	 * Section callback
	 */
	public function section_callback() {
		echo '<p>' . esc_html__( 'Configure the main plugin settings.', 'my-advanced-plugin' ) . '</p>';
	}

	/**
	 * API endpoint field callback
	 */
	public function api_endpoint_callback() {
		$options = get_option( 'my_advanced_plugin_options', array() );
		$value   = isset( $options['api_endpoint'] ) ? $options['api_endpoint'] : '';
		?>
		<input type="text" name="my_advanced_plugin_options[api_endpoint]" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<p class="description"><?php esc_html_e( 'Enter your API endpoint URL.', 'my-advanced-plugin' ); ?></p>
		<?php
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<a href="?page=my_advanced_plugin&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'General', 'my-advanced-plugin' ); ?>
				</a>
				<a href="?page=my_advanced_plugin&tab=advanced" class="nav-tab <?php echo 'advanced' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Advanced', 'my-advanced-plugin' ); ?>
				</a>
				<?php do_action( 'my_advanced_plugin_tabs', $active_tab ); ?>
			</h2>

			<div class="tab-content">
				<?php
				switch ( $active_tab ) {
					case 'general':
						$this->render_general_tab();
						break;
					case 'advanced':
						$this->render_advanced_tab();
						break;
					default:
						do_action( 'my_advanced_plugin_content', $active_tab );
						break;
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
			settings_fields( 'my_advanced_plugin_general' );
			do_settings_sections( 'my_advanced_plugin_general' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Render advanced tab
	 */
	private function render_advanced_tab() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Advanced Settings', 'my-advanced-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Advanced configuration options...', 'my-advanced-plugin' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Check if license is active
	 *
	 * @return bool
	 */
	public function is_license_active() {
		if ( ! $this->license ) {
			return false;
		}

		return $this->license->is_license_active();
	}

	/**
	 * Get license instance
	 *
	 * @return \Closemarketing\WPLicenseManager\License|null
	 */
	public function get_license() {
		return $this->license;
	}
}

// Initialize plugin.
function my_advanced_plugin_init() {
	return new My_Advanced_Plugin();
}
add_action( 'plugins_loaded', 'my_advanced_plugin_init' );

/**
 * Helper function to check license status
 *
 * @return bool
 */
function my_advanced_plugin_is_premium() {
	$plugin = my_advanced_plugin_init();
	return $plugin->is_license_active();
}

