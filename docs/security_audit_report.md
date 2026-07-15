# Security Audit and Code Review Report: Google Login Plugin

We have conducted a security audit of the `login-with-google` plugin (OAuth Login) focused on external integrations, credential safety, secure coding standards, and test alignment.

---

## 1. Outgoing External HTTP Requests & SSRF Mitigation
The plugin performs multiple external API calls to Google's endpoints for OIDC token exchange, user profile fetching, and verification. Currently, these calls use the raw WordPress HTTP API functions rather than the restricted equivalents.

### Finding 1.1: Raw `wp_remote_get`/`wp_remote_post` in Google API Communication
* **Risk:** Medium (Vulnerability to Server-Side Request Forgery if endpoint options are compromised or manipulated, allowing the server to query internal networks).
* **Details:** Multiple files explicitly bypass the standard WordPress VIP checks by adding inline `//phpcs:ignore` or `//phpcs:disable` blocks.
* **Locations:**
  * [src/Modules/Login.php](file:///Users/davidstrachan/Projects/login-with-google/src/Modules/Login.php#L287) (`wp_remote_post`)
  * [src/Modules/Login.php](file:///Users/davidstrachan/Projects/login-with-google/src/Modules/Login.php#L313) (`wp_remote_get` with ignore comment)
  * [src/Modules/ProviderTestLogin.php](file:///Users/davidstrachan/Projects/login-with-google/src/Modules/ProviderTestLogin.php#L171) (`wp_remote_post`)
  * [src/Modules/ProviderTestLogin.php](file:///Users/davidstrachan/Projects/login-with-google/src/Modules/ProviderTestLogin.php#L201) (`wp_remote_get` with ignore comment)
  * [src/Providers/Google/TokenVerifier.php](file:///Users/davidstrachan/Projects/login-with-google/src/Providers/Google/TokenVerifier.php#L166) (`wp_remote_get` with disable comment)
  * [src/Utils/GoogleClient.php](file:///Users/davidstrachan/Projects/login-with-google/src/Utils/GoogleClient.php#L187) (`wp_remote_post`)
  * [src/Utils/GoogleClient.php](file:///Users/davidstrachan/Projects/login-with-google/src/Utils/GoogleClient.php#L220) (`wp_remote_get`)
* **Fix:** Replace these calls with `wp_safe_remote_get()` and `wp_safe_remote_post()`.

#### Code Refactor Example (GoogleClient.php):
```diff
-		$response = wp_remote_post(
+		$response = wp_safe_remote_post(
 			self::TOKEN_URL,
 			[
 				'body' => [
```

---

## 2. Rate Limiting & API Caching Compliance
* **Status:** Secure. 
* **Details:** In [TokenVerifier.php](file:///Users/davidstrachan/Projects/login-with-google/src/Providers/Google/TokenVerifier.php#L159), Google's JSON Web Key Set (JWKS) public keys are securely cached in transient variables (`lwg_pk_{key_id}`) based on the HTTP response `max-age` header (minus a 5-minute safety buffer). This prevents redundant HTTP requests to Google's key endpoints on every login.

---

## 3. Database Security
* **Status:** Secure.
* **Details:** The plugin does not perform direct database queries via `$wpdb`. All state changes (OAuth configurations and user bindings) are executed using standard WordPress core APIs (`get_option`, `update_user_meta`), eliminating SQL injection risks.

---

## 4. Test Suite Alignment Issues
* **File:** `tests/php/Unit/Modules/AssetsTest.php`
* **Root Cause:** Two unit tests are failing (`testRegisterLoginStyles` and `testEnqueueLoginStyleWithStyleNotRegistered`) because they assert that style files match version `style-2.2.0.css`. The actual plugin version has been updated to `2.3.1` across the main files, causing a mock expectation mismatch.
* **Fix:** Update the hardcoded version assets expectations in the test file to match version `2.3.1`.
