<?php
/**
 * Google OAuth Provider.
 *
 * Implements the OAuthProvider interface for Google OAuth 2.0.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.0.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Providers;

use stdClass;
use Circularlizard\OAuthLogin\Interfaces\OAuthProvider;

/**
 * Class GoogleProvider
 *
 * @package Circularlizard\OAuthLogin\Providers
 */
class GoogleProvider implements OAuthProvider {

	/**
	 * Provider ID.
	 *
	 * @var string
	 */
	const PROVIDER_ID = 'google';

	/**
	 * Google OAuth authorization URL.
	 *
	 * @var string
	 */
	const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

	/**
	 * Google OAuth token URL.
	 *
	 * @var string
	 */
	const TOKEN_URL = 'https://oauth2.googleapis.com/token';

	/**
	 * Google user info URL.
	 *
	 * @var string
	 */
	const USER_INFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';

	/**
	 * Client ID.
	 *
	 * @var string
	 */
	private string $client_id;

	/**
	 * Client secret.
	 *
	 * @var string
	 */
	private string $client_secret;

	/**
	 * GoogleProvider constructor.
	 *
	 * @param string $client_id     OAuth client ID.
	 * @param string $client_secret OAuth client secret.
	 */
	public function __construct( string $client_id = '', string $client_secret = '' ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
	}

	/**
	 * Get the unique provider identifier.
	 *
	 * @return string Provider ID.
	 */
	public function get_provider_id(): string {
		return self::PROVIDER_ID;
	}

	/**
	 * Get the human-readable provider name.
	 *
	 * @return string Provider name.
	 */
	public function get_provider_name(): string {
		return __( 'Google', 'oauth-login' );
	}

	/**
	 * Get the OAuth authorization URL.
	 *
	 * @return string Authorization endpoint URL.
	 */
	public function get_authorize_url(): string {
		return self::AUTHORIZE_URL;
	}

	/**
	 * Get the OAuth token URL.
	 *
	 * @return string Token endpoint URL.
	 */
	public function get_token_url(): string {
		return self::TOKEN_URL;
	}

	/**
	 * Get the user info URL.
	 *
	 * @return string User info endpoint URL.
	 */
	public function get_user_info_url(): string {
		return self::USER_INFO_URL;
	}

	/**
	 * Get the OAuth scopes required by Google.
	 *
	 * @return array List of scope strings.
	 */
	public function get_scopes(): array {
		return [
			'email',
			'profile',
			'openid',
		];
	}

	/**
	 * Get the client ID.
	 *
	 * @return string OAuth client ID.
	 */
	public function get_client_id(): string {
		return $this->client_id;
	}

	/**
	 * Get the client secret.
	 *
	 * @return string OAuth client secret.
	 */
	public function get_client_secret(): string {
		return $this->client_secret;
	}

	/**
	 * Parse the user info response from Google.
	 *
	 * @param stdClass $response Raw response from user info endpoint.
	 *
	 * @return stdClass Normalized user data.
	 */
	public function parse_user_response( stdClass $response ): stdClass {
		$user = new stdClass();

		$user->email      = $response->email ?? '';
		$user->name       = $response->name ?? '';
		$user->first_name = $response->given_name ?? '';
		$user->last_name  = $response->family_name ?? '';
		$user->picture    = $response->picture ?? '';
		$user->locale     = $response->locale ?? '';
		$user->provider   = self::PROVIDER_ID;

		return $user;
	}

	/**
	 * Get the callback URL for Google.
	 *
	 * @return string Callback URL (defaults to wp-login.php).
	 */
	public function get_callback_url(): string {
		return wp_login_url();
	}

	/**
	 * Get the button text for Google.
	 *
	 * @return string Button text.
	 */
	public function get_button_text(): string {
		return __( 'Login with Google', 'oauth-login' );
	}

	/**
	 * Get the button icon URL for Google.
	 *
	 * @return string Icon URL (empty for default Google icon).
	 */
	public function get_button_icon(): string {
		return '';
	}

	/**
	 * Get the button styles for Google.
	 *
	 * @return array Default Google button styles.
	 */
	public function get_button_styles(): array {
		return [
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
	}

	/**
	 * Get the field mappings for Google.
	 *
	 * @return array Field mappings.
	 */
	public function get_field_mappings(): array {
		return [
			'email'        => 'email',
			'first_name'   => 'given_name',
			'last_name'    => 'family_name',
			'display_name' => 'name',
			'avatar'       => 'picture',
		];
	}
}
