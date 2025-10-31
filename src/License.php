<?php
/**
 * License Manager Library
 *
 * A reusable library for managing WordPress plugin licenses with WooCommerce API Manager.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2019 Closemarketing
 * @version    2.0.0
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
		'file'              => '',           // Plugin main file (required).
		'version'           => '',           // Plugin version (required).
		'slug'              => '',           // Plugin slug (required).
		'name'              => '',           // Plugin name (required).
		'text_domain'       => 'default',    // Text domain for translations.
		'plugin_slug'       => '',           // Plugin slug for updates.
		'plugin_name'       => '',           // Plugin name for updates.
		'settings_page'     => '',           // Settings page slug.
		'settings_tabs'     => '',           // Action for settings tabs.
		'settings_content'  => '',           // Action for settings content.
		'option_group'      => '',           // Option group for settings.
		'settings_section'  => '',           // Settings section ID.
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
		$required = array( 'api_url', 'file', 'version', 'slug', 'name' );

		foreach ( $required as $key ) {
			if ( empty( $this->options[ $key ] ) ) {
				// translators: %s: Option key name.
				throw new \Exception( sprintf( 'License Manager: Required option "%s" is missing.', $key ) );
			}
		}

		// Set defaults for optional fields based on slug.
		if ( empty( $this->options['plugin_slug'] ) ) {
			$this->options['plugin_slug'] = $this->options['slug'];
		}

		if ( empty( $this->options['plugin_name'] ) ) {
			$this->options['plugin_name'] = plugin_basename( $this->options['file'] );
		}

		if ( empty( $this->options['settings_page'] ) ) {
			$this->options['settings_page'] = $this->options['slug'] . '_settings';
		}

		if ( empty( $this->options['settings_tabs'] ) ) {
			$this->options['settings_tabs'] = $this->options['slug'] . '_settings_tabs';
		}

		if ( empty( $this->options['settings_content'] ) ) {
			$this->options['settings_content'] = $this->options['slug'] . '_settings_tabs_content';
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
		// Settings tabs and content.
		add_action( $this->options['settings_tabs'], array( $this, 'add_settings_tab' ) );
		add_action( $this->options['settings_content'], array( $this, 'add_license_content' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

		// Creates license activation.
		register_activation_hook( $this->options['file'], array( $this, 'license_instance_activation' ) );

		// Check for external blocking.
		add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );

		// Update checks.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
		add_filter( 'plugins_api', array( $this, 'information_request' ), 10, 3 );
	}


	/**
	 * Add settings tab.
	 *
	 * @param string $active_tab Active tab.
	 */
	public function add_settings_tab( $active_tab ) {
		?>
		<a href="?page=<?php echo esc_attr( $this->options['settings_page'] ); ?>&tab=license" class="nav-tab <?php echo 'license' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'License', $this->options['text_domain'] ); ?>
		</a>
		<?php
	}

	/**
	 * Add settings tab content.
	 *
	 * @param string $active_tab Active tab.
	 */
	public function add_license_content( $active_tab ) {
		if ( 'license' !== $active_tab ) {
			return;
		}

		echo '<div class="license-manager-settings">';
		echo '<div class="license">';

		echo '<form method="post" action="options.php">';
		settings_fields( $this->options['option_group'] );
		do_settings_sections( $this->options['settings_section'] );
		wp_nonce_field( 'Update_License_Options', 'license_nonce' );
		submit_button(
			__( 'Save', $this->options['text_domain'] ),
			'primary',
			'submit_license'
		);
		echo '</form>';

		echo '</div>';
		echo '<div class="settings">';
		echo '<h2>' . esc_html__( 'What is the license for?', $this->options['text_domain'] ) . '</h2>';
		echo '<p>';
		echo sprintf(
			// translators: %s Name of plugin.
			esc_html__( 'With the license, you\'ll have updates and automatic fixes to what\'s new or change in your system, so you\'ll always have the latest functionalities for %s.', $this->options['text_domain'] ),
			'<strong>' . esc_html( $this->options['name'] ) . '</strong>'
		);
		echo '</p>';
		echo '</div><div class="help">';
		echo '<h2>' . esc_html__( 'License Instance', $this->options['text_domain'] ) . '</h2>';
		echo '<p style="color:#50575e;">' . esc_html__( 'Instance:', $this->options['text_domain'] ) . ' ' . esc_html( get_option( $this->get_option_key( 'instance' ) ) ) . '</p>';
		echo '</div></div>';
	}

	/**
	 * Page init - Register settings
	 */
	public function page_init() {
		register_setting(
			$this->options['option_group'],
			$this->options['option_group'],
			array( $this, 'sanitize_fields_license' )
		);

		add_settings_section(
			$this->options['option_group'],
			'',
			'',
			$this->options['settings_section']
		);

		add_settings_field(
			$this->get_option_key( 'apikey' ),
			__( 'License API Key', $this->options['text_domain'] ),
			array( $this, 'license_apikey_callback' ),
			$this->options['settings_section'],
			$this->options['option_group']
		);

		add_settings_field(
			$this->get_option_key( 'product_id' ),
			__( 'License Product ID', $this->options['text_domain'] ),
			array( $this, 'license_product_id_callback' ),
			$this->options['settings_section'],
			$this->options['option_group']
		);

		add_settings_field(
			$this->get_option_key( 'status' ),
			__( 'License Status', $this->options['text_domain'] ),
			array( $this, 'license_status_callback' ),
			$this->options['settings_section'],
			$this->options['option_group']
		);

		add_settings_field(
			$this->get_option_key( 'deactivate' ),
			__( 'Deactivate License', $this->options['text_domain'] ),
			array( $this, 'license_deactivate_callback' ),
			$this->options['settings_section'],
			$this->options['option_group']
		);
	}

	/**
	 * Sanitize fields before saves in DB
	 *
	 * @param array $input Input fields.
	 * @return array
	 */
	public function sanitize_fields_license( $input ) {
		$apikey_key = $this->get_option_key( 'apikey' );
		$product_key = $this->get_option_key( 'product_id' );

		$license_instance = get_option( $this->get_option_key( 'instance' ) );
		if ( empty( $license_instance ) ) {
			$this->license_instance_activation();
		}

		if ( isset( $_POST[ $apikey_key ] ) ) {
			update_option( $apikey_key, sanitize_text_field( wp_unslash( $_POST[ $apikey_key ] ) ) );
		}

		if ( isset( $_POST[ $product_key ] ) ) {
			update_option( $product_key, sanitize_text_field( wp_unslash( $_POST[ $product_key ] ) ) );
		}

		$this->validate_license( $_POST );

		return $input;
	}

	/**
	 * Callback for Setting License API Key
	 *
	 * @return void
	 */
	public function license_apikey_callback() {
		$value = get_option( $this->get_option_key( 'apikey' ) );
		echo '<input type="text" class="regular-text" name="' . esc_attr( $this->get_option_key( 'apikey' ) ) . '" id="license_apikey" value="' . esc_attr( $value ) . '">';
	}

	/**
	 * Callback for Setting license Product ID
	 *
	 * @return void
	 */
	public function license_product_id_callback() {
		$value = get_option( $this->get_option_key( 'product_id' ) );
		echo '<input type="text" class="regular-text" name="' . esc_attr( $this->get_option_key( 'product_id' ) ) . '" size="25" id="license_product_id" value="' . esc_attr( $value ) . '">';
	}

	/**
	 * Callback for License status
	 *
	 * @return void
	 */
	public function license_status_callback() {
		if ( $this->get_api_key_status( true ) ) {
			$license_status_check = esc_html__( 'Activated', $this->options['text_domain'] );
			update_option( $this->get_option_key( 'activated' ), 'Activated' );
			update_option( $this->get_option_key( 'deactivate_checkbox' ), 'off' );
		} else {
			$license_status_check = esc_html__( 'Deactivated', $this->options['text_domain'] );
		}

		echo esc_attr( $license_status_check );
	}

	/**
	 * Callback for deactivate checkbox
	 *
	 * @return void
	 */
	public function license_deactivate_callback() {
		echo '<input type="checkbox" id="license_deactivate_checkbox" name="' . esc_attr( $this->get_option_key( 'deactivate_checkbox' ) ) . '" value="on"';
		echo checked( get_option( $this->get_option_key( 'deactivate_checkbox' ) ), 'on' );
		echo '/>';
		echo '<span class="description">';
		esc_html_e( 'Deactivates License so it can be used on another site.', $this->options['text_domain'] );
		echo '</span>';
	}

	/**
	 * Validates license option
	 *
	 * @param array $input Settings input option.
	 * @return array
	 */
	public function validate_license( $input ) {
		$apikey_key        = $this->get_option_key( 'apikey' );
		$api_key           = isset( $input[ $apikey_key ] ) ? trim( $input[ $apikey_key ] ) : '';
		$api_key           = sanitize_text_field( $api_key );
		$activation_status = get_option( $this->get_option_key( 'activated' ) );
		$checkbox_status   = get_option( $this->get_option_key( 'deactivate_checkbox' ) );
		$current_api_key   = get_option( $apikey_key, '' );

		// Product ID.
		$product_key = $this->get_option_key( 'product_id' );
		if ( isset( $input[ $product_key ] ) ) {
			$new_product_id = absint( $input[ $product_key ] );
			if ( ! empty( $new_product_id ) ) {
				update_option( $product_key, $new_product_id );
			}
		}

		// Deactivate License.
		if ( isset( $input[ $this->get_option_key( 'deactivate_checkbox' ) ] ) && 'on' === $input[ $this->get_option_key( 'deactivate_checkbox' ) ] ) {
			$args = array(
				'api_key' => ! empty( $api_key ) ? $api_key : '',
			);
			$deactivation_result = $this->license_deactivate( $args );

			if ( ! empty( $deactivation_result ) && is_array( $deactivation_result ) ) {
				if ( true === $deactivation_result['success'] && true === $deactivation_result['deactivated'] ) {
					update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
					update_option( $apikey_key, '' );
					update_option( $product_key, '' );
					add_settings_error( 'license_deactivate', 'deactivate_msg', esc_html__( 'License deactivated successfully.', $this->options['text_domain'] ), 'updated' );
					return;
				}

				if ( isset( $deactivation_result['data']['error_code'] ) ) {
					add_settings_error( 'license_error', 'license_client_error', esc_attr( $deactivation_result['data']['error'] ), 'error' );
					update_option( $this->get_option_key( 'activated' ), 'Deactivated' );

					return array(
						'status'  => 'error',
						'message' => $deactivation_result['data']['error'],
					);
				}
			}

			// Remove anyway.
			update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
			update_option( $apikey_key, '' );
			update_option( $product_key, '' );
			return;
		}

		// Activate License.
		if ( 'Deactivated' === $activation_status || empty( $activation_status ) || '' === $api_key || 'on' === $checkbox_status || $current_api_key !== $api_key ) {
			// Replace existing key if different.
			if ( ! empty( $current_api_key ) && $current_api_key !== $api_key ) {
				$this->replace_license_key( $current_api_key );
			}

			$activation_result = $this->license_activate( $api_key );

			if ( ! empty( $activation_result ) ) {
				$activate_results = json_decode( $activation_result, true );

				if ( true === $activate_results['success'] && true === $activate_results['activated'] ) {
					$message = __( 'License activated successfully.', $this->options['text_domain'] ) . ' ' . esc_attr( $activate_results['message'] );

					add_settings_error( 'activate_text', 'activate_msg', $message, 'updated' );

					// Update license key and status.
					update_option( $apikey_key, $api_key );
					update_option( $this->get_option_key( 'activated' ), 'Activated' );
					update_option( $this->get_option_key( 'deactivate_checkbox' ), 'off' );

					return array(
						'status'  => 'ok',
						'message' => $message,
					);
				}

				if ( false === $activate_results && ! empty( get_option( $this->get_option_key( 'activated' ) ) ) ) {
					add_settings_error( 'api_key_check', 'api_key_check_error', esc_html__( 'Connection failed to the License Key API server. Try again later.', $this->options['text_domain'] ), 'error' );
	
					update_option( $this->get_option_key( 'activated' ), 'Deactivated' );

					return array(
						'status' => 'error',
						'message' => esc_html__( 'Connection failed to the License Key API server. Try again later.', $this->options['text_domain'] ),
					);
				}

				if ( isset( $activate_results['data']['error_code'] ) ) {
					add_settings_error( 'license_error', 'license_client_error', esc_attr( $activate_results['data']['error'] ), 'error' );
					update_option( $this->get_option_key( 'activated' ), 'Deactivated' );
				}
			} else {
				$message = esc_html__( 'The API Key activation could not be completed due to an unknown error.', $this->options['text_domain'] );

				add_settings_error( 'not_activated', 'not_activated_error', $message );

				return array(
					'status' => 'error',
					'message' => $message,
				);
			}
		}
	}

	/**
	 * Sends the request to activate to the API Manager.
	 *
	 * @param string $api_key API Key to activate.
	 * @return string
	 */
	public function license_activate( $api_key ) {
		if ( empty( $api_key ) ) {
			add_settings_error( 'not_activated', 'not_activated_error', esc_html__( 'The API Key is missing from the activation request.', $this->options['text_domain'] ), 'error' );
			return '';
		}

		$defaults            = $this->get_license_defaults( 'activate', true );
		$defaults['api_key'] = $api_key;
		$target_url          = esc_url_raw( $this->create_software_api_url( $defaults ) );
		$request             = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $request );
	}

	/**
	 * Sends the request to deactivate to the API Manager.
	 *
	 * @param array $args Arguments to deactivate.
	 * @return array|void
	 */
	public function license_deactivate( $args ) {
		if ( empty( $args ) ) {
			add_settings_error( 'not_deactivated', 'not_deactivated_error', esc_html__( 'The API Key is missing from the deactivation request.', $this->options['text_domain'] ), 'error' );
			return;
		}

		$defaults   = $this->get_license_defaults( 'deactivate' );
		$args       = wp_parse_args( $defaults, $args );
		$target_url = esc_url_raw( $this->create_software_api_url( $args ) );
		$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );
		$body_json  = wp_remote_retrieve_body( $request );
		$result_api = json_decode( $body_json, true );

		$error = ! empty( $result_api['error'] ) ? $result_api['error'] : '';

		if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) || $error ) {
			add_settings_error(
				'not_deactivated',
				'not_deactivated_error',
				$error,
				'error'
			);
			return;
		}

		return $result_api;
	}

	/**
	 * Returns true if the API Key status is Activated.
	 *
	 * @param bool $live Do not set to true if using to activate software.
	 * @return bool
	 */
	public function get_api_key_status( $live = false ) {
		if ( $live ) {
			$license_status = $this->license_key_status();
			return ! empty( $license_status ) && ! empty( $license_status['data']['activated'] ) && $license_status['data']['activated'];
		}

		return 'Activated' === get_option( $this->get_option_key( 'activated' ) );
	}

	/**
	 * Returns the API Key status.
	 *
	 * @return array|mixed|object
	 */
	public function license_key_status() {
		$status = $this->status();
		return ! empty( $status ) ? json_decode( $status, true ) : $status;
	}

	/**
	 * Sends the status check request to the API Manager.
	 *
	 * @return bool|string
	 */
	public function status() {
		if ( empty( get_option( $this->get_option_key( 'apikey' ) ) ) ) {
			return '';
		}

		$defaults   = $this->get_license_defaults( 'status' );
		$target_url = esc_url_raw( $this->create_software_api_url( $defaults ) );
		$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $request );
	}

	/**
	 * Get license defaults
	 *
	 * @param string $action            Action to license defaults.
	 * @param bool   $software_version Software version.
	 * @return array
	 */
	private function get_license_defaults( $action, $software_version = false ) {
		$api_key    = get_option( $this->get_option_key( 'apikey' ) );
		$product_id = get_option( $this->get_option_key( 'product_id' ) );

		$defaults = array(
			'wc_am_action' => $action,
			'api_key'      => $api_key,
			'product_id'   => $product_id,
			'instance'     => get_option( $this->get_option_key( 'instance' ) ),
			'object'       => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
		);

		if ( $software_version ) {
			$defaults['software_version'] = $this->options['version'];
		}

		return $defaults;
	}

	/**
	 * Builds the URL containing the API query string.
	 *
	 * @param array $args Arguments data.
	 * @return string
	 */
	public function create_software_api_url( $args ) {
		return add_query_arg( 'wc-api', 'wc-am-api', $this->options['api_url'] ) . '&' . http_build_query( $args );
	}

	/**
	 * Generate the default data.
	 */
	public function license_instance_activation() {
		$instance_exists = get_option( $this->get_option_key( 'instance' ) );

		if ( ! $instance_exists ) {
			update_option( $this->get_option_key( 'instance' ), wp_generate_password( 20, false ) );
		}
	}

	/**
	 * Deactivate the current API Key before activating the new API Key
	 *
	 * @param string $current_api_key Current api key.
	 */
	public function replace_license_key( $current_api_key ) {
		$args = array(
			'api_key' => $current_api_key,
		);

		$this->license_deactivate( $args );
	}

	/**
	 * Sends and receives data to and from the server API
	 *
	 * @param array $args Arguments for query.
	 * @return bool|string
	 */
	public function send_query( $args ) {
		$target_url = esc_url_raw( add_query_arg( 'wc-api', 'wc-am-api', $this->options['api_url'] ) . '&' . http_build_query( $args ) );
		$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return false;
		}

		$response = wp_remote_retrieve_body( $request );
		return ! empty( $response ) ? $response : false;
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

		$args = array(
			'wc_am_action' => 'update',
			'slug'         => $this->options['plugin_slug'],
			'plugin_name'  => $this->options['plugin_name'],
			'version'      => $this->options['version'],
			'product_id'   => get_option( $this->get_option_key( 'product_id' ) ),
			'api_key'      => get_option( $this->get_option_key( 'apikey' ) ),
			'instance'     => get_option( $this->get_option_key( 'instance' ) ),
		);

		$response = json_decode( $this->send_query( $args ), true );

		if ( isset( $response['data']['error_code'] ) ) {
			add_settings_error( 'license_error', 'license_client_error', $response['data']['error'], 'error' );
		}

		if ( false !== $response && true === $response['success'] ) {
			$new_version  = (string) $response['data']['package']['new_version'];
			$curr_version = (string) $this->options['version'];

			$package = array(
				'id'             => $response['data']['package']['id'],
				'slug'           => $response['data']['package']['slug'],
				'plugin'         => $response['data']['package']['plugin'],
				'new_version'    => $response['data']['package']['new_version'],
				'url'            => $response['data']['package']['url'],
				'tested'         => $response['data']['package']['tested'],
				'package'        => $response['data']['package']['package'],
				'upgrade_notice' => $response['data']['package']['upgrade_notice'],
			);

			if ( ! empty( $new_version ) && ! empty( $curr_version ) ) {
				if ( version_compare( $new_version, $curr_version, '>' ) ) {
					$transient->response[ $this->options['plugin_name'] ] = (object) $package;
					unset( $transient->no_update[ $this->options['plugin_name'] ] );
				}
			}
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
		if ( isset( $args->slug ) ) {
			if ( $this->options['plugin_slug'] !== $args->slug ) {
				return $result;
			}
		} else {
			return $result;
		}

		$query_args = array(
			'wc_am_action' => 'plugininformation',
			'plugin_name'  => $this->options['plugin_slug'],
			'version'      => $this->options['version'],
			'product_id'   => get_option( $this->get_option_key( 'product_id' ) ),
			'api_key'      => get_option( $this->get_option_key( 'apikey' ) ),
			'instance'     => get_option( $this->get_option_key( 'instance' ) ),
			'object'       => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
		);

		$response = unserialize( $this->send_query( $query_args ) );

		if ( isset( $response ) && is_object( $response ) && false !== $response ) {
			return $response;
		}

		return $result;
	}

	/**
	 * Check for external blocking constant.
	 */
	public function check_external_blocking() {
		if ( ! current_user_can( $this->options['capabilities'] ) ) {
			return;
		}

		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && true === WP_HTTP_BLOCK_EXTERNAL ) {
			$host = parse_url( $this->options['api_url'], PHP_URL_HOST );

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
}
