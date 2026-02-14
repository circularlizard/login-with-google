<?php
/**
 * OAuth State Helper.
 *
 * Encodes and decodes OAuth state parameters with HMAC integrity
 * protection to prevent tampering.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.1.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Utils;

/**
 * Class OAuthState
 *
 * @package Circularlizard\OAuthLogin\Utils
 */
class OAuthState {

	/**
	 * Encode state data with HMAC signature.
	 *
	 * @param array $data State data to encode.
	 *
	 * @return string Base64-encoded state with HMAC.
	 */
	public static function encode( array $data ): string {
		$json      = wp_json_encode( $data );
		$signature = hash_hmac( 'sha256', $json, wp_salt( 'nonce' ) );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $json . '|' . $signature );
	}

	/**
	 * Decode and verify a state parameter.
	 *
	 * Returns null if the signature is invalid or the data is malformed.
	 *
	 * @param string $state Raw state string from the callback.
	 *
	 * @return array|null Decoded state data or null on failure.
	 */
	public static function decode( string $state ): ?array {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded = base64_decode( $state, true );

		if ( false === $decoded ) {
			return null;
		}

		$pipe_pos = strrpos( $decoded, '|' );

		if ( false === $pipe_pos ) {
			// Legacy unsigned state — decode as plain JSON for backward compatibility.
			$data = json_decode( $decoded, true );
			return is_array( $data ) ? $data : null;
		}

		$json      = substr( $decoded, 0, $pipe_pos );
		$signature = substr( $decoded, $pipe_pos + 1 );

		// Verify HMAC.
		$expected = hash_hmac( 'sha256', $json, wp_salt( 'nonce' ) );

		if ( ! hash_equals( $expected, $signature ) ) {
			return null;
		}

		$data = json_decode( $json, true );

		return is_array( $data ) ? $data : null;
	}
}
