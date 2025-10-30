<?php
/**
 * Settings Page Renderer
 *
 * Handles all the HTML/form rendering for license settings.
 * This class can be used standalone or integrated into existing settings pages.
 *
 * @package    Closemarketing\WPLicenseManager
 * @author     David Perez <david@closemarketing.es>
 * @copyright  2019 Closemarketing
 * @version    2.0.0
 */

namespace Closemarketing\WPLicenseManager;

defined( 'ABSPATH' ) || exit;

/**
 * Settings Class
 *
 * Provides ready-to-use settings page rendering.
 *
 * @since 2.0.0
 */
class Settings {
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
		'page_title'       => 'License Settings',
		'menu_title'       => 'License',
		'menu_slug'        => 'license-settings',
		'capability'       => 'manage_options',
		'parent_slug'      => 'options-general.php', // Settings submenu.
		'icon_url'         => 'dashicons-admin-network',
		'position'         => null,
		'show_header'      => true,
		'show_tabs'        => false,
		'custom_css'       => true,
		'standalone_page'  => false, // If true, creates its own menu page.
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

		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		if ( $this->options['custom_css'] ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}
	}

	/**
	 * Add settings page to WordPress admin
	 */
	public function add_settings_page() {
		if ( $this->options['standalone_page'] ) {
			// Add as top-level menu.
			add_menu_page(
				$this->options['page_title'],
				$this->options['menu_title'],
				$this->options['capability'],
				$this->options['menu_slug'],
				array( $this, 'render_settings_page' ),
				$this->options['icon_url'],
				$this->options['position']
			);
		} else {
			// Add as submenu.
			add_submenu_page(
				$this->options['parent_slug'],
				$this->options['page_title'],
				$this->options['menu_title'],
				$this->options['capability'],
				$this->options['menu_slug'],
				array( $this, 'render_settings_page' )
			);
		}
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		$option_group = $this->license->get_option_group();
		$section_id   = $this->license->get_settings_section();

		register_setting( $option_group, $option_group );

		add_settings_section(
			$option_group,
			'',
			array( $this, 'render_section_description' ),
			$section_id
		);

		// API Key field.
		add_settings_field(
			'license_apikey',
			__( 'License API Key', $this->license->get_text_domain() ),
			array( $this, 'render_apikey_field' ),
			$section_id,
			$option_group
		);

		// Product ID field.
		add_settings_field(
			'license_product_id',
			__( 'Product ID', $this->license->get_text_domain() ),
			array( $this, 'render_product_id_field' ),
			$section_id,
			$option_group
		);

		// Status field.
		add_settings_field(
			'license_status',
			__( 'License Status', $this->license->get_text_domain() ),
			array( $this, 'render_status_field' ),
			$section_id,
			$option_group
		);

		// Deactivate checkbox.
		add_settings_field(
			'license_deactivate',
			__( 'Deactivate License', $this->license->get_text_domain() ),
			array( $this, 'render_deactivate_field' ),
			$section_id,
			$option_group
		);
	}

	/**
	 * Render section description
	 */
	public function render_section_description() {
		?>
		<p><?php esc_html_e( 'Enter your license information to activate automatic updates and support.', $this->license->get_text_domain() ); ?></p>
		<?php
	}

	/**
	 * Render API Key field
	 */
	public function render_apikey_field() {
		$value = $this->license->get_option_value( 'apikey' );
		?>
		<input type="text" 
			   name="<?php echo esc_attr( $this->license->get_option_key( 'apikey' ) ); ?>" 
			   id="license_apikey" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text">
		<p class="description">
			<?php esc_html_e( 'Enter your license API key. You can find this in your account dashboard.', $this->license->get_text_domain() ); ?>
		</p>
		<?php
	}

	/**
	 * Render Product ID field
	 */
	public function render_product_id_field() {
		$value = $this->license->get_option_value( 'product_id' );
		?>
		<input type="text" 
			   name="<?php echo esc_attr( $this->license->get_option_key( 'product_id' ) ); ?>" 
			   id="license_product_id" 
			   value="<?php echo esc_attr( $value ); ?>" 
			   class="regular-text">
		<p class="description">
			<?php esc_html_e( 'Enter the product ID associated with your license.', $this->license->get_text_domain() ); ?>
		</p>
		<?php
	}

	/**
	 * Render Status field
	 */
	public function render_status_field() {
		$is_active = $this->license->is_license_active();
		$status_class = $is_active ? 'license-status-active' : 'license-status-inactive';
		$status_text  = $is_active ? __( 'Activated', $this->license->get_text_domain() ) : __( 'Deactivated', $this->license->get_text_domain() );
		?>
		<span class="<?php echo esc_attr( $status_class ); ?>">
			<?php echo esc_html( $status_text ); ?>
		</span>
		<?php if ( $is_active ) : ?>
			<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
		<?php else : ?>
			<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render Deactivate field
	 */
	public function render_deactivate_field() {
		$checked = get_option( $this->license->get_option_key( 'deactivate_checkbox' ) );
		?>
		<label>
			<input type="checkbox" 
				   name="<?php echo esc_attr( $this->license->get_option_key( 'deactivate_checkbox' ) ); ?>" 
				   id="license_deactivate_checkbox" 
				   value="on" 
				   <?php checked( $checked, 'on' ); ?>>
			<?php esc_html_e( 'Check this box to deactivate the license on this site.', $this->license->get_text_domain() ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Deactivating allows you to use the license on another site.', $this->license->get_text_domain() ); ?>
		</p>
		<?php
	}

	/**
	 * Render the complete settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( $this->options['capability'] ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->license->get_text_domain() ) );
		}
		?>
		<div class="wrap license-settings-wrap">
			<?php if ( $this->options['show_header'] ) : ?>
				<h1><?php echo esc_html( $this->options['page_title'] ); ?></h1>
			<?php endif; ?>

			<?php settings_errors(); ?>

			<div class="license-settings-container">
				<div class="license-settings-main">
					<form method="post" action="options.php">
						<?php
						settings_fields( $this->license->get_option_group() );
						do_settings_sections( $this->license->get_settings_section() );
						wp_nonce_field( 'Update_License_Options', 'license_nonce' );
						submit_button( __( 'Save License Settings', $this->license->get_text_domain() ) );
						?>
					</form>
				</div>

				<div class="license-settings-sidebar">
					<?php $this->render_sidebar(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render sidebar with helpful information
	 */
	private function render_sidebar() {
		?>
		<div class="license-info-box">
			<h3><?php esc_html_e( 'License Information', $this->license->get_text_domain() ); ?></h3>
			<p>
				<?php
				printf(
					// translators: %s: Plugin name.
					esc_html__( 'Your license key provides access to automatic updates and support for %s.', $this->license->get_text_domain() ),
					'<strong>' . esc_html( $this->license->get_plugin_name() ) . '</strong>'
				);
				?>
			</p>
		</div>

		<div class="license-info-box">
			<h3><?php esc_html_e( 'Instance ID', $this->license->get_text_domain() ); ?></h3>
			<p>
				<code><?php echo esc_html( $this->license->get_option_value( 'instance' ) ); ?></code>
			</p>
			<p class="description">
				<?php esc_html_e( 'This unique identifier is used to track activations.', $this->license->get_text_domain() ); ?>
			</p>
		</div>

		<div class="license-info-box">
			<h3><?php esc_html_e( 'Need Help?', $this->license->get_text_domain() ); ?></h3>
			<ul class="license-help-links">
				<li>
					<span class="dashicons dashicons-book"></span>
					<a href="<?php echo esc_url( $this->get_documentation_url() ); ?>" target="_blank">
						<?php esc_html_e( 'Documentation', $this->license->get_text_domain() ); ?>
					</a>
				</li>
				<li>
					<span class="dashicons dashicons-sos"></span>
					<a href="<?php echo esc_url( $this->get_support_url() ); ?>" target="_blank">
						<?php esc_html_e( 'Support', $this->license->get_text_domain() ); ?>
					</a>
				</li>
				<li>
					<span class="dashicons dashicons-admin-users"></span>
					<a href="<?php echo esc_url( $this->get_account_url() ); ?>" target="_blank">
						<?php esc_html_e( 'My Account', $this->license->get_text_domain() ); ?>
					</a>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Enqueue custom CSS
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_styles( $hook ) {
		// Only enqueue on our settings page.
		$allowed_hooks = array(
			'settings_page_' . $this->options['menu_slug'],
			'toplevel_page_' . $this->options['menu_slug'],
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		// Inline CSS.
		$css = '
		.license-settings-wrap {
			max-width: 1200px;
		}
		.license-settings-container {
			display: grid;
			grid-template-columns: 2fr 1fr;
			gap: 20px;
			margin-top: 20px;
		}
		.license-settings-main {
			background: #fff;
			padding: 20px;
			border: 1px solid #ccd0d4;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
		}
		.license-settings-sidebar {
			display: flex;
			flex-direction: column;
			gap: 20px;
		}
		.license-info-box {
			background: #fff;
			padding: 20px;
			border: 1px solid #ccd0d4;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
		}
		.license-info-box h3 {
			margin-top: 0;
			padding-bottom: 10px;
			border-bottom: 1px solid #dcdcde;
		}
		.license-info-box code {
			display: block;
			padding: 8px;
			background: #f0f0f1;
			border-radius: 3px;
			word-break: break-all;
		}
		.license-help-links {
			list-style: none;
			padding: 0;
			margin: 0;
		}
		.license-help-links li {
			padding: 8px 0;
			border-bottom: 1px solid #f0f0f1;
		}
		.license-help-links li:last-child {
			border-bottom: none;
		}
		.license-help-links .dashicons {
			margin-right: 5px;
			color: #2271b1;
		}
		.license-status-active {
			color: #00a32a;
			font-weight: bold;
		}
		.license-status-inactive {
			color: #d63638;
			font-weight: bold;
		}
		@media screen and (max-width: 782px) {
			.license-settings-container {
				grid-template-columns: 1fr;
			}
		}
		';

		wp_add_inline_style( 'wp-admin', $css );
	}

	/**
	 * Get documentation URL
	 *
	 * @return string
	 */
	private function get_documentation_url() {
		return apply_filters( 'license_manager_documentation_url', 'https://close.technology/docs/' );
	}

	/**
	 * Get support URL
	 *
	 * @return string
	 */
	private function get_support_url() {
		return apply_filters( 'license_manager_support_url', 'https://close.technology/support/' );
	}

	/**
	 * Get account URL
	 *
	 * @return string
	 */
	private function get_account_url() {
		return apply_filters( 'license_manager_account_url', 'https://close.technology/my-account/' );
	}
}


