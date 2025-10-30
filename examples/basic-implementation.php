<?php
/**
 * Basic Implementation Example
 *
 * This is a complete example of how to integrate the License Manager
 * library into your WordPress plugin.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

/*
Plugin Name: My Awesome Plugin
Plugin URI:  https://example.com/my-awesome-plugin
Description: Example plugin with license management
Version:     1.0.0
Author:      Your Name
Author URI:  https://example.com
Text Domain: my-awesome-plugin
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'MY_PLUGIN_VERSION', '1.0.0' );
define( 'MY_PLUGIN_FILE', __FILE__ );
define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include Composer autoloader.
require_once MY_PLUGIN_PATH . 'vendor/autoload.php';

/**
 * Initialize the plugin
 */
function my_plugin_init() {
	// Your plugin initialization code here.
}
add_action( 'plugins_loaded', 'my_plugin_init' );

/**
 * Initialize License Manager
 */
function my_plugin_init_license() {
	try {
		new \Closemarketing\WPLicenseManager\License(
			array(
				'api_url'      => 'https://yourstore.com/',  // Your WooCommerce store URL.
				'file'         => MY_PLUGIN_FILE,
				'version'      => MY_PLUGIN_VERSION,
				'slug'         => 'my_awesome_plugin',
				'name'         => 'My Awesome Plugin',
				'text_domain'  => 'my-awesome-plugin',
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
add_action( 'plugins_loaded', 'my_plugin_init_license' );

/**
 * Add settings menu
 */
function my_plugin_add_admin_menu() {
	add_options_page(
		__( 'My Plugin Settings', 'my-awesome-plugin' ),
		__( 'My Plugin', 'my-awesome-plugin' ),
		'manage_options',
		'my_plugin_settings',
		'my_plugin_settings_page'
	);
}
add_action( 'admin_menu', 'my_plugin_add_admin_menu' );

/**
 * Render settings page
 */
function my_plugin_settings_page() {
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<h2 class="nav-tab-wrapper">
			<a href="?page=my_plugin_settings&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'General', 'my-awesome-plugin' ); ?>
			</a>
			<?php
			/**
			 * Action hook for adding additional settings tabs.
			 *
			 * @param string $active_tab Current active tab.
			 */
			do_action( 'my_plugin_settings_tabs', $active_tab );
			?>
		</h2>

		<div class="tab-content">
			<?php
			if ( 'general' === $active_tab ) {
				my_plugin_general_settings();
			}

			/**
			 * Action hook for adding settings tab content.
			 *
			 * @param string $active_tab Current active tab.
			 */
			do_action( 'my_plugin_settings_content', $active_tab );
			?>
		</div>
	</div>
	<?php
}

/**
 * Render general settings tab
 */
function my_plugin_general_settings() {
	?>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'my_plugin_general' );
		do_settings_sections( 'my_plugin_general' );
		submit_button();
		?>
	</form>
	<?php
}

/**
 * Register general settings
 */
function my_plugin_register_settings() {
	register_setting(
		'my_plugin_general',
		'my_plugin_general_options'
	);

	add_settings_section(
		'my_plugin_general_section',
		__( 'General Settings', 'my-awesome-plugin' ),
		'my_plugin_general_section_callback',
		'my_plugin_general'
	);

	add_settings_field(
		'my_plugin_enable_feature',
		__( 'Enable Feature', 'my-awesome-plugin' ),
		'my_plugin_enable_feature_callback',
		'my_plugin_general',
		'my_plugin_general_section'
	);
}
add_action( 'admin_init', 'my_plugin_register_settings' );

/**
 * General settings section callback
 */
function my_plugin_general_section_callback() {
	echo '<p>' . esc_html__( 'Configure your plugin settings below.', 'my-awesome-plugin' ) . '</p>';
}

/**
 * Enable feature field callback
 */
function my_plugin_enable_feature_callback() {
	$options = get_option( 'my_plugin_general_options', array() );
	$checked = isset( $options['enable_feature'] ) ? $options['enable_feature'] : 0;
	?>
	<input type="checkbox" name="my_plugin_general_options[enable_feature]" value="1" <?php checked( 1, $checked ); ?>>
	<span class="description"><?php esc_html_e( 'Check to enable this feature.', 'my-awesome-plugin' ); ?></span>
	<?php
}

/**
 * Check if license is active
 *
 * Use this function to conditionally enable premium features.
 *
 * @return bool
 */
function my_plugin_is_premium() {
	static $license = null;

	if ( null === $license ) {
		try {
			$license = new \Closemarketing\WPLicenseManager\License(
				array(
					'api_url'      => 'https://yourstore.com/',
					'file'         => MY_PLUGIN_FILE,
					'version'      => MY_PLUGIN_VERSION,
					'slug'         => 'my_awesome_plugin',
					'name'         => 'My Awesome Plugin',
					'text_domain'  => 'my-awesome-plugin',
				)
			);
		} catch ( \Exception $e ) {
			return false;
		}
	}

	return $license->is_license_active();
}

/**
 * Example of using premium feature check
 */
function my_plugin_premium_feature() {
	if ( my_plugin_is_premium() ) {
		// Enable premium feature.
		echo '<p>' . esc_html__( 'Premium feature enabled!', 'my-awesome-plugin' ) . '</p>';
	} else {
		// Show upgrade message.
		echo '<p>' . esc_html__( 'Please activate your license to use premium features.', 'my-awesome-plugin' ) . '</p>';
	}
}

