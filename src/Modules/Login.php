<?php
/**
 * Login class.
 *
 * This will manage the login flow, which includes adding the
 * OAuth login button on wp-login page, authorizing the user,
 * authenticating user and redirecting him to admin.
 *
 * @package Circularlizard\OAuthLogin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Modules;

use WP_User;
use WP_Error;
use stdClass;
use Throwable;
use Exception;
use Circularlizard\OAuthLogin\Utils\Helper;
use Circularlizard\OAuthLogin\Utils\GoogleClient;
use Circularlizard\OAuthLogin\Utils\Authenticator;
use Circularlizard\OAuthLogin\Utils\LoginButtonRenderer;
use Circularlizard\OAuthLogin\Utils\ProviderRegistry;
use Circularlizard\OAuthLogin\Utils\OAuthState;
use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;
use function Circularlizard\OAuthLogin\plugin;

/**
 * Class Login.
 *
 * @package Circularlizard\OAuthLogin\Modules
 */
class Login implements ModuleInterface {
	/**
	 * Google client instance.
	 *
	 * @var GoogleClient
	 */
	private $gh_client;

	/**
	 * Authenticator instance.
	 *
	 * @var Authenticator
	 */
	private $authenticator;

	/**
	 * Login button renderer.
	 *
	 * @var LoginButtonRenderer|null
	 */
	private $button_renderer;

	/**
	 * Provider registry.
	 *
	 * @var ProviderRegistry|null
	 */
	private $provider_registry;

	/**
	 * Flag for determining whether the user has been authenticated
	 * from plugin.
	 *
	 * @var bool
	 */
	private $authenticated = false;

	/**
	 * Login constructor.
	 *
	 * @param GoogleClient  $client GH Client object.
	 * @param Authenticator $authenticator Settings object.
	 */
	public function __construct( GoogleClient $client, Authenticator $authenticator ) {
		$this->gh_client     = $client;
		$this->authenticator = $authenticator;
	}

	/**
	 * Set the login button renderer.
	 *
	 * @param LoginButtonRenderer $renderer Button renderer.
	 *
	 * @return void
	 */
	public function set_button_renderer( LoginButtonRenderer $renderer ): void {
		$this->button_renderer = $renderer;
	}

	/**
	 * Set the provider registry.
	 *
	 * @param ProviderRegistry $registry Provider registry.
	 *
	 * @return void
	 */
	public function set_provider_registry( ProviderRegistry $registry ): void {
		$this->provider_registry = $registry;
	}

	/**
	 * Module name.
	 *
	 * @return string
	 */
	public function name(): string {
		return 'login_flow';
	}

	/**
	 * Initialize login flow.
	 *
	 * @return void
	 */
	public function init(): void {
		/**
		 * Actions.
		 */
		add_action( 'login_footer', [ $this, 'login_button' ] );
		// Priority is 20 because of issue: https://core.trac.wordpress.org/ticket/46748.
		add_action( 'authenticate', [ $this, 'authenticate' ], 20 );
		add_action( 'rtcamp.google_register_user', [ $this->authenticator, 'register' ] );
		add_action( 'rtcamp.google_user_created', [ $this, 'user_meta' ] );
		add_action( 'wp_login', [ $this, 'login_redirect' ] );

		/**
		 * Filters.
		 */
		add_filter( 'rtcamp.google_redirect_url', [ $this, 'redirect_url' ] );
		add_filter( 'rtcamp.google_login_state', [ $this, 'state_redirect' ] );
	}

	/**
	 * Add the login button to login form.
	 *
	 * @return void
	 */
	public function login_button(): void {
		echo '<div id="oauth-login-buttons-wrapper">';

		if ( null !== $this->button_renderer ) {
			$this->button_renderer->render();
		} else {
			// Fallback to legacy Google-only button if renderer not available.
			$template  = trailingslashit( plugin()->template_dir ) . 'google-login-button.php';
			$login_url = plugin()->container()->get( 'gh_client' )->authorization_url();

			Helper::render_template(
				$template,
				[
					'login_url' => $login_url,
				]
			);
		}

		echo '</div>';

		// Move the buttons into the #login container, after the login form.
		?>
		<script>
		(function() {
			var wrapper = document.getElementById('oauth-login-buttons-wrapper');
			var loginDiv = document.getElementById('login');
			if (wrapper && loginDiv) {
				loginDiv.appendChild(wrapper);
			}
		})();
		</script>
		<?php
	}

	/**
	 * Authenticate the user.
	 *
	 * @param WP_User|null $user User object. Default is null.
	 *
	 * @return WP_User|WP_Error
	 * @throws Exception During authentication.
	 */
	public function authenticate( $user = null ) {
		if ( $user instanceof WP_User ) {
			return $user;
		}

		$code = Helper::filter_input( INPUT_GET, 'code', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $code ) {
			return $user;
		}

		$state         = Helper::filter_input( INPUT_GET, 'state', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$decoded_state = $state ? OAuthState::decode( $state ) : null;

		if ( ! is_array( $decoded_state ) || empty( $decoded_state['provider'] ) ) {
			return $user;
		}

		$provider_id = $decoded_state['provider'];

		// Verify nonce.
		$nonce_valid = ! empty( $decoded_state['nonce'] ) && wp_verify_nonce( $decoded_state['nonce'], 'oauth_login_' . $provider_id );

		// Legacy nonce fallback ONLY for Google provider (backward compatibility).
		if ( ! $nonce_valid && 'google' === $provider_id ) {
			$nonce_valid = ! empty( $decoded_state['nonce'] ) && wp_verify_nonce( $decoded_state['nonce'], 'login_with_google' );
		}

		if ( ! $nonce_valid ) {
			return $user;
		}

		// For Google provider, use the legacy GoogleClient flow.
		if ( 'google' === $provider_id ) {
			return $this->authenticate_google( $code, $decoded_state );
		}

		// For custom providers, use the provider registry.
		return $this->authenticate_custom( $code, $provider_id, $decoded_state );
	}

	/**
	 * Authenticate via Google OAuth (legacy flow).
	 *
	 * @param string $code          Authorization code.
	 * @param array  $decoded_state Decoded state data.
	 *
	 * @return WP_User|WP_Error
	 * @throws Exception If authentication fails.
	 */
	private function authenticate_google( string $code, array $decoded_state ) {
		try {
			$this->gh_client->set_access_token( $code );
			$user = $this->gh_client->user();
			$user = $this->authenticator->authenticate( $user );

			if ( $user instanceof WP_User ) {
				$this->authenticated = true;

				/**
				 * Fires once the user has been authenticated via Google OAuth.
				 *
				 * @since 1.3.0
				 *
				 * @param WP_User $user WP User object.
				 */
				do_action( 'rtcamp.google_user_authenticated', $user );

				return $user;
			}

			throw new Exception( __( 'Could not authenticate the user, please try again.', 'oauth-login' ) );

		} catch ( Throwable $e ) {
			return new WP_Error( 'google_login_failed', $e->getMessage() );
		}
	}

	/**
	 * Authenticate via a custom OAuth provider.
	 *
	 * @param string $code          Authorization code.
	 * @param string $provider_id   Provider ID.
	 * @param array  $decoded_state Decoded state data.
	 *
	 * @return WP_User|WP_Error
	 * @throws Exception If authentication fails.
	 */
	private function authenticate_custom( string $code, string $provider_id, array $decoded_state ) {
		if ( null === $this->provider_registry ) {
			return new WP_Error( 'oauth_login_failed', __( 'Provider registry not available.', 'oauth-login' ) );
		}

		$provider = $this->provider_registry->get( $provider_id );

		if ( null === $provider ) {
			return new WP_Error( 'oauth_login_failed', __( 'Unknown OAuth provider.', 'oauth-login' ) );
		}

		try {
			// Exchange code for access token.
			$token_response = wp_remote_post(
				$provider->get_token_url(),
				[
					'headers' => [ 'Accept' => 'application/json' ],
					'body'    => [
						'client_id'     => $provider->get_client_id(),
						'client_secret' => $provider->get_client_secret(),
						'redirect_uri'  => $provider->get_callback_url(),
						'code'          => $code,
						'grant_type'    => 'authorization_code',
					],
				]
			);

			if ( 200 !== wp_remote_retrieve_response_code( $token_response ) ) {
				throw new Exception( __( 'Could not retrieve the access token, please try again.', 'oauth-login' ) );
			}

			$token_data   = json_decode( wp_remote_retrieve_body( $token_response ) );
			$access_token = $token_data->access_token ?? '';

			if ( empty( $access_token ) ) {
				throw new Exception( __( 'Access token not found in provider response.', 'oauth-login' ) );
			}

			// Fetch user info.
			$user_response = wp_remote_get( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
				$provider->get_user_info_url(),
				[
					'headers' => [
						'Accept'        => 'application/json',
						'Authorization' => 'Bearer ' . $access_token,
					],
				]
			);

			if ( 200 !== wp_remote_retrieve_response_code( $user_response ) ) {
				throw new Exception( __( 'Could not retrieve user information from provider.', 'oauth-login' ) );
			}

			$raw_user = json_decode( wp_remote_retrieve_body( $user_response ) );
			$user     = $provider->parse_user_response( $raw_user );
			$user     = $this->authenticator->authenticate( $user );

			if ( $user instanceof WP_User ) {
				$this->authenticated = true;

				/**
				 * Fires once the user has been authenticated via OAuth.
				 *
				 * @since 2.1.0
				 *
				 * @param WP_User $user        WP User object.
				 * @param string  $provider_id Provider ID.
				 */
				do_action( 'oauth.user_authenticated', $user, $provider_id );
				do_action( 'rtcamp.google_user_authenticated', $user );

				return $user;
			}

			throw new Exception( __( 'Could not authenticate the user, please try again.', 'oauth-login' ) );

		} catch ( Throwable $e ) {
			return new WP_Error( 'oauth_login_failed', $e->getMessage() );
		}
	}

	/**
	 * Add extra meta information about user.
	 *
	 * @param int $uid  User ID.
	 *
	 * @return void
	 */
	public function user_meta( int $uid ) {
		$state         = Helper::filter_input( INPUT_GET, 'state', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$decoded_state = $state ? OAuthState::decode( $state ) : null;
		$provider_id   = $decoded_state['provider'] ?? 'google';

		add_user_meta( $uid, 'oauth_user', 1, true );
		add_user_meta( $uid, 'oauth_provider', $provider_id, true );
	}

	/**
	 * Redirect URL.
	 *
	 * This is useful when redirect URL is present when
	 * trying to login to wp-admin.
	 *
	 * @param string $url Redirect URL address.
	 *
	 * @return string
	 */
	public function redirect_url( string $url ): string {

		return remove_query_arg( 'redirect_to', $url );
	}

	/**
	 * Add redirect_to location in state.
	 *
	 * @param array $state State data.
	 *
	 * @return array
	 */
	public function state_redirect( array $state ): array {
		$redirect_to = Helper::filter_input( INPUT_GET, 'redirect_to', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		/**
		 * Filter the default redirect URL in case redirect_to param is not available.
		 * Default to admin URL.
		 *
		 * @param string $admin_url Admin URL address.
		 */
		$state['redirect_to'] = $redirect_to ?? apply_filters( 'rtcamp.google_default_redirect', admin_url() );

		return $state;
	}

	/**
	 * Add a redirect once user has been authenticated successfully.
	 *
	 * @return void
	 */
	public function login_redirect(): void {
		$state = Helper::filter_input( INPUT_GET, 'state', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $state || ! $this->authenticated ) {
			return;
		}

		$decoded = OAuthState::decode( $state );

		if ( is_array( $decoded ) && ! empty( $decoded['provider'] ) && ! empty( $decoded['redirect_to'] ) ) {
			wp_safe_redirect( $decoded['redirect_to'], 302, 'OAuth Login' );
			exit;
		}
	}
}
