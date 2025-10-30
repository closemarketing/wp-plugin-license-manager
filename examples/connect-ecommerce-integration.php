<?php
/**
 * Connect Ecommerce Integration Example
 *
 * This example shows how to integrate the license manager into the
 * Connect Ecommerce plugin using the existing action hooks.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

/*
Plugin Name: Connect WooCommerce for [System]
Plugin URI:  https://close.technology/wordpress-plugins/
Description: Connects WooCommerce with external system
Version:     1.0.0
Author:      Closemarketing
Text Domain: connect-ecommerce
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'CONN_VERSION', '1.0.0' );
define( 'CONN_PLUGIN', __FILE__ );
define( 'CONN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CONN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Include Composer autoloader.
require_once CONN_PLUGIN_PATH . 'vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;

/**
 * Initialize License Manager for Connect Ecommerce
 *
 * This integrates with your existing settings page structure.
 */
function conn_init_license() {
	try {
		new License(
			array(
				'api_url'           => 'https://close.technology/',  // Your store URL.
				'file'              => CONN_PLUGIN,
				'version'           => CONN_VERSION,
				'slug'              => 'connect_ecommerce',          // Plugin slug.
				'name'              => 'Connect Ecommerce',          // Plugin name.
				'text_domain'       => 'connect-ecommerce',          // Your text domain.
				
				// IMPORTANT: Use your existing action hooks!
				'settings_tabs'     => 'connect_ecommerce_settings_tabs',
				'settings_content'  => 'connect_ecommerce_settings_tabs_content',
				'settings_page'     => 'connect_ecommerce',          // Your settings page slug.
				'option_group'      => 'connect_ecommerce_license',
				'settings_section'  => 'conn_settings_admin_license',
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
add_action( 'plugins_loaded', 'conn_init_license' );

/**
 * Add Connect Ecommerce settings menu
 *
 * This is your existing settings page function.
 */
function conn_add_admin_menu() {
	add_menu_page(
		__( 'Connect Ecommerce', 'connect-ecommerce' ),
		__( 'Connect Ecommerce', 'connect-ecommerce' ),
		'manage_options',
		'connect_ecommerce',
		'conn_render_settings_page',
		'dashicons-admin-generic',
		30
	);
}
add_action( 'admin_menu', 'conn_add_admin_menu' );

/**
 * Render Connect Ecommerce settings page
 *
 * This is your existing settings page with tabs.
 */
function conn_render_settings_page() {
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<h2 class="nav-tab-wrapper">
			<!-- Your existing tabs -->
			<a href="?page=connect_ecommerce&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'General', 'connect-ecommerce' ); ?>
			</a>
			<a href="?page=connect_ecommerce&tab=products" class="nav-tab <?php echo 'products' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Products', 'connect-ecommerce' ); ?>
			</a>
			<a href="?page=connect_ecommerce&tab=orders" class="nav-tab <?php echo 'orders' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Orders', 'connect-ecommerce' ); ?>
			</a>
			
			<!-- The License tab is automatically added here by the License class! -->
			<?php do_action( 'connect_ecommerce_settings_tabs', $active_tab ); ?>
		</h2>

		<div class="tab-content">
			<?php
			// Your existing tab content.
			switch ( $active_tab ) {
				case 'general':
					conn_render_general_tab();
					break;
				case 'products':
					conn_render_products_tab();
					break;
				case 'orders':
					conn_render_orders_tab();
					break;
				default:
					// The License tab content is automatically added here!
					do_action( 'connect_ecommerce_settings_tabs_content', $active_tab );
					break;
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Render general tab (your existing tab)
 */
function conn_render_general_tab() {
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
 * Render products tab (your existing tab)
 */
function conn_render_products_tab() {
	?>
	<h2><?php esc_html_e( 'Product Settings', 'connect-ecommerce' ); ?></h2>
	<p><?php esc_html_e( 'Configure product synchronization settings here.', 'connect-ecommerce' ); ?></p>
	<?php
}

/**
 * Render orders tab (your existing tab)
 */
function conn_render_orders_tab() {
	?>
	<h2><?php esc_html_e( 'Order Settings', 'connect-ecommerce' ); ?></h2>
	<p><?php esc_html_e( 'Configure order synchronization settings here.', 'connect-ecommerce' ); ?></p>
	<?php
}

/**
 * Register general settings (your existing settings)
 */
function conn_register_general_settings() {
	register_setting( 'connect_ecommerce_general', 'connect_ecommerce_options' );

	add_settings_section(
		'connect_ecommerce_main',
		__( 'API Settings', 'connect-ecommerce' ),
		function() {
			echo '<p>' . esc_html__( 'Enter your API credentials below.', 'connect-ecommerce' ) . '</p>';
		},
		'connect_ecommerce_general'
	);

	add_settings_field(
		'api_key',
		__( 'API Key', 'connect-ecommerce' ),
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
add_action( 'admin_init', 'conn_register_general_settings' );



