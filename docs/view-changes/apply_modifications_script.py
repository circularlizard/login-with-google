#!/usr/bin/env python3
import os
import sys
import shutil

PLUGIN_ROOT = os.path.dirname(os.path.abspath(__file__))

def find_entry_file():
    """
    Locate the main plugin entry file: oauth-login.php
    """
    candidate = os.path.join(PLUGIN_ROOT, 'oauth-login.php')
    if os.path.isfile(candidate):
        with open(candidate, 'r', encoding='utf-8', errors='ignore') as f:
            head = f.read(4096)
            if 'Plugin Name:' in head and 'OAuth Login' in head:
                return candidate
    return None


def module_path():
    """Path to the new module file."""
    return os.path.join(PLUGIN_ROOT, 'src', 'Modules', 'OidcFirstLogin.php')


def container_path():
    """Path to the DI container."""
    return os.path.join(PLUGIN_ROOT, 'src', 'Container.php')


def plugin_path():
    """Path to the Plugin class."""
    return os.path.join(PLUGIN_ROOT, 'src', 'Plugin.php')


def create_module_file():
    """Create src/Modules/OidcFirstLogin.php."""
    code = '''<?php
/**
 * OIDC-First Login customization module.
 *
 * Intercepts the WordPress login page to prioritize OIDC
 * authentication over traditional username/password login.
 *
 * @package Circularlizard\\OAuthLogin
 * @since 2.3.0
 */

declare(strict_types=1);

namespace Circularlizard\\OAuthLogin\\Modules;

use Circularlizard\\OAuthLogin\\Interfaces\\Module as ModuleInterface;

/**
 * Class OidcFirstLogin.
 *
 * @package Circularlizard\\OAuthLogin\\Modules
 */
class OidcFirstLogin implements ModuleInterface {

\t/**
\t * Module name.
\t *
\t * @return string
\t */
\tpublic function name(): string {
\t\treturn 'oidc_first_login';
\t}

\t/**
\t * Initialize the OIDC-first login module.
\t *
\t * @return void
\t */
\tpublic function init(): void {
\t\tadd_filter( 'login_body_class', [ $this, 'login_classes' ] );
\t\tadd_action( 'login_head', [ $this, 'inject_styles' ] );
\t\tadd_filter( 'login_message', [ $this, 'inject_help_text' ] );
\t\t// Priority 20 ensures this runs after Login::login_button (priority 10).
\t\tadd_action( 'login_footer', [ $this, 'add_backdoor_link' ], 20 );
\t}

\t/**
\t * Add CSS classes to the login <body> tag.
\t *
\t * @param string[] $classes Existing body classes.
\t * @return string[]
\t */
\tpublic function login_classes( array $classes ): array {
\t\t$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

\t\tif ( 'native' === $action ) {
\t\t\t$classes[] = 'viewing-native';
\t\t} else {
\t\t\t$classes[] = 'viewing-oidc-only';
\t\t}

\t\treturn $classes;
\t}

\t/**
\t * Inject CSS to hide/show login elements based on routing state.
\t *
\t * @action login_head
\t * @return void
\t */
\tpublic function inject_styles(): void {
\t\t?>
\t\t<style type="text/css">
\t\t\t/* OIDC-ONLY STATE: hide traditional WordPress login form elements */
\t\t\t.viewing-oidc-only #loginform p:not(.forgetmenot):not(.submit),
\t\t\t.viewing-oidc-only #loginform .forgetmenot,
\t\t\t.viewing-oidc-only #loginform .submit,
\t\t\t.viewing-oidc-only #nav {
\t\t\t\tdisplay: none !important;
\t\t\t}

\t\t\t/* Standardize OIDC login wrapper styling */
\t\t\t.viewing-oidc-only #loginform {
\t\t\t\tpadding: 30px 24px;
\t\t\t\ttext-align: center;
\t\t\t\tbox-shadow: 0 1px 3px rgba(0,0,0,.04);
\t\t\t\tborder: 1px solid #c3c4c7;
\t\t\t\tbackground: #fff;
\t\t\t\tborder-radius: 8px;
\t\t\t}

\t\t\t/* Admin backdoor hyperlink styling */
\t\t\t.lwg-backdoor-link {
\t\t\t\tdisplay: block;
\t\t\t\ttext-align: center;
\t\t\t\tmargin-top: 25px;
\t\t\t\tfont-size: 11px;
\t\t\t\tcolor: #8c8f94 !important;
\t\t\t\ttext-decoration: none;
\t\t\t}
\t\t\t.lwg-backdoor-link:hover {
\t\t\t\tcolor: #2271b1 !important;
\t\t\t\ttext-decoration: underline;
\t\t\t}

\t\t\t/* NATIVE LOGIN STATE: hide all OAuth login components */
\t\t\t.viewing-native #oauth-login-buttons-wrapper,
\t\t\t.viewing-native .oauth-login-buttons,
\t\t\t.viewing-native .oauth-login-button-container,
\t\t\t.viewing-native .oauth-login-separator,
\t\t\t.viewing-native .wp_google_login {
\t\t\t\tdisplay: none !important;
\t\t\t}
\t\t</style>
\t\t<?php
\t}

\t/**
\t * Inject instructional text into the login page.
\t *
\t * @param string $message Existing login message.
\t * @return string
\t */
\tpublic function inject_help_text( string $message ): string {
\t\t$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

\t\tif ( 'native' === $action ) {
\t\t\treturn $message . '
\t\t\t<div class="notice notice-warning" style="border-left: 4px solid #dba617; padding: 12px; margin-bottom: 20px; background: #fff; border-radius: 4px;">
\t\t\t\t<strong>Database Administration Mode:</strong> Standard username/password verification is active.
\t\t\t</div>';
\t\t}

\t\t$help_html = '
\t\t<div class="lwg-help-container" style="margin-bottom: 25px; text-align: left; font-size: 13px; line-height: 1.6; color: #50575e; font-family: -apple-system, BlinkMacSystemFont, \\"Segoe UI\\", Roboto, Oxygen-Sans, Ubuntu, Cantarell, \\"Helvetica Neue\\", sans-serif;">
\t\t\t<h2 style="font-size: 15px; font-weight: 600; margin: 0 0 10px 0; color: #1d2327;">Authorized Login</h2>
\t\t\t<p style="margin: 0 0 12px 0;">Please use your corporate Single Sign-On profile to log in. You do not need to register a separate site password.</p>
\t\t\t
\t\t\t<ul style="padding-left: 18px; margin: 10px 0; list-style-type: square; color: #646970;">
\t\t\t\t<li style="margin-bottom: 6px;">Authentication is restricted to official email domains.</li>
\t\t\t\t<li style="margin-bottom: 6px;">Verify that you are currently logged into your workplace profile.</li>
\t\t\t</ul>
\t\t\t
\t\t\t<p style="font-size: 12px; margin: 15px 0 0 0; border-top: 1px solid #f0f0f1; padding-top: 12px; color: #8c8f94;">
\t\t\t\tEncountering errors? Reach out to IT Operations: <a href="mailto:support@yourcompany.com" style="color: #2271b1; text-decoration: none;">support@yourcompany.com</a>
\t\t\t</p>
\t\t</div>';

\t\treturn $message . $help_html;
\t}

\t/**
\t * Append backdoor link to the login footer on OIDC state.
\t *
\t * @action login_footer (priority 20)
\t * @return void
\t */
\tpublic function add_backdoor_link(): void {
\t\t$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

\t\tif ( 'native' !== $action ) {
\t\t\t$native_url = esc_url( add_query_arg( 'action', 'native', wp_login_url() ) );
\t\t\techo '<a class="lwg-backdoor-link" href="' . $native_url . '">' . esc_html__( 'Administrative Password Login', 'oauth-login' ) . '</a>';
\t\t}
\t}
}
'''

    target = module_path()
    if os.path.isfile(target):
        print(f"[!] Module file already exists: {target}")
        print("    Skipping creation. Delete it first if you want to regenerate.")
        return True

    print(f"[+] Creating module file: {target}")
    with open(target, 'w', encoding='utf-8') as f:
        f.write(code)
    return True


def patch_container():
    """Register the new module service in Container.php."""
    target = container_path()
    if not os.path.isfile(target):
        print(f"[-] Container not found: {target}")
        return False

    with open(target, 'r', encoding='utf-8') as f:
        content = f.read()

    marker = "oidc_first_login"
    if marker in content:
        print(f"[!] Container already contains '{marker}' service. Skipping.")
        return True

    # Create backup.
    backup = target + '.bak'
    shutil.copy2(target, backup)
    print(f"[+] Backup created: {backup}")

    # Inject before the final do_action at the end of define_services().
    injection = '''
\t/**
\t * OIDC-First Login customization module.
\t *
\t * @return Modules\\OidcFirstLogin
\t */
\t$this->container['oidc_first_login'] = function () {
\t\treturn new Modules\\OidcFirstLogin();
\t};

'''
    # Find the last do_action in define_services() and inject before it.
    last_do_action = content.rfind("do_action( 'rtcamp.google_login_services'")
    if last_do_action == -1:
        print("[-] Could not find do_action('rtcamp.google_login_services') anchor in Container.php")
        return False

    # Find the line start of that do_action.
    line_start = content.rfind('\n', 0, last_do_action) + 1
    new_content = content[:line_start] + injection + content[line_start:]

    print(f"[+] Patching service into: {target}")
    with open(target, 'w', encoding='utf-8') as f:
        f.write(new_content)
    return True


def patch_active_modules():
    """Add 'oidc_first_login' to Plugin::$active_modules."""
    target = plugin_path()
    if not os.path.isfile(target):
        print(f"[-] Plugin class not found: {target}")
        return False

    with open(target, 'r', encoding='utf-8') as f:
        content = f.read()

    if "'oidc_first_login'" in content:
        print("[!] Plugin already lists 'oidc_first_login'. Skipping.")
        return True

    backup = target + '.bak'
    shutil.copy2(target, backup)
    print(f"[+] Backup created: {backup}")

    # Replace the closing bracket of the active_modules array.
    old = "\t\t'provider_test_login',\n\t];"
    new = "\t\t'provider_test_login',\n\t\t'oidc_first_login',\n\t];"

    if old not in content:
        print("[-] Could not find active_modules array pattern in Plugin.php")
        return False

    new_content = content.replace(old, new, 1)

    print(f"[+] Adding 'oidc_first_login' to active_modules in: {target}")
    with open(target, 'w', encoding='utf-8') as f:
        f.write(new_content)
    return True


def main():
    print("==============================================")
    print("OAuth Login — OIDC-First Customization Tool")
    print("==============================================\n")

    entry = find_entry_file()
    if not entry:
        print("[-] Error: Could not locate oauth-login.php")
        print("    Run this script from the plugin root directory.")
        sys.exit(1)

    print(f"[+] Verified plugin entry file: {entry}\n")

    steps = [
        ("Create OidcFirstLogin module", create_module_file),
        ("Register module in Container", patch_container),
        ("Add module to active_modules", patch_active_modules),
    ]

    ok = 0
    for name, fn in steps:
        print(f">> Step: {name}")
        if fn():
            ok += 1
            print(f"[+] Success: {name}\n")
        else:
            print(f"[-] Failed: {name}\n")

    print("==============================================")
    print(f"Applied {ok}/{len(steps)} steps.")
    if ok == len(steps):
        print("[+] Done! Refresh your wp-login.php page to verify.")
    else:
        print("[-] Some steps failed. Check output above.")
        sys.exit(1)


if __name__ == '__main__':
    main()