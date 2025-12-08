<?php
/**
 * FormsCRM Settings Renderer
 *
 * Renders FormsCRM-style license settings UI with modern design.
 * This class provides a consistent UI across all FormsCRM plugins.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2024 Closemarketing
 * @version    1.0.0
 */

namespace Closemarketing\WPLicenseManager;

defined( 'ABSPATH' ) || exit;

/**
 * FormsCRM Settings Class
 *
 * Provides FormsCRM-branded settings page rendering.
 *
 * @since 1.2.0
 */
class FormsCRMSettings {
	/**
	 * License instance
	 *
	 * @var License
	 */
	private $license;

	/**
	 * Configuration options
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Default settings options
	 *
	 * @var array
	 */
	private $default_options = array(
		'title'        => 'License Management',
		'description'  => 'Manage your license to receive updates and support.',
		'plugin_name'  => '',
		'purchase_url' => 'https://close.technology/',
		'renew_url'    => 'https://close.technology/my-account/',
		'benefits'     => array(
			'Automatic plugin updates',
			'Access to new features',
			'Priority support',
			'Security patches',
		),
	);

	/**
	 * Constructor
	 *
	 * @param License $license License instance.
	 * @param array   $options Settings options.
	 */
	public function __construct( License $license, $options = array() ) {
		$this->license = $license;
		$this->options = wp_parse_args( $options, $this->default_options );

		// Set plugin name from license if not provided.
		if ( empty( $this->options['plugin_name'] ) ) {
			$this->options['plugin_name'] = $this->license->get_plugin_name();
		}
	}

	/**
	 * Render the complete settings UI
	 *
	 * @return void
	 */
	public function render() {
		// Get license status data.
		$status_data = $this->get_license_status_data();

		?>
		<div class="formscrm-license-wrapper">
			<div class="formscrm-card">
				<div class="formscrm-card-header">
					<h2><?php echo esc_html( $this->options['title'] ); ?></h2>
					<p><?php echo esc_html( $this->options['description'] ); ?></p>
				</div>

				<form method="post" action="options.php" class="formscrm-license-form">
					<?php settings_fields( $this->license->get_option_group() ); ?>
					<?php wp_nonce_field( 'Update_License_Options', 'license_nonce' ); ?>

					<div class="formscrm-form-group">
						<label for="<?php echo esc_attr( $this->license->get_option_key( 'apikey' ) ); ?>" class="formscrm-label">
							<?php echo esc_html__( 'License Key', $this->license->get_text_domain() ); ?>
						</label>
						<div class="formscrm-input-group">
							<input 
								type="text"
								id="<?php echo esc_attr( $this->license->get_option_key( 'apikey' ) ); ?>"
								name="<?php echo esc_attr( $this->license->get_option_key( 'apikey' ) ); ?>"
								value="<?php echo esc_attr( $status_data['license_key'] ); ?>"
								placeholder="<?php echo esc_attr__( 'Enter your license key', $this->license->get_text_domain() ); ?>"
								class="formscrm-input"
								<?php echo 'active' === $status_data['status'] ? 'readonly' : ''; ?>
							/>
							<?php if ( 'active' === $status_data['status'] ) : ?>
								<label class="formscrm-deactivate-label">
									<input type="checkbox" name="<?php echo esc_attr( $this->license->get_option_key( 'deactivate_checkbox' ) ); ?>" value="on" />
									<span><?php echo esc_html__( 'Deactivate', $this->license->get_text_domain() ); ?></span>
								</label>
							<?php endif; ?>
						</div>
						<p class="formscrm-help-text">
							<?php echo esc_html__( 'Enter your license key from your purchase confirmation email.', $this->license->get_text_domain() ); ?>
						</p>
					</div>

					<div class="formscrm-form-group">
						<label class="formscrm-label"><?php echo esc_html__( 'License Status', $this->license->get_text_domain() ); ?></label>
						<div class="formscrm-status-box <?php echo esc_attr( $status_data['status_class'] ); ?>">
							<span class="formscrm-status-icon"><?php echo $status_data['status_icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="formscrm-status-text"><?php echo esc_html( $status_data['status_text'] ); ?></span>
						</div>
					</div>

					<?php if ( empty( $status_data['license_key'] ) ) : ?>
						<div class="formscrm-notice formscrm-notice-info">
							<p>
								<?php
								printf(
									/* translators: %s: purchase link */
									esc_html__( 'Don\'t have a license? %s to get started.', $this->license->get_text_domain() ),
									'<a href="' . esc_url( $this->options['purchase_url'] ) . '" target="_blank" rel="noopener noreferrer">' . sprintf(
										/* translators: %s: plugin name */
										esc_html__( 'Purchase %s', $this->license->get_text_domain() ),
										esc_html( $this->options['plugin_name'] )
									) . '</a>'
								);
								?>
							</p>
						</div>
					<?php endif; ?>

					<?php if ( 'expired' === $status_data['status'] ) : ?>
						<div class="formscrm-notice formscrm-notice-error">
							<p>
								<?php
								printf(
									/* translators: %s: renewal link */
									esc_html__( 'Your license has expired. %s to continue receiving updates and support.', $this->license->get_text_domain() ),
									'<a href="' . esc_url( $this->options['renew_url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Renew your license', $this->license->get_text_domain() ) . '</a>'
								);
								?>
							</p>
						</div>
					<?php endif; ?>

					<div class="formscrm-form-actions">
						<button type="submit" name="submit_license" class="formscrm-button formscrm-button-primary">
							<?php echo 'active' === $status_data['status'] ? esc_html__( 'Update License', $this->license->get_text_domain() ) : esc_html__( 'Activate License', $this->license->get_text_domain() ); ?>
						</button>
					</div>
				</form>
			</div>

			<div class="formscrm-info-card">
				<h3><?php echo esc_html__( 'What is the license for?', $this->license->get_text_domain() ); ?></h3>
				<p>
					<?php
					printf(
						/* translators: %s: Plugin name */
						esc_html__( 'A valid license key is required to receive %s updates and support.', $this->license->get_text_domain() ),
						'<strong>' . esc_html( $this->options['plugin_name'] ) . '</strong>'
					);
					?>
				</p>
				<ul class="formscrm-benefits-list">
					<?php foreach ( $this->options['benefits'] as $benefit ) : ?>
						<li><?php echo esc_html( $benefit ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Get license status data
	 *
	 * Returns an array with all status-related information.
	 *
	 * @return array
	 */
	private function get_license_status_data() {
		$license_key     = $this->license ? $this->license->get_option_value( 'apikey' ) : '';
		$activated_value = $this->license ? $this->license->get_option_value( 'activated' ) : 'Deactivated';

		// Determine license status.
		$license_status = 'inactive';
		if ( 'Activated' === $activated_value ) {
			$license_status = 'active';
		} elseif ( 'Expired' === $activated_value ) {
			$license_status = 'expired';
		}

		$status_text  = '';
		$status_class = '';
		$status_icon  = '';

		switch ( $license_status ) {
			case 'active':
				$status_text  = __( 'Active', $this->license->get_text_domain() );
				$status_class = 'formscrm-status-active';
				$status_icon  = $this->get_status_icon( 'active' );
				break;
			case 'expired':
				$status_text  = __( 'Expired', $this->license->get_text_domain() );
				$status_class = 'formscrm-status-expired';
				$status_icon  = $this->get_status_icon( 'expired' );
				break;
			default:
				$status_text  = __( 'Not Activated', $this->license->get_text_domain() );
				$status_class = 'formscrm-status-inactive';
				$status_icon  = $this->get_status_icon( 'inactive' );
				break;
		}

		return array(
			'license_key'  => $license_key,
			'status'       => $license_status,
			'status_text'  => $status_text,
			'status_class' => $status_class,
			'status_icon'  => $status_icon,
		);
	}

	/**
	 * Get status icon SVG
	 *
	 * @param string $status Status type (active, expired, inactive).
	 * @return string SVG icon HTML.
	 */
	private function get_status_icon( $status ) {
		$icons = array(
			'active'   => '<svg class="fcod-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
			'expired'  => '<svg class="fcod-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
			'inactive' => '<svg class="fcod-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
		);

		return isset( $icons[ $status ] ) ? $icons[ $status ] : $icons['inactive'];
	}
}
