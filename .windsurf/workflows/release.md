---
description: Run tests, bump version, and build output files for release
---

# Release Workflow

Runs the full QA suite, bumps the plugin version across all four required files, then builds the release zip.

## Steps

1. **Run the full QA suite** (PHPCS + PHPUnit). Stop and report failures before proceeding.
   ```bash
   composer qa
   ```

2. **Determine the current version** from the primary source of truth.
   ```bash
   grep "Version:" oauth-login.php | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+"
   ```

3. **Ask the user which part of the version to bump** — patch (X.Y.Z+1), minor (X.Y+1.0), or major (X+1.0.0) — then calculate the new version string.

4. **Update all four version files** with the new version (NEW_VERSION = result from step 3):

   - `oauth-login.php` — change `* Version: OLD` to `* Version: NEW`
   - `readme.txt` — change `Stable tag: OLD` to `Stable tag: NEW`
   - `webpack.mix.js` — change `style-OLD.css` to `style-NEW.css`
   - `src/Modules/Assets.php` — change `style-OLD.css` to `style-NEW.css`

5. **Validate version consistency** across all files.
   ```bash
   ./bin/validate-version.sh
   ```

6. **Build the release zip** (runs asset compilation + packaging).
   ```bash
   composer run build-plugin-zip
   ```

7. **Verify the output** zip exists with the correct version.
   ```bash
   ls -lh release/oauth-login-*.zip
   ```
