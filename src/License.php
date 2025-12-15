<?php
/**
 * License Manager Library
 *
 * A reusable library for managing WordPress plugin licenses with Enwikuna License Manager.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2019 Closemarketing
 * @version    3.0.0
 */

namespace Closemarketing\WPLicenseManager;

defined( 'ABSPATH' ) || exit;

/**
 * License Manager Class
 *
 * Handles plugin license activation, deactivation, and updates.
 *
 * @since 1.0.0
 */
class License {
	/**
	 * Configuration options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Default configuration options.
	 *
	 * @var array
	 */
	private $default_options = array(
		'api_url'           => '',           // API URL (required).
		'rest_api_key'      => '',           // REST API Consumer Key (required).
		'rest_api_secret'   => '',           // REST API Consumer Secret (required).
		'product_uuid'      => '',           // Product UUID (required).
		'file'              => '',           // Plugin main file (required).
		'version'           => '',           // Plugin version (required).
		'slug'              => '',           // Plugin slug (required).
		'name'              => '',           // Plugin name (required).
		'text_domain'       => 'default',    // Text domain for translations.
		'plugin_slug'       => '',           // Plugin slug for updates.
		'plugin_name'       => '',           // Plugin name for updates.
		'capabilities'      => 'manage_options', // Required capability.
	);

	/**
	 * Constructor
	 *
	 * @param array $options Configuration options.
	 */
	public function __construct( $options = array() ) {
		$this->options = wp_parse_args( $options, $this->default_options );

		// Validate required options.
		$this->validate_required_options();

		// Initialize hooks.
		$this->init_hooks();
	}

	/**
	 * Validate required options
	 *
	 * @throws \Exception If required options are missing.
	 * @return void
	 */
	private function validate_required_options() {
		$required = array( 'api_url', 'rest_api_key', 'rest_api_secret', 'product_uuid', 'file', 'version', 'slug', 'name' );

		foreach ( $required as $key ) {
			if ( empty( $this->options[ $key ] ) ) {
				// translators: %s: Option key name.
				throw new \Exception( sprintf( 'License Manager: Required option "%s" is missing.', $key ) );
			}
		}

		// Ensure API URL ends with slash.
		$this->options['api_url'] = trailingslashit( $this->options['api_url'] ) . 'wp-json/elm/v1/';

		// Set defaults for optional fields based on slug.
		if ( empty( $this->options['plugin_slug'] ) ) {
			$this->options['plugin_slug'] = $this->options['slug'];
		}

		if ( empty( $this->options['plugin_name'] ) ) {
			$this->options['plugin_name'] = plugin_basename( $this->options['file'] );
		}

		if ( empty( $this->options['option_group'] ) ) {
			$this->options['option_group'] = $this->options['slug'] . '_license';
		}

		if ( empty( $this->options['settings_section'] ) ) {
			$this->options['settings_section'] = $this->options['slug'] . '_settings_admin_license';
		}
	}

	/**
	 * Initialize hooks
	 *
	 * @return void
	 */
	private function init_hooks() {
		// Check for external blocking.
		add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );

		// Update checks.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
		add_filter( 'plugins_api', array( $this, 'information_request' ), 10, 3 );
	}

	/**
	 * Make API request to Enwikuna License Manager.
	 *
	 * @param string $endpoint    API endpoint (e.g., 'licenses/activate').
	 * @param string $license_key License key to append to URL.
	 * @param array  $body        Request body.
	 * @param string $method      HTTP method (GET, POST).
	 * @return object|WP_Error
	 */
	private function api_request( $endpoint, $license_key = '', $body = array(), $method = 'POST' ) {
		// Build URL: {API_URL}{endpoint}/{license_key}.
		$url = $this->options['api_url'] . $endpoint;
		if ( ! empty( $license_key ) ) {
			$url .= '/' . $license_key;
		}

		$args = array(
			'method'      => $method,
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'sslverify'   => true,
			'headers'     => array(
				'Authorization' => 'Basic ' . base64_encode( $this->options['rest_api_key'] . ':' . $this->options['rest_api_secret'] ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			),
		);

		if ( ! empty( $body ) ) {
			$args['body'] = $body;
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code   = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body );

		if ( in_array( $status_code, array( 400, 401, 403, 404, 405, 500 ), true ) ) {
			$message = isset( $data->message ) ? $data->message : __( 'Unknown error occurred.', $this->options['text_domain'] );
			return new \WP_Error( 'api_error', $message, array( 'status' => $status_code ) );
		}

		return $data;
	}

	/**
	 * Validates license option
	 *
	 * @param array $input Settings input option.
	 * @return array
	 */
	public function validate_license( $input ) {
		$apikey_key        = $this->get_option_key( 'apikey' );
		$deactivate_key    = $this->get_option_key( 'deactivate_checkbox' );
		$api_key           = isset( $input[ $apikey_key ] ) ? trim( $input[ $apikey_key ] ) : '';
		$api_key           = sanitize_text_field( $api_key );
		$activation_status = get_option( $this->get_option_key( 'activated' ) );
		$checkbox_status   = get_option( $deactivate_key );
		$current_api_key   = get_option( $apikey_key, '' );

		// Deactivate License - Check this FIRST before any activation logic.
		if ( isset( $input[ $deactivate_key ] ) && 'on' === $input[ $deactivate_key ] ) {
			// Always use current_api_key if api_key is empty (e.g., when field is readonly).
			$key_to_deactivate = ! empty( $api_key ) ? $api_key : $current_api_key;

			// If still empty, try to get it directly from options as fallback.
			if ( empty( $key_to_deactivate ) ) {
				$key_to_deactivate = $this->get_option_value( 'apikey' );
			}

			if ( ! empty( $key_to_deactivate ) ) {
				$deactivation_result = $this->license_deactivate( $key_to_deactivate );

				if ( ! is_wp_error( $deactivation_result ) ) {
					update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
					update_option( $apikey_key, '' );
					add_settings_error( 'license_deactivate', 'deactivate_msg', esc_html__( 'License deactivated successfully.', $this->options['text_domain'] ), 'updated' );
					return array();
				}

				// Deactivate locally anyway.
				update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
				update_option( $apikey_key, '' );
				add_settings_error( 'license_deactivate', 'deactivate_msg', esc_html__( 'License deactivated locally.', $this->options['text_domain'] ), 'updated' );
				return array();
			}

			// If no key found, still deactivate locally.
			update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
			update_option( $apikey_key, '' );
			add_settings_error( 'license_deactivate', 'deactivate_msg', esc_html__( 'License deactivated locally (no key found to deactivate remotely).', $this->options['text_domain'] ), 'updated' );
			return array();
		}

		// Save license key first if provided.
		if ( ! empty( $api_key ) && $current_api_key !== $api_key ) {
			update_option( $apikey_key, $api_key );
		}

		// Activate License if key changed or status is deactivated.
		// IMPORTANT: Don't activate if deactivate checkbox is present in input (already handled above).
		$is_deactivating = isset( $input[ $deactivate_key ] ) && 'on' === $input[ $deactivate_key ];
		if ( ! $is_deactivating && ! empty( $api_key ) && ( 'Deactivated' === $activation_status || empty( $activation_status ) || $current_api_key !== $api_key ) ) {
			// Deactivate existing key if different.
			if ( ! empty( $current_api_key ) && $current_api_key !== $api_key ) {
				$this->license_deactivate( $current_api_key );
			}

			$activation_result = $this->license_activate( $api_key );

			if ( ! is_wp_error( $activation_result ) && ! empty( $activation_result->data ) ) {
				$status = isset( $activation_result->data->status ) ? $activation_result->data->status : '';

				if ( 'active' === $status ) {
					$message = __( 'License activated successfully.', $this->options['text_domain'] );
					add_settings_error( 'activate_text', 'activate_msg', $message, 'updated' );

					// Update license key and status.
					update_option( $apikey_key, $api_key );
					update_option( $this->get_option_key( 'activated' ), 'Activated' );
					update_option( $this->get_option_key( 'deactivate_checkbox' ), 'off' );

					return array(
						'status'  => 'ok',
						'message' => $message,
					);
				} elseif ( 'expired' === $status ) {
					$message = __( 'License has expired.', $this->options['text_domain'] );
					add_settings_error( 'license_expired', 'expired_msg', $message, 'error' );
					update_option( $apikey_key, $api_key );
					update_option( $this->get_option_key( 'activated' ), 'Expired' );
					return array();
				}
			}

			if ( is_wp_error( $activation_result ) ) {
				add_settings_error( 'license_error', 'license_client_error', $activation_result->get_error_message(), 'error' );
				update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
			} else {
				$message = esc_html__( 'The license key activation could not be completed.', $this->options['text_domain'] );
				add_settings_error( 'not_activated', 'not_activated_error', $message );
				update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
			}
		} elseif ( ! empty( $api_key ) && $current_api_key === $api_key && 'Activated' === $activation_status ) {
			// Key is the same and already activated, just verify status.
			$response = $this->api_request( 'licenses', $api_key, array(), 'GET' );
			if ( ! is_wp_error( $response ) && ! empty( $response->data ) ) {
				$api_status = isset( $response->data->status ) ? $response->data->status : '';
				if ( 'active' === $api_status ) {
					update_option( $this->get_option_key( 'activated' ), 'Activated' );
				} elseif ( 'expired' === $api_status ) {
					update_option( $this->get_option_key( 'activated' ), 'Expired' );
				}
			}
		}

		return array();
	}

	/**
	 * Sends the request to activate to the Enwikuna License Manager.
	 *
	 * @param string $api_key License Key to activate.
	 * @return object|WP_Error
	 */
	public function license_activate( $api_key ) {
		if ( empty( $api_key ) ) {
			return new \WP_Error( 'missing_key', __( 'The License Key is missing from the activation request.', $this->options['text_domain'] ) );
		}

		return $this->api_request(
			'licenses/activate',
			$api_key,
			array(
				'host'         => home_url(),
				'product_uuid' => $this->options['product_uuid'],
			),
			'POST'
		);
	}

	/**
	 * Sends the request to deactivate to the Enwikuna License Manager.
	 *
	 * @param string $license_key License key to deactivate.
	 * @return object|WP_Error
	 */
	public function license_deactivate( $license_key ) {
		if ( empty( $license_key ) ) {
			return new \WP_Error( 'missing_key', __( 'The License Key is missing from the deactivation request.', $this->options['text_domain'] ) );
		}

		return $this->api_request(
			'licenses/deactivate',
			$license_key,
			array(
				'host' => home_url(),
			),
			'POST'
		);
	}

	/**
	 * Returns true if the API Key status is Activated.
	 *
	 * @param bool $live Do not set to true if using to activate software.
	 * @return bool
	 */
	public function get_api_key_status( $live = false ) {
		if ( $live ) {
			$license_key = get_option( $this->get_option_key( 'apikey' ) );
			if ( empty( $license_key ) ) {
				return false;
			}

			$response = $this->api_request( 'licenses', $license_key, array(), 'GET' );

			if ( ! is_wp_error( $response ) && ! empty( $response->data ) ) {
				return 'active' === $response->data->status;
			}

			return false;
		}

		return 'Activated' === get_option( $this->get_option_key( 'activated' ) );
	}

	/**
	 * Check for updates against the remote server.
	 *
	 * @param object $transient Transient plugins.
	 * @return object
	 */
	public function update_check( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$license_key = get_option( $this->get_option_key( 'apikey' ) );

		if ( empty( $license_key ) || ! $this->get_api_key_status() ) {
			return $transient;
		}

		$response = $this->api_request( 'products/update', $license_key, array(), 'GET' );

		if ( is_wp_error( $response ) || empty( $response->data ) ) {
			return $transient;
		}

		$new_version  = isset( $response->data->version ) ? (string) $response->data->version : '';
		$curr_version = (string) $this->options['version'];

		if ( ! empty( $new_version ) && ! empty( $curr_version ) && version_compare( $new_version, $curr_version, '>' ) ) {
			$package = array(
				'id'             => isset( $response->data->id ) ? $response->data->id : '',
				'slug'           => isset( $response->data->slug ) ? $response->data->slug : $this->options['plugin_slug'],
				'plugin'         => $this->options['plugin_name'],
				'new_version'    => $new_version,
				'url'            => isset( $response->data->url ) ? $response->data->url : '',
				'tested'         => isset( $response->data->tested ) ? $response->data->tested : '',
				'package'        => isset( $response->data->download_url ) ? $response->data->download_url : '',
				'upgrade_notice' => isset( $response->data->upgrade_notice ) ? $response->data->upgrade_notice : '',
			);

			$transient->response[ $this->options['plugin_name'] ] = (object) $package;
			unset( $transient->no_update[ $this->options['plugin_name'] ] );
		}

		return $transient;
	}

	/**
	 * API request for information.
	 *
	 * @param false|object|array $result The result object or array.
	 * @param string             $action The type of information being requested.
	 * @param object             $args   Arguments of object.
	 * @return object
	 */
	public function information_request( $result, $action, $args ) {
		if ( ! isset( $args->slug ) || $this->options['plugin_slug'] !== $args->slug ) {
			return $result;
		}

		$license_key = get_option( $this->get_option_key( 'apikey' ) );

		if ( empty( $license_key ) ) {
			return $result;
		}

		$response = $this->api_request( 'products/update', $license_key, array(), 'GET' );

		if ( is_wp_error( $response ) || empty( $response->data ) ) {
			return $result;
		}

		$data = $response->data;

		return (object) array(
			'name'          => isset( $data->name ) ? $data->name : $this->options['name'],
			'slug'          => isset( $data->slug ) ? $data->slug : $this->options['plugin_slug'],
			'version'       => isset( $data->version ) ? $data->version : '',
			'author'        => isset( $data->author ) ? $data->author : '',
			'homepage'      => isset( $data->homepage ) ? $data->homepage : '',
			'requires'      => isset( $data->requires ) ? $data->requires : '',
			'tested'        => isset( $data->tested ) ? $data->tested : '',
			'requires_php'  => isset( $data->requires_php ) ? $data->requires_php : '',
			'last_updated'  => isset( $data->released_at ) ? $data->released_at : '',
			'sections'      => array(
				'description' => isset( $data->description ) ? $data->description : '',
				'changelog'   => isset( $data->changelog ) ? $data->changelog : '',
			),
			'download_link' => isset( $data->download_url ) ? $data->download_url : '',
			'banners'       => array(),
		);
	}

	/**
	 * Check for external blocking constant.
	 */
	public function check_external_blocking() {
		if ( ! current_user_can( $this->options['capabilities'] ) ) {
			return;
		}

		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && true === WP_HTTP_BLOCK_EXTERNAL ) {
			$host = wp_parse_url( $this->options['api_url'], PHP_URL_HOST );

			if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || false === stristr( WP_ACCESSIBLE_HOSTS, $host ) ) {
				?>
				<div class="notice notice-error">
					<p>
						<?php
						printf(
							// translators: %1$s Name of plugin %2$s host %3$s Accessible hosts.
							esc_html__( '<b>Warning!</b> You\'re blocking external requests which means you won\'t be able to get %1$s updates. Please add %2$s to %3$s.', $this->options['text_domain'] ),
							esc_html( $this->options['name'] ),
							'<strong>' . esc_html( $host ) . '</strong>',
							'<code>WP_ACCESSIBLE_HOSTS</code>'
						);
						?>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Get license status
	 *
	 * Public method to check if license is activated.
	 *
	 * @return bool
	 */
	public function is_license_active() {
		return $this->get_api_key_status();
	}

	/**
	 * Get option key with slug prefix
	 *
	 * Public method to get option key.
	 *
	 * @param string $key Option key.
	 * @return string
	 */
	public function get_option_key( $key ) {
		return $this->options['slug'] . '_license_' . $key;
	}

	/**
	 * Get option value
	 *
	 * @param string $key Option key.
	 * @return mixed
	 */
	public function get_option_value( $key ) {
		return get_option( $this->get_option_key( $key ) );
	}

	/**
	 * Get option group
	 *
	 * @return string
	 */
	public function get_option_group() {
		return $this->options['option_group'];
	}

	/**
	 * Get settings section
	 *
	 * @return string
	 */
	public function get_settings_section() {
		return $this->options['settings_section'];
	}

	/**
	 * Get text domain
	 *
	 * @return string
	 */
	public function get_text_domain() {
		return $this->options['text_domain'];
	}

	/**
	 * Get plugin name
	 *
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->options['name'];
	}

	/**
	 * Get API URL
	 *
	 * @return string
	 */
	public function get_api_url() {
		return $this->options['api_url'];
	}

	/**
	 * Get plugin file
	 *
	 * @return string
	 */
	public function get_plugin_file() {
		return $this->options['file'];
	}
}
