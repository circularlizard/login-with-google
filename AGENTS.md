# AGENTS.md

## Quick start

```bash
composer install          # PHP deps (includes PHPUnit, PHPCS, WP_Mock)
npm install               # JS build toolchain (wp-scripts, laravel-mix)
composer qa               # Run lint + unit tests (the one-command check)
```

## Commands

| Command | What it does |
|---|---|
| `composer qa` | PHPCS lint then PHPUnit unit tests |
| `composer cs` | PHPCS only (WordPress-VIP-Go + WP-Coding-Standards) |
| `composer cs:fix` | PHPCS autofix |
| `composer tests:unit` | PHPUnit unit tests only |
| `composer tests:unit -- --filter=LoginTest` | Single test file |
| `npm run production` | Build JS/Sass assets for release |
| `npm run watch` | Dev watch mode |
| `composer pot` | Regenerate .pot translation file |
| `bash bin/build-plugin-zip.sh` | Build the deployable release zip (validates versions, builds assets, creates zip) |
| `bash bin/validate-version.sh` | Check version consistency across all files |
| `bash bin/build-assets.sh` | Build JS/Sass assets only |

## Finalizing changes (mandatory)

After any code changes:
1. **Bump the version** — increment the version in all four files (see "Version sync" below). The build will abort if versions don't match.
2. Run `bash bin/build-plugin-zip.sh` to build the deployable release zip. This script:
   - Validates version consistency across all files
   - Builds JS/Sass assets
   - Creates a release zip at `release/oauth-login-X.Y.Z.zip`

## Architecture

- **Entry file:** `oauth-login.php` — namespaced `Circularlizard\OAuthLogin`, bootstraps Pimple DI container
- **Modules:** `src/Modules/` — each class implements `ModuleInterface` with `init()` + `name()`
- **Registration:** modules are defined as services in `src/Container.php::define_services()`, activated in `src/Plugin.php::$active_modules`
- **Providers:** `src/Providers/` — implement `OAuthProvider` interface; Google is built-in, custom providers registered via `ProviderRegistry`
- **Namespace:** `Circularlizard\OAuthLogin`, PSR-4 autoloaded to `src/`
- **Tests:** `tests/php/Unit/` — WP_Mock-based unit tests, no WP install required
- **i18n domain:** `oauth-login` — all `_` functions must use this text domain

## Adding a new module

1. Create `src/Modules/YourModule.php` implementing `Circularlizard\OAuthLogin\Interfaces\Module`
2. Register service in `src/Container.php::define_services()`:
   ```php
   $this->container['your_module'] = function () {
       return new Modules\YourModule();
   };
   ```
3. Add `'your_module'` to `$active_modules` in `src/Plugin.php`

## Version sync (critical)

Version X.Y.Z must match in **all four** files before any build:

| File | Location |
|---|---|
| `oauth-login.php` | `Version: X.Y.Z` header |
| `readme.txt` | `Stable tag: X.Y.Z` |
| `webpack.mix.js` | CSS filename `style-X.Y.Z.css` |
| `src/Modules/Assets.php` | CSS filename `style-X.Y.Z.css` |

Run `composer qa` and `composer build-plugin-zip` after any version bump. The build script aborts on mismatch.

## PHP coding standards

- `declare(strict_types=1);` on every file
- WordPress-Core + WordPress-VIP-Go standards (see `phpcs.xml`)
- Short array syntax `[]` and short ternary `?:` are allowed
- One class per file, PSR-4 naming

## Gotchas

- PHPCS ignores `tests/`, `vendor/`, `assets/build/`, `.github/` — only lints `src/` and `oauth-login.php`
- Tests use WP_Mock (patchwork), not real WordPress — mock all WP functions
- CSS filename is versioned for cache busting; changing version requires updating `webpack.mix.js` AND `Assets.php` in tandem
- The `bin/` directory, `.distignore`, and `.windsurf/` are excluded from release zips
