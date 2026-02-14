# OAuth Login

> WordPress plugin to login/register via OAuth 2.0 providers (Google and more)

- [OAuth Login](#oauth-login)
  - [Overview](#overview)
  - [Features](#features)
  - [Installation](#installation)
  - [Browser support](#browser-support)
  - [Usage Instructions](#usage-instructions)
    - [Plugin Constants](#plugin-constants)
    - [Hooks](#hooks)
      - [Filters](#filters)
      - [Actions](#actions)
  - [Shortcode](#shortcode)
  - [Adding Custom Providers](#adding-custom-providers)
  - [Contribute](#contribute)
  - [Unit testing](#unit-testing)
  - [Code Snippets](#code-snippets)
  - [Minimum Requirements](#minimum-requirements)
  - [License](#license)

## Overview

OAuth Login provides a seamless experience for users to login to WordPress sites using their OAuth 2.0 provider accounts. The plugin includes built-in support for Google OAuth 2.0 with an extensible architecture for adding custom OAuth providers. Features include a provider management UI, test mode for configuration validation, customizable login buttons, and comprehensive security hardening.

## Features

- **Multi-Provider OAuth 2.0** - Login with Google and custom OAuth providers
- **Google One Tap Login** - Google's streamlined one-tap authentication
- **Extensible Provider System** - Add custom OAuth providers via the `oauth.register_providers` action
- **Provider Configuration UI** - Admin panel for managing provider credentials and settings
- **Test Mode** - Test OAuth flows before going live
- **Customizable Login Button** - Style and position the login button via admin settings
- **Shortcode & Block** - Embed login buttons anywhere on your site
- **Whitelisted Domains** - Restrict registration to specific email domains
- **WP-CLI Support** - Configure via constants in wp-config.php
- **Security Hardening** - HMAC-signed state, encrypted secrets, SSRF protection, and more

## Installation

1. Clone this repository.
2. Run `composer install --no-dev` inside the cloned directory.
3. Use `nvm` to install the recommended node version (see `.nvmrc`).
4. Use `npm i` to install the dev dependencies.
5. Run `npm run production` inside the cloned directory to build assets.
6. Upload the directory to the `wp-content/plugins` directory.
7. Activate the plugin from the WordPress dashboard.

### Building and Versioning

The plugin uses semantic versioning (MAJOR.MINOR.PATCH) with automated version synchronization:

- **Primary source:** Version is defined in `oauth-login.php` header (`Version: X.Y.Z`)
- **Dependent files:** `readme.txt`, `webpack.mix.js`, and `src/Modules/Assets.php` are auto-synced
- **Build process:** `npm run production` outputs versioned CSS (`style-X.Y.Z.css`)
- **Release:** `composer run build-plugin-zip` validates versions and creates `oauth-login-X.Y.Z.zip`

See `.windsurf/rules/versioning.md` for detailed version management rules.

## Browser support
[These browsers are supported](https://developers.google.com/identity/gsi/web/guides/supported-browsers). Note, for example, that One Tap Login is not supported on Edge in iOS.

## Usage Instructions

### Google OAuth Setup

1. Register a new application at https://console.cloud.google.com/apis/dashboard

2. Configure the OAuth consent screen and create OAuth 2.0 credentials (Web application type)

3. Set the following in your Google Cloud Console:
   - **Authorization callback URL:** `https://yourdomain.com/wp-login.php`
   - **Authorized JavaScript origins:** `https://yourdomain.com`

4. In WordPress admin, navigate to `Settings > OAuth Login` and add your provider:
   - Select "Google" as the provider type
   - Enter your `Client ID` and `Client Secret`
   - Optionally enable Test Mode to validate configuration before going live
   - Click "Test Config" to verify credentials work correctly

### General Settings

5. **Create new user** - Enables new user registration irrespective of `Membership` settings in `Settings > General`. The plugin prioritizes this setting over the WordPress membership setting, so if either is enabled, new users will be registered after successful authorization.

6. **Whitelisted Domains** - Restrict registration to specific email domains. For example, to allow only users from your organization (`myorg.com`), enter `myorg.com` in whitelisted domains. Users with emails like `abc@myorg.com` will be able to register, while `something@gmail.com` will not.

7. **Button Customization** - Customize the appearance and position of the login button:
   - Choose button text, size, and theme
   - Customize colors and styling
   - Select where the button appears (login form, footer, etc.)

### Plugin Constants

Above mentioned settings can also be configured via PHP constants by defining them in wp-config.php
file.

Refer following list of constants.

|                                   | Type    | Description                                                                                                                                                                 |
|-----------------------------------|---------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| WP_GOOGLE_LOGIN_CLIENT_ID         | String  | Google client ID of your application.                                                                                                                                       |
| WP_GOOGLE_LOGIN_SECRET            | String  | Secret key of your application                                                                                                                                              |
| WP_GOOGLE_LOGIN_USER_REGISTRATION | Boolean | (Optional) Set True If you want to enable new user registration. By default, user registration defers to `Settings > General Settings > Membership` if constant is not set. |
| WP_GOOGLE_LOGIN_WHITELIST_DOMAINS | String  | (Optional) Domain name, if you want to restrict login with your custom domain. By default, It will allow all domains. You can whitelist multiple domains.                   |

These constants can also be configured
via [wp-cli](https://developer.wordpress.org/cli/commands/config/).

**Note:** If you have defined the constant in wp-config.php file, corresponding settings field will be disable
(locked for editing) on the settings page.

### Hooks
#### Filters

| Filter | Description | Parameters |
| --- | ----------- | --- |
| `rtcamp.google_scope` | This filter can be used to filter existing scope used in Google Sign in. <br />You can ask for additional permission while user logs in. | <ul><li>`scope` - contains array of scopes.</li></ul>
| `rtcamp.google_login_modules` | Filter out active modules before modules are initialized. | <ul><li>`active_modules` - contains array of active modules.</li></ul>
| `rtcamp.google_login_button_display` | This filter is useful where we want to forcefully display login button, even when user is already logged-in in system. | <ul><li>`display` - contains a boolean value of whether to display the button or not.</li></ul>
| `rtcamp.google_default_redirect` | Filter the default redirect URL in case redirect_to param is not available. <br />Default to admin URL. | <ul><li>`admin_url` - contains the admin URL address which is used as redirect URL by default.</li></ul>
| `rtcamp.google_register_user` | Check if we need to register the user. | <ul><li>`user` - contains the user object from Google.</li></ul>
| `rtcamp.google_client_args` | Filter the arguments for sending in query. <br />This is useful in cases for example: choosing the correct prompt. | <ul><li>`client_args` - contains the list of query arguments to send to Google OAuth.</li></ul>
| `rtcamp.google_login_state` | Filters the state to pass to the Google API. | <ul><li>`state_data` - contains the default state data.</li></ul>
| `rtcamp.default_algorithm` | Filters default algorithm for openssl signature verification | <ul><li>`default_algo` - Default algorithm.</li><li>`algo` - Algorithm from JWT header.</li></ul>
| `rtcamp.google_redirect_url` | Filters the URL to which the user will be redirected post successful authentication | <ul><li> `redirect_uri` - contains the URL to be redirected to. Defaults to the current URL.</li></ul>

#### Actions

| Action | Description | Parameters |
| --- | ----------- | --- |
| `rtcamp.google_login_services` | Define any additional services. | <ul><li>`container` - Container object.</li></ul>
| `rtcamp.google_user_authenticated` | Fires once the user has been authenticated via Google OAuth. | <ul><li>`user` - User object.</li></ul>
| `rtcamp.id_token_verified` | Do something when token has been verified successfully.<br />If we are here that means ID token has been verified.
| `rtcamp.google_user_logged_in` | Fires once the user has been authenticated. | <ul><li>`user_wp` - WP User data object.</li><li>`user` - User data object returned by Google.</li></ul>
| `rtcamp.google_user_created` | Fires once the user has been registered successfully. | <ul><li>`uid` - User ID</li><li>`user` - WP user object.</li></ul>
| `rtcamp.login_with_google_exception` | Fires when an exception is raised during token verification. | <ul><li>`exception` - The exception which is being raised.</li></ul>

## Shortcode

You can add the Google login button to any page/post using shortcode: `google_login`

**Example:**
```php
[google_login button_text="Login with Google" force_display="yes" /]
```

**Supported attributes for shortcode**

| Parameter      | Description                                                   | Values | Default            |
| -------------- | --------------------------------------------------------------| -------| ------------------ |
| button_text    | Text to show for login button                                 | string | Login with Google  |
| force_display  | Whether to display button when user is already logged in      | yes/no | no                 |
| redirect_to    | URL where user should be redirected post login                | URL    | `wp-admin`         |

## Security Features

The plugin includes comprehensive security hardening:

- **HMAC-Signed State** - OAuth state parameters are signed with HMAC-SHA256 to prevent state tampering
- **Encrypted Secrets** - Provider secrets are encrypted at rest using AES-256-CBC
- **SSRF Protection** - External URLs are validated to prevent server-side request forgery attacks
- **postMessage Origin Validation** - Cross-origin communication is validated against site URL
- **Input Sanitization** - Strict validation for colors, dimensions, URLs, and all user inputs
- **Nonce Isolation** - Legacy nonce usage is restricted to prevent cross-provider attacks

## Adding Custom Providers

You can register additional OAuth providers using the `oauth.register_providers` action:

```php
add_action( 'oauth.register_providers', function( $registry ) {
    // Register a custom provider that implements OAuthProvider interface
    $registry->register( new MyCustomProvider( $client_id, $client_secret ) );
});
```

Your custom provider must implement the `Circularlizard\OAuthLogin\Interfaces\OAuthProvider` interface.

### Provider Configuration

Providers can be configured through the WordPress admin UI at `Settings > OAuth Login`:

1. Click "Add Provider" to add a new OAuth provider
2. Select the provider type (Google or custom)
3. Enter the Client ID and Client Secret
4. Configure optional settings (whitelisted domains, user creation, etc.)
5. Use "Test Config" to validate the configuration before saving
6. Save or Save & Close to apply changes

The admin UI provides real-time validation and test capabilities for each provider configuration.

## Contribute
- For contributing to this plugin, please refer to [CONTRIBUTING.md](docs/CONTRIBUTING.md) for more details.

## Unit testing

Unit tests can be run with simple command `composer tests:unit`.
Please note that you'll need to do `composer install` (need to install dev dependencies) for running
unit tests.

You should have PHP CLI >= 7.4 installed. If you have Xdebug enabled with php, code coverage report will be
generated at `/tmp/report/html`

## Code Snippets
Code snippets to extend and customize the plugin can be found [here](docs/CODE_SNIPPETS.md).

## Minimum Requirements

WordPress >= 5.5.0

PHP >= 7.4

## License

This library is released under
["GPL 2.0 or later" License](LICENSE).

## Credits

This plugin is a fork of [Login with Google](https://github.com/rtCamp/login-with-google) by rtCamp.
