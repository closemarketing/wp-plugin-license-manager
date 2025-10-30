<?php
/**
 * Migration Script Example
 *
 * Use this script to migrate from the old license class to the new library.
 * Run this once during plugin update.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Migrate license options from old format to new format
 *
 * @param string $old_slug Old plugin slug.
 * @param string $new_slug New plugin slug.
 * @return bool True on success, false on failure.
 */
function migrate_license_options( $old_slug, $new_slug ) {
	// Skip if already migrated.
	if ( get_option( $new_slug . '_license_migrated' ) ) {
		return true;
	}

	$migrated = false;

	// Migrate API key.
	$old_apikey = get_option( $old_slug . '_license_apikey' );
	if ( $old_apikey && ! get_option( $new_slug . '_license_apikey' ) ) {
		update_option( $new_slug . '_license_apikey', $old_apikey );
		$migrated = true;
	}

	// Migrate product ID.
	$old_product_id = get_option( $old_slug . '_license_product_id' );
	if ( $old_product_id && ! get_option( $new_slug . '_license_product_id' ) ) {
		update_option( $new_slug . '_license_product_id', $old_product_id );
		$migrated = true;
	}

	// Migrate activation status.
	$old_activated = get_option( $old_slug . '_license_activated' );
	if ( $old_activated && ! get_option( $new_slug . '_license_activated' ) ) {
		update_option( $new_slug . '_license_activated', $old_activated );
		$migrated = true;
	}

	// Migrate instance.
	$old_instance = get_option( $old_slug . '_license_instance' );
	if ( $old_instance && ! get_option( $new_slug . '_license_instance' ) ) {
		update_option( $new_slug . '_license_instance', $old_instance );
		$migrated = true;
	}

	// Migrate deactivate checkbox.
	$old_checkbox = get_option( $old_slug . '_license_deactivate_checkbox' );
	if ( $old_checkbox && ! get_option( $new_slug . '_license_deactivate_checkbox' ) ) {
		update_option( $new_slug . '_license_deactivate_checkbox', $old_checkbox );
		$migrated = true;
	}

	if ( $migrated ) {
		// Mark as migrated.
		update_option( $new_slug . '_license_migrated', true );

		// Optional: Delete old options (uncomment if you want to clean up).
		// delete_option( $old_slug . '_license_apikey' );
		// delete_option( $old_slug . '_license_product_id' );
		// delete_option( $old_slug . '_license_activated' );
		// delete_option( $old_slug . '_license_instance' );
		// delete_option( $old_slug . '_license_deactivate_checkbox' );

		return true;
	}

	return false;
}

/**
 * Example plugin activation hook
 */
function my_plugin_activation() {
	$migrated = migrate_license_options(
		'old_plugin_slug',  // Old slug.
		'new_plugin_slug'   // New slug.
	);

	if ( $migrated ) {
		// Show admin notice.
		set_transient( 'my_plugin_license_migrated_notice', true, 30 );
	}
}
register_activation_hook( __FILE__, 'my_plugin_activation' );

/**
 * Show migration notice
 */
function my_plugin_migration_notice() {
	if ( get_transient( 'my_plugin_license_migrated_notice' ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<strong><?php esc_html_e( 'License Migrated:', 'my-plugin' ); ?></strong>
				<?php esc_html_e( 'Your license has been successfully migrated to the new format.', 'my-plugin' ); ?>
			</p>
		</div>
		<?php
		delete_transient( 'my_plugin_license_migrated_notice' );
	}
}
add_action( 'admin_notices', 'my_plugin_migration_notice' );

/**
 * Alternative: Migrate on plugin update
 *
 * This function checks the plugin version and migrates if needed.
 */
function my_plugin_check_version_and_migrate() {
	$current_version = get_option( 'my_plugin_version' );
	$new_version     = '2.0.0'; // Your new version.

	// If version is less than 2.0.0, migrate.
	if ( version_compare( $current_version, '2.0.0', '<' ) ) {
		$migrated = migrate_license_options(
			'old_plugin_slug',
			'new_plugin_slug'
		);

		if ( $migrated ) {
			// Update version.
			update_option( 'my_plugin_version', $new_version );

			// Show notice.
			set_transient( 'my_plugin_license_migrated_notice', true, 30 );
		}
	}
}
add_action( 'admin_init', 'my_plugin_check_version_and_migrate' );

/**
 * Advanced migration with data validation
 *
 * This function includes validation and error handling.
 *
 * @param string $old_slug Old plugin slug.
 * @param string $new_slug New plugin slug.
 * @return array Migration results.
 */
function advanced_migrate_license_options( $old_slug, $new_slug ) {
	$results = array(
		'success' => false,
		'migrated' => array(),
		'errors'   => array(),
	);

	// Check if already migrated.
	if ( get_option( $new_slug . '_license_migrated' ) ) {
		$results['errors'][] = 'Already migrated';
		return $results;
	}

	// Define options to migrate.
	$options_to_migrate = array(
		'apikey',
		'product_id',
		'activated',
		'instance',
		'deactivate_checkbox',
	);

	foreach ( $options_to_migrate as $option ) {
		$old_key = $old_slug . '_license_' . $option;
		$new_key = $new_slug . '_license_' . $option;

		$old_value = get_option( $old_key );

		if ( false !== $old_value && '' !== $old_value ) {
			// Validate before migrating.
			$valid = true;

			if ( 'product_id' === $option && ! is_numeric( $old_value ) ) {
				$valid = false;
				$results['errors'][] = "Invalid product_id: {$old_value}";
			}

			if ( $valid ) {
				$updated = update_option( $new_key, $old_value );

				if ( $updated ) {
					$results['migrated'][] = $option;
				} else {
					$results['errors'][] = "Failed to migrate {$option}";
				}
			}
		}
	}

	if ( ! empty( $results['migrated'] ) ) {
		update_option( $new_slug . '_license_migrated', true );
		update_option( $new_slug . '_license_migration_date', current_time( 'mysql' ) );
		$results['success'] = true;
	}

	return $results;
}

/**
 * Admin page to show migration status
 */
function my_plugin_migration_status_page() {
	$old_slug = 'old_plugin_slug';
	$new_slug = 'new_plugin_slug';

	$migrated = get_option( $new_slug . '_license_migrated' );
	$migration_date = get_option( $new_slug . '_license_migration_date' );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'License Migration Status', 'my-plugin' ); ?></h1>

		<?php if ( $migrated ) : ?>
			<div class="notice notice-success">
				<p>
					<strong><?php esc_html_e( 'Migration Completed', 'my-plugin' ); ?></strong><br>
					<?php
					printf(
						// translators: %s: Migration date.
						esc_html__( 'License data was migrated on: %s', 'my-plugin' ),
						esc_html( $migration_date )
					);
					?>
				</p>
			</div>
		<?php else : ?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Migration Pending', 'my-plugin' ); ?></strong><br>
					<?php esc_html_e( 'License data has not been migrated yet.', 'my-plugin' ); ?>
				</p>
			</div>

			<form method="post">
				<?php wp_nonce_field( 'migrate_license', 'migrate_license_nonce' ); ?>
				<input type="submit" name="migrate_now" class="button button-primary" value="<?php esc_attr_e( 'Migrate Now', 'my-plugin' ); ?>">
			</form>

			<?php
			if ( isset( $_POST['migrate_now'] ) && check_admin_referer( 'migrate_license', 'migrate_license_nonce' ) ) {
				$results = advanced_migrate_license_options( $old_slug, $new_slug );

				if ( $results['success'] ) {
					echo '<div class="notice notice-success"><p>' . esc_html__( 'Migration completed successfully!', 'my-plugin' ) . '</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'Migration failed. Check the errors below.', 'my-plugin' ) . '</p>';
					echo '<ul>';
					foreach ( $results['errors'] as $error ) {
						echo '<li>' . esc_html( $error ) . '</li>';
					}
					echo '</ul></div>';
				}
			}
			?>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Migration Details', 'my-plugin' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Option', 'my-plugin' ); ?></th>
					<th><?php esc_html_e( 'Old Value', 'my-plugin' ); ?></th>
					<th><?php esc_html_e( 'New Value', 'my-plugin' ); ?></th>
					<th><?php esc_html_e( 'Status', 'my-plugin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$options_to_check = array( 'apikey', 'product_id', 'activated', 'instance' );
				foreach ( $options_to_check as $option ) :
					$old_value = get_option( $old_slug . '_license_' . $option );
					$new_value = get_option( $new_slug . '_license_' . $option );
					$status    = $new_value ? '✓' : '—';
					?>
					<tr>
						<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $option ) ) ); ?></td>
						<td><?php echo $old_value ? '****' : '—'; ?></td>
						<td><?php echo $new_value ? '****' : '—'; ?></td>
						<td><?php echo esc_html( $status ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

