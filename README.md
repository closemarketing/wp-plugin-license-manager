# WordPress Plugin License Manager

A reusable Composer library for managing WordPress plugin licenses with Enwikuna License Manager.

## Features

- ✅ Easy integration with any WordPress plugin
- ✅ License activation/deactivation
- ✅ Automatic plugin updates
- ✅ Configurable text domain for translations
- ✅ Flexible settings integration with customizable page and tabs
- ✅ Automatic settings UI generation with Settings class
- ✅ Improved license deactivation handling
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

### Using the Settings Class (Recommended)

The `Settings` class provides automatic UI generation and form handling. This is the easiest way to integrate license management:

```php
use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

add_action( 'plugins_loaded', function() {
    global $myplugin_license;

    if ( ! class_exists( '\Closemarketing\WPLicenseManager\License' ) ) {
        return;
    }

    try {
        // Create license instance.
        $myplugin_license = new License(
            array(
                'api_url'         => MYPLUGIN_LICENSE_API_URL,
                'rest_api_key'    => MYPLUGIN_LICENSE_API_KEY,
                'rest_api_secret' => MYPLUGIN_LICENSE_API_SECRET,
                'product_uuid'    => MYPLUGIN_LICENSE_PRODUCT_UUID,
                'file'           => __FILE__,
                'version'        => '1.0.0',
                'slug'           => 'my-plugin',
                'name'           => 'My Awesome Plugin',
                'text_domain'    => 'my-plugin',
            )
        );

        // Create Settings instance with automatic UI.
        $license_settings = new Settings(
            $myplugin_license,
            array(
                'title'         => __( 'My Plugin License', 'my-plugin' ),
                'description'   => __( 'Manage your license to receive updates and support.', 'my-plugin' ),
                'plugin_name'   => 'My Awesome Plugin',
                'purchase_url'  => 'https://yourstore.com/plugins/my-plugin/',
                'renew_url'     => 'https://yourstore.com/my-account/',
                'benefits'      => array(
                    __( 'Automatic plugin updates', 'my-plugin' ),
                    __( 'Access to new features', 'my-plugin' ),
                    __( 'Priority support', 'my-plugin' ),
                    __( 'Security patches', 'my-plugin' ),
                ),
                'settings_page' => 'my-plugin-settings',  // Your settings page slug.
                'default_tab'   => 'license',            // Default tab for redirects.
                'tab_param'     => 'tab',                // URL parameter for tabs.
            )
        );

        // Add license tab to your existing settings page.
        add_filter( 'my_plugin_settings_tabs', function( $tabs ) {
            $tabs[] = array(
                'tab'    => 'license',
                'label'  => __( 'License', 'my-plugin' ),
                'action' => 'my_plugin_license_content',
            );
            return $tabs;
        });

        // Render license content.
        add_action( 'my_plugin_license_content', function() use ( $license_settings ) {
            $license_settings->render();
        });

    } catch ( \Exception $e ) {
        add_action( 'admin_notices', function() use ( $e ) {
            echo '<div class="notice notice-error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
        });
    }
}, 5 );
```

### Settings Class Options

The `Settings` class accepts the following options:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `title` | string | 'License Management' | Title displayed in the license card |
| `description` | string | 'Manage your license...' | Description text |
| `plugin_name` | string | Auto from License | Plugin name for display |
| `purchase_url` | string | 'https://close.technology/' | URL to purchase page |
| `renew_url` | string | 'https://close.technology/my-account/' | URL to renew license |
| `benefits` | array | Default list | Array of benefit strings |
| `settings_page` | string | 'connect_ecommerce' | Settings page slug where form is submitted |
| `default_tab` | string | '' | Default tab for redirects after form submission |
| `tab_param` | string | 'tab' | URL parameter name for tabs |

### Custom Settings Page Integration

If you want to handle the license UI yourself, you can still use the `License` class without the `Settings` class:

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

### License Class Methods

#### `is_license_active()`

Check if the license is currently active:

```php
if ( $license->is_license_active() ) {
    // License is active, enable premium features.
}
```

#### `get_api_key_status( $live = false )`

Get license status:

```php
// Get cached status.
$status = $license->get_api_key_status();

// Get real-time status from API.
$status = $license->get_api_key_status( true );
```

#### `get_option_key( $key )`

Get the full option key with slug prefix:

```php
$apikey_key = $license->get_option_key( 'apikey' );
// Returns: 'my-plugin_license_apikey'
```

#### `get_option_value( $key )`

Get an option value:

```php
$license_key = $license->get_option_value( 'apikey' );
```

#### `validate_license( $input )`

Validate and process license activation/deactivation:

```php
$input = array(
    'my-plugin_license_apikey' => 'license-key-here',
    'my-plugin_license_deactivate_checkbox' => 'on', // Optional, for deactivation
);
$license->validate_license( $input );
```

### Settings Class Methods

#### `render()`

Render the complete license settings UI:

```php
$settings = new Settings( $license, $options );
$settings->render();
```

The Settings class automatically:
- Handles form submission
- Validates nonces
- Processes license activation/deactivation
- Redirects after form submission
- Displays success/error messages

## Database Options

The library creates the following options in the WordPress database:

- `{slug}_license_apikey` - License key
- `{slug}_license_activated` - Activation status ('Activated', 'Deactivated', or 'Expired')
- `{slug}_license_deactivate_checkbox` - Deactivation checkbox status

Replace `{slug}` with your plugin slug.

## Changelog

### 1.2.1
- **New**: Added configurable options to Settings class (`settings_page`, `default_tab`, `tab_param`)
- **Improved**: Enhanced license deactivation handling with better fallback mechanisms
- **Improved**: Settings class now works with any plugin's settings page structure
- **Fixed**: License deactivation now properly handles readonly fields
- **Fixed**: License key is properly retrieved when deactivating from readonly input fields
- **Documentation**: Updated README with comprehensive Settings class usage examples

### 1.2.0
- Clean code.
- Unify Libraries and have styles.

### 1.1.1-1.1.2
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
