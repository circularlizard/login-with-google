<?php
/**
 * Register the settings under settings page and also
 * provide the interface to retrieve the settings.
 *
 * @package Circularlizard\OAuthLogin
 * @since 1.0.0
 * @author Circularlizard (forked from rtCamp)
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Modules;

use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;
use Circularlizard\OAuthLogin\Utils\ProviderRegistry;

/**
 * Class Settings.
 *
 * @property string|null whitelisted_domains
 * @property string|null client_id
 * @property string|null client_secret
 * @property bool|null registration_enabled
 * @property bool|null one_tap_login
 * @property string    one_tap_login_screen
 * @property string|null login_message
 *
 * @package Circularlizard\OAuthLogin\Modules
 */
class Settings implements ModuleInterface {

	/**
	 * Settings values (legacy structure).
	 *
	 * @var array
	 */
	public $options;

	/**
	 * New settings structure for multi-provider support.
	 *
	 * @var array
	 */
	private array $provider_settings = array();

	/**
	 * Provider registry instance.
	 *
	 * @var ProviderRegistry|null
	 */
	private ?ProviderRegistry $registry = null;

	/**
	 * Getters for settings values.
	 *
	 * @var string[]
	 */
	private $getters = array(
		'WP_GOOGLE_LOGIN_CLIENT_ID'         => 'client_id',
		'WP_GOOGLE_LOGIN_SECRET'            => 'client_secret',
		'WP_GOOGLE_LOGIN_USER_REGISTRATION' => 'registration_enabled',
		'WP_GOOGLE_LOGIN_WHITELIST_DOMAINS' => 'whitelisted_domains',
		'WP_GOOGLE_ONE_TAP_LOGIN'           => 'one_tap_login',
		'WP_GOOGLE_ONE_TAP_LOGIN_SCREEN'    => 'one_tap_login_screen',
		'WP_OAUTH_LOGIN_MESSAGE'            => 'login_message',
	);

	/**
	 * Set the provider registry.
	 *
	 * @param ProviderRegistry $registry Provider registry instance.
	 *
	 * @return void
	 */
	public function set_registry( ProviderRegistry $registry ): void {
		$this->registry = $registry;
	}

	/**
	 * Get the provider registry.
	 *
	 * @return ProviderRegistry|null
	 */
	public function get_registry(): ?ProviderRegistry {
		return $this->registry;
	}

	/**
	 * Getter method.
	 *
	 * @param string $name Name of option to fetch.
	 */
	public function __get( string $name ) {
		if ( in_array( $name, $this->getters, true ) ) {
			$constant_name = array_search( $name, $this->getters, true );

			return defined( $constant_name ) ? constant( $constant_name ) : ( $this->options[ $name ] ?? '' );
		}

		return null;
	}

	/**
	 * Return module name.
	 *
	 * @return string
	 */
	public function name(): string {
		return 'settings';
	}

	/**
	 * Initialization of module.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->options           = get_option( 'wp_google_login_settings', array() );
		$this->provider_settings = get_option( 'wp_oauth_login_settings', array() );

		/**
		 * Actions.
		 */
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'settings_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_filter( 'wp_redirect', array( $this, 'pass_reopen_panel_on_save' ) );

		/**
		 * Filters.
		 */
		// Add filters here.
	}

	/**
	 * Enqueue admin scripts for the settings page.
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts( string $hook_suffix ): void {
		if ( 'settings_page_oauth-login' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	 * Pass the oauth_reopen_panel parameter through the settings save redirect.
	 *
	 * When the "Save" button is clicked (as opposed to "Save & Close"),
	 * the hidden field oauth_reopen_panel is set to the provider ID.
	 * This filter appends it to the redirect URL so the JS can reopen the panel.
	 *
	 * @param string $location Redirect URL.
	 *
	 * @return string Modified redirect URL.
	 */
	public function pass_reopen_panel_on_save( string $location ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by settings API.
		$reopen = sanitize_key( $_POST['oauth_reopen_panel'] ?? '' );

		if ( ! empty( $reopen ) && str_contains( $location, 'settings-updated' ) ) {
			$location = add_query_arg( 'oauth_reopen_panel', $reopen, $location );
		}

		return $location;
	}

	/**
	 * Get provider-specific settings.
	 *
	 * @param string $provider_id Provider ID.
	 * @param string $key         Setting key.
	 * @param mixed  $fallback    Fallback value if not found.
	 *
	 * @return mixed
	 */
	public function get_provider_setting( string $provider_id, string $key, $fallback = '' ) {
		return $this->provider_settings['providers'][ $provider_id ][ $key ] ?? $fallback;
	}

	/**
	 * Get all settings for a provider.
	 *
	 * @param string $provider_id Provider ID.
	 *
	 * @return array
	 */
	public function get_provider_settings( string $provider_id ): array {
		return $this->provider_settings['providers'][ $provider_id ] ?? array();
	}

	/**
	 * Check if a provider is enabled.
	 *
	 * @param string $provider_id Provider ID.
	 *
	 * @return bool
	 */
	public function is_provider_enabled( string $provider_id ): bool {
		$settings = $this->get_provider_settings( $provider_id );

		// Provider is enabled if it has client_id and client_secret, and enabled is not explicitly false.
		if ( empty( $settings['client_id'] ) || empty( $settings['client_secret'] ) ) {
			return false;
		}

		return $settings['enabled'] ?? true;
	}

	/**
	 * Register the settings, section and fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'wp_oauth_login',
			'wp_google_login_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_legacy_settings' ),
			)
		);
		register_setting(
			'wp_oauth_login',
			'wp_oauth_login_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		// Providers section - shows the provider list table.
		add_settings_section(
			'wp_oauth_providers_section',
			__( 'OAuth Providers', 'oauth-login' ),
			array( $this, 'render_providers_section' ),
			'oauth-login'
		);

		// General settings section.
		add_settings_section(
			'wp_oauth_general_section',
			__( 'General Settings', 'oauth-login' ),
			function () {
			},
			'oauth-login'
		);

		add_settings_field(
			'wp_google_allow_registration',
			__( 'Create New User', 'oauth-login' ),
			array( $this, 'user_registration' ),
			'oauth-login',
			'wp_oauth_general_section',
			array( 'label_for' => 'user-registration' )
		);

		add_settings_field(
			'wp_google_whitelisted_domain',
			__( 'Whitelisted Domains', 'oauth-login' ),
			array( $this, 'whitelisted_domains' ),
			'oauth-login',
			'wp_oauth_general_section',
			array( 'label_for' => 'whitelisted-domains' )
		);

		add_settings_field(
			'wp_oauth_login_message',
			__( 'Login Message', 'oauth-login' ),
			array( $this, 'login_message_field' ),
			'oauth-login',
			'wp_oauth_general_section',
			array( 'label_for' => 'login-message' )
		);

		// Google One Tap section (Google-specific, only if Google is configured).
		if ( $this->is_provider_enabled( 'google' ) ) {
			add_settings_section(
				'wp_google_one_tap_section',
				__( 'Google One Tap Login', 'oauth-login' ),
				function () {
					echo wp_kses_post(
						'<p>' . esc_html__( 'Configure Google\'s One Tap authentication feature.', 'oauth-login' ) . '</p>'
					);
				},
				'oauth-login'
			);

			add_settings_field(
				'wp_google_one_tap_login',
				__( 'Enable One Tap Login', 'oauth-login' ),
				array( $this, 'one_tap_login' ),
				'oauth-login',
				'wp_google_one_tap_section',
				array( 'label_for' => 'one-tap-login' )
			);

			add_settings_field(
				'wp_google_one_tap_login_screen',
				__( 'One Tap Login Locations', 'oauth-login' ),
				array( $this, 'one_tap_login_screens' ),
				'oauth-login',
				'wp_google_one_tap_section',
				array( 'label_for' => 'one-tap-login-screen' )
			);
		}
	}

	/**
	 * Get the provider order.
	 *
	 * @return array Ordered list of provider IDs.
	 */
	public function get_provider_order(): array {
		return $this->provider_settings['provider_order'] ?? array();
	}

	/**
	 * Render the providers section with a list of all configured providers.
	 *
	 * @return void
	 */
	public function render_providers_section(): void {
		$providers = $this->get_all_provider_configs();
		$order     = $this->get_provider_order();
		?>
		<p><?php esc_html_e( 'Manage your OAuth login providers. Drag to reorder how buttons appear on the login form.', 'oauth-login' ); ?></p>

		<table class="widefat oauth-providers-table" id="oauth-providers-table">
			<thead>
				<tr>
					<th style="width:30px;"></th>
					<th><?php esc_html_e( 'Provider', 'oauth-login' ); ?></th>
					<th><?php esc_html_e( 'Type', 'oauth-login' ); ?></th>
					<th><?php esc_html_e( 'Status', 'oauth-login' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'oauth-login' ); ?></th>
				</tr>
			</thead>
			<tbody id="oauth-providers-list">
				<?php if ( ! empty( $providers ) ) : ?>
					<?php foreach ( $providers as $provider_id => $config ) : ?>
						<?php $this->render_provider_row( $provider_id, $config ); ?>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="oauth-no-providers">
						<td colspan="5"><?php esc_html_e( 'No providers configured. Add one below.', 'oauth-login' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<!-- Hidden field for provider order -->
		<input type="hidden" name="wp_oauth_login_settings[provider_order]" id="oauth-provider-order" value="<?php echo esc_attr( implode( ',', $order ) ); ?>" />

		<!-- Hidden field to reopen a panel after save -->
		<input type="hidden" name="oauth_reopen_panel" id="oauth-reopen-panel" value="" />

		<div style="margin-top: 15px;">
			<button type="button" class="button" id="oauth-add-google-provider" <?php echo $this->has_provider_config( 'google' ) ? 'disabled' : ''; ?>>
				<?php esc_html_e( 'Add Google', 'oauth-login' ); ?>
			</button>
			<button type="button" class="button" id="oauth-add-custom-provider">
				<?php esc_html_e( 'Add Custom OAuth Provider', 'oauth-login' ); ?>
			</button>
		</div>

		<!-- Provider edit panels (hidden by default, shown via JS) -->
		<?php foreach ( $providers as $provider_id => $config ) : ?>
			<?php $this->render_provider_edit_panel( $provider_id, $config ); ?>
		<?php endforeach; ?>

		<!-- Template for new Google provider -->
		<div id="oauth-new-google-panel" class="oauth-provider-panel" style="display:none;">
			<?php $this->render_provider_edit_panel( 'google', $this->get_default_google_config(), true ); ?>
		</div>

		<!-- Template for new custom provider -->
		<div id="oauth-new-custom-panel" class="oauth-provider-panel" style="display:none;">
			<?php $this->render_new_custom_provider_form(); ?>
		</div>

		<?php $this->render_provider_admin_script(); ?>
		<?php
	}

	/**
	 * Render a single provider row in the providers table.
	 *
	 * @param string $provider_id Provider ID.
	 * @param array  $config      Provider configuration.
	 *
	 * @return void
	 */
	private function render_provider_row( string $provider_id, array $config ): void {
		$name       = $config['name'] ?? $provider_id;
		$type       = $config['type'] ?? 'custom';
		$has_creds  = ! empty( $config['client_id'] ) && ! empty( $config['client_secret'] );
		$is_enabled = $config['enabled'] ?? true;
		?>
		<tr data-provider-id="<?php echo esc_attr( $provider_id ); ?>" class="oauth-provider-row">
			<td class="oauth-drag-handle" style="cursor:move;text-align:center;">&#9776;</td>
			<td><strong><?php echo esc_html( $name ); ?></strong> <code>(<?php echo esc_html( $provider_id ); ?>)</code></td>
			<td><?php echo 'google' === $type ? esc_html__( 'Google', 'oauth-login' ) : esc_html__( 'Custom OAuth', 'oauth-login' ); ?></td>
			<td>
				<?php if ( $has_creds && $is_enabled ) : ?>
					<span style="color:green;">&#10003; <?php esc_html_e( 'Enabled', 'oauth-login' ); ?></span>
				<?php elseif ( $has_creds && ! $is_enabled ) : ?>
					<span style="color:gray;">&#9679; <?php esc_html_e( 'Disabled', 'oauth-login' ); ?></span>
				<?php else : ?>
					<span style="color:orange;">&#9888; <?php esc_html_e( 'Incomplete', 'oauth-login' ); ?></span>
				<?php endif; ?>
			</td>
			<td>
				<button type="button" class="button button-small oauth-edit-provider" data-provider="<?php echo esc_attr( $provider_id ); ?>">
					<?php esc_html_e( 'Edit', 'oauth-login' ); ?>
				</button>
				<?php if ( 'google' !== $provider_id ) : ?>
					<label style="margin-left:10px;">
						<input type="checkbox" name="wp_oauth_login_settings[providers][<?php echo esc_attr( $provider_id ); ?>][delete]" value="1" class="oauth-delete-provider" />
						<?php esc_html_e( 'Delete', 'oauth-login' ); ?>
					</label>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render the edit panel for a provider.
	 *
	 * @param string $provider_id Provider ID.
	 * @param array  $config      Provider configuration.
	 * @param bool   $is_new      Whether this is a new provider form.
	 *
	 * @return void
	 */
	public function render_provider_edit_panel( string $provider_id, array $config, bool $is_new = false ): void {
		$prefix    = $is_new ? 'wp_oauth_login_settings[new_provider]' : 'wp_oauth_login_settings[providers][' . $provider_id . ']';
		$type      = $config['type'] ?? 'custom';
		$is_google = 'google' === $type || 'google' === $provider_id;
		$name      = $config['name'] ?? '';

		$styles   = $config['button_styles'] ?? array();
		$mappings = $config['field_mappings'] ?? array();

		$default_styles = array(
			'background_color'       => '#ffffff',
			'text_color'             => '#3d4145',
			'border_color'           => '#ccced0',
			'border_width'           => '1px',
			'border_radius'          => '4px',
			'padding'                => '10px 15px',
			'font_size'              => '14px',
			'hover_background_color' => '#f7f7f7',
			'hover_text_color'       => '#3d4145',
			'hover_border_color'     => '#babcbe',
		);
		$styles         = wp_parse_args( $styles, $default_styles );

		$default_mappings = array(
			'email'        => 'email',
			'first_name'   => '',
			'last_name'    => '',
			'display_name' => '',
			'avatar'       => '',
		);
		if ( $is_google ) {
			$default_mappings = array(
				'email'        => 'email',
				'first_name'   => 'given_name',
				'last_name'    => 'family_name',
				'display_name' => 'name',
				'avatar'       => 'picture',
			);
		}
		$mappings = wp_parse_args( $mappings, $default_mappings );
		?>
		<div class="oauth-provider-edit-panel" id="oauth-panel-<?php echo esc_attr( $provider_id ); ?>" style="<?php echo $is_new ? '' : 'display:none;'; ?> margin-top:15px; padding:15px; border:1px solid #ccd0d4; background:#f9f9f9;">
			<h3>
				<?php
				if ( $is_new ) {
					echo esc_html( $is_google ? __( 'Add Google Provider', 'oauth-login' ) : __( 'Add Custom Provider', 'oauth-login' ) );
				} else {
					/* translators: %s: Provider name */
					printf( esc_html__( 'Edit: %s', 'oauth-login' ), esc_html( $name ?: $provider_id ) );
				}
				?>
			</h3>

			<?php if ( $is_new && $is_google ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>[slug]" value="google" />
			<?php endif; ?>

			<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>[type]" value="<?php echo esc_attr( $type ); ?>" />

			<table class="form-table" role="presentation">
				<!-- Enabled -->
				<tr>
					<th scope="row"><?php esc_html_e( 'Enabled', 'oauth-login' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $prefix ); ?>[enabled]" value="1" <?php checked( $config['enabled'] ?? true ); ?> />
							<?php esc_html_e( 'Enable this provider for login', 'oauth-login' ); ?>
						</label>
					</td>
				</tr>

				<!-- Provider Name -->
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Provider Name', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[name]" value="<?php echo esc_attr( $name ); ?>" class="regular-text" <?php echo $is_google ? 'placeholder="Google"' : 'placeholder="My Provider"'; ?> />
					</td>
				</tr>

				<!-- Client ID -->
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Client ID', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[client_id]" value="<?php echo esc_attr( $config['client_id'] ?? '' ); ?>" class="regular-text" autocomplete="off" />
						<?php if ( $is_google ) : ?>
							<p class="description">
								<?php
								echo wp_kses_post(
									sprintf(
										'%1s <a target="_blank" href="%2s">%3s</a>.',
										esc_html__( 'Get your Google OAuth Client ID from', 'oauth-login' ),
										'https://console.cloud.google.com/apis/credentials',
										'Google Cloud Console'
									)
								);
								?>
							</p>
						<?php endif; ?>
					</td>
				</tr>

				<!-- Client Secret -->
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Client Secret', 'oauth-login' ); ?></label></th>
					<td>
						<input type="password" name="<?php echo esc_attr( $prefix ); ?>[client_secret]" value="<?php echo esc_attr( self::decrypt_secret( $config['client_secret'] ?? '' ) ); ?>" class="regular-text" autocomplete="off" />
					</td>
				</tr>

				<?php if ( ! $is_google ) : ?>
					<!-- OAuth URLs (custom providers only) -->
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Authorize URL', 'oauth-login' ); ?></label></th>
						<td>
							<input type="url" name="<?php echo esc_attr( $prefix ); ?>[authorize_url]" value="<?php echo esc_attr( $config['authorize_url'] ?? '' ); ?>" class="regular-text" placeholder="https://provider.com/oauth/authorize" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Token URL', 'oauth-login' ); ?></label></th>
						<td>
							<input type="url" name="<?php echo esc_attr( $prefix ); ?>[token_url]" value="<?php echo esc_attr( $config['token_url'] ?? '' ); ?>" class="regular-text" placeholder="https://provider.com/oauth/token" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'User Info URL', 'oauth-login' ); ?></label></th>
						<td>
							<input type="url" name="<?php echo esc_attr( $prefix ); ?>[user_info_url]" value="<?php echo esc_attr( $config['user_info_url'] ?? '' ); ?>" class="regular-text" placeholder="https://provider.com/api/user" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Scopes', 'oauth-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $prefix ); ?>[scopes]" value="<?php echo esc_attr( $config['scopes'] ?? '' ); ?>" class="regular-text" placeholder="email,profile" />
							<p class="description"><?php esc_html_e( 'Comma-separated list of OAuth scopes.', 'oauth-login' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Callback URL', 'oauth-login' ); ?></label></th>
						<td>
							<input type="url" name="<?php echo esc_attr( $prefix ); ?>[callback_url]" value="<?php echo esc_attr( $config['callback_url'] ?? wp_login_url() ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'The URL the provider will redirect to after authentication. Default is your login page.', 'oauth-login' ); ?></p>
						</td>
					</tr>
				<?php endif; ?>
			</table>

			<!-- Button Styling Section -->
			<h4><?php esc_html_e( 'Button Appearance', 'oauth-login' ); ?></h4>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Button Text', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_text]" value="<?php echo esc_attr( $config['button_text'] ?? '' ); ?>" class="regular-text" placeholder="<?php /* translators: %s: Provider name */ printf( esc_attr__( 'Login with %s', 'oauth-login' ), esc_attr( $name ?: $provider_id ) ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Button Icon URL', 'oauth-login' ); ?></label></th>
					<td>
						<input type="url" name="<?php echo esc_attr( $prefix ); ?>[button_icon]" value="<?php echo esc_attr( $config['button_icon'] ?? '' ); ?>" class="regular-text" placeholder="https://example.com/icon.png" />
						<p class="description"><?php esc_html_e( 'URL to a custom icon image. Displayed on the left side of the button.', 'oauth-login' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Background Color', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][background_color]" value="<?php echo esc_attr( $styles['background_color'] ); ?>" class="small-text" placeholder="#ffffff" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Text Color', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][text_color]" value="<?php echo esc_attr( $styles['text_color'] ); ?>" class="small-text" placeholder="#3d4145" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Border Color', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][border_color]" value="<?php echo esc_attr( $styles['border_color'] ); ?>" class="small-text" placeholder="#ccced0" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Border Width', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][border_width]" value="<?php echo esc_attr( $styles['border_width'] ); ?>" class="small-text" placeholder="1px" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Border Radius', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][border_radius]" value="<?php echo esc_attr( $styles['border_radius'] ); ?>" class="small-text" placeholder="4px" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Padding', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][padding]" value="<?php echo esc_attr( $styles['padding'] ); ?>" class="small-text" placeholder="10px 15px" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Font Size', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][font_size]" value="<?php echo esc_attr( $styles['font_size'] ); ?>" class="small-text" placeholder="14px" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Hover Background', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][hover_background_color]" value="<?php echo esc_attr( $styles['hover_background_color'] ); ?>" class="small-text" placeholder="#f7f7f7" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Hover Text Color', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][hover_text_color]" value="<?php echo esc_attr( $styles['hover_text_color'] ); ?>" class="small-text" placeholder="#3d4145" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Hover Border Color', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="<?php echo esc_attr( $prefix ); ?>[button_styles][hover_border_color]" value="<?php echo esc_attr( $styles['hover_border_color'] ); ?>" class="small-text" placeholder="#babcbe" />
					</td>
				</tr>
			</table>

			<?php if ( ! $is_google ) : ?>
				<!-- Field Mappings Section -->
				<h4><?php esc_html_e( 'Field Mappings', 'oauth-login' ); ?></h4>
				<p class="description"><?php esc_html_e( 'Map provider response fields to user data. Use dot notation for nested fields (e.g., "user.email").', 'oauth-login' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Email', 'oauth-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $prefix ); ?>[field_mappings][email]" value="<?php echo esc_attr( $mappings['email'] ); ?>" class="regular-text" placeholder="email" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'First Name', 'oauth-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $prefix ); ?>[field_mappings][first_name]" value="<?php echo esc_attr( $mappings['first_name'] ); ?>" class="regular-text" placeholder="first_name" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Last Name', 'oauth-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $prefix ); ?>[field_mappings][last_name]" value="<?php echo esc_attr( $mappings['last_name'] ); ?>" class="regular-text" placeholder="last_name" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Display Name', 'oauth-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $prefix ); ?>[field_mappings][display_name]" value="<?php echo esc_attr( $mappings['display_name'] ); ?>" class="regular-text" placeholder="name" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Avatar', 'oauth-login' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $prefix ); ?>[field_mappings][avatar]" value="<?php echo esc_attr( $mappings['avatar'] ); ?>" class="regular-text" placeholder="avatar_url" />
						</td>
					</tr>
				</table>
			<?php endif; ?>

			<p>
				<button type="submit" class="button button-primary oauth-save-provider" data-provider="<?php echo esc_attr( $provider_id ); ?>">
					<?php esc_html_e( 'Save', 'oauth-login' ); ?>
				</button>
				<button type="submit" class="button oauth-save-close-provider">
					<?php echo esc_html__( 'Save', 'oauth-login' ) . ' &amp; ' . esc_html__( 'Close', 'oauth-login' ); ?>
				</button>
				<?php if ( ! $is_new && ! $is_google ) : ?>
					<button type="button" class="button oauth-test-provider" data-provider="<?php echo esc_attr( $provider_id ); ?>">
						<?php echo esc_html__( 'Test Configuration', 'oauth-login' ) . ' &amp; ' . esc_html__( 'Map Fields', 'oauth-login' ); ?>
					</button>
				<?php endif; ?>
				<button type="button" class="button oauth-close-panel" data-provider="<?php echo esc_attr( $provider_id ); ?>">
					<?php esc_html_e( 'Close', 'oauth-login' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the new custom provider form.
	 *
	 * @return void
	 */
	private function render_new_custom_provider_form(): void {
		?>
		<div class="oauth-provider-edit-panel" id="oauth-panel-new-custom" style="margin-top:15px; padding:15px; border:1px solid #ccd0d4; background:#f9f9f9;">
			<h3><?php esc_html_e( 'Add Custom OAuth Provider', 'oauth-login' ); ?></h3>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Provider Slug', 'oauth-login' ); ?></label></th>
					<td>
						<input type="text" name="wp_oauth_login_settings[new_provider][slug]" class="regular-text" pattern="[a-z0-9_-]+" placeholder="my-provider" />
						<p class="description"><?php esc_html_e( 'Unique identifier (lowercase, no spaces). Example: github, facebook', 'oauth-login' ); ?></p>
					</td>
				</tr>
			</table>
			<?php
			$this->render_provider_edit_panel(
				'new-custom',
				array(
					'type'          => 'custom',
					'name'          => '',
					'client_id'     => '',
					'client_secret' => '',
					'enabled'       => true,
				),
				true
			);
			?>
		</div>
		<?php
	}

	/**
	 * Get all provider configurations from settings.
	 *
	 * @return array All provider configs keyed by provider ID.
	 */
	private function get_all_provider_configs(): array {
		return $this->provider_settings['providers'] ?? array();
	}

	/**
	 * Check if a provider config exists.
	 *
	 * @param string $provider_id Provider ID.
	 *
	 * @return bool
	 */
	private function has_provider_config( string $provider_id ): bool {
		return isset( $this->provider_settings['providers'][ $provider_id ] );
	}

	/**
	 * Get default Google provider configuration.
	 *
	 * @return array Default Google config.
	 */
	private function get_default_google_config(): array {
		return array(
			'type'          => 'google',
			'name'          => 'Google',
			'client_id'     => '',
			'client_secret' => '',
			'enabled'       => true,
			'button_text'   => '',
			'button_icon'   => '',
			'button_styles' => array(
				'background_color'       => '#ffffff',
				'text_color'             => '#3d4145',
				'border_color'           => '#ccced0',
				'border_width'           => '1px',
				'border_radius'          => '4px',
				'padding'                => '10px 15px',
				'font_size'              => '14px',
				'hover_background_color' => '#f7f7f7',
				'hover_text_color'       => '#3d4145',
				'hover_border_color'     => '#babcbe',
			),
		);
	}

	/**
	 * Render the admin JavaScript for provider management.
	 *
	 * @return void
	 */
	private function render_provider_admin_script(): void {
		// phpcs:disable
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Reopen panel after save if requested via URL parameter.
			var urlParams = new URLSearchParams(window.location.search);
			var reopenPanel = urlParams.get('oauth_reopen_panel');
			if (reopenPanel) {
				$('#oauth-panel-' + reopenPanel).show();
			}

			// "Save" button sets the reopen field so the panel reopens after page reload.
			$('.oauth-save-provider').on('click', function() {
				$('#oauth-reopen-panel').val($(this).data('provider'));
			});

			// Toggle edit panels.
			$('.oauth-edit-provider').on('click', function() {
				var providerId = $(this).data('provider');
				$('#oauth-panel-' + providerId).slideToggle();
			});

			$('.oauth-close-panel').on('click', function() {
				$(this).closest('.oauth-provider-edit-panel').slideUp();
			});

			// Add Google provider.
			$('#oauth-add-google-provider').on('click', function() {
				$('#oauth-new-google-panel').slideToggle();
			});

			// Add custom provider.
			$('#oauth-add-custom-provider').on('click', function() {
				$('#oauth-new-custom-panel').slideToggle();
			});

			// Drag and drop ordering.
			if (typeof $.fn.sortable !== 'undefined') {
				$('#oauth-providers-list').sortable({
					handle: '.oauth-drag-handle',
					update: function() {
						var order = [];
						$('#oauth-providers-list tr.oauth-provider-row').each(function() {
							order.push($(this).data('provider-id'));
						});
						$('#oauth-provider-order').val(order.join(','));
					}
				});
			}

			// Test provider configuration.
			var testBtnLabel = <?php echo wp_json_encode( __( 'Test Configuration & Map Fields', 'oauth-login' ) ); ?>;
			$('.oauth-test-provider').on('click', function() {
				var providerId = $(this).data('provider');
				var $btn = $(this);
				$btn.prop('disabled', true).text(<?php echo wp_json_encode( __( 'Initiating...', 'oauth-login' ) ); ?>);

				$.post(ajaxurl, {
					action: 'oauth_test_login_init',
					nonce: <?php echo wp_json_encode( wp_create_nonce( 'oauth_test_login' ) ); ?>,
					provider_id: providerId
				}, function(response) {
					$btn.prop('disabled', false).text(testBtnLabel);
					if (response.success && response.data.auth_url) {
						window.open(response.data.auth_url, 'oauth_test_' + providerId, 'width=600,height=700');
					} else {
						alert(response.data ? response.data.message : <?php echo wp_json_encode( __( 'Failed to initiate test login.', 'oauth-login' ) ); ?>);
					}
				}).fail(function() {
					$btn.prop('disabled', false).text(testBtnLabel);
					alert(<?php echo wp_json_encode( __( 'Request failed. Please try again.', 'oauth-login' ) ); ?>);
				});
			});

			// Listen for field mapping results from test login popup.
			var trustedOrigin = <?php echo wp_json_encode( untrailingslashit( site_url() ) ); ?>;
			window.addEventListener('message', function(event) {
				if (event.origin !== trustedOrigin) return;
				if (!event.data || event.data.type !== 'oauth_test_mappings') return;

				var providerId = event.data.provider_id;
				var mappings = event.data.mappings;
				var prefix = 'wp_oauth_login_settings[providers][' + providerId + '][field_mappings]';

				// Update the field mapping inputs in the edit panel.
				$.each(mappings, function(field, path) {
					$('input[name="' + prefix + '[' + field + ']"]').val(path);
				});

				// Flash the panel to indicate update.
				var $panel = $('#oauth-panel-' + providerId);
				$panel.css('background-color', '#e7f5e7');
				setTimeout(function() { $panel.css('background-color', '#f9f9f9'); }, 1500);
			});
		});
		</script>
		<?php
		// phpcs:enable
	}

	/**
	 * Sanitize legacy Google login settings.
	 *
	 * @param mixed $input Raw input (may be array, null, or other type).
	 *
	 * @return array Sanitized input.
	 */
	public function sanitize_legacy_settings( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$allowed_keys = array( 'client_id', 'client_secret', 'registration_enabled', 'one_tap_login', 'one_tap_login_screen', 'whitelisted_domains', 'login_message' );

		$sanitized = array();
		foreach ( $allowed_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				if ( 'login_message' === $key ) {
					$sanitized[ $key ] = wp_kses_post( $input[ $key ] );
				} else {
					$sanitized[ $key ] = sanitize_text_field( $input[ $key ] );
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param mixed $input Raw input (may be array, null, or other type).
	 *
	 * @return array Sanitized input.
	 */
	public function sanitize_settings( $input ): array {
		// Handle cases where WordPress passes null or non-array input.
		if ( ! is_array( $input ) ) {
			// Return empty array - cannot call get_option here as it causes infinite recursion.
			return array();
		}

		try {
			// NOTE: Legacy settings handling removed from here to prevent infinite recursion.
			// Legacy settings (wp_google_login_settings) are now handled by a separate sanitize callback.

			$sanitized = array(
				'version'        => '2.2.1',
				'providers'      => array(),
				'provider_order' => array(),
			);

			// Sanitize provider settings.
			if ( isset( $input['providers'] ) && is_array( $input['providers'] ) ) {
				foreach ( $input['providers'] as $provider_id => $settings ) {
					$provider_id = sanitize_key( $provider_id );

					// Skip if marked for deletion.
					if ( ! empty( $settings['delete'] ) ) {
						continue;
					}

					$sanitized['providers'][ $provider_id ] = $this->sanitize_provider( $settings );
				}
			}

			// Handle new provider addition.
			if ( isset( $input['new_provider'] ) && ! empty( $input['new_provider']['slug'] ) ) {
				$new_slug = sanitize_key( $input['new_provider']['slug'] );

				if ( ! empty( $new_slug ) && ! isset( $sanitized['providers'][ $new_slug ] ) ) {
					$sanitized['providers'][ $new_slug ] = $this->sanitize_provider( $input['new_provider'] );
				}
			}

			// Sanitize provider order.
			if ( ! empty( $input['provider_order'] ) ) {
				$order = is_array( $input['provider_order'] )
					? $input['provider_order']
					: array_filter( array_map( 'trim', explode( ',', $input['provider_order'] ) ) );

				$sanitized['provider_order'] = array_map( 'sanitize_key', $order );
			} else {
				$sanitized['provider_order'] = array_keys( $sanitized['providers'] );
			}

			return $sanitized;
		} catch ( \Exception $e ) {
			// Return empty array - cannot call get_option here as it causes infinite recursion.
			return array();
		}
	}

	/**
	 * Sanitize a single provider's settings.
	 *
	 * @param array $provider Raw provider data.
	 *
	 * @return array Sanitized provider data.
	 */
	private function sanitize_provider( array $provider ): array {
		// Client secret needs special handling - don't use sanitize_text_field as it corrupts the secret.
		$client_secret = $provider['client_secret'] ?? '';

		// Safely handle slashing - wp_unslash may not always be available.
		if ( is_string( $client_secret ) && function_exists( 'wp_unslash' ) ) {
			$client_secret = wp_unslash( $client_secret );
		} elseif ( is_string( $client_secret ) ) {
			// Fallback: manually remove slashes if wp_unslash doesn't exist.
			$client_secret = stripslashes( $client_secret );
		} else {
			$client_secret = '';
		}

		$sanitized = array(
			'type'          => sanitize_key( $provider['type'] ?? 'custom' ),
			'name'          => sanitize_text_field( $provider['name'] ?? '' ),
			'enabled'       => ! empty( $provider['enabled'] ),
			'client_id'     => sanitize_text_field( $provider['client_id'] ?? '' ),
			'client_secret' => $this->encrypt_secret( $client_secret ),
			'button_text'   => sanitize_text_field( $provider['button_text'] ?? '' ),
			'button_icon'   => esc_url_raw( $provider['button_icon'] ?? '' ),
			'button_styles' => $this->sanitize_button_styles( $provider['button_styles'] ?? array() ),
		);

		// Custom provider fields.
		if ( 'google' !== ( $provider['type'] ?? 'custom' ) ) {
			$sanitized['authorize_url']  = $this->sanitize_external_url( $provider['authorize_url'] ?? '' );
			$sanitized['token_url']      = $this->sanitize_external_url( $provider['token_url'] ?? '' );
			$sanitized['user_info_url']  = $this->sanitize_external_url( $provider['user_info_url'] ?? '' );
			$sanitized['scopes']         = sanitize_text_field( $provider['scopes'] ?? '' );
			$sanitized['callback_url']   = esc_url_raw( $provider['callback_url'] ?? '' );
			$sanitized['field_mappings'] = $this->sanitize_field_mappings( $provider['field_mappings'] ?? array() );
		}

		return $sanitized;
	}

	/**
	 * Sanitize button styles.
	 *
	 * @param array $styles Raw styles.
	 *
	 * @return array Sanitized styles.
	 */
	private function sanitize_button_styles( array $styles ): array {
		$color_keys = array(
			'background_color',
			'text_color',
			'border_color',
			'hover_background_color',
			'hover_text_color',
			'hover_border_color',
		);

		$dimension_keys = array(
			'border_width',
			'border_radius',
			'padding',
			'font_size',
		);

		$sanitized = array();

		foreach ( $color_keys as $key ) {
			if ( isset( $styles[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_css_color( $styles[ $key ] );
			}
		}

		foreach ( $dimension_keys as $key ) {
			if ( isset( $styles[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_css_dimension( $styles[ $key ] );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize a CSS color value.
	 *
	 * Allows hex colors (#fff, #ffffff, #ffffffff) and
	 * rgb/rgba functional notation only.
	 *
	 * @param string $value Raw color value.
	 *
	 * @return string Sanitized color or empty string.
	 */
	private function sanitize_css_color( string $value ): string {
		$value = trim( $value );

		// Hex colors: #fff, #ffffff, #ffffffff.
		if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) ) {
			return $value;
		}

		// rgb()/rgba() with numeric values only.
		if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+))?\s*\)$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitize a CSS dimension value.
	 *
	 * Allows values like "4px", "10px 15px", "1.5em", "50%".
	 *
	 * @param string $value Raw dimension value.
	 *
	 * @return string Sanitized dimension or empty string.
	 */
	private function sanitize_css_dimension( string $value ): string {
		$value = trim( $value );

		// One or more space-separated dimension tokens.
		if ( preg_match( '/^[\d.]+(px|em|rem|%)(\s+[\d.]+(px|em|rem|%))*$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * Sanitize an external URL for use as an OAuth endpoint.
	 *
	 * Requires HTTPS and rejects private/reserved IP ranges
	 * to prevent Server-Side Request Forgery (SSRF).
	 *
	 * @param string $url Raw URL.
	 *
	 * @return string Sanitized URL or empty string if invalid.
	 */
	private function sanitize_external_url( string $url ): string {
		$url = esc_url_raw( $url );

		if ( empty( $url ) ) {
			return '';
		}

		$parsed = wp_parse_url( $url );

		// Require HTTPS scheme.
		if ( empty( $parsed['scheme'] ) || 'https' !== $parsed['scheme'] ) {
			return '';
		}

		if ( empty( $parsed['host'] ) ) {
			return '';
		}

		$host = strtolower( $parsed['host'] );

		// Reject localhost and common loopback names.
		$blocked_hosts = array( 'localhost', '127.0.0.1', '0.0.0.0', '::1', '[::1]' );
		if ( in_array( $host, $blocked_hosts, true ) ) {
			return '';
		}

		// Reject IP addresses in private/reserved ranges (without DNS lookup to avoid blocking).
		// Note: This does not prevent hostnames that resolve to private IPs, but avoids
		// the performance/timeout issues of DNS lookups during form submission.
		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			if ( ! filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return '';
			}
		}

		return $url;
	}

	/**
	 * Encrypt a secret value for storage.
	 *
	 * Uses AES-256-CBC with a key derived from WordPress AUTH_KEY salt.
	 * If the value is already encrypted (prefixed with 'enc:'), it is returned as-is.
	 * If encryption is unavailable, falls back to storing the raw value.
	 *
	 * @param string $value Plaintext secret.
	 *
	 * @return string Encrypted secret prefixed with 'enc:' or raw value.
	 */
	private function encrypt_secret( string $value ): string {
		if ( empty( $value ) ) {
			return '';
		}

		// Already encrypted — don't double-encrypt.
		if ( str_starts_with( $value, 'enc:' ) ) {
			return $value;
		}

		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return $value;
		}

		try {
			$key = hash( 'sha256', wp_salt( 'auth' ), true );
			$iv  = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );

			$encrypted = openssl_encrypt( $value, 'aes-256-cbc', $key, 0, $iv );

			if ( false === $encrypted ) {
				return $value;
			}

			return 'enc:' . base64_encode( $encrypted ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		} catch ( \Exception $e ) {
			// Catch any encryption errors and return the raw value.
			return $value;
		}
	}

	/**
	 * Decrypt a stored secret value.
	 *
	 * @param string $value Encrypted secret (prefixed with 'enc:') or plaintext.
	 *
	 * @return string Decrypted plaintext secret.
	 */
	public static function decrypt_secret( string $value ): string {
		if ( empty( $value ) ) {
			return '';
		}

		// Not encrypted — return as-is (backward compatibility).
		if ( ! str_starts_with( $value, 'enc:' ) ) {
			return $value;
		}

		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return '';
		}

		$key       = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv        = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
		$encrypted = base64_decode( substr( $value, 4 ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		$decrypted = openssl_decrypt( $encrypted, 'aes-256-cbc', $key, 0, $iv );

		return false !== $decrypted ? $decrypted : '';
	}

	/**
	 * Sanitize field mappings.
	 *
	 * @param array $mappings Raw mappings.
	 *
	 * @return array Sanitized mappings.
	 */
	private function sanitize_field_mappings( array $mappings ): array {
		$allowed_keys = array( 'email', 'first_name', 'last_name', 'display_name', 'avatar' );
		$sanitized    = array();

		foreach ( $allowed_keys as $key ) {
			if ( isset( $mappings[ $key ] ) ) {
				// Allow dot notation for nested fields, sanitize each part.
				$parts             = explode( '.', $mappings[ $key ] );
				$parts             = array_map( 'sanitize_text_field', $parts );
				$sanitized[ $key ] = implode( '.', $parts );
			}
		}

		return $sanitized;
	}

	/**
	 * Render client ID field.
	 *
	 * @return void
	 */
	public function client_id_field(): void {
		?>
		<input type='text' name='wp_google_login_settings[client_id]' id="client-id" value='<?php echo esc_attr( $this->client_id ); ?>' autocomplete="off" <?php $this->disabled( 'client_id' ); ?> />
		<p class="description">
			<?php
			echo wp_kses_post(
				sprintf(
					'%1s <a target="_blank" href="%2s">%3s</a>.',
					esc_html__( 'Get your Google OAuth Client ID from', 'oauth-login' ),
					'https://console.cloud.google.com/apis/credentials',
					'Google Cloud Console'
				)
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render client secret field.
	 *
	 * @return void
	 */
	public function client_secret_field(): void {
		?>
		<input type='password' name='wp_google_login_settings[client_secret]' id="client-secret" value='<?php echo esc_attr( $this->client_secret ); ?>' autocomplete="off" <?php $this->disabled( 'client_secret' ); ?> />
		<?php
	}

	/**
	 * User registration field.
	 *
	 * This will tell us whether or not to create the user
	 * if the user does not exist on WP application.
	 *
	 * This is irrespective of registration flag present in Settings > General
	 *
	 * @return void
	 */
	public function user_registration(): void {
		?>
		<label style='display:block;margin-top:6px;'><input <?php $this->disabled( 'registration_enabled' ); ?> type='checkbox'
															name='wp_google_login_settings[registration_enabled]'
															id="user-registration" <?php echo esc_attr( checked( $this->registration_enabled ) ); ?>
															value='1'>
			<?php esc_html_e( 'Create a new user account if it does not exist already', 'oauth-login' ); ?>
		</label>
		<p class="<?php echo esc_attr( 'error-message' ); ?>">
			<?php
			echo wp_kses_post(
				sprintf(
				/* translators: %1s will be replaced by page link */
					__( 'If this setting is checked, a new user will be created even if <a target="_blank" href="%1s">membership setting</a> is off.', 'oauth-login' ),
					is_multisite() ? 'network/settings.php' : 'options-general.php'
				)
			);
			?>
		</p>
		<?php
	}

	/**
	 * Toggle One Tap Login functionality.
	 *
	 * @return void
	 */
	public function one_tap_login(): void {
		?>
		<label style='display:block;margin-top:6px;'><input <?php $this->disabled( 'one_tap_login' ); ?>
					type='checkbox'
					name='wp_google_login_settings[one_tap_login]'
					id="one-tap-login" <?php echo esc_attr( checked( $this->one_tap_login ) ); ?>
					value='1'>
			<?php esc_html_e( 'Enable Google One Tap Login', 'oauth-login' ); ?>
		</label>
		<p class="<?php echo esc_attr( 'error-message' ); ?>">
			<?php esc_html_e( 'Warning: One Tap login is more convenient, but it bypasses two-factor authentication (2FA).', 'oauth-login' ); ?>
		</p>
		<?php
	}

	/**
	 * One tap login screens.
	 *
	 * It can be enabled only for wp-login.php OR sitewide.
	 *
	 * @return void
	 */
	public function one_tap_login_screens(): void {
		$default = $this->one_tap_login_screen ?? '';
		?>
		<label style='display:block;margin-top:6px;'><input <?php $this->disabled( 'one_tap_login' ); ?>
					type='radio'
					name='wp_google_login_settings[one_tap_login_screen]'
					id="one-tap-login-screen-login" <?php echo esc_attr( checked( $this->one_tap_login_screen, $default ) ); ?>
					value='login'>
			<?php esc_html_e( 'Enable One Tap Login Only on Login Screen', 'oauth-login' ); ?>
		</label>
		<label style='display:block;margin-top:6px;'><input <?php $this->disabled( 'one_tap_login' ); ?>
					type='radio'
					name='wp_google_login_settings[one_tap_login_screen]'
					id="one-tap-login-screen-sitewide" <?php echo esc_attr( checked( $this->one_tap_login_screen, 'sitewide' ) ); ?>
					value='sitewide'>
			<?php esc_html_e( 'Enable One Tap Login Site-wide', 'oauth-login' ); ?>
		</label>
		<?php
		// phpcs:disable
		?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                var toggle = function () {
                    var enabled = jQuery("#one-tap-login").is(":checked");
                    var tr_elem = jQuery("#one-tap-login-screen-login").parents("tr");
                    if (enabled) {
                        tr_elem.show();
                        return;
                    }

                    tr_elem.hide();
                };
                jQuery("#one-tap-login").on('change', toggle);
                toggle();
            });
        </script>
		<?php
		// phpcs:enable
	}

	/**
	 * Whitelisted domains for registration.
	 *
	 * Only emails belonging to these domains would be preferred
	 * for registration.
	 *
	 * If left blank, all domains would be allowed.
	 *
	 * @return void
	 */
	public function whitelisted_domains(): void {
		?>
		<input <?php $this->disabled( 'whitelisted_domains' ); ?> type='text' name='wp_google_login_settings[whitelisted_domains]' id="whitelisted-domains" value='<?php echo esc_attr( $this->whitelisted_domains ); ?>' autocomplete="off" />
		<p class="description">
			<?php echo esc_html( __( 'Add each domain comma separated', 'oauth-login' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Render the login message setting field.
	 *
	 * @return void
	 */
	public function login_message_field(): void {
		$value = $this->login_message ?? '';
		?>
		<textarea <?php $this->disabled( 'login_message' ); ?> name='wp_google_login_settings[login_message]' id="login-message" rows="6" cols="60" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'This HTML message will be displayed on the login page. Safe HTML tags are allowed.', 'oauth-login' ); ?>
		</p>
		<?php
	}

	/**
	 * Add settings sub-menu page in admin menu.
	 *
	 * @return void
	 */
	public function settings_page(): void {
		add_options_page(
			__( 'OAuth Login Settings', 'oauth-login' ),
			__( 'OAuth Login', 'oauth-login' ),
			'manage_options',
			'oauth-login',
			array( $this, 'output' )
		);
	}

	/**
	 * Output the plugin settings.
	 *
	 * @return void
	 */
	public function output(): void {
		?>
		<div class="wrap">
		<h1><?php esc_html_e( 'OAuth Login Settings', 'oauth-login' ); ?></h1>
		<form action='options.php' method='post'>
			<?php
			settings_fields( 'wp_oauth_login' );
			do_settings_sections( 'oauth-login' );

			// Include hidden fields for legacy settings so they are submitted with the form.
			?>
			<input type="hidden" name="wp_google_login_settings[client_id]" value="<?php echo esc_attr( $this->client_id ); ?>" />
			<input type="hidden" name="wp_google_login_settings[client_secret]" value="<?php echo esc_attr( $this->client_secret ); ?>" />
			<?php
			submit_button();
			?>
		</form>
		</div>
		<?php
	}

	/**
	 * Outputs the disabled attribute if field needs to
	 * be disabled.
	 *
	 * @param string $id Input ID.
	 *
	 * @return void
	 */
	private function disabled( string $id ): void {
		if ( empty( $id ) ) {
			return;
		}

		$constant_name = array_search( $id, $this->getters, true );

		if ( false !== $constant_name ) {
			if ( defined( $constant_name ) ) {
				echo esc_attr( 'disabled="disabled"' );
			}
		}
	}
}
