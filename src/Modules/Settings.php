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
	private array $provider_settings = [];

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
	private $getters = [
		'WP_GOOGLE_LOGIN_CLIENT_ID'         => 'client_id',
		'WP_GOOGLE_LOGIN_SECRET'            => 'client_secret',
		'WP_GOOGLE_LOGIN_USER_REGISTRATION' => 'registration_enabled',
		'WP_GOOGLE_LOGIN_WHITELIST_DOMAINS' => 'whitelisted_domains',
		'WP_GOOGLE_ONE_TAP_LOGIN'           => 'one_tap_login',
		'WP_GOOGLE_ONE_TAP_LOGIN_SCREEN'    => 'one_tap_login_screen',
	];

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
		$this->options           = get_option( 'wp_google_login_settings', [] );
		$this->provider_settings = get_option( 'wp_oauth_login_settings', [] );

		/**
		 * Actions.
		 */
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'settings_page' ] );

		/**
		 * Filters.
		 */
		// Add filters here.
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
		return $this->provider_settings['providers'][ $provider_id ] ?? [];
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
		// Register both old (for backward compat) and new settings.
		register_setting( 'wp_google_login', 'wp_google_login_settings' );
		register_setting(
			'wp_oauth_login',
			'wp_oauth_login_settings',
			[
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
			]
		);

		// Register dynamic provider sections.
		$this->register_provider_settings();

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
			[ $this, 'user_registration' ],
			'oauth-login',
			'wp_oauth_general_section',
			[ 'label_for' => 'user-registration' ]
		);

		add_settings_field(
			'wp_google_whitelisted_domain',
			__( 'Whitelisted Domains', 'oauth-login' ),
			[ $this, 'whitelisted_domains' ],
			'oauth-login',
			'wp_oauth_general_section',
			[ 'label_for' => 'whitelisted-domains' ]
		);

		// Google One Tap section (Google-specific).
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
			[ $this, 'one_tap_login' ],
			'oauth-login',
			'wp_google_one_tap_section',
			[ 'label_for' => 'one-tap-login' ]
		);

		add_settings_field(
			'wp_google_one_tap_login_screen',
			__( 'One Tap Login Locations', 'oauth-login' ),
			[ $this, 'one_tap_login_screens' ],
			'oauth-login',
			'wp_google_one_tap_section',
			[ 'label_for' => 'one-tap-login-screen' ]
		);

		// Custom providers section.
		add_settings_section(
			'wp_oauth_custom_providers_section',
			__( 'Custom OAuth Providers', 'oauth-login' ),
			function () {
				echo wp_kses_post(
					'<p>' . esc_html__( 'Add custom OAuth 2.0 providers. These providers will appear in the login form.', 'oauth-login' ) . '</p>'
				);
			},
			'oauth-login'
		);

		add_settings_field(
			'wp_oauth_custom_providers',
			__( 'Add Custom Provider', 'oauth-login' ),
			[ $this, 'render_custom_provider_form' ],
			'oauth-login',
			'wp_oauth_custom_providers_section'
		);
	}

	/**
	 * Register settings for each provider in the registry.
	 *
	 * @return void
	 */
	public function register_provider_settings(): void {
		if ( null === $this->registry ) {
			// Fallback: register only Google section if no registry.
			$this->register_legacy_google_section();
			return;
		}

		$providers = $this->registry->get_all();

		if ( empty( $providers ) ) {
			return;
		}

		foreach ( $providers as $provider_id => $provider ) {
			$section_id = 'wp_oauth_provider_' . $provider_id;

			add_settings_section(
				$section_id,
				/* translators: %s: Provider name */
				sprintf( __( '%s Settings', 'oauth-login' ), $provider->get_provider_name() ),
				function () use ( $provider ) {
					echo wp_kses_post(
						'<p>' . sprintf(
							/* translators: %s: Provider name */
							esc_html__( 'Configure %s OAuth 2.0 authentication.', 'oauth-login' ),
							esc_html( $provider->get_provider_name() )
						) . '</p>'
					);
				},
				'oauth-login'
			);

			add_settings_field(
				'wp_oauth_' . $provider_id . '_enabled',
				__( 'Enabled', 'oauth-login' ),
				[ $this, 'render_provider_enabled_field' ],
				'oauth-login',
				$section_id,
				[
					'provider_id' => $provider_id,
					'label_for'   => 'provider-' . $provider_id . '-enabled',
				]
			);

			add_settings_field(
				'wp_oauth_' . $provider_id . '_client_id',
				__( 'Client ID', 'oauth-login' ),
				[ $this, 'render_provider_client_id_field' ],
				'oauth-login',
				$section_id,
				[
					'provider_id' => $provider_id,
					'label_for'   => 'provider-' . $provider_id . '-client-id',
				]
			);

			add_settings_field(
				'wp_oauth_' . $provider_id . '_client_secret',
				__( 'Client Secret', 'oauth-login' ),
				[ $this, 'render_provider_client_secret_field' ],
				'oauth-login',
				$section_id,
				[
					'provider_id' => $provider_id,
					'label_for'   => 'provider-' . $provider_id . '-client-secret',
				]
			);
		}
	}

	/**
	 * Register legacy Google section for backward compatibility.
	 *
	 * @return void
	 */
	private function register_legacy_google_section(): void {
		add_settings_section(
			'wp_google_login_section',
			__( 'Google Settings', 'oauth-login' ),
			function () {
				echo wp_kses_post(
					'<p>' . esc_html__( 'Configure Google OAuth 2.0 authentication.', 'oauth-login' ) . '</p>'
				);
			},
			'oauth-login'
		);

		add_settings_field(
			'wp_google_login_client_id',
			__( 'Client ID', 'oauth-login' ),
			[ $this, 'client_id_field' ],
			'oauth-login',
			'wp_google_login_section',
			[ 'label_for' => 'client-id' ]
		);

		add_settings_field(
			'wp_google_login_client_secret',
			__( 'Client Secret', 'oauth-login' ),
			[ $this, 'client_secret_field' ],
			'oauth-login',
			'wp_google_login_section',
			[ 'label_for' => 'client-secret' ]
		);
	}

	/**
	 * Render provider enabled checkbox field.
	 *
	 * @param array $args Field arguments.
	 *
	 * @return void
	 */
	public function render_provider_enabled_field( array $args ): void {
		$provider_id = $args['provider_id'];
		$enabled     = $this->get_provider_setting( $provider_id, 'enabled', true );
		$field_id    = 'provider-' . $provider_id . '-enabled';
		$field_name  = 'wp_oauth_login_settings[providers][' . $provider_id . '][enabled]';
		?>
		<label style='display:block;margin-top:6px;'>
			<input type='checkbox'
				name='<?php echo esc_attr( $field_name ); ?>'
				id='<?php echo esc_attr( $field_id ); ?>'
				<?php checked( $enabled ); ?>
				value='1'>
			<?php esc_html_e( 'Enable this provider for login', 'oauth-login' ); ?>
		</label>
		<?php
	}

	/**
	 * Render provider client ID field.
	 *
	 * @param array $args Field arguments.
	 *
	 * @return void
	 */
	public function render_provider_client_id_field( array $args ): void {
		$provider_id = $args['provider_id'];
		$value       = $this->get_provider_setting( $provider_id, 'client_id', '' );
		$field_id    = 'provider-' . $provider_id . '-client-id';
		$field_name  = 'wp_oauth_login_settings[providers][' . $provider_id . '][client_id]';

		// Check for legacy Google settings.
		if ( 'google' === $provider_id && empty( $value ) ) {
			$value = $this->client_id ?? '';
		}
		?>
		<input type='text'
			name='<?php echo esc_attr( $field_name ); ?>'
			id='<?php echo esc_attr( $field_id ); ?>'
			value='<?php echo esc_attr( $value ); ?>'
			class='regular-text'
			autocomplete='off' />
		<?php
	}

	/**
	 * Render provider client secret field.
	 *
	 * @param array $args Field arguments.
	 *
	 * @return void
	 */
	public function render_provider_client_secret_field( array $args ): void {
		$provider_id = $args['provider_id'];
		$value       = $this->get_provider_setting( $provider_id, 'client_secret', '' );
		$field_id    = 'provider-' . $provider_id . '-client-secret';
		$field_name  = 'wp_oauth_login_settings[providers][' . $provider_id . '][client_secret]';

		// Check for legacy Google settings.
		if ( 'google' === $provider_id && empty( $value ) ) {
			$value = $this->client_secret ?? '';
		}
		?>
		<input type='password'
			name='<?php echo esc_attr( $field_name ); ?>'
			id='<?php echo esc_attr( $field_id ); ?>'
			value='<?php echo esc_attr( $value ); ?>'
			class='regular-text'
			autocomplete='off' />
		<?php
	}

	/**
	 * Render the custom provider form.
	 *
	 * @return void
	 */
	public function render_custom_provider_form(): void {
		$custom_providers = $this->provider_settings['custom_providers'] ?? [];
		?>
		<div id="oauth-custom-providers">
			<?php if ( ! empty( $custom_providers ) ) : ?>
				<table class="widefat" style="margin-bottom: 20px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Provider', 'oauth-login' ); ?></th>
							<th><?php esc_html_e( 'Slug', 'oauth-login' ); ?></th>
							<th><?php esc_html_e( 'Status', 'oauth-login' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'oauth-login' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $custom_providers as $slug => $provider ) : ?>
							<tr>
								<td><?php echo esc_html( $provider['name'] ?? $slug ); ?></td>
								<td><code><?php echo esc_html( $slug ); ?></code></td>
								<td>
									<?php if ( ! empty( $provider['client_id'] ) && ! empty( $provider['client_secret'] ) ) : ?>
										<span style="color: green;">✓ <?php esc_html_e( 'Configured', 'oauth-login' ); ?></span>
									<?php else : ?>
										<span style="color: orange;">⚠ <?php esc_html_e( 'Incomplete', 'oauth-login' ); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<label>
										<input type="checkbox"
											name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][delete]"
											value="1">
										<?php esc_html_e( 'Delete', 'oauth-login' ); ?>
									</label>
								</td>
							</tr>
							<!-- Hidden fields to preserve existing data -->
							<input type="hidden" name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][name]" value="<?php echo esc_attr( $provider['name'] ?? '' ); ?>">
							<input type="hidden" name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][authorize_url]" value="<?php echo esc_attr( $provider['authorize_url'] ?? '' ); ?>">
							<input type="hidden" name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][token_url]" value="<?php echo esc_attr( $provider['token_url'] ?? '' ); ?>">
							<input type="hidden" name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][user_info_url]" value="<?php echo esc_attr( $provider['user_info_url'] ?? '' ); ?>">
							<input type="hidden" name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][scopes]" value="<?php echo esc_attr( $provider['scopes'] ?? '' ); ?>">
							<input type="hidden" name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][client_id]" value="<?php echo esc_attr( $provider['client_id'] ?? '' ); ?>">
							<input type="hidden" name="wp_oauth_login_settings[custom_providers][<?php echo esc_attr( $slug ); ?>][client_secret]" value="<?php echo esc_attr( $provider['client_secret'] ?? '' ); ?>">
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<details style="margin-top: 10px;">
				<summary style="cursor: pointer; font-weight: bold;">
					<?php esc_html_e( 'Add New Custom Provider', 'oauth-login' ); ?>
				</summary>
				<table class="form-table" role="presentation" style="margin-top: 10px;">
					<tr>
						<th scope="row">
							<label for="new-provider-slug"><?php esc_html_e( 'Provider Slug', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="text" id="new-provider-slug" name="wp_oauth_login_settings[new_provider][slug]" class="regular-text" pattern="[a-z0-9_-]+" placeholder="my-provider">
							<p class="description"><?php esc_html_e( 'Unique identifier (lowercase, no spaces). Example: github, facebook', 'oauth-login' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="new-provider-name"><?php esc_html_e( 'Provider Name', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="text" id="new-provider-name" name="wp_oauth_login_settings[new_provider][name]" class="regular-text" placeholder="My Provider">
							<p class="description"><?php esc_html_e( 'Display name shown on login button.', 'oauth-login' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="new-provider-authorize-url"><?php esc_html_e( 'Authorize URL', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="url" id="new-provider-authorize-url" name="wp_oauth_login_settings[new_provider][authorize_url]" class="regular-text" placeholder="https://provider.com/oauth/authorize">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="new-provider-token-url"><?php esc_html_e( 'Token URL', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="url" id="new-provider-token-url" name="wp_oauth_login_settings[new_provider][token_url]" class="regular-text" placeholder="https://provider.com/oauth/token">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="new-provider-user-info-url"><?php esc_html_e( 'User Info URL', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="url" id="new-provider-user-info-url" name="wp_oauth_login_settings[new_provider][user_info_url]" class="regular-text" placeholder="https://provider.com/api/user">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="new-provider-scopes"><?php esc_html_e( 'Scopes', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="text" id="new-provider-scopes" name="wp_oauth_login_settings[new_provider][scopes]" class="regular-text" placeholder="email,profile">
							<p class="description"><?php esc_html_e( 'Comma-separated list of OAuth scopes.', 'oauth-login' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="new-provider-client-id"><?php esc_html_e( 'Client ID', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="text" id="new-provider-client-id" name="wp_oauth_login_settings[new_provider][client_id]" class="regular-text" autocomplete="off">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="new-provider-client-secret"><?php esc_html_e( 'Client Secret', 'oauth-login' ); ?></label>
						</th>
						<td>
							<input type="password" id="new-provider-client-secret" name="wp_oauth_login_settings[new_provider][client_secret]" class="regular-text" autocomplete="off">
						</td>
					</tr>
				</table>
			</details>
		</div>
		<?php
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input.
	 *
	 * @return array Sanitized input.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = [];

		// Sanitize built-in provider settings.
		if ( isset( $input['providers'] ) && is_array( $input['providers'] ) ) {
			foreach ( $input['providers'] as $provider_id => $settings ) {
				$provider_id = sanitize_key( $provider_id );

				$sanitized['providers'][ $provider_id ] = [
					'enabled'       => ! empty( $settings['enabled'] ),
					'client_id'     => sanitize_text_field( $settings['client_id'] ?? '' ),
					'client_secret' => sanitize_text_field( $settings['client_secret'] ?? '' ),
				];
			}
		}

		// Sanitize custom providers.
		if ( isset( $input['custom_providers'] ) && is_array( $input['custom_providers'] ) ) {
			foreach ( $input['custom_providers'] as $slug => $provider ) {
				$slug = sanitize_key( $slug );

				// Skip if marked for deletion.
				if ( ! empty( $provider['delete'] ) ) {
					continue;
				}

				$sanitized['custom_providers'][ $slug ] = $this->sanitize_custom_provider( $provider );
			}
		}

		// Handle new provider addition.
		if ( isset( $input['new_provider'] ) && ! empty( $input['new_provider']['slug'] ) ) {
			$new_slug = sanitize_key( $input['new_provider']['slug'] );

			if ( ! empty( $new_slug ) ) {
				$sanitized['custom_providers'][ $new_slug ] = $this->sanitize_custom_provider( $input['new_provider'] );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize a single custom provider's settings.
	 *
	 * @param array $provider Raw provider data.
	 *
	 * @return array Sanitized provider data.
	 */
	private function sanitize_custom_provider( array $provider ): array {
		return [
			'name'          => sanitize_text_field( $provider['name'] ?? '' ),
			'authorize_url' => esc_url_raw( $provider['authorize_url'] ?? '' ),
			'token_url'     => esc_url_raw( $provider['token_url'] ?? '' ),
			'user_info_url' => esc_url_raw( $provider['user_info_url'] ?? '' ),
			'scopes'        => sanitize_text_field( $provider['scopes'] ?? '' ),
			'client_id'     => sanitize_text_field( $provider['client_id'] ?? '' ),
			'client_secret' => sanitize_text_field( $provider['client_secret'] ?? '' ),
		];
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
			[ $this, 'output' ]
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
			// Include both settings groups for saving.
			settings_fields( 'wp_oauth_login' );
			settings_fields( 'wp_google_login' );
			do_settings_sections( 'oauth-login' );
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
