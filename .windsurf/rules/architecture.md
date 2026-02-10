# Provider Abstraction Architecture

## Core Principle
All OAuth providers must implement the `OAuthProvider` interface. No provider-specific logic should exist outside `src/Providers/`.

## OAuthProvider Interface
```php
interface OAuthProvider {
    public function get_provider_id(): string;
    public function get_provider_name(): string;
    public function get_authorize_url(): string;
    public function get_token_url(): string;
    public function get_user_info_url(): string;
    public function get_scopes(): array;
    public function get_client_id(): string;
    public function get_client_secret(): string;
    public function parse_user_response( stdClass $response ): stdClass;
}
```

## Provider Registration
- Use `ProviderRegistry` to register/retrieve providers
- Third-party providers register via filter: `oauth.register_providers`
- Built-in providers: Google (default)

## Directory Structure
```
src/Providers/
├── GoogleProvider.php          # Implements OAuthProvider
└── Google/
    ├── OneTapLogin.php         # Google-specific One Tap feature
    └── TokenVerifier.php       # Google JWT verification
```

## Settings Structure
```php
wp_oauth_login_settings[providers][{slug}] = [
    'client_id'     => '',
    'client_secret' => '',
    'enabled'       => true,
    // Provider-specific settings...
];
```

## Adding a New Provider
1. Create `src/Providers/{Name}Provider.php` implementing `OAuthProvider`
2. Register in `ProviderRegistry`
3. Add settings fields in `Settings::register_provider_settings()`
4. Create button template or use generic one
