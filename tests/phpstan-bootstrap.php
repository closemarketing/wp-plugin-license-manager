<?php
/**
 * PHPStan Bootstrap File
 *
 * Minimal bootstrap file for PHPStan analysis.
 * WordPress functions are provided by php-stubs/wordpress-stubs package.
 *
 * @package Closemarketing\WPLicenseManager
 */

// The WordPress stubs loaded in phpstan.neon.dist provide all WordPress
// functions and classes, so we don't need to mock them here.

// Only define plugin-specific constants that are not part of WordPress core.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/path/to/wordpress/' );
}
