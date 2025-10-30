<?php
/**
 * Connect Ecommerce with Settings Class Integration
 *
 * This example shows how to use BOTH the License class (for integration
 * with existing tabs) AND the Settings class (for automatic field rendering).
 *
 * This is the EASIEST way - no need to create any fields manually!
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

/*
Plugin Name: Connect WooCommerce for [System] - Auto Settings
Plugin URI:  https://close.technology/wordpress-plugins/
Description: Connects WooCommerce with external system (with automatic settings)
Version:     2.0.0
Author:      Closemarketing
Text Domain: connect-ecommerce-auto
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'CONN_AUTO_VERSION', '2.0.0' );
define( 'CONN_AUTO_PLUGIN', __FILE__ );
define( 'CONN_AUTO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CONN_AUTO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Include Composer autoloader.
require_once CONN_AUTO_PLUGIN_PATH . 'vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

/**
 * Initialize with Settings Class
 *
 * Option 1: Standalone License Page (Simplest)
 */
function conn_auto_init_standalone() {
	try {
		// Create license instance.
		$license = new License(
			array(
				'api_url'     => 'https://close.technology/',
				'file'        => CONN_AUTO_PLUGIN,
				'version'     => CONN_AUTO_VERSION,
				'slug'        => 'connect_ecommerce',
				'name'        => 'Connect Ecommerce',
				'text_domain' => 'connect-ecommerce-auto',
			)
		);

		// Create automatic settings page - ALL FIELDS GENERATED AUTOMATICALLY!
		new Settings(
			$license,
			array(
				'page_title'  => __( 'Connect Ecommerce License', 'connect-ecommerce-auto' ),
				'menu_title'  => __( 'License', 'connect-ecommerce-auto' ),
				'menu_slug'   => 'connect-ecommerce-license',
				'parent_slug' => 'options-general.php', // Under Settings menu.
			)
		);

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
// Uncomment to use standalone license page:
// add_action( 'plugins_loaded', 'conn_auto_init_standalone' );

/**
 * Option 2: Integrate into Connect Ecommerce Settings Page
 *
 * This uses your existing action hooks AND auto-generates the fields!
 */
function conn_auto_init_integrated() {
	try {
		// Create license instance with your existing hooks.
		$license = new License(
			array(
				'api_url'           => 'https://close.technology/',
				'file'              => CONN_AUTO_PLUGIN,
				'version'           => CONN_AUTO_VERSION,
				'slug'              => 'connect_ecommerce',
				'name'              => 'Connect Ecommerce',
				'text_domain'       => 'connect-ecommerce-auto',
				
				// Use your existing action hooks.
				'settings_tabs'     => 'connect_ecommerce_settings_tabs',
				'settings_content'  => 'connect_ecommerce_settings_tabs_content',
				'settings_page'     => 'connect_ecommerce',
			)
		);

		// Now the License tab is automatically added to your existing settings page!
		// No need to create the Settings class - it's handled by the License class hooks.

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
// Uncomment to use integrated license tab:
add_action( 'plugins_loaded', 'conn_auto_init_integrated' );

/**
 * Your existing Connect Ecommerce settings page
 *
 * This remains exactly as you have it now.
 */
function conn_auto_add_admin_menu() {
	add_menu_page(
		__( 'Connect Ecommerce', 'connect-ecommerce-auto' ),
		__( 'Connect', 'connect-ecommerce-auto' ),
		'manage_options',
		'connect_ecommerce',
		'conn_auto_render_settings_page',
		'dashicons-admin-generic',
		30
	);
}
add_action( 'admin_menu', 'conn_auto_add_admin_menu' );

/**
 * Render settings page
 */
function conn_auto_render_settings_page() {
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<h2 class="nav-tab-wrapper">
			<a href="?page=connect_ecommerce&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'General', 'connect-ecommerce-auto' ); ?>
			</a>
			<a href="?page=connect_ecommerce&tab=products" class="nav-tab <?php echo 'products' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Products', 'connect-ecommerce-auto' ); ?>
			</a>
			<a href="?page=connect_ecommerce&tab=orders" class="nav-tab <?php echo 'orders' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Orders', 'connect-ecommerce-auto' ); ?>
			</a>
			
			<!-- LICENSE TAB AUTOMATICALLY ADDED HERE! -->
			<?php do_action( 'connect_ecommerce_settings_tabs', $active_tab ); ?>
		</h2>

		<div class="tab-content">
			<?php
			switch ( $active_tab ) {
				case 'general':
					conn_auto_render_general_tab();
					break;
				case 'products':
					echo '<h2>' . esc_html__( 'Products', 'connect-ecommerce-auto' ) . '</h2>';
					echo '<p>' . esc_html__( 'Product settings here.', 'connect-ecommerce-auto' ) . '</p>';
					break;
				case 'orders':
					echo '<h2>' . esc_html__( 'Orders', 'connect-ecommerce-auto' ) . '</h2>';
					echo '<p>' . esc_html__( 'Order settings here.', 'connect-ecommerce-auto' ) . '</p>';
					break;
				default:
					// LICENSE CONTENT AUTOMATICALLY RENDERED HERE!
					do_action( 'connect_ecommerce_settings_tabs_content', $active_tab );
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
function conn_auto_render_general_tab() {
	?>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'connect_ecommerce_general' );
		do_settings_sections( 'connect_ecommerce_general' );
		submit_button();
		?>
	</form>
	<?php
}

/**
 * Register settings
 */
function conn_auto_register_settings() {
	register_setting( 'connect_ecommerce_general', 'connect_ecommerce_options' );

	add_settings_section(
		'connect_ecommerce_main',
		__( 'API Settings', 'connect-ecommerce-auto' ),
		function() {
			echo '<p>' . esc_html__( 'Configure your API settings.', 'connect-ecommerce-auto' ) . '</p>';
		},
		'connect_ecommerce_general'
	);

	add_settings_field(
		'api_key',
		__( 'API Key', 'connect-ecommerce-auto' ),
		function() {
			$options = get_option( 'connect_ecommerce_options', array() );
			$value   = isset( $options['api_key'] ) ? $options['api_key'] : '';
			?>
			<input type="text" name="connect_ecommerce_options[api_key]" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
			<?php
		},
		'connect_ecommerce_general',
		'connect_ecommerce_main'
	);
}
add_action( 'admin_init', 'conn_auto_register_settings' );

/**
 * Helper function to check if licensed
 *
 * Use this throughout your plugin to enable/disable features.
 */
function conn_auto_is_licensed() {
	static $license = null;

	if ( null === $license ) {
		try {
			$license = new License(
				array(
					'api_url'     => 'https://close.technology/',
					'file'        => CONN_AUTO_PLUGIN,
					'version'     => CONN_AUTO_VERSION,
					'slug'        => 'connect_ecommerce',
					'name'        => 'Connect Ecommerce',
					'text_domain' => 'connect-ecommerce-auto',
				)
			);
		} catch ( \Exception $e ) {
			return false;
		}
	}

	return $license->is_license_active();
}

/**
 * Example: Conditional feature based on license
 */
function conn_auto_premium_sync() {
	if ( ! conn_auto_is_licensed() ) {
		return;
	}

	// Premium sync feature only for licensed users.
	// Your code here...
}
add_action( 'woocommerce_order_status_completed', 'conn_auto_premium_sync' );



