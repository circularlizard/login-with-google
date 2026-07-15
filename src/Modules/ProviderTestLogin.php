<?php
/**
 * Provider Test Login Module.
 *
 * Handles AJAX-based test login flow for custom OAuth providers,
 * allowing admins to test configuration and map user data fields.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.1.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Modules;

use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;
use Circularlizard\OAuthLogin\Utils\ProviderRegistry;

/**
 * Class ProviderTestLogin
 *
 * @package Circularlizard\OAuthLogin\Modules
 */
class ProviderTestLogin implements ModuleInterface {

	/**
	 * Provider registry.
	 *
	 * @var ProviderRegistry|null
	 */
	private ?ProviderRegistry $registry = null;

	/**
	 * Module name.
	 *
	 * @return string
	 */
	public function name(): string {
		return 'provider_test_login';
	}

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
	 * Initialize the module.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_ajax_oauth_test_login_init', array( $this, 'handle_test_init' ) );
		add_action( 'wp_ajax_oauth_test_save_mappings', array( $this, 'handle_save_mappings' ) );

		// Intercept test login callbacks at the real callback URL (login page).
		// Runs early (priority 1) so it fires before the normal authenticate filter.
		add_action( 'login_init', array( $this, 'maybe_handle_test_callback' ), 1 );
	}

	/**
	 * Handle test login initialization - returns the authorization URL.
	 *
	 * @return void
	 */
	public function handle_test_init(): void {
		check_ajax_referer( 'oauth_test_login', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'oauth-login' ) ) );
		}

		$provider_id = sanitize_key( $_POST['provider_id'] ?? '' );

		if ( empty( $provider_id ) || null === $this->registry ) {
			wp_send_json_error( array( 'message' => __( 'Invalid provider.', 'oauth-login' ) ) );
		}

		$provider = $this->registry->get( $provider_id );

		if ( null === $provider ) {
			wp_send_json_error( array( 'message' => __( 'Provider not found in registry.', 'oauth-login' ) ) );
		}

		$state_data = array(
			'nonce'     => wp_create_nonce( 'oauth_test_' . $provider_id ),
			'provider'  => $provider_id,
			'test_mode' => true,
		);

		$args = array(
			'client_id'     => $provider->get_client_id(),
			'redirect_uri'  => $provider->get_callback_url(),
			'state'         => base64_encode( wp_json_encode( $state_data ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'scope'         => implode( ' ', $provider->get_scopes() ),
			'response_type' => 'code',
		);

		$auth_url = $provider->get_authorize_url() . '?' . http_build_query( $args );

		wp_send_json_success( array( 'auth_url' => $auth_url ) );
	}

	/**
	 * Check if the current login page request is a test login callback.
	 *
	 * Inspects the state parameter for test_mode flag and routes to the
	 * test callback handler if present. This allows the test flow to use
	 * the same callback URL as the real login flow.
	 *
	 * @return void
	 */
	public function maybe_handle_test_callback(): void {
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $state ) || empty( $code ) ) {
			return;
		}

		$decoded_state = json_decode( base64_decode( $state ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( ! is_array( $decoded_state ) || empty( $decoded_state['test_mode'] ) ) {
			return;
		}

		// This is a test callback — handle it and exit.
		$this->handle_test_callback( $code, $decoded_state );
	}

	/**
	 * Handle the test login callback - exchanges code for token and fetches user data.
	 *
	 * @param string $code          Authorization code from provider.
	 * @param array  $decoded_state Decoded state data.
	 *
	 * @return void
	 */
	private function handle_test_callback( string $code, array $decoded_state ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'oauth-login' ) );
		}

		if ( empty( $decoded_state['provider'] ) ) {
			wp_die( esc_html__( 'Invalid state data.', 'oauth-login' ) );
		}

		$provider_id = $decoded_state['provider'];

		if ( ! wp_verify_nonce( $decoded_state['nonce'] ?? '', 'oauth_test_' . $provider_id ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'oauth-login' ) );
		}

		if ( null === $this->registry ) {
			wp_die( esc_html__( 'Provider registry not available.', 'oauth-login' ) );
		}

		$provider = $this->registry->get( $provider_id );

		if ( null === $provider ) {
			wp_die( esc_html__( 'Provider not found.', 'oauth-login' ) );
		}

		// Exchange code for access token using the provider's real callback URL.
		$token_response = wp_safe_remote_post(
			$provider->get_token_url(),
			array(
				'headers' => array( 'Accept' => 'application/json' ),
				'body'    => array(
					'client_id'     => $provider->get_client_id(),
					'client_secret' => $provider->get_client_secret(),
					'redirect_uri'  => $provider->get_callback_url(),
					'code'          => $code,
					'grant_type'    => 'authorization_code',
				),
			)
		);

		if ( is_wp_error( $token_response ) ) {
			$this->render_test_result( $provider_id, null, $token_response->get_error_message() );
			return;
		}

		$token_code = wp_remote_retrieve_response_code( $token_response );
		$token_body = wp_remote_retrieve_body( $token_response );
		$token_data = json_decode( $token_body );

		if ( 200 !== $token_code || empty( $token_data->access_token ) ) {
			$error = $token_data->error_description ?? $token_data->error ?? __( 'Failed to get access token.', 'oauth-login' );
			$this->render_test_result( $provider_id, null, (string) $error );
			return;
		}

		// Fetch user info.
		$user_response = wp_safe_remote_get(
			$provider->get_user_info_url(),
			array(
				'headers' => array(
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . $token_data->access_token,
				),
			)
		);

		if ( is_wp_error( $user_response ) ) {
			$this->render_test_result( $provider_id, null, $user_response->get_error_message() );
			return;
		}

		$user_code = wp_remote_retrieve_response_code( $user_response );
		$user_body = wp_remote_retrieve_body( $user_response );
		$user_data = json_decode( $user_body );

		if ( 200 !== $user_code ) {
			$this->render_test_result( $provider_id, null, __( 'Failed to fetch user info. HTTP status: ', 'oauth-login' ) . $user_code );
			return;
		}

		$this->render_test_result( $provider_id, $user_data, null );
	}

	/**
	 * Render the test result page in the popup.
	 *
	 * @param string      $provider_id Provider ID.
	 * @param object|null $user_data   Raw user data from provider.
	 * @param string|null $error       Error message if any.
	 *
	 * @return void
	 */
	private function render_test_result( string $provider_id, $user_data, ?string $error ): void {
		$json_pretty = $user_data ? wp_json_encode( $user_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) : '';
		$fields      = $user_data ? $this->flatten_object( $user_data ) : array();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?php esc_html_e( 'OAuth Test Result', 'oauth-login' ); ?></title>
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; max-width: 700px; margin: 0 auto; }
				h2 { margin-top: 0; }
				.success { color: #00a32a; }
				.error { color: #d63638; }
				pre { background: #f0f0f1; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 13px; max-height: 300px; }
				table { width: 100%; border-collapse: collapse; margin-top: 15px; }
				th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
				th { background: #f0f0f1; }
				select { min-width: 200px; }
				.btn { padding: 8px 16px; cursor: pointer; border: 1px solid #2271b1; background: #2271b1; color: #fff; border-radius: 3px; }
				.btn:hover { background: #135e96; }
			</style>
		</head>
		<body>
			<h2><?php esc_html_e( 'OAuth Test Login Result', 'oauth-login' ); ?></h2>

			<?php if ( $error ) : ?>
				<p class="error"><strong><?php esc_html_e( 'Error:', 'oauth-login' ); ?></strong> <?php echo esc_html( $error ); ?></p>
			<?php else : ?>
				<p class="success"><strong><?php esc_html_e( 'Success!', 'oauth-login' ); ?></strong> <?php esc_html_e( 'Connected to provider and retrieved user data.', 'oauth-login' ); ?></p>

				<h3><?php esc_html_e( 'Raw Response', 'oauth-login' ); ?></h3>
				<pre><?php echo esc_html( $json_pretty ); ?></pre>

				<h3><?php esc_html_e( 'Field Mapping', 'oauth-login' ); ?></h3>
				<p><?php esc_html_e( 'Select which response fields map to user data:', 'oauth-login' ); ?></p>

				<table>
					<thead>
						<tr>
							<th><?php esc_html_e( 'User Field', 'oauth-login' ); ?></th>
							<th><?php esc_html_e( 'Provider Field', 'oauth-login' ); ?></th>
							<th><?php esc_html_e( 'Sample Value', 'oauth-login' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$mapping_fields = array(
							'email'        => __( 'Email', 'oauth-login' ),
							'first_name'   => __( 'First Name', 'oauth-login' ),
							'last_name'    => __( 'Last Name', 'oauth-login' ),
							'display_name' => __( 'Display Name', 'oauth-login' ),
							'avatar'       => __( 'Avatar URL', 'oauth-login' ),
						);
						foreach ( $mapping_fields as $field_key => $field_label ) :
							?>
							<tr>
								<td><strong><?php echo esc_html( $field_label ); ?></strong></td>
								<td>
									<select id="mapping-<?php echo esc_attr( $field_key ); ?>" class="field-mapping-select" data-field="<?php echo esc_attr( $field_key ); ?>">
										<option value=""><?php esc_html_e( '-- Not mapped --', 'oauth-login' ); ?></option>
										<?php foreach ( $fields as $path => $value ) : ?>
											<option value="<?php echo esc_attr( $path ); ?>"><?php echo esc_html( $path ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
								<td class="sample-value" id="sample-<?php echo esc_attr( $field_key ); ?>">-</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p style="margin-top:15px;">
					<button type="button" class="btn" id="apply-mappings">
						<?php esc_html_e( 'Apply Mappings to Settings', 'oauth-login' ); ?>
					</button>
				</p>

				<script>
				(function() {
					var fields = <?php echo wp_json_encode( $fields, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>;
					var providerId = <?php echo wp_json_encode( $provider_id, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>;

					document.querySelectorAll('.field-mapping-select').forEach(function(select) {
						select.addEventListener('change', function() {
							var field = this.dataset.field;
							var path = this.value;
							var sampleEl = document.getElementById('sample-' + field);
							sampleEl.textContent = path && fields[path] !== undefined ? String(fields[path]) : '-';
						});
					});

					document.getElementById('apply-mappings').addEventListener('click', function() {
						var mappings = {};
						document.querySelectorAll('.field-mapping-select').forEach(function(select) {
							if (select.value) {
								mappings[select.dataset.field] = select.value;
							}
						});

						if (window.opener) {
							window.opener.postMessage({
								type: 'oauth_test_mappings',
								provider_id: providerId,
								mappings: mappings
							}, <?php echo wp_json_encode( admin_url() ); ?>);
							window.close();
						} else {
							alert('<?php echo esc_js( __( 'Could not communicate with settings page. Please copy the mappings manually.', 'oauth-login' ) ); ?>');
						}
					});
				})();
				</script>
			<?php endif; ?>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Handle saving field mappings via AJAX.
	 *
	 * @return void
	 */
	public function handle_save_mappings(): void {
		check_ajax_referer( 'oauth_test_login', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'oauth-login' ) ) );
		}

		$provider_id  = sanitize_key( $_POST['provider_id'] ?? '' );
		$raw_mappings = isset( $_POST['mappings'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['mappings'] ) ) : array();

		// Only allow known mapping keys.
		$allowed_keys = array( 'email', 'first_name', 'last_name', 'display_name', 'avatar' );
		$mappings     = array_intersect_key( $raw_mappings, array_flip( $allowed_keys ) );

		if ( empty( $provider_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid provider.', 'oauth-login' ) ) );
		}

		$settings = get_option( 'wp_oauth_login_settings', array() );

		if ( ! isset( $settings['providers'][ $provider_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Provider not found in settings.', 'oauth-login' ) ) );
		}

		$settings['providers'][ $provider_id ]['field_mappings'] = $mappings;
		update_option( 'wp_oauth_login_settings', $settings );

		wp_send_json_success( array( 'message' => __( 'Field mappings saved.', 'oauth-login' ) ) );
	}

	/**
	 * Flatten an object/array into dot-notation paths with their values.
	 *
	 * @param mixed  $data   Data to flatten.
	 * @param string $prefix Current path prefix.
	 *
	 * @return array Associative array of path => value.
	 */
	private function flatten_object( $data, string $prefix = '' ): array {
		$result = array();

		if ( $data instanceof \stdClass ) {
			$data = (array) $data;
		}

		if ( ! is_array( $data ) ) {
			return $result;
		}

		foreach ( $data as $key => $value ) {
			$path = $prefix ? $prefix . '.' . $key : $key;

			if ( is_object( $value ) || is_array( $value ) ) {
				$result = array_merge( $result, $this->flatten_object( $value, $path ) );
			} else {
				$result[ $path ] = $value;
			}
		}

		return $result;
	}
}
