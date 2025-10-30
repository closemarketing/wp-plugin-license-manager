# Migration Guide

This guide will help you migrate from the old license class to the new library version.

## From Version 1.x to 2.0

### What Changed

1. **Namespace**: The class now uses PSR-4 autoloading with namespace `Closemarketing\WPLicenseManager`
2. **Location**: Class moved from `class-license.php` to `src/License.php`
3. **Configuration**: All settings are now configurable via constructor options
4. **Text Domain**: No longer hardcoded, can be customized
5. **Option Keys**: All option keys now use the plugin slug as prefix

### Step-by-Step Migration

#### Step 1: Update Composer

If you were manually including the file, update to use Composer:

**Old:**
```php
require_once 'path/to/class-license.php';
```

**New:**
```php
require_once 'vendor/autoload.php';
```

#### Step 2: Update Namespace

**Old:**
```php
namespace CLOSE\ConnectEcommerce;

$license = new License( $options );
```

**New:**
```php
use Closemarketing\WPLicenseManager\License;

$license = new License( $options );
```

#### Step 3: Update Configuration Options

**Old:**
```php
$options = array(
	'api_url' => 'https://yourstore.com/',
	'file'    => __FILE__,
	'version' => '1.0.0',
	'slug'    => 'my-plugin',
	'name'    => 'Plugin Name',
);

new \CLOSE\ConnectEcommerce\License( $options );
```

**New:**
```php
$options = array(
	'api_url'      => 'https://yourstore.com/',
	'file'         => __FILE__,
	'version'      => '1.0.0',
	'slug'         => 'my_plugin',        // Note: use underscore
	'name'         => 'Plugin Name',
	'text_domain'  => 'my-plugin',        // Add text domain
);

new \Closemarketing\WPLicenseManager\License( $options );
```

#### Step 4: Update Hook Names

If you were using custom hooks, update them:

**Old:**
```php
add_action( 'connect_ecommerce_settings_tabs', 'my_function' );
add_action( 'connect_ecommerce_settings_tabs_content', 'my_function' );
```

**New:**
```php
// Use your custom hook names or let the library use defaults
$options = array(
	// ... other options
	'settings_tabs'    => 'my_plugin_settings_tabs',
	'settings_content' => 'my_plugin_settings_content',
);
```

#### Step 5: Update Option Keys in Database

The option keys have changed. You may want to migrate existing data:

**Old option keys:**
- `{slug}_license_apikey`
- `{slug}_license_product_id`
- `{slug}_license_activated`
- `{slug}_license_instance`

**New option keys:**
(Same format, but ensure your slug uses underscores)

Migration script example:

```php
function my_plugin_migrate_license_options() {
	$old_slug = 'old-plugin-slug';
	$new_slug = 'new_plugin_slug';
	
	// Migrate API key.
	$old_apikey = get_option( $old_slug . '_license_apikey' );
	if ( $old_apikey ) {
		update_option( $new_slug . '_license_apikey', $old_apikey );
		delete_option( $old_slug . '_license_apikey' );
	}
	
	// Migrate product ID.
	$old_product_id = get_option( $old_slug . '_license_product_id' );
	if ( $old_product_id ) {
		update_option( $new_slug . '_license_product_id', $old_product_id );
		delete_option( $old_slug . '_license_product_id' );
	}
	
	// Migrate activation status.
	$old_activated = get_option( $old_slug . '_license_activated' );
	if ( $old_activated ) {
		update_option( $new_slug . '_license_activated', $old_activated );
		delete_option( $old_slug . '_license_activated' );
	}
	
	// Migrate instance.
	$old_instance = get_option( $old_slug . '_license_instance' );
	if ( $old_instance ) {
		update_option( $new_slug . '_license_instance', $old_instance );
		delete_option( $old_slug . '_license_instance' );
	}
}

// Run migration on plugin update.
register_activation_hook( __FILE__, 'my_plugin_migrate_license_options' );
```

#### Step 6: Update Text Domain in Translations

**Old:**
All strings used `'connect-woocommerce-holded'`

**New:**
All strings now use your custom text domain specified in options.

Update your translation files accordingly.

### Complete Example

**Before (Version 1.x):**

```php
<?php
namespace CLOSE\ConnectEcommerce;

require_once 'class-license.php';

$options = array(
	'api_url' => 'https://yourstore.com/',
	'file'    => __FILE__,
	'version' => '1.0.0',
	'slug'    => 'connect-ecommerce',
	'name'    => 'Connect Ecommerce',
);

new License( $options );
```

**After (Version 2.0):**

```php
<?php
require_once 'vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;

try {
	new License(
		array(
			'api_url'      => 'https://yourstore.com/',
			'file'         => __FILE__,
			'version'      => '1.0.0',
			'slug'         => 'connect_ecommerce',
			'name'         => 'Connect Ecommerce',
			'text_domain'  => 'connect-ecommerce',
		)
	);
} catch ( \Exception $e ) {
	// Handle initialization error.
	error_log( 'License Manager Error: ' . $e->getMessage() );
}
```

### Breaking Changes

1. **Namespace**: Changed from `CLOSE\ConnectEcommerce` to `Closemarketing\WPLicenseManager`
2. **File Location**: Class moved to `src/License.php`
3. **Text Domain**: No longer hardcoded, must be specified in options
4. **Error Handling**: Constructor now throws exceptions for missing required options
5. **Hook Names**: Custom hook names must be specified in options

### New Features in 2.0

1. **Fully Configurable**: All strings and hooks can be customized
2. **Better Error Handling**: Exceptions for initialization errors
3. **Public Method**: `is_license_active()` for easy status checking
4. **PSR-4 Autoloading**: Modern PHP standards
5. **Improved Documentation**: Comprehensive README and examples
6. **Translation Ready**: Customizable text domain

### Need Help?

If you encounter any issues during migration:

1. Check the [README.md](README.md) for complete documentation
2. Review the [examples](examples/) directory for working code
3. Open an issue on GitHub
4. Contact support@close.marketing

### Rollback

If you need to rollback to version 1.x:

1. Restore the old `class-license.php` file
2. Revert your code changes
3. Your database options will remain unchanged


