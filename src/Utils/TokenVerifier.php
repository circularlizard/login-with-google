<?php
/**
 * JWT Token Verifier.
 *
 * This class is deprecated. Use Circularlizard\OAuthLogin\Providers\Google\TokenVerifier instead.
 *
 * @package Circularlizard\OAuthLogin
 * @since 1.0.16
 * @deprecated 2.0.0 Use Circularlizard\OAuthLogin\Providers\Google\TokenVerifier instead.
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Utils;

use Circularlizard\OAuthLogin\Providers\Google\TokenVerifier as GoogleTokenVerifier;

/**
 * Class TokenVerifier
 *
 * @package Circularlizard\OAuthLogin\Utils
 * @deprecated 2.0.0 Use Circularlizard\OAuthLogin\Providers\Google\TokenVerifier instead.
 */
class TokenVerifier extends GoogleTokenVerifier {
}
