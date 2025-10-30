# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-10-27

### Added
- Complete refactor as Composer library
- PSR-4 autoloading with namespace `Closemarketing\WPLicenseManager`
- Configurable options for easy integration
- Comprehensive README with usage examples
- Basic and advanced implementation examples
- Support for custom settings pages and tabs
- Public method `is_license_active()` for easy license checking
- Better error handling with exceptions
- Translation-ready with configurable text domain
- Admin notices for external blocking detection
- Automatic plugin updates integration

### Changed
- Moved class to `src/License.php` with proper namespace
- Improved code structure and organization
- Better separation of concerns
- All hardcoded values now configurable
- Improved documentation and inline comments
- WordPress Coding Standards compliance
- Updated composer.json with proper metadata

### Improved
- Security enhancements
- Input sanitization and validation
- Output escaping
- Yoda conditions throughout
- Better error messages

### Fixed
- Various code quality issues
- Consistent option naming
- Proper nonce verification

## [1.0.0] - 2019-01-01

### Added
- Initial release
- Basic license activation/deactivation
- WooCommerce API Manager integration
- Settings page integration

