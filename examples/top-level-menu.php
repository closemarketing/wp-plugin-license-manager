<?php
/**
 * Top-Level Menu Example
 *
 * This example shows how to create a top-level menu item for the license page.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

/*
Plugin Name: My Plugin with Top Menu
Plugin URI:  https://example.com/my-plugin
Description: Example plugin with top-level license menu
Version:     1.0.0
Author:      Your Name
Text Domain: my-plugin-top
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'MY_TOP_PLUGIN_VERSION', '1.0.0' );
define( 'MY_TOP_PLUGIN_FILE', __FILE__ );

// Include Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

/**
 * Initialize plugin with top-level menu
 */
function my_top_plugin_init() {
	try {
		// Create license instance.
		$license = new License(
			array(
				'api_url'     => 'https://yourstore.com/',
				'file'        => MY_TOP_PLUGIN_FILE,
				'version'     => MY_TOP_PLUGIN_VERSION,
				'slug'        => 'my_top_plugin',
				'name'        => 'My Top Plugin',
				'text_domain' => 'my-plugin-top',
			)
		);

		// Create top-level menu page.
		new Settings(
			$license,
			array(
				'page_title'      => __( 'My Plugin Settings', 'my-plugin-top' ),
				'menu_title'      => __( 'My Plugin', 'my-plugin-top' ),
				'menu_slug'       => 'my-plugin-settings',
				'icon_url'        => 'dashicons-admin-network',
				'position'        => 30,
				'standalone_page' => true, // Creates top-level menu.
				'custom_css'      => true,
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
add_action( 'plugins_loaded', 'my_top_plugin_init' );

