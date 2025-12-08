# WordPress Plugin License Manager

A reusable Composer library for managing WordPress plugin licenses with Enwikuna License Manager.

## Features

- ✅ Easy integration with any WordPress plugin
- ✅ License activation/deactivation
- ✅ Automatic plugin updates
- ✅ Configurable text domain for translations
- ✅ Flexible settings integration
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
        "closemarketing/wp-plugin-license-manager": "@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "../wp-plugin-license-manager"
        }
    ]
}
```

## Usage

### Basic Integration

Add this code to your main plugin file:

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Version: 1.0.0
 */

// Include Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;

// License configuration constants.
define( 'MYPLUGIN_LICENSE_API_URL', 'https://yourstore.com/' );
define( 'MYPLUGIN_LICENSE_API_KEY', 'ck_your_consumer_key' );
define( 'MYPLUGIN_LICENSE_API_SECRET', 'cs_your_consumer_secret' );
define( 'MYPLUGIN_LICENSE_PRODUCT_UUID', 'YOUR-PRODUCT-UUID' );

// Global license instance.
$myplugin_license = null;

// Initialize the license manager.
add_action( 'plugins_loaded', function() {
    global $myplugin_license;

    if ( ! class_exists( '\Closemarketing\WPLicenseManager\License' ) ) {
        return;
    }

    try {
        $myplugin_license = new License(
            array(
                'api_url'          => MYPLUGIN_LICENSE_API_URL,
                'rest_api_key'     => MYPLUGIN_LICENSE_API_KEY,
                'rest_api_secret'  => MYPLUGIN_LICENSE_API_SECRET,
                'product_uuid'     => MYPLUGIN_LICENSE_PRODUCT_UUID,
                'file'             => __FILE__,
                'version'          => '1.0.0',
                'slug'             => 'my-plugin',
                'name'             => 'My Awesome Plugin',
                'text_domain'      => 'my-plugin',
                'settings_page'    => 'my-plugin-settings',
                'settings_tabs'    => 'my_plugin_tabs_hook',    // Your tabs action hook.
                'settings_content' => 'my_plugin_content_hook', // Your content action hook.
            )
        );
    } catch ( \Exception $e ) {
        add_action( 'admin_notices', function() use ( $e ) {
            echo '<div class="notice notice-error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
        });
    }
}, 5 );

// Helper function to check license status.
function myplugin_is_license_valid() {
    global $myplugin_license;
    return $myplugin_license && $myplugin_license->is_license_active();
}
```

### Custom Settings Page Integration

If you want to handle the license UI yourself (like FrontBlocks does), set the tab hooks to unused values and create your own form:

```php
$license = new License(
    array(
        // ... other options ...
        'settings_tabs'    => 'myplugin_tabs_disabled',    // Disabled - UI handled manually.
        'settings_content' => 'myplugin_content_disabled', // Disabled - UI handled manually.
    )
);
```

Then in your settings page, create a form that submits to `options.php` with:

```php
<form method="post" action="options.php">
    <?php settings_fields( 'my-plugin_license' ); ?>
    
    <input type="text" 
        name="my-plugin_license_apikey" 
        value="<?php echo esc_attr( get_option( 'my-plugin_license_apikey' ) ); ?>"
    />
    
    <!-- For deactivation, add checkbox when license is active -->
    <input type="checkbox" name="my-plugin_license_deactivate_checkbox" value="on" />
    
    <button type="submit">Save</button>
</form>
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
- `{slug}_license_activated` - Activation status ('Activated', 'Deactivated', or 'Expired')
- `{slug}_license_deactivate_checkbox` - Deactivation checkbox status

Replace `{slug}` with your plugin slug.

## Changelog

### 1.1.1
- Add support to FormsCRM plugins.

### 1.1.0
- **Breaking Change**: Migrated from WooCommerce API Manager to Enwikuna License Manager
- New required options: `rest_api_key`, `rest_api_secret`, `product_uuid`
- Removed `product_id` option (replaced by `product_uuid`)
- Updated API endpoints to use Enwikuna REST API
- Simplified license field (removed Product ID field)

### 1.0.1
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
