<?php
/**
 * Provider Registry.
 *
 * Manages registration and retrieval of OAuth providers.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.0.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Utils;

use Circularlizard\OAuthLogin\Interfaces\OAuthProvider;

/**
 * Class ProviderRegistry
 *
 * @package Circularlizard\OAuthLogin\Utils
 */
class ProviderRegistry {

	/**
	 * Registered providers.
	 *
	 * @var array<string, OAuthProvider>
	 */
	private array $providers = [];

	/**
	 * Register a provider.
	 *
	 * @param OAuthProvider $provider Provider instance.
	 *
	 * @return void
	 */
	public function register( OAuthProvider $provider ): void {
		$this->providers[ $provider->get_provider_id() ] = $provider;
	}

	/**
	 * Get a provider by ID.
	 *
	 * @param string $provider_id Provider ID.
	 *
	 * @return OAuthProvider|null Provider instance or null if not found.
	 */
	public function get( string $provider_id ): ?OAuthProvider {
		return $this->providers[ $provider_id ] ?? null;
	}

	/**
	 * Get all registered providers.
	 *
	 * @return array<string, OAuthProvider> All registered providers.
	 */
	public function get_all(): array {
		return $this->providers;
	}

	/**
	 * Check if a provider is registered.
	 *
	 * @param string $provider_id Provider ID.
	 *
	 * @return bool True if registered, false otherwise.
	 */
	public function has( string $provider_id ): bool {
		return isset( $this->providers[ $provider_id ] );
	}

	/**
	 * Get all enabled providers.
	 *
	 * @return array<string, OAuthProvider> Enabled providers.
	 */
	public function get_enabled(): array {
		return array_filter(
			$this->providers,
			function ( OAuthProvider $provider ) {
				return ! empty( $provider->get_client_id() ) && ! empty( $provider->get_client_secret() );
			}
		);
	}

	/**
	 * Get provider IDs.
	 *
	 * @return array<string> List of provider IDs.
	 */
	public function get_provider_ids(): array {
		return array_keys( $this->providers );
	}
}
