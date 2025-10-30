<?php
/**
 * Standalone Settings Example
 *
 * This example shows how to use the Settings class to automatically
 * create a complete license settings page without writing any HTML/fields.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

/*
Plugin Name: My Plugin with Auto Settings
Plugin URI:  https://example.com/my-plugin
Description: Example plugin with automatic license settings page
Version:     1.0.0
Author:      Your Name
Text Domain: my-plugin
*/

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'MY_PLUGIN_VERSION', '1.0.0' );
define( 'MY_PLUGIN_FILE', __FILE__ );

// Include Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

/**
 * Initialize plugin with automatic settings page
 */
function my_plugin_init() {
	try {
		// Create license instance.
		$license = new License(
			array(
				'api_url'     => 'https://yourstore.com/',
				'file'        => MY_PLUGIN_FILE,
				'version'     => MY_PLUGIN_VERSION,
				'slug'        => 'my_plugin',
				'name'        => 'My Plugin',
				'text_domain' => 'my-plugin',
			)
		);

		// Create automatic settings page - NO FIELDS NEEDED!
		new Settings(
			$license,
			array(
				'page_title'      => __( 'My Plugin License', 'my-plugin' ),
				'menu_title'      => __( 'License', 'my-plugin' ),
				'menu_slug'       => 'my-plugin-license',
				'parent_slug'     => 'options-general.php', // Under Settings menu.
				'standalone_page' => false, // Set to true for top-level menu.
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
add_action( 'plugins_loaded', 'my_plugin_init' );

