<?php
/**
 * OAuthProvider interface.
 *
 * Defines the contract for OAuth 2.0 provider implementations.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.0.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Interfaces;

use stdClass;

/**
 * Interface OAuthProvider
 *
 * @package Circularlizard\OAuthLogin\Interfaces
 */
interface OAuthProvider {

	/**
	 * Get the unique provider identifier.
	 *
	 * @return string Provider ID (e.g., 'google', 'github').
	 */
	public function get_provider_id(): string;

	/**
	 * Get the human-readable provider name.
	 *
	 * @return string Provider name (e.g., 'Google', 'GitHub').
	 */
	public function get_provider_name(): string;

	/**
	 * Get the OAuth authorization URL.
	 *
	 * @return string Authorization endpoint URL.
	 */
	public function get_authorize_url(): string;

	/**
	 * Get the OAuth token URL.
	 *
	 * @return string Token endpoint URL.
	 */
	public function get_token_url(): string;

	/**
	 * Get the user info URL.
	 *
	 * @return string User info endpoint URL.
	 */
	public function get_user_info_url(): string;

	/**
	 * Get the OAuth scopes required by this provider.
	 *
	 * @return array List of scope strings.
	 */
	public function get_scopes(): array;

	/**
	 * Get the client ID for this provider.
	 *
	 * @return string OAuth client ID.
	 */
	public function get_client_id(): string;

	/**
	 * Get the client secret for this provider.
	 *
	 * @return string OAuth client secret.
	 */
	public function get_client_secret(): string;

	/**
	 * Parse the user info response from the provider.
	 *
	 * @param stdClass $response Raw response from user info endpoint.
	 *
	 * @return stdClass Normalized user data with email, name, etc.
	 */
	public function parse_user_response( stdClass $response ): stdClass;

	/**
	 * Get the callback URL for this provider.
	 *
	 * @return string Callback URL.
	 */
	public function get_callback_url(): string;

	/**
	 * Get the button text for this provider.
	 *
	 * @return string Button text (e.g., 'Login with Google').
	 */
	public function get_button_text(): string;

	/**
	 * Get the button icon URL for this provider.
	 *
	 * @return string Icon URL or empty string for default.
	 */
	public function get_button_icon(): string;

	/**
	 * Get the button styles for this provider.
	 *
	 * @return array Associative array of CSS properties.
	 */
	public function get_button_styles(): array;

	/**
	 * Get the field mappings for this provider.
	 *
	 * Maps provider response fields to normalized user fields.
	 *
	 * @return array Associative array of field mappings.
	 */
	public function get_field_mappings(): array;
}
