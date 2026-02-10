<?php
/**
 * One Tap Login Class.
 *
 * This class is deprecated. Use Circularlizard\OAuthLogin\Providers\Google\OneTapLogin instead.
 *
 * @package Circularlizard\OAuthLogin\Modules
 * @since 1.0.16
 * @deprecated 2.0.0 Use Circularlizard\OAuthLogin\Providers\Google\OneTapLogin instead.
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Modules;

use Circularlizard\OAuthLogin\Providers\Google\OneTapLogin as GoogleOneTapLogin;

/**
 * Class OneTapLogin
 *
 * @package Circularlizard\OAuthLogin\Modules
 * @deprecated 2.0.0 Use Circularlizard\OAuthLogin\Providers\Google\OneTapLogin instead.
 */
class OneTapLogin extends GoogleOneTapLogin {

	/**
	 * Module name.
	 *
	 * @return string
	 */
	public function name(): string {
		return 'one_tap_login';
	}
}
