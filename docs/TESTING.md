# Testing Guide

This document provides detailed information about testing the WordPress Plugin License Manager library.

## Overview

The library includes comprehensive unit tests using PHPUnit and the WordPress testing framework. Tests are designed to work across multiple PHP versions (7.4 to 8.3).

## Test Structure

```
tests/
├── bootstrap.php              # PHPUnit bootstrap file
├── phpstan-bootstrap.php      # PHPStan mock functions
└── Unit/
    ├── LicenseTest.php       # Tests for License class (9 tests)
    └── SettingsTest.php      # Tests for Settings class (5 tests)
```

## Setup

### 1. Install Dependencies

```bash
composer install
```

This installs:
- PHPUnit 9.6
- WordPress test framework (wp-phpunit)
- PHPUnit polyfills (for compatibility)
- All other required dependencies

### 2. Setup WordPress Test Environment

```bash
composer test-install
```

This script:
- Downloads WordPress core
- Sets up WordPress test library
- Configures test database (requires MySQL running locally)

**Database Configuration:**
- Database: `wordpress_test`
- User: `root`
- Password: `root`
- Host: `127.0.0.1`

To customize, edit the command in `composer.json`:
```json
"test-install": "bash bin/install-wp-tests.sh DB_NAME DB_USER DB_PASS DB_HOST WP_VERSION"
```

## Running Tests

### All Tests

```bash
composer test
```

Expected output:
```
PHPUnit 9.6.31 by Sebastian Bergmann and contributors.

..............                                                    14 / 14 (100%)

Time: 00:00.026, Memory: 42.50 MB

OK (14 tests, 16 assertions)
```

### Specific Test File

```bash
./vendor/bin/phpunit tests/Unit/LicenseTest.php
```

### Specific Test Method

```bash
./vendor/bin/phpunit --filter test_license_instantiation
```

### With Debug (Xdebug)

```bash
composer test-debug
```

### With Coverage Report

```bash
./vendor/bin/phpunit --coverage-html coverage
```

Then open `coverage/index.html` in your browser.

## Test Coverage

### LicenseTest.php (9 tests)

1. **test_license_instantiation** - Verifies License class can be instantiated with valid options
2. **test_missing_required_options_throws_exception** - Ensures missing required options throw exceptions
3. **test_get_option_key** - Tests option key generation with plugin slug prefix
4. **test_is_license_active_returns_false_when_not_activated** - Validates inactive license state
5. **test_is_license_active_returns_true_when_activated** - Validates active license state
6. **test_get_plugin_name** - Tests plugin name retrieval
7. **test_get_text_domain** - Tests text domain retrieval
8. **test_get_option_value** - Tests option value retrieval from WordPress options
9. **tearDown** - Cleanup after each test

### SettingsTest.php (5 tests)

1. **test_settings_instantiation** - Verifies Settings class instantiation
2. **test_settings_with_default_options** - Tests default options handling
3. **test_admin_init_hooks_registered** - Validates WordPress hooks registration
4. **test_render_method_exists** - Ensures render method is available
5. **test_settings_with_custom_benefits** - Tests custom configuration options

## Continuous Integration

Tests run automatically via GitHub Actions on:
- Pull requests to `main` or `develop` branches
- Push to `main` or `develop` branches

### PHP Version Matrix

The CI workflow tests against:
- PHP 8.3
- PHP 8.2
- PHP 8.1
- PHP 7.4

### Why No `composer.lock`?

This is a **library**, not an application. The `composer.lock` file is intentionally not versioned because:

1. **Compatibility**: Each PHP version needs to resolve dependencies appropriate for that version
2. **Flexibility**: Consumers of the library should control their dependency versions
3. **Best Practice**: Libraries should define dependency ranges, not locked versions

When CI runs with PHP 7.4, it will install compatible versions of all dependencies (e.g., `doctrine/instantiator 1.x` instead of `2.x`).

## Code Quality Tools

### PHP CodeSniffer (Linting)

```bash
# Check coding standards
composer lint

# Auto-fix issues
composer format
```

Configuration: `phpcs.xml.dist`
- Standards: WordPress, PHPCompatibilityWP
- Target: PHP 7.4+

### PHPStan (Static Analysis)

```bash
composer phpstan
```

Configuration: `phpstan.neon.dist`
- Level: 5
- Paths: `src/`
- WordPress Stubs: `php-stubs/wordpress-stubs` (provides all WordPress functions and classes)
- PHPStan WordPress Extension: `szepeviktor/phpstan-wordpress` (WordPress-specific rules)

## Troubleshooting

### "Could not find functions.php" Error

The WordPress test suite isn't installed. Run:
```bash
composer test-install
```

### Database Connection Error

Ensure MySQL is running and credentials are correct:
```bash
mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS wordpress_test;"
```

### PHP Version Compatibility Issues

If you get dependency errors, ensure you're using a compatible PHP version:
```bash
php -v
```

The library requires PHP 7.4 or higher.

### Composer Lock File Issues

If you accidentally commit `composer.lock`, remove it:
```bash
git rm composer.lock
git commit -m "Remove composer.lock (library, not application)"
```

## Writing New Tests

### Test File Template

```php
<?php
/**
 * Class MyClassTest
 *
 * @package Closemarketing\WPLicenseManager
 */

use Closemarketing\WPLicenseManager\MyClass;

/**
 * MyClass test case.
 */
class MyClassTest extends WP_UnitTestCase {

    /**
     * Test description
     */
    public function test_something() {
        // Arrange
        $instance = new MyClass();
        
        // Act
        $result = $instance->doSomething();
        
        // Assert
        $this->assertTrue( $result );
    }

    /**
     * Cleanup after each test
     */
    public function tearDown(): void {
        parent::tearDown();
        // Clean up options, transients, etc.
    }
}
```

### Best Practices

1. **One assertion per test** (when possible)
2. **Use descriptive test names** (test_method_scenario_expectedResult)
3. **Arrange-Act-Assert pattern**
4. **Clean up after tests** (tearDown method)
5. **Test edge cases and error conditions**
6. **Mock external dependencies**

## GitHub Actions Workflow

Location: `.github/workflows/phpunit.yml`

The workflow:
1. Checks out code
2. Sets up PHP (matrix: 7.4, 8.1, 8.2, 8.3)
3. Installs Composer dependencies (`composer update` for flexibility)
4. Installs WordPress test suite
5. Runs PHPUnit tests

## Resources

- [WordPress PHPUnit Documentation](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)

## Support

For testing issues:
1. Check this documentation
2. Review test output carefully
3. Check GitHub Actions logs
4. Open an issue with full error details
