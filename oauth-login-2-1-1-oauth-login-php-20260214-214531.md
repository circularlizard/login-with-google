# Plugin Check Report

**Plugin:** OAuth Login
**Generated at:** 2026-02-14 21:45:31


## `src/Providers/GoogleProvider.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 93 | 30 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 190 | 41 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Providers/CustomProvider.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 184 | 50 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Providers/Google/OneTapLogin.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 188 | 75 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 198 | 24 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | Hook names invoked by a theme/plugin should start with the theme/plugin prefix. Found: &quot;rtcamp.id_token_verified&quot;. |  |
| 230 | 80 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Providers/Google/TokenVerifier.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 81 | 35 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | Hook names invoked by a theme/plugin should start with the theme/plugin prefix. Found: &quot;rtcamp.default_algorithm&quot;. |  |
| 106 | 24 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | Hook names invoked by a theme/plugin should start with the theme/plugin prefix. Found: &quot;rtcamp.login_with_google_exception&quot;. |  |
| 206 | 69 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 214 | 69 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 243 | 105 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 257 | 101 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 267 | 77 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 271 | 87 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 275 | 89 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 279 | 87 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Modules/Shortcode.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 120 | 61 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Modules/Settings.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 219 | 3 | ERROR | PluginCheck.CodeAnalysis.SettingSanitization.register_settingMissing | Sanitization missing for register_setting(). | [Docs](https://developer.wordpress.org/reference/functions/register_setting/) |
| 231 | 36 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 239 | 37 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 247 | 36 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 256 | 40 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 267 | 45 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 270 | 100 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 278 | 45 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 287 | 48 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 314 | 122 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 320 | 55 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 321 | 51 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 322 | 53 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 323 | 54 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 333 | 102 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 347 | 49 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 350 | 64 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 390 | 71 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 390 | 117 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 393 | 86 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 395 | 85 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 397 | 89 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 402 | 47 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 407 | 53 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 469 | 76 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 469 | 121 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 472 | 53 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 486 | 66 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 490 | 81 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 497 | 79 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 505 | 75 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 514 | 93 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 527 | 79 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 536 | 83 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 542 | 79 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 548 | 83 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 554 | 76 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 557 | 111 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 561 | 82 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 564 | 161 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 571 | 56 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 574 | 77 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 576 | 277 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 580 | 81 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 583 | 139 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 587 | 82 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 593 | 76 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 599 | 78 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 605 | 78 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 611 | 79 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 617 | 73 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 623 | 75 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 629 | 82 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 635 | 82 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 641 | 84 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 650 | 57 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 651 | 161 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 654 | 75 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 660 | 80 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 666 | 79 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 672 | 82 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 678 | 76 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 688 | 47 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 691 | 52 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 691 | 103 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 695 | 70 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 695 | 126 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 699 | 48 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 714 | 64 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 717 | 79 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 720 | 137 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 911 | 47 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;wp_google_login_settings&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 1230 | 73 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1267 | 89 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1274 | 152 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1295 | 62 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1298 | 126 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1318 | 76 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1325 | 65 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1364 | 73 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1376 | 41 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1377 | 32 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 1392 | 55 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Modules/Login.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 256 | 92 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 275 | 96 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 281 | 87 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 301 | 100 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 308 | 90 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 323 | 96 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 341 | 28 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | Hook names invoked by a theme/plugin should start with the theme/plugin prefix. Found: &quot;oauth.user_authenticated&quot;. |  |
| 347 | 92 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Modules/ProviderTestLogin.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 76 | 81 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 82 | 73 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 88 | 87 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 120 | 33 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;state&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 121 | 33 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_GET[&#039;code&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 147 | 62 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 151 | 56 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 157 | 63 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 161 | 69 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 167 | 56 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 195 | 113 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 221 | 108 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 244 | 59 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 260 | 62 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 263 | 70 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 265 | 74 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 265 | 171 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 267 | 55 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 270 | 56 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 271 | 88 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 276 | 65 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 277 | 69 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 278 | 67 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 284 | 60 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 285 | 65 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 286 | 64 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 287 | 67 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 288 | 65 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 296 | 96 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 310 | 73 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 344 | 139 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 365 | 81 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 369 | 91 | WARNING | WordPress.Security.ValidatedSanitizedInput.MissingUnslash | $_POST[&#039;mappings&#039;] not unslashed before sanitization. Use wp_unslash() or similar |  |
| 376 | 73 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 382 | 87 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 388 | 75 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Utils/GoogleClient.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 98 | 96 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 204 | 104 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 230 | 112 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Utils/LoginButtonRenderer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 68 | 43 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 145 | 38 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | Hook names invoked by a theme/plugin should start with the theme/plugin prefix. Found: &quot;oauth.login_state&quot;. |  |
| 161 | 32 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | Hook names invoked by a theme/plugin should start with the theme/plugin prefix. Found: &quot;oauth.authorization_args&quot;. |  |

## `src/Utils/Authenticator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 58 | 104 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 99 | 78 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 124 | 86 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Plugin.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 124 | 3 | ERROR | PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound | load_plugin_textdomain() has been discouraged since WordPress version 4.6. When your plugin is hosted on WordPress.org, you no longer need to manually include this function call for translations under your plugin slug. WordPress will automatically load the translations for you as needed. | [Docs](https://make.wordpress.org/core/2016/07/06/i18n-improvements-in-4-6/) |
| 161 | 37 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `src/Container.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 71 | 67 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 286 | 24 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound | Hook names invoked by a theme/plugin should start with the theme/plugin prefix. Found: &quot;oauth.register_providers&quot;. |  |

## `oauth-login.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | WARNING | textdomain_mismatch | The "Text Domain" header in the plugin file does not match the slug. Found "oauth-login", expected "oauth-login-2.1.1". | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 30 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$hooks&quot;. |  |
| 39 | 25 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$hook&quot;. |  |
| 45 | 21 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 52 | 25 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |

## `templates/google-login-button.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |
| 12 | 5 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$button_text&quot;. |  |
| 14 | 5 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$button_text&quot;. |  |
| 14 | 90 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 21 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$button_url&quot;. |  |
| 24 | 5 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$button_text&quot;. |  |
| 24 | 36 | ERROR | WordPress.WP.I18n.TextDomainMismatch | Mismatched text domain. Expected 'oauth-login-2.1.1' but got 'oauth-login'. | [Docs](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/) |
| 25 | 5 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$redirect_url&quot;. |  |
| 26 | 5 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$button_url&quot;. |  |

## `.DS_Store`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | hidden_files | Hidden files are not permitted. |  |

## `.nvmrc`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | hidden_files | Hidden files are not permitted. |  |

## `src/Modules/Assets.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `src/Modules/Block.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `src/Utils/Helper.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | ERROR | missing_direct_file_access_protection | PHP file should prevent direct access. Add a check like: if ( ! defined( 'ABSPATH' ) ) exit; | [Docs](https://developer.wordpress.org/plugins/wordpress-org/common-issues/#direct-file-access) |

## `.windsurf`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | WARNING | ai_instruction_directory | AI instruction directory ".windsurf" detected. These directories should not be included in production plugins. |  |

## `WORDPRESS_ORG_SUBMISSION_CHECKLIST.md`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | WARNING | unexpected_markdown_file | Unexpected markdown file "WORDPRESS_ORG_SUBMISSION_CHECKLIST.md" detected in plugin root. Only specific markdown files are expected in production plugins. |  |

## `readme.txt`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | WARNING | upgrade_notice_limit | The upgrade notice for "1.4.1" exceeds the limit of 300 characters. |  |
| 0 | 0 | WARNING | upgrade_notice_limit | The upgrade notice for "1.4.0" exceeds the limit of 300 characters. |  |
| 0 | 0 | WARNING | readme_parser_warnings_too_many_tags | One or more tags were ignored. Please limit your plugin to 5 tags. |  |

## `src/Interfaces/OAuthProvider.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.InvalidPrefixPassed | The &quot;rtcamp.google&quot; prefix is not a valid namespace/function/class/variable/constant prefix in PHP. |  |
