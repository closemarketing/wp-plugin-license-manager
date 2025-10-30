# WordPress Plugin License Manager

A reusable Composer library for managing WordPress plugin licenses with WooCommerce API Manager.

## Features

- ✅ **Zero-code settings page** - Automatic UI generation, no fields to create!
- ✅ Easy integration with any WordPress plugin
- ✅ License activation/deactivation
- ✅ Automatic plugin updates
- ✅ Beautiful, responsive admin interface
- ✅ Configurable text domain for translations
- ✅ Flexible settings page integration
- ✅ WooCommerce API Manager compatible
- ✅ WordPress Coding Standards compliant

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- WooCommerce API Manager (server-side)

## Installation

Install via Composer:

```bash
composer require closemarketing/wp-plugin-license-manager
```

Or manually add to your `composer.json`:

```json
{
    "require": {
        "closemarketing/wp-plugin-license-manager": "^2.0"
    }
}
```

## Usage

### 🚀 The Easiest Way (Recommended)

**Want a complete settings page with ZERO field creation?** Use the `Settings` class:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

add_action( 'plugins_loaded', function() {
    $license = new License(
        array(
            'api_url'     => 'https://yourstore.com/',
            'file'        => __FILE__,
            'version'     => '1.0.0',
            'slug'        => 'my_plugin',
            'name'        => 'My Plugin',
            'text_domain' => 'my-plugin',
        )
    );
    
    // That's it! Complete settings page automatically created
    new Settings( $license );
} );
```

**Result:** Complete license settings page under Settings → License with all fields, styling, and functionality!

See [QUICK-START.md](./docs/QUICK-START.md) for more examples.

### Basic Integration (Manual Settings)

If you want to create your own settings page, add this code to your main plugin file:

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Version: 1.0.0
 */

// Include Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;

// Initialize the license manager.
function my_plugin_init_license() {
	try {
		$license = new License(
			array(
				'api_url'      => 'https://yourstore.com/', // Your WooCommerce store URL.
				'file'         => __FILE__,                 // Main plugin file.
				'version'      => '1.0.0',                  // Plugin version.
				'slug'         => 'my-plugin',              // Plugin slug.
				'name'         => 'My Awesome Plugin',      // Plugin name.
				'text_domain'  => 'my-plugin',              // Text domain for translations.
			)
		);
	} catch ( \Exception $e ) {
		// Handle initialization error.
		add_action(
			'admin_notices',
			function() use ( $e ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
			}
		);
	}
}
add_action( 'plugins_loaded', 'my_plugin_init_license' );
```

### With Settings Page Integration

If your plugin already has a settings page with tabs:

```php
<?php
use Closemarketing\WPLicenseManager\License;

function my_plugin_init_license() {
	$license = new License(
		array(
			'api_url'           => 'https://yourstore.com/',
			'file'              => __FILE__,
			'version'           => '1.0.0',
			'slug'              => 'my_plugin',
			'name'              => 'My Awesome Plugin',
			'text_domain'       => 'my-plugin',
			'settings_page'     => 'my_plugin_settings',         // Your settings page slug.
			'settings_tabs'     => 'my_plugin_settings_tabs',    // Action hook for tabs.
			'settings_content'  => 'my_plugin_settings_content', // Action hook for content.
		)
	);
}
add_action( 'plugins_loaded', 'my_plugin_init_license' );
```

### Complete Settings Page Example

Here's a complete example of how to integrate the license manager with a settings page:

```php
<?php
// Add menu item.
add_action( 'admin_menu', 'my_plugin_add_admin_menu' );
function my_plugin_add_admin_menu() {
	add_options_page(
		'My Plugin Settings',
		'My Plugin',
		'manage_options',
		'my_plugin_settings',
		'my_plugin_settings_page'
	);
}

// Render settings page.
function my_plugin_settings_page() {
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<h2 class="nav-tab-wrapper">
			<a href="?page=my_plugin_settings&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'General', 'my-plugin' ); ?>
			</a>
			<?php do_action( 'my_plugin_settings_tabs', $active_tab ); ?>
		</h2>
		<div class="tab-content">
			<?php
			if ( 'general' === $active_tab ) {
				echo '<p>' . esc_html__( 'General settings here...', 'my-plugin' ) . '</p>';
			}
			do_action( 'my_plugin_settings_content', $active_tab );
			?>
		</div>
	</div>
	<?php
}

// Initialize License Manager.
use Closemarketing\WPLicenseManager\License;

$license = new License(
	array(
		'api_url'           => 'https://yourstore.com/',
		'file'              => __FILE__,
		'version'           => '1.0.0',
		'slug'              => 'my_plugin',
		'name'              => 'My Awesome Plugin',
		'text_domain'       => 'my-plugin',
		'settings_page'     => 'my_plugin_settings',
		'settings_tabs'     => 'my_plugin_settings_tabs',
		'settings_content'  => 'my_plugin_settings_content',
	)
);
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| `api_url` | string | Yes | - | Your WooCommerce API Manager URL |
| `file` | string | Yes | - | Main plugin file path (`__FILE__`) |
| `version` | string | Yes | - | Plugin version |
| `slug` | string | Yes | - | Plugin slug (used for option keys) |
| `name` | string | Yes | - | Plugin name (display) |
| `text_domain` | string | No | 'default' | Text domain for translations |
| `plugin_slug` | string | No | Same as `slug` | Plugin slug for updates |
| `plugin_name` | string | No | Auto-generated | Plugin basename |
| `settings_page` | string | No | `{slug}_settings` | Settings page slug |
| `settings_tabs` | string | No | `{slug}_settings_tabs` | Action hook for tabs |
| `settings_content` | string | No | `{slug}_settings_tabs_content` | Action hook for content |
| `option_group` | string | No | `{slug}_license` | Settings option group |
| `settings_section` | string | No | `{slug}_settings_admin_license` | Settings section ID |
| `capabilities` | string | No | 'manage_options' | Required user capability |

## Public Methods

### `is_license_active()`

Check if the license is currently active:

```php
if ( $license->is_license_active() ) {
	// License is active, enable premium features.
}
```

### `get_api_key_status( $live = false )`

Get license status:

```php
// Get cached status.
$status = $license->get_api_key_status();

// Get real-time status from API.
$status = $license->get_api_key_status( true );
```

## Database Options

The library creates the following options in the WordPress database:

- `{slug}_license_apikey` - API key
- `{slug}_license_product_id` - Product ID
- `{slug}_license_activated` - Activation status ('Activated' or 'Deactivated')
- `{slug}_license_instance` - Unique instance ID
- `{slug}_license_deactivate_checkbox` - Deactivation checkbox status

Replace `{slug}` with your plugin slug.

## WooCommerce API Manager Setup

This library is designed to work with the [WooCommerce API Manager](https://www.toddlahman.com/shop/woocommerce-api-manager/) plugin on your WooCommerce store.

### Server Requirements

1. Install WooCommerce API Manager on your store
2. Create a product with API management enabled
3. Set the API product ID
4. Distribute the API key to your customers

## Translation

The library is translation-ready. All strings use the text domain you specify in the configuration.

To translate:

1. Use your plugin's text domain
2. Generate `.pot` file with your plugin's translations
3. The license strings will be included automatically

## Error Handling

The library throws exceptions during initialization if required options are missing:

```php
try {
	$license = new License( $options );
} catch ( \Exception $e ) {
	// Handle error - required option missing.
	error_log( 'License Manager Error: ' . $e->getMessage() );
}
```

## Security

- All inputs are sanitized and validated
- Uses WordPress nonces for form submissions
- Escapes all output
- Follows WordPress coding standards
- Uses Yoda conditions for comparisons

## Changelog

### 2.0.0
- Complete refactor as Composer library
- PSR-4 autoloading
- Configurable options
- Improved error handling
- Better documentation

### 1.0.0
- Initial release

## Support

For support, please visit:
- [GitHub Issues](https://github.com/closemarketing/wp-plugin-license-manager/issues)
- [Close Marketing](https://close.marketing)

## License

GPL-2.0-or-later

## Credits

Developed by [Close Marketing](https://close.marketing)

Author: David Perez <david@closemarketing.es>

