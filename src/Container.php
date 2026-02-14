<?php
/**
 * Class Container.
 *
 * This will be useful for creation of object.
 * We are using Pimple DI Container, which will be
 * useful for defining services and serves as service
 * locator.
 *
 * @package Circularlizard\OAuthLogin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin;

use Circularlizard\OAuthLogin\Interfaces\Container as ContainerInterface;
use Pimple\Container as PimpleContainer;
use InvalidArgumentException;
use Circularlizard\OAuthLogin\Modules\Assets;
use Circularlizard\OAuthLogin\Modules\Block;
use Circularlizard\OAuthLogin\Modules\Login;
use Circularlizard\OAuthLogin\Providers\Google\OneTapLogin as GoogleOneTapLogin;
use Circularlizard\OAuthLogin\Providers\Google\TokenVerifier as GoogleTokenVerifier;
use Circularlizard\OAuthLogin\Modules\Settings;
use Circularlizard\OAuthLogin\Modules\ProviderTestLogin;
use Circularlizard\OAuthLogin\Utils\Authenticator;
use Circularlizard\OAuthLogin\Utils\GoogleClient;
use Circularlizard\OAuthLogin\Modules\Shortcode;
use Circularlizard\OAuthLogin\Utils\ProviderRegistry;
use Circularlizard\OAuthLogin\Utils\LoginButtonRenderer;
use Circularlizard\OAuthLogin\Providers\GoogleProvider;
use Circularlizard\OAuthLogin\Providers\CustomProvider;

/**
 * Class Container
 *
 * @package Circularlizard\OAuthLogin
 */
class Container implements ContainerInterface {
	/**
	 * Pimple container.
	 *
	 * @var PimpleContainer
	 */
	public $container;

	/**
	 * Container constructor.
	 *
	 * @param PimpleContainer $container Pimple Container.
	 */
	public function __construct( PimpleContainer $container ) {
		$this->container = $container;
	}

	/**
	 * Get the service object.
	 *
	 * @param string $service Service object in need.
	 *
	 * @return object
	 *
	 * @throws InvalidArgumentException Exception for invalid service.
	 */
	public function get( string $service ) {
		if ( ! in_array( $service, $this->container->keys(), true ) ) {
			$error_message = sprintf(
				/* translators: %$s is replaced with requested service name. */
				__( 'Invalid Service %s Passed to the container', 'oauth-login' ),
				$service
			);

			throw new InvalidArgumentException( esc_html( $error_message ) );
		}

		return $this->container[ $service ];
	}

	/**
	 * Define common services in container.
	 *
	 * All the module specific services will be defined inside
	 * respective module's container.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return void
	 */
	public function define_services(): void {
		/**
		 * Define Settings service to add settings page and retrieve setting values.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return Settings
		 */
		$this->container['settings'] = function ( PimpleContainer $c ) {
			$settings = new Settings();

			// Inject provider registry directly (all factories are defined before instantiation).
			if ( isset( $c['provider_registry'] ) ) {
				$settings->set_registry( $c['provider_registry'] );
			}

			return $settings;
		};

		/**
		 * Define the login flow service.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return Login
		 */
		$this->container['login_flow'] = function ( PimpleContainer $c ) {
			$login = new Login( $c['gh_client'], $c['authenticator'] );

			if ( isset( $c['login_button_renderer'] ) ) {
				$login->set_button_renderer( $c['login_button_renderer'] );
			}
			if ( isset( $c['provider_registry'] ) ) {
				$login->set_provider_registry( $c['provider_registry'] );
			}

			return $login;
		};

		/**
		 * Define a service for Google OAuth client.
		 *
		 * @param PimpleContainer $c Pimple container instance.
		 *
		 * @return GoogleClient
		 */
		$this->container['gh_client'] = function ( PimpleContainer $c ) {
			$settings          = $c['settings'];
			$provider_settings = get_option( 'wp_oauth_login_settings', [] );
			$google_config     = $provider_settings['providers']['google'] ?? [];

			// Prefer new provider settings, fall back to legacy settings.
			$client_id     = ! empty( $google_config['client_id'] ) ? $google_config['client_id'] : ( $settings->client_id ?? '' );
			$client_secret = ! empty( $google_config['client_secret'] ) ? Settings::decrypt_secret( $google_config['client_secret'] ) : ( $settings->client_secret ?? '' );

			return new GoogleClient(
				[
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
					'redirect_uri'  => wp_login_url(),
				]
			);
		};

		/**
		 * Define Assets service to add styles or script.
		 *
		 * @return Assets
		 */
		$this->container['assets'] = function () {
			return new Assets();
		};

		/**
		 * Define Shortcode service to register shortcode for google login.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return Shortcode
		 */
		$this->container['shortcode'] = function ( PimpleContainer $c ) {
			$shortcode = new Shortcode( $c['gh_client'], $c['assets'] );

			if ( isset( $c['login_button_renderer'] ) ) {
				$shortcode->set_button_renderer( $c['login_button_renderer'] );
			}

			return $shortcode;
		};

		/**
		 * Define Google Token Verifier Service.
		 *
		 * Useful in verifying JWT Auth token for Google.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return GoogleTokenVerifier
		 */
		$this->container['google_token_verifier'] = function ( PimpleContainer $c ) {
			return new GoogleTokenVerifier( $c['settings'] );
		};

		// Backward compatibility alias.
		$this->container['token_verifier'] = function ( PimpleContainer $c ) {
			return $c['google_token_verifier'];
		};

		/**
		 * Google One Tap Login Service.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return GoogleOneTapLogin
		 */
		$this->container['google_one_tap_login'] = function ( PimpleContainer $c ) {
			return new GoogleOneTapLogin( $c['settings'], $c['google_token_verifier'], $c['gh_client'], $c['authenticator'] );
		};

		// Backward compatibility alias.
		$this->container['one_tap_login'] = function ( PimpleContainer $c ) {
			return $c['google_one_tap_login'];
		};

		/**
		 * Authenticator utility.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return Authenticator
		 */
		$this->container['authenticator'] = function ( PimpleContainer $c ) {
			return new Authenticator( $c['settings'] );
		};

		/**
		 * Define Block service to add gutenberg block.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return Block
		 */
		$this->container['google_login_block'] = function ( PimpleContainer $c ) {
			$block = new Block( $c['assets'], $c['gh_client'] );

			if ( isset( $c['login_button_renderer'] ) ) {
				$block->set_button_renderer( $c['login_button_renderer'] );
			}

			return $block;
		};


		/**
		 * Define Provider Registry service.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return ProviderRegistry
		 */
		$this->container['provider_registry'] = function ( PimpleContainer $c ) {
			$registry          = new ProviderRegistry();
			$provider_settings = get_option( 'wp_oauth_login_settings', [] );
			$providers         = $provider_settings['providers'] ?? [];

			foreach ( $providers as $provider_id => $config ) {
				$type = $config['type'] ?? 'custom';

				// Decrypt client_secret before passing to providers.
				$decrypted_config = $config;
				if ( ! empty( $decrypted_config['client_secret'] ) ) {
					$decrypted_config['client_secret'] = Settings::decrypt_secret( $decrypted_config['client_secret'] );
				}

				if ( 'google' === $type || 'google' === $provider_id ) {
					$google = new GoogleProvider(
						$decrypted_config['client_id'] ?? '',
						$decrypted_config['client_secret'] ?? ''
					);
					$registry->register( $google );
				} else {
					$custom = new CustomProvider(
						array_merge( $decrypted_config, [ 'provider_id' => $provider_id ] )
					);
					$registry->register( $custom );
				}
			}

			/**
			 * Allow third-party providers to register.
			 *
			 * @param ProviderRegistry $registry Provider registry instance.
			 *
			 * @since 2.0.0
			 */
			do_action( 'oauth.register_providers', $registry );

			return $registry;
		};

		/**
		 * Define Login Button Renderer service.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return LoginButtonRenderer
		 */
		$this->container['login_button_renderer'] = function ( PimpleContainer $c ) {
			return new LoginButtonRenderer( $c['provider_registry'], $c['settings'] );
		};

		/**
		 * Define Provider Test Login service.
		 *
		 * @param PimpleContainer $c Pimple container object.
		 *
		 * @return ProviderTestLogin
		 */
		$this->container['provider_test_login'] = function ( PimpleContainer $c ) {
			$test_login = new ProviderTestLogin();

			if ( isset( $c['provider_registry'] ) ) {
				$test_login->set_registry( $c['provider_registry'] );
			}

			return $test_login;
		};

		/**
		 * Define any additional services.
		 *
		 * @param ContainerInterface $container Container object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'rtcamp.google_login_services', $this );
	}
}
