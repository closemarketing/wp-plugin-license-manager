# Quick Start Guide

## 🚀 The Easiest Way to Add License Management

### Step 1: Install (1 minute)

Add to your plugin's `composer.json`:

```json
{
    "require": {
        "closemarketing/wp-plugin-license-manager": "^2.0"
    }
}
```

Run: `composer install`

### Step 2: Add These 10 Lines to Your Plugin (2 minutes)

```php
<?php
// In your main plugin file

require_once __DIR__ . '/vendor/autoload.php';

use Closemarketing\WPLicenseManager\License;
use Closemarketing\WPLicenseManager\Settings;

add_action( 'plugins_loaded', function() {
    // Initialize License.
    $license = new License(
        array(
            'api_url'     => 'https://close.technology/',  // Your WooCommerce store.
            'file'        => __FILE__,
            'version'     => '1.0.0',
            'slug'        => 'my_plugin',
            'name'        => 'My Plugin',
            'text_domain' => 'my-plugin',
        )
    );
    
    // Create automatic settings page - NO FIELDS NEEDED!
    new Settings( $license );
} );
```

### That's It! ✨

You now have:
- ✅ Complete license settings page with all fields
- ✅ Automatic plugin updates
- ✅ License activation/deactivation
- ✅ Beautiful, responsive UI
- ✅ Translation-ready
- ✅ Zero HTML/CSS coding required!

## 📍 Where to Find Your Settings

Go to: **Settings → License** (in WordPress admin)

## 🎨 Customization Options

### Change Menu Location

**Put it under a different menu:**
```php
new Settings(
    $license,
    array(
        'parent_slug' => 'tools.php', // Under Tools menu.
    )
);
```

**Create a top-level menu:**
```php
new Settings(
    $license,
    array(
        'standalone_page' => true,
        'icon_url'        => 'dashicons-admin-network',
        'position'        => 30,
    )
);
```

### Customize Titles

```php
new Settings(
    $license,
    array(
        'page_title' => 'My Plugin License',
        'menu_title' => 'License',
    )
);
```

## 🔧 Common Scenarios

### Scenario 1: Standalone License Page

**Perfect for: Simple plugins with just license settings**

```php
$license = new License( [...] );
new Settings( $license, array( 'standalone_page' => true ) );
```

Result: Clean, dedicated license page.

### Scenario 2: Integrate into Existing Settings

**Perfect for: Plugins that already have settings pages**

See `examples/custom-integration.php` for complete example.

### Scenario 3: Multiple Licenses

**Perfect for: Plugins with different editions (Pro, Agency, etc.)**

```php
$license_pro = new License( array( 'slug' => 'my_plugin_pro', ... ) );
new Settings( $license_pro, array( 'menu_title' => 'Pro License' ) );

$license_agency = new License( array( 'slug' => 'my_plugin_agency', ... ) );
new Settings( $license_agency, array( 'menu_title' => 'Agency License' ) );
```

## ✅ Check License Status Anywhere

```php
if ( $license->is_license_active() ) {
    // Enable premium features.
}
```

## 🎯 Complete Examples

Check the `examples/` directory:

1. **standalone-settings.php** - Simplest setup (recommended for most)
2. **top-level-menu.php** - Creates main menu item
3. **custom-integration.php** - Advanced: integrate with existing settings
4. **basic-implementation.php** - Minimal setup
5. **advanced-implementation.php** - Full-featured

## 📚 Settings Class Options

| Option | Default | Description |
|--------|---------|-------------|
| `page_title` | 'License Settings' | Page title |
| `menu_title` | 'License' | Menu item text |
| `menu_slug` | 'license-settings' | URL slug |
| `parent_slug` | 'options-general.php' | Parent menu (Settings) |
| `standalone_page` | `false` | Create top-level menu |
| `icon_url` | 'dashicons-admin-network' | Menu icon |
| `position` | `null` | Menu position |
| `custom_css` | `true` | Include styling |

## 🆘 Troubleshooting

**Settings page not showing?**
- Check user has 'manage_options' capability
- Verify License and Settings classes are initialized
- Check for PHP errors in debug log

**Fields not saving?**
- Ensure you're passing the License instance to Settings
- Check nonces are being generated
- Verify WordPress is calling 'admin_init' action

**Styling looks broken?**
- Set `custom_css` to `true` in Settings options
- Check for CSS conflicts with other plugins
- Try different menu location

## 🎓 Next Steps

1. ✅ Install the library
2. ✅ Copy the 10-line example above
3. ✅ Customize menu location (optional)
4. ✅ Test activation/deactivation
5. 🚀 Ship it!

## 💡 Pro Tips

1. **Store license instance globally:**
   ```php
   global $my_plugin_license;
   $my_plugin_license = new License( [...] );
   ```

2. **Create helper function:**
   ```php
   function my_plugin_is_licensed() {
       global $my_plugin_license;
       return $my_plugin_license && $my_plugin_license->is_license_active();
   }
   ```

3. **Show upgrade notice:**
   ```php
   if ( ! my_plugin_is_licensed() ) {
       add_action( 'admin_notices', function() {
           echo '<div class="notice notice-warning">';
           echo '<p>Activate your license for updates!</p>';
           echo '</div>';
       } );
   }
   ```

## 📖 Full Documentation

For complete documentation, see:
- [README.md](README.md) - Full API reference
- [INTEGRATION-GUIDE.md](INTEGRATION-GUIDE.md) - Integration details
- [MIGRATION.md](MIGRATION.md) - Migrating from v1.x

## 🤝 Support

- Email: support@close.marketing
- Issues: GitHub Issues
- Docs: https://close.marketing/docs/



