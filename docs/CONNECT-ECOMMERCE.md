# Connect Ecommerce Integration Guide

This guide shows how to integrate the License Manager into your Connect Ecommerce plugins.

## Your Existing Hooks

Your Connect Ecommerce plugins use these action hooks:
- `connect_ecommerce_settings_tabs` - For adding tabs
- `connect_ecommerce_settings_tabs_content` - For tab content

## 🚀 The Easiest Way (Recommended)

**Just add these lines to your Connect Ecommerce plugin:**

```php
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;

add_action( 'plugins_loaded', function() {
    new License(
        array(
            'api_url'           => 'https://close.technology/',
            'file'              => __FILE__,
            'version'           => '1.0.0',
            'slug'              => 'connect_ecommerce',
            'name'              => 'Connect Ecommerce',
            'text_domain'       => 'connect-woocommerce-holded',
            
            // Use your existing hooks!
            'settings_tabs'     => 'connect_ecommerce_settings_tabs',
            'settings_content'  => 'connect_ecommerce_settings_tabs_content',
            'settings_page'     => 'connect_ecommerce',
        )
    );
} );
```

**That's it!** The License tab will automatically appear in your existing settings page with all fields pre-created.

## How It Works

### 1. Your Settings Page (Unchanged)

```php
function render_settings_page() {
    $tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
    ?>
    <div class="wrap">
        <h1>Connect Ecommerce</h1>
        
        <h2 class="nav-tab-wrapper">
            <!-- Your existing tabs -->
            <a href="?page=connect_ecommerce&tab=general" class="nav-tab">General</a>
            <a href="?page=connect_ecommerce&tab=products" class="nav-tab">Products</a>
            
            <!-- License tab automatically added here! -->
            <?php do_action( 'connect_ecommerce_settings_tabs', $tab ); ?>
        </h2>
        
        <div class="tab-content">
            <?php
            if ( 'general' === $tab ) {
                render_general_tab();
            } elseif ( 'products' === $tab ) {
                render_products_tab();
            } else {
                // License content automatically rendered here!
                do_action( 'connect_ecommerce_settings_tabs_content', $tab );
            }
            ?>
        </div>
    </div>
    <?php
}
```

### 2. What You Get Automatically

✅ **License Tab** - Automatically added to your tabs
✅ **All Fields** - API Key, Product ID, Status, Deactivate
✅ **Styling** - Beautiful, responsive design
✅ **Validation** - Activation/deactivation logic
✅ **Updates** - Automatic plugin updates
✅ **No Code** - Zero HTML/CSS/fields to create!

## Examples for Different Connect Ecommerce Plugins

### Connect WooCommerce for Holded

```php
new License(
    array(
        'api_url'           => 'https://close.technology/',
        'file'              => CONNWOO_PLUGIN,
        'version'           => CONNWOO_VERSION,
        'slug'              => 'connect_woocommerce_holded',
        'name'              => 'Holded',
        'text_domain'       => 'connect-woocommerce-holded',
        'settings_tabs'     => 'connect_ecommerce_settings_tabs',
        'settings_content'  => 'connect_ecommerce_settings_tabs_content',
        'settings_page'     => 'connect_ecommerce',
    )
);
```

### Connect WooCommerce for Factusol

```php
new License(
    array(
        'api_url'           => 'https://close.technology/',
        'file'              => CONNWOO_FACTUSOL_PLUGIN,
        'version'           => CONNWOO_FACTUSOL_VERSION,
        'slug'              => 'connect_woocommerce_factusol',
        'name'              => 'Factusol',
        'text_domain'       => 'connect-woocommerce-factusol',
        'settings_tabs'     => 'connect_ecommerce_settings_tabs',
        'settings_content'  => 'connect_ecommerce_settings_tabs_content',
        'settings_page'     => 'connect_ecommerce',
    )
);
```

### Connect WooCommerce for Odoo

```php
new License(
    array(
        'api_url'           => 'https://close.technology/',
        'file'              => CONNWOO_ODOO_PLUGIN,
        'version'           => CONNWOO_ODOO_VERSION,
        'slug'              => 'connect_woocommerce_odoo',
        'name'              => 'Odoo',
        'text_domain'       => 'connect-woocommerce-odoo',
        'settings_tabs'     => 'connect_ecommerce_settings_tabs',
        'settings_content'  => 'connect_ecommerce_settings_tabs_content',
        'settings_page'     => 'connect_ecommerce',
    )
);
```

## Complete Integration Example

See the example files:
- `examples/connect-ecommerce-integration.php` - Basic integration
- `examples/connect-ecommerce-with-settings-class.php` - With automatic fields

## Check License Status

Use this anywhere in your plugin to enable/disable features:

```php
// Helper function.
function connwoo_is_licensed() {
    static $license = null;
    
    if ( null === $license ) {
        $license = new License( [...] );
    }
    
    return $license->is_license_active();
}

// Use it.
if ( connwoo_is_licensed() ) {
    // Enable premium sync features.
    add_action( 'woocommerce_order_status_completed', 'sync_to_system' );
}
```

## Migration from Old Code

### Before (Old class-license.php)

```php
namespace CLOSE\ConnectEcommerce;

require_once 'class-license.php';

$options = array(
    'api_url' => 'https://close.technology/',
    'file'    => __FILE__,
    'version' => '1.0.0',
    'slug'    => 'connect-ecommerce',
    'name'    => 'Holded',
);

new License( $options );
```

### After (New Library)

```php
use Closemarketing\WPLicenseManager\License;

new License(
    array(
        'api_url'           => 'https://close.technology/',
        'file'              => __FILE__,
        'version'           => '1.0.0',
        'slug'              => 'connect_ecommerce',
        'name'              => 'Holded',
        'text_domain'       => 'connect-woocommerce-holded',
        'settings_tabs'     => 'connect_ecommerce_settings_tabs',
        'settings_content'  => 'connect_ecommerce_settings_tabs_content',
        'settings_page'     => 'connect_ecommerce',
    )
);
```

## Database Options

The library uses these option keys (all automatic):
- `connect_ecommerce_license_apikey`
- `connect_ecommerce_license_product_id`
- `connect_ecommerce_license_activated`
- `connect_ecommerce_license_instance`
- `connect_ecommerce_license_deactivate_checkbox`

Replace `connect_ecommerce` with your specific plugin slug.

## Installation Steps

### 1. Add to composer.json

```json
{
    "require": {
        "closemarketing/wp-plugin-license-manager": "^2.0"
    }
}
```

### 2. Install

```bash
composer install
```

### 3. Add License Code

Copy one of the examples above into your main plugin file.

### 4. Test

1. Go to Connect Ecommerce settings
2. You should see a new "License" tab
3. Enter API Key and Product ID
4. Click Save
5. Status should show "Activated"

## Troubleshooting

**License tab not showing?**
- Verify action hooks are correct: `connect_ecommerce_settings_tabs` and `connect_ecommerce_settings_tabs_content`
- Check License class is initialized on `plugins_loaded`
- Look for PHP errors in debug log

**Fields not saving?**
- Ensure `settings_page` matches your page slug
- Check WordPress is running `admin_init` action
- Verify user has `manage_options` capability

**Updates not working?**
- Activate license first
- Check `api_url` is correct
- Verify WooCommerce API Manager is configured on server

## Support

- Email: david@closemarketing.es
- Docs: https://close.technology/docs/
- GitHub: https://github.com/closemarketing/wp-plugin-license-manager



