<?php
/**
 * Custom OAuth Provider.
 *
 * Implements the OAuthProvider interface for generic OAuth 2.0 providers
 * configured through the admin settings.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.1.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Providers;

use stdClass;
use Circularlizard\OAuthLogin\Interfaces\OAuthProvider;

/**
 * Class CustomProvider
 *
 * @package Circularlizard\OAuthLogin\Providers
 */
class CustomProvider implements OAuthProvider {

	/**
	 * Provider configuration.
	 *
	 * @var array
	 */
	private array $config;

	/**
	 * CustomProvider constructor.
	 *
	 * @param array $config Provider configuration array.
	 */
	public function __construct( array $config ) {
		$this->config = wp_parse_args(
			$config,
			[
				'provider_id'            => '',
				'name'                   => '',
				'authorize_url'          => '',
				'token_url'              => '',
				'user_info_url'          => '',
				'scopes'                 => '',
				'client_id'              => '',
				'client_secret'          => '',
				'callback_url'           => '',
				'button_text'            => '',
				'button_icon'            => '',
				'button_styles'          => [],
				'field_mappings'         => [],
				'enabled'                => true,
			]
		);
	}

	/**
	 * Get the unique provider identifier.
	 *
	 * @return string Provider ID.
	 */
	public function get_provider_id(): string {
		return $this->config['provider_id'];
	}

	/**
	 * Get the human-readable provider name.
	 *
	 * @return string Provider name.
	 */
	public function get_provider_name(): string {
		return $this->config['name'];
	}

	/**
	 * Get the OAuth authorization URL.
	 *
	 * @return string Authorization endpoint URL.
	 */
	public function get_authorize_url(): string {
		return $this->config['authorize_url'];
	}

	/**
	 * Get the OAuth token URL.
	 *
	 * @return string Token endpoint URL.
	 */
	public function get_token_url(): string {
		return $this->config['token_url'];
	}

	/**
	 * Get the user info URL.
	 *
	 * @return string User info endpoint URL.
	 */
	public function get_user_info_url(): string {
		return $this->config['user_info_url'];
	}

	/**
	 * Get the OAuth scopes.
	 *
	 * @return array List of scope strings.
	 */
	public function get_scopes(): array {
		$scopes = $this->config['scopes'];

		if ( is_string( $scopes ) ) {
			return array_filter( array_map( 'trim', explode( ',', $scopes ) ) );
		}

		return is_array( $scopes ) ? $scopes : [];
	}

	/**
	 * Get the client ID.
	 *
	 * @return string OAuth client ID.
	 */
	public function get_client_id(): string {
		return $this->config['client_id'];
	}

	/**
	 * Get the client secret.
	 *
	 * @return string OAuth client secret.
	 */
	public function get_client_secret(): string {
		return $this->config['client_secret'];
	}

	/**
	 * Parse the user info response using field mappings.
	 *
	 * @param stdClass $response Raw response from user info endpoint.
	 *
	 * @return stdClass Normalized user data.
	 */
	public function parse_user_response( stdClass $response ): stdClass {
		$user     = new stdClass();
		$mappings = $this->get_field_mappings();

		$user->email      = $this->extract_field( $response, $mappings['email'] ?? '' );
		$user->name       = $this->extract_field( $response, $mappings['display_name'] ?? '' );
		$user->first_name = $this->extract_field( $response, $mappings['first_name'] ?? '' );
		$user->last_name  = $this->extract_field( $response, $mappings['last_name'] ?? '' );
		$user->picture    = $this->extract_field( $response, $mappings['avatar'] ?? '' );
		$user->provider   = $this->get_provider_id();

		return $user;
	}

	/**
	 * Get the callback URL for this provider.
	 *
	 * @return string Callback URL.
	 */
	public function get_callback_url(): string {
		$callback = $this->config['callback_url'] ?? '';

		if ( empty( $callback ) ) {
			return wp_login_url();
		}

		return $callback;
	}

	/**
	 * Get the button text for this provider.
	 *
	 * @return string Button text.
	 */
	public function get_button_text(): string {
		$text = $this->config['button_text'] ?? '';

		if ( empty( $text ) ) {
			/* translators: %s: Provider name */
			return sprintf( __( 'Login with %s', 'oauth-login' ), $this->get_provider_name() );
		}

		return $text;
	}

	/**
	 * Get the button icon URL for this provider.
	 *
	 * @return string Icon URL.
	 */
	public function get_button_icon(): string {
		return $this->config['button_icon'] ?? '';
	}

	/**
	 * Get the button styles for this provider.
	 *
	 * @return array Associative array of CSS properties.
	 */
	public function get_button_styles(): array {
		$defaults = [
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
		];

		$styles = $this->config['button_styles'] ?? [];

		if ( is_array( $styles ) ) {
			return wp_parse_args( $styles, $defaults );
		}

		return $defaults;
	}

	/**
	 * Get the field mappings for this provider.
	 *
	 * @return array Associative array of field mappings.
	 */
	public function get_field_mappings(): array {
		$defaults = [
			'email'        => 'email',
			'first_name'   => '',
			'last_name'    => '',
			'display_name' => '',
			'avatar'       => '',
		];

		$mappings = $this->config['field_mappings'] ?? [];

		if ( is_array( $mappings ) ) {
			return wp_parse_args( $mappings, $defaults );
		}

		return $defaults;
	}

	/**
	 * Extract a field value from a response object using dot notation.
	 *
	 * Supports nested paths like 'user.email' or 'data.profile.name'.
	 *
	 * @param stdClass|array $data Response data.
	 * @param string         $path Dot-notation field path.
	 *
	 * @return string Extracted value or empty string.
	 */
	private function extract_field( $data, string $path ): string {
		if ( empty( $path ) ) {
			return '';
		}

		$parts   = explode( '.', $path );
		$current = $data;

		foreach ( $parts as $part ) {
			if ( $current instanceof stdClass && isset( $current->$part ) ) {
				$current = $current->$part;
			} elseif ( is_array( $current ) && isset( $current[ $part ] ) ) {
				$current = $current[ $part ];
			} else {
				return '';
			}
		}

		return is_scalar( $current ) ? (string) $current : '';
	}
}
