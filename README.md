# WordPress Plugin License Manager

A reusable Composer library for managing WordPress plugin licenses with Enwikuna License Manager.

## Features

- ✅ **Zero-code settings page** - Automatic UI generation, no fields to create!
- ✅ Easy integration with any WordPress plugin
- ✅ License activation/deactivation
- ✅ Automatic plugin updates
- ✅ Beautiful, responsive admin interface
- ✅ Configurable text domain for translations
- ✅ Flexible settings page integration
- ✅ Enwikuna License Manager compatible
- ✅ WordPress Coding Standards compliant

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- Enwikuna License Manager (server-side)

## Installation

Install via Composer:

```bash
composer require closemarketing/wp-plugin-license-manager
```

Or manually add to your `composer.json`:

```json
{
    "require": {
        "closemarketing/wp-plugin-license-manager": "^3.0"
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
            'api_url'         => 'https://yourstore.com/',
            'rest_api_key'    => 'ck_your_consumer_key',
            'rest_api_secret' => 'cs_your_consumer_secret',
            'product_uuid'    => 'YOUR-PRODUCT-UUID',
            'file'            => __FILE__,
            'version'         => '1.0.0',
            'slug'            => 'my_plugin',
            'name'            => 'My Plugin',
            'text_domain'     => 'my-plugin',
        )
    );
    
    // That's it! Complete settings page automatically created
    new Settings( $license );
} );
```

**Result:** Complete license settings page under Settings → License with all fields, styling, and functionality!

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
                'api_url'         => 'https://yourstore.com/',    // Your Enwikuna License Manager URL.
                'rest_api_key'    => 'ck_your_consumer_key',      // REST API Consumer Key.
                'rest_api_secret' => 'cs_your_consumer_secret',   // REST API Consumer Secret.
                'product_uuid'    => 'YOUR-PRODUCT-UUID',         // Product UUID from Enwikuna.
                'file'            => __FILE__,                    // Main plugin file.
                'version'         => '1.0.0',                     // Plugin version.
                'slug'            => 'my-plugin',                 // Plugin slug.
                'name'            => 'My Awesome Plugin',         // Plugin name.
                'text_domain'     => 'my-plugin',                 // Text domain for translations.
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
            'rest_api_key'      => 'ck_your_consumer_key',
            'rest_api_secret'   => 'cs_your_consumer_secret',
            'product_uuid'      => 'YOUR-PRODUCT-UUID',
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

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| `api_url` | string | Yes | - | Your Enwikuna License Manager URL |
| `rest_api_key` | string | Yes | - | REST API Consumer Key (ck_xxx) |
| `rest_api_secret` | string | Yes | - | REST API Consumer Secret (cs_xxx) |
| `product_uuid` | string | Yes | - | Product UUID from Enwikuna |
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

## Enwikuna License Manager Setup

### Server Requirements

1. Install Enwikuna License Manager on your store
2. Create a product with a UUID
3. Create REST API credentials:
   - Go to **Enwikuna License Manager → Settings → REST API**
   - Click **Add Key**
   - Set **Permission** to **Read/Write**
   - Enable routes: **104, 106, 107** (for licenses) and **301-304** (for updates)
   - Copy the **Consumer Key** and **Consumer Secret**

### Required Routes

| Route | Description |
|-------|-------------|
| 104 | Validate License |
| 106 | Activate License |
| 107 | Deactivate License |
| 301-304 | Release/Update info |

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

- `{slug}_license_apikey` - License key
- `{slug}_license_activated` - Activation status ('Activated' or 'Deactivated')
- `{slug}_license_deactivate_checkbox` - Deactivation checkbox status

Replace `{slug}` with your plugin slug.

## Migration from v2.x (WooCommerce API Manager)

If you're migrating from the WooCommerce API Manager version:

1. Update your configuration to include new required options:
   - `rest_api_key` - Your Enwikuna REST API Consumer Key
   - `rest_api_secret` - Your Enwikuna REST API Consumer Secret
   - `product_uuid` - Your product's UUID from Enwikuna

2. Remove the old `product_id` option (no longer needed)

3. The `api_url` now points to your Enwikuna License Manager installation

4. License keys from the old system will need to be re-activated

## Changelog

### 3.0.0
- **Breaking Change**: Migrated from WooCommerce API Manager to Enwikuna License Manager
- New required options: `rest_api_key`, `rest_api_secret`, `product_uuid`
- Removed `product_id` option (replaced by `product_uuid`)
- Updated API endpoints to use Enwikuna REST API
- Simplified license field (removed Product ID field)

### 2.0.0
- Added Settings class for automatic settings page generation
- Improved UI and styling

### 1.0.0
- Initial release with WooCommerce API Manager support

## Support

For support, please visit:
- [GitHub Issues](https://github.com/closemarketing/wp-plugin-license-manager/issues)
- [Close Marketing](https://close.marketing)

## License

GPL-2.0-or-later

## Credits

Developed by [Close Marketing](https://close.marketing)

Author: David Perez <david@closemarketing.es>
