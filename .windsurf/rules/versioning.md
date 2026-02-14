---
trigger: model_decision
---
# Version Management Rules

## Overview
The plugin uses semantic versioning (MAJOR.MINOR.PATCH). All version references must be kept in sync across the codebase to ensure consistent builds and releases.

## Version Sources of Truth
Version must be defined in exactly ONE location and synchronized to all dependent files:

### Primary Source: `oauth-login.php`
```php
* Version: X.Y.Z
```
This is the authoritative version number. All other files derive from this.

## Dependent Files (Auto-Sync Required)

### 1. `readme.txt` - Stable tag
```
Stable tag: X.Y.Z
```
**Purpose:** WordPress.org plugin repository uses this for release detection.
**Build Impact:** `bin/build-plugin-zip.sh` extracts version from this file to name the output zip.

### 2. `webpack.mix.js` - CSS filename versioning
```javascript
.sass( 'assets/src/scss/button/style.scss', 'assets/build/css/button/style-X.Y.Z.css' );
```
**Purpose:** Cache busting for CSS assets. Filename includes version for browser cache invalidation.
**Build Impact:** `Assets.php` must reference this exact filename.

### 3. `src/Modules/Assets.php` - CSS asset registration
```php
$this->register_style( self::LOGIN_BUTTON_STYLE_HANDLE, 'build/css/button/style-X.Y.Z.css' );
```
**Purpose:** WordPress enqueues the versioned CSS file.
**Build Impact:** Must match the output from `webpack.mix.js`.

## Build Process Flow

```
1. Extract version from oauth-login.php (Version: X.Y.Z)
2. Validate all dependent files match X.Y.Z
3. Build assets (webpack.mix.js outputs style-X.Y.Z.css)
4. Create release zip using readme.txt stable tag
5. Verify output filename matches version
```

## Version Increment Workflow

When bumping version from X.Y.Z to X.Y.(Z+1):

1. **Update `oauth-login.php`** (primary source)
   ```php
   * Version: X.Y.(Z+1)
   ```

2. **Update `readme.txt`** (stable tag)
   ```
   Stable tag: X.Y.(Z+1)
   ```

3. **Update `webpack.mix.js`** (CSS filename)
   ```javascript
   .sass( 'assets/src/scss/button/style.scss', 'assets/build/css/button/style-X.Y.(Z+1).css' );
   ```

4. **Update `src/Modules/Assets.php`** (asset registration)
   ```php
   $this->register_style( self::LOGIN_BUTTON_STYLE_HANDLE, 'build/css/button/style-X.Y.(Z+1).css' );
   ```

5. **Run build** to verify consistency
   ```bash
   npm run production
   composer run build-plugin-zip
   ```

## Build Validation Checklist

After any version change, verify:
- [ ] `oauth-login.php` Version header matches intended version
- [ ] `readme.txt` Stable tag matches `oauth-login.php`
- [ ] `webpack.mix.js` CSS filename includes version
- [ ] `src/Modules/Assets.php` CSS filename matches `webpack.mix.js`
- [ ] Build completes without errors
- [ ] Output zip filename is `oauth-login-X.Y.Z.zip`
- [ ] CSS file inside zip is `style-X.Y.Z.css`

## Common Mistakes to Avoid

❌ **Don't:** Update only `oauth-login.php` and forget other files
❌ **Don't:** Use different version numbers in different files
❌ **Don't:** Manually edit the output zip filename
❌ **Don't:** Change CSS filename without updating `Assets.php`

✅ **Do:** Update all four files when bumping version
✅ **Do:** Run full build after version changes
✅ **Do:** Verify output zip filename matches version

## Build Script Validation

The `bin/build-plugin-zip.sh` script:
1. **Calls `bin/validate-version.sh`** (line 12) - Validates all versions match before building
2. Extracts version from `readme.txt` (line 23)
3. Uses it to name output zip: `oauth-login-{VERSION}.zip`
4. Aborts with error if version mismatch detected

**Validation is mandatory** - Build cannot proceed without passing version consistency checks.
