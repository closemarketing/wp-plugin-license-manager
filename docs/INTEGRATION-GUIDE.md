# Integration Guide

Quick reference for integrating the WordPress Plugin License Manager into your projects.

## Quick Start

### 1. Install via Composer

Add to your plugin's `composer.json`:

```json
{
    "require": {
        "closemarketing/wp-plugin-license-manager": "^2.0"
    }
}
```

Then run:
```bash
composer install
```

### 2. Basic Integration (5 minutes)

Add this to your main plugin file:

```php
<?php
/**
 * Plugin Name: Your Plugin Name
 * Version: 1.0.0
 */

// Load Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;

// Initialize license manager.
add_action( 'plugins_loaded', function() {
	new License(
		array(
			'api_url'     => 'https://yourstore.com/',  // Required.
			'file'        => __FILE__,                  // Required.
			'version'     => '1.0.0',                   // Required.
			'slug'        => 'your_plugin',             // Required.
			'name'        => 'Your Plugin Name',        // Required.
			'text_domain' => 'your-plugin',             // Recommended.
		)
	);
} );
```

### 3. Add Settings Page (10 minutes)

```php
// Add menu item.
add_action( 'admin_menu', function() {
	add_options_page(
		'Your Plugin',
		'Your Plugin',
		'manage_options',
		'your_plugin_settings',
		'your_plugin_settings_page'
	);
} );

// Render page.
function your_plugin_settings_page() {
	$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
	?>
	<div class="wrap">
		<h1>Your Plugin Settings</h1>
		<h2 class="nav-tab-wrapper">
			<a href="?page=your_plugin_settings&tab=general" class="nav-tab">General</a>
			<?php do_action( 'your_plugin_tabs', $tab ); ?>
		</h2>
		<?php do_action( 'your_plugin_content', $tab ); ?>
	</div>
	<?php
}

// Initialize with custom hooks.
new License(
	array(
		'api_url'          => 'https://yourstore.com/',
		'file'             => __FILE__,
		'version'          => '1.0.0',
		'slug'             => 'your_plugin',
		'name'             => 'Your Plugin',
		'text_domain'      => 'your-plugin',
		'settings_page'    => 'your_plugin_settings',
		'settings_tabs'    => 'your_plugin_tabs',
		'settings_content' => 'your_plugin_content',
	)
);
```

### 4. Check License Status

Use this anywhere in your code:

```php
// Create a helper function.
function your_plugin_is_licensed() {
	static $license = null;
	
	if ( null === $license ) {
		try {
			$license = new License(
				array(
					'api_url'     => 'https://yourstore.com/',
					'file'        => YOUR_PLUGIN_FILE,
					'version'     => YOUR_PLUGIN_VERSION,
					'slug'        => 'your_plugin',
					'name'        => 'Your Plugin',
					'text_domain' => 'your-plugin',
				)
			);
		} catch ( \Exception $e ) {
			return false;
		}
	}
	
	return $license->is_license_active();
}

// Use it.
if ( your_plugin_is_licensed() ) {
	// Enable premium features.
}
```

## Configuration Options Reference

| Option | Required | Default | Description |
|--------|----------|---------|-------------|
| `api_url` | Yes | - | WooCommerce store URL |
| `file` | Yes | - | Plugin main file (`__FILE__`) |
| `version` | Yes | - | Plugin version |
| `slug` | Yes | - | Plugin slug (use underscores) |
| `name` | Yes | - | Plugin display name |
| `text_domain` | No | 'default' | Translation text domain |
| `settings_page` | No | `{slug}_settings` | Settings page slug |
| `settings_tabs` | No | `{slug}_settings_tabs` | Tabs action hook |
| `settings_content` | No | `{slug}_settings_tabs_content` | Content action hook |

## Common Use Cases

### Standalone License Page

```php
add_action( 'admin_menu', function() {
	add_menu_page(
		'License',
		'License',
		'manage_options',
		'your_plugin_license',
		'render_license_page',
		'dashicons-admin-network',
		100
	);
} );

function render_license_page() {
	do_action( 'your_plugin_tabs', 'license' );
	do_action( 'your_plugin_content', 'license' );
}
```

### Conditional Premium Features

```php
class Your_Plugin {
	private $license;
	
	public function __construct() {
		$this->license = new License( [...] );
		
		if ( $this->license->is_license_active() ) {
			$this->init_premium_features();
		} else {
			$this->show_upgrade_notices();
		}
	}
}
```

### Multiple Products

```php
// Product 1.
new License(
	array(
		'slug' => 'your_plugin_pro',
		'name' => 'Your Plugin Pro',
		// ... other options
	)
);

// Product 2.
new License(
	array(
		'slug' => 'your_plugin_agency',
		'name' => 'Your Plugin Agency',
		// ... other options
	)
);
```

## Testing

### Test License Activation

1. Go to Settings → Your Plugin → License tab
2. Enter your API Key and Product ID
3. Click Save
4. Status should show "Activated"

### Test License Deactivation

1. Check "Deactivate License"
2. Click Save
3. Status should show "Deactivated"

### Test Updates

1. Activate license
2. Modify plugin version to lower number
3. Go to Plugins → check for updates
4. Your plugin should show an update available

## Troubleshooting

### License Not Activating

1. Check API URL is correct
2. Verify API Key and Product ID
3. Check server can make external requests
4. Look for errors in WordPress debug log

### Updates Not Working

1. Verify license is activated
2. Check plugin slug matches
3. Ensure WooCommerce API Manager is configured correctly
4. Test API connection manually

### Options Not Saving

1. Check user has `manage_options` capability
2. Verify nonce is being generated
3. Check for JavaScript errors
4. Disable other plugins to test conflicts

## Support

- **Documentation**: See [README.md](README.md)
- **Examples**: Check [examples/](examples/) directory
- **Migration**: Read [MIGRATION.md](MIGRATION.md)
- **Issues**: https://github.com/closemarketing/wp-plugin-license-manager/issues
- **Email**: support@close.marketing

## Next Steps

1. ✅ Install the library
2. ✅ Add basic integration
3. ✅ Test activation/deactivation
4. ✅ Implement premium features check
5. ✅ Add to your settings page
6. ✅ Test plugin updates
7. ✅ Translate if needed
8. 🚀 Deploy!


