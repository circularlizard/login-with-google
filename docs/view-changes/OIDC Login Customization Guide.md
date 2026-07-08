# **TECHNICAL SPECIFICATION & IMPLEMENTATION GUIDE**

## **Custom OIDC-First Login Experience for OAuth Login**

This specification document outlines how to safely modify the OAuth Login WordPress plugin to deliver a clean, zero-distraction OIDC authentication page for standard users while preserving a reliable, hidden "break-glass" backdoor route for native WordPress administration.

## **1\. Architectural Strategy**

The standard WordPress login sequence displays a form with fields for Username, Password, and a Submit button. This modification intercepts that login UI using safe, core WordPress filters and hooks, following the plugin's existing namespaced, DI-container, module-based architecture.

### **Plugin Architecture Overview**

- **Entry file:** `oauth-login.php` (namespace `Circularlizard\OAuthLogin`)
- **Container:** `src/Container.php` (Pimple DI container with `define_services()`)
- **Modules:** `src/Modules/` — each implements `ModuleInterface` with `init()` and `name()`
- **Active modules:** registered in `src/Plugin.php::$active_modules`
- **OAuth buttons rendered via:**
  - `login_footer` hook in `src/Modules/Login.php::login_button()`
  - Wrapper ID: `#oauth-login-buttons-wrapper`
  - Button classes: `.oauth-login-buttons`, `.oauth-login-button`, `.oauth-login-separator`
  - Legacy fallback: `.wp_google_login`, `.wp_google_login__button`

### **State Routing Flow**

                      [ User visits /wp-login.php ]
                                   |
                ----------------------------------------
               |                                       |
      Is Action "native"?                      No Special Action
               |                                       |
               v                                       v
   [ CLASS: viewing-native ]               [ CLASS: viewing-oidc-only ]
               |                                       |
               v                                       v
  - Hide OAuth Buttons (CSS)             - Hide Standard Login Fields (CSS)
  - Display Username/Password            - Style login wrapper as button container
  - Admin logs in directly               - Render OAuth button + Rich Help HTML

### **Implementation Approach**

A new module class `src/Modules/OidcFirstLogin.php` is created to encapsulate all OIDC-first customization logic. The module is:
1. Registered in `src/Container.php::define_services()`
2. Added to `src/Plugin.php::$active_modules`

This preserves backward compatibility and follows the existing plugin architecture.

## **2\. Step-by-Step Implementation Instructions**

An agentic coder or developer must perform the following tasks:

### **Task 1: Create the OidcFirstLogin Module**

Create `src/Modules/OidcFirstLogin.php` implementing `ModuleInterface`. The module registers four hooks:

- **`login_body_class`** — injects CSS classes based on `$_GET['action']`
- **`login_head`** — injects structural styles to hide/display forms
- **`login_message`** — injects styled help text above the login form
- **`login_footer`** — appends the admin backdoor hyperlink (priority 20, after `Login::login_button` at priority 10)

### **Task 2: Register the Module in the Container**

Add a service definition in `src/Container.php::define_services()`:

```php
$this->container['oidc_first_login'] = function () {
    return new Modules\OidcFirstLogin();
};
```

### **Task 3: Add the Module to Active Modules**

Add `'oidc_first_login'` to `$active_modules` in `src/Plugin.php`.

## **3\. Code Modifications**

### **3.1 New File: src/Modules/OidcFirstLogin.php**

```php
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
			.viewing-oidc-only #loginform p:not(.forgetmenot):not(.submit),
			.viewing-oidc-only #loginform .forgetmenot,
			.viewing-oidc-only #loginform .submit,
			.viewing-oidc-only #nav {
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
		$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'native' === $action ) {
			return $message . '
			<div class="notice notice-warning" style="border-left: 4px solid #dba617; padding: 12px; margin-bottom: 20px; background: #fff; border-radius: 4px;">
				<strong>Database Administration Mode:</strong> Standard username/password verification is active.
			</div>';
		}

		$help_html = '
		<div class="lwg-help-container" style="margin-bottom: 25px; text-align: left; font-size: 13px; line-height: 1.6; color: #50575e; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Oxygen-Sans, Ubuntu, Cantarell, \"Helvetica Neue\", sans-serif;">
			<h2 style="font-size: 15px; font-weight: 600; margin: 0 0 10px 0; color: #1d2327;">Authorized Login</h2>
			<p style="margin: 0 0 12px 0;">Please use your corporate Single Sign-On profile to log in. You do not need to register a separate site password.</p>
			
			<ul style="padding-left: 18px; margin: 10px 0; list-style-type: square; color: #646970;">
				<li style="margin-bottom: 6px;">Authentication is restricted to official email domains.</li>
				<li style="margin-bottom: 6px;">Verify that you are currently logged into your workplace profile.</li>
			</ul>
			
			<p style="font-size: 12px; margin: 15px 0 0 0; border-top: 1px solid #f0f0f1; padding-top: 12px; color: #8c8f94;">
				Encountering errors? Reach out to IT Operations: <a href="mailto:support@yourcompany.com" style="color: #2271b1; text-decoration: none;">support@yourcompany.com</a>
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
		$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( 'native' !== $action ) {
			$native_url = esc_url( add_query_arg( 'action', 'native', wp_login_url() ) );
			echo '<a class="lwg-backdoor-link" href="' . $native_url . '">' . esc_html__( 'Administrative Password Login', 'oauth-login' ) . '</a>';
		}
	}
}
```

### **3.2 Modification: src/Container.php**

Add the following service definition inside `define_services()`, before the closing `do_action` call (before line 326):

```php
/**
 * OIDC-First Login customization module.
 *
 * @return Modules\OidcFirstLogin
 */
$this->container['oidc_first_login'] = function () {
    return new Modules\OidcFirstLogin();
};
```

### **3.3 Modification: src/Plugin.php**

Add `'oidc_first_login'` to the `$active_modules` array (line 71):

```php
public $active_modules = [
    'settings',
    'login_flow',
    'assets',
    'shortcode',
    'one_tap_login',
    'google_login_block',
    'provider_test_login',
    'oidc_first_login',
];
```

## **4\. Validation & Verification Instructions**

To verify successful implementation, the coder must execute the following checks:

### **Test Case 1: Default Login Page Verification**

1. Access the site login URL (`http://yourdomain.com/wp-login.php`).
2. **Expectation:**
   - The fields for Username, Password, and the Log In button must **not** be visible in the viewport.
   - The custom instructional Help Box ("Authorized Login") is fully visible.
   - The OAuth login buttons (Google + any configured providers) are fully visible within the white card.
   - A tiny hyperlink "Administrative Password Login" is present below the white card.

### **Test Case 2: Administrative Backdoor Route Verification**

1. Navigate directly to `http://yourdomain.com/wp-login.php?action=native`.
2. **Expectation:**
   - The custom instructional Help Box is replaced by a Yellow Warning Alert ("Database Administration Mode").
   - The Standard Username and Password fields along with the blue "Log In" button are completely visible.
   - All OAuth login buttons are completely invisible.

### **Test Case 3: Module Isolation Verification**

1. Confirm `OidcFirstLogin` appears as a registered service in the container.
2. Confirm no existing tests in `tests/php/Unit/` are broken.
3. Verify `phpcs` passes on `src/Modules/OidcFirstLogin.php`.

### **Test Case 4: Mobile Viewport Validation**

1. View both screens on a narrow screen simulated environment (max-width 480px).
2. **Expectation:** Layout containers scale within bounds without generating horizontal scrolling.
