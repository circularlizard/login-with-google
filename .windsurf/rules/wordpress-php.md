# WordPress PHP Coding Standards

## General Rules
- Follow WordPress-Core coding standards
- Use strict types: `declare(strict_types=1);`
- Use PSR-4 autoloading with namespace `Circularlizard\OAuthLogin`
- Always use text domain `oauth-login` for i18n functions

## Namespace Structure
```
Circularlizard\OAuthLogin\
├── Interfaces/     # Contracts and interfaces
├── Modules/        # Feature modules (Login, Settings, Block, etc.)
├── Providers/      # OAuth provider implementations
│   └── Google/     # Google-specific features
└── Utils/          # Utility classes (Authenticator, Helper, etc.)
```

## Class Naming
- One class per file
- Class name matches filename (PSR-4)
- Interfaces suffixed with nothing special, placed in `Interfaces/`
- Provider implementations in `Providers/{ProviderName}/`

## Hooks and Filters
- Use dot notation for namespaced hooks: `rtcamp.google_*` → `oauth.login_*`
- Document all hooks with `@since` and `@param` tags

## Security
- Escape all output: `esc_html()`, `esc_attr()`, `wp_kses_post()`
- Sanitize all input: `sanitize_text_field()`, `absint()`
- Use nonces for form submissions
- Never trust user input
