<?php
/**
 * OIDC-First Login customization module.
 *
 * Intercepts the WordPress login page to prioritize OIDC
 * authentication over traditional username/password login.
 *
 * @package Circularlizard\OAuthLogin
 * @since 2.3.0
 */

declare(strict_types=1);

namespace Circularlizard\OAuthLogin\Modules;

use Circularlizard\OAuthLogin\Interfaces\Module as ModuleInterface;

/**
 * Class OidcFirstLogin.
 *
 * @package Circularlizard\OAuthLogin\Modules
 */
class OidcFirstLogin implements ModuleInterface {

	/**
	 * Module name.
	 *
	 * @return string
	 */
	public function name(): string {
		return 'oidc_first_login';
	}

	/**
	 * Initialize the OIDC-first login module.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'login_body_class', [ $this, 'login_classes' ] );
		add_action( 'login_head', [ $this, 'inject_styles' ] );
		add_filter( 'login_message', [ $this, 'inject_help_text' ] );
		// Priority 20 ensures this runs after Login::login_button (priority 10).
		add_action( 'login_footer', [ $this, 'add_backdoor_link' ], 20 );
	}

	/**
	 * Add CSS classes to the login <body> tag.
	 *
	 * @param string[] $classes Existing body classes.
	 * @return string[]
	 */
	public function login_classes( array $classes ): array {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only URL parameter, not form processing.
		$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'native' === $action ) {
			$classes[] = 'viewing-native';
		} else {
			$classes[] = 'viewing-oidc-only';
		}

		return $classes;
	}

	/**
	 * Inject CSS to hide/show login elements based on routing state.
	 *
	 * @action login_head
	 * @return void
	 */
	public function inject_styles(): void {
		?>
		<style type="text/css">
			/* OIDC-ONLY STATE: hide traditional WordPress login form elements */
			.viewing-oidc-only #loginform,
			.viewing-oidc-only #nav,
			.viewing-oidc-only .oauth-login-separator,
			.viewing-oidc-only #backtoblog {
				display: none !important;
			}

			/* Standardize OIDC login wrapper styling */
			.viewing-oidc-only #loginform {
				padding: 30px 24px;
				text-align: center;
				box-shadow: 0 1px 3px rgba(0,0,0,.04);
				border: 1px solid #c3c4c7;
				background: #fff;
				border-radius: 8px;
			}

			/* Admin backdoor hyperlink styling */
			.lwg-backdoor-link {
				display: block;
				text-align: center;
				margin-top: 25px;
				font-size: 11px;
				color: #8c8f94 !important;
				text-decoration: none;
			}
			.lwg-backdoor-link:hover {
				color: #2271b1 !important;
				text-decoration: underline;
			}

			/* NATIVE LOGIN STATE: hide all OAuth login components */
			/* Targets wrapper, button containers, and legacy Google button */
			.viewing-native #oauth-login-buttons-wrapper,
			.viewing-native .oauth-login-buttons,
			.viewing-native .oauth-login-button-container,
			.viewing-native .oauth-login-separator,
			.viewing-native .wp_google_login {
				display: none !important;
			}
		</style>
		<?php
	}

	/**
	 * Inject instructional text into the login page.
	 *
	 * @param string $message Existing login message.
	 * @return string
	 */
	public function inject_help_text( string $message ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only URL parameter, not form processing.
		$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'native' === $action ) {
			return $message . '
			<div class="notice notice-warning" style="border-left: 4px solid #dba617; padding: 12px; margin-bottom: 20px; background: #fff; border-radius: 4px;">
				<strong>Database Administration Mode:</strong> Standard username/password verification is active.
			</div>';
		}

		$help_html = '
		<div class="notice notice-info">
			<h2>Authorized Login</h2>
			<p>Please use your corporate Single Sign-On profile to log in. You do not need to register a separate site password.</p>

			<ul>
				<li>Authentication is restricted to official email domains.</li>
				<li>Verify that you are currently logged into your workplace profile.</li>
			</ul>

			<p>
				Encountering errors? Reach out to IT Operations: <a href="mailto:support@yourcompany.com">support@yourcompany.com</a>
			</p>
		</div>';

		return $message . $help_html;
	}

	/**
	 * Append backdoor link to the login footer on OIDC state.
	 *
	 * @action login_footer (priority 20)
	 * @return void
	 */
	public function add_backdoor_link(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only URL parameter, not form processing.
		$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'native' !== $action ) {
			$native_url = add_query_arg( 'action', 'native', wp_login_url() );
			echo '<a class="lwg-backdoor-link" href="' . esc_url( $native_url ) . '">' . esc_html__( 'Administrative Password Login', 'oauth-login' ) . '</a>';
		}
	}
}
