# Build Process Documentation

## Overview
The OAuth Login plugin uses a multi-stage build process that combines PHP, JavaScript, and CSS compilation with version management and release packaging.

## Build Pipeline

### Stage 1: Version Validation
**Script:** `bin/validate-version.sh`

Before any build occurs, the system validates that all version references are consistent:

```
oauth-login.php (Version header)
    ↓
readme.txt (Stable tag)
webpack.mix.js (CSS filename)
Assets.php (CSS registration)
```

**What it checks:**
- All four files contain the same version number
- Version format is valid (X.Y.Z)
- Reports mismatches with specific file locations

**Exit behavior:**
- ✅ Success: Continues to build
- ❌ Failure: Aborts build with error message listing files to update

### Stage 2: Frontend Asset Build
**Command:** `npm run production`

Compiles frontend assets:
1. **Laravel Mix** processes SCSS and JavaScript
   - Input: `assets/src/scss/button/style.scss`
   - Output: `assets/build/css/button/style-X.Y.Z.css` (versioned filename)
   - Minifies and optimizes for production

2. **WordPress Scripts** builds Gutenberg blocks
   - Input: `assets/src/blocks/login-button/`
   - Output: `assets/build/blocks/`
   - Generates block manifest

3. **Block Manifest Generation**
   - Creates `assets/build/blocks/blocks-manifest.php`
   - Registers all blocks for WordPress

### Stage 3: PHP Dependency Installation
**Inside build-plugin-zip.sh:**

```bash
composer install --no-dev --optimize-autoloader
```

Installs production PHP dependencies:
- `pimple/pimple` (3.5.*)
- `psr/container` (dependency of pimple)

Excludes dev dependencies to reduce package size.

### Stage 4: Release Packaging
**Script:** `bin/build-plugin-zip.sh`

Creates distributable plugin package:

1. **Temp Directory Setup**
   - Copies all project files to temporary location
   - Excludes: `node_modules`, `vendor`, `.git`, `.idea`, `temp`

2. **Asset Building** (in temp directory)
   - Runs `bin/build-assets.sh`
   - Installs composer dependencies
   - Runs npm production build

3. **Distribution Filtering**
   - Uses `.distignore` to exclude development files
   - Keeps only production-ready files

4. **Zip Creation**
   - Creates: `release/oauth-login-X.Y.Z.zip`
   - Directory structure: `oauth-login-X.Y.Z/` (inside zip)

5. **Cleanup**
   - Removes temporary directories
   - Leaves only final zip file

## File Structure in Release Zip

```
oauth-login-2.1.0/
├── oauth-login.php                    # Plugin entry point
├── readme.txt                         # WordPress.org metadata
├── LICENSE
├── src/                               # PHP source code
│   ├── Modules/
│   ├── Providers/
│   ├── Interfaces/
│   └── Utils/
├── assets/
│   ├── build/
│   │   ├── css/button/style-2.1.0.css
│   │   ├── js/
│   │   └── blocks/
│   └── src/                           # (excluded by .distignore)
├── templates/
├── languages/
├── vendor/                            # Production dependencies only
├── bin/
├── docs/
└── .github/
```

## Version Management

### Current Version: 2.1.0

**Files containing version:**
1. `oauth-login.php` - Line 5: `* Version: 2.1.0`
2. `readme.txt` - Line 8: `Stable tag: 2.1.0`
3. `webpack.mix.js` - Line 17: `style-2.1.0.css`
4. `src/Modules/Assets.php` - Line 67: `style-2.1.0.css`

### Bumping Version

To release version 2.1.1:

```bash
# 1. Update oauth-login.php
# Change: * Version: 2.1.0
# To:     * Version: 2.1.1

# 2. Update readme.txt
# Change: Stable tag: 2.1.0
# To:     Stable tag: 2.1.1

# 3. Update webpack.mix.js
# Change: style-2.1.0.css
# To:     style-2.1.1.css

# 4. Update src/Modules/Assets.php
# Change: style-2.1.0.css
# To:     style-2.1.1.css

# 5. Build and verify
npm run production
composer run build-plugin-zip

# 6. Verify output
ls -lh release/oauth-login-2.1.1.zip
```

## Build Commands

### Development Build
```bash
npm run development
```
- Unminified assets
- Source maps included
- Faster compilation

### Production Build
```bash
npm run production
```
- Minified assets
- Optimized for distribution
- Versioned CSS filename

### Full Plugin Build
```bash
composer run build-plugin-zip
```
- Validates versions
- Builds all assets
- Creates release zip
- Cleans up temporary files

### Watch Mode (Development)
```bash
npm run watch
```
- Continuous compilation
- Auto-rebuild on file changes
- Useful for development

## Validation Workflow

Before each build, the validation script checks:

```
✅ oauth-login.php Version header exists
✅ readme.txt Stable tag exists
✅ webpack.mix.js CSS filename includes version
✅ Assets.php CSS filename includes version
✅ All versions match exactly
```

If any check fails:
```
❌ Version mismatch detected!
Files to update:
  - readme.txt (change 'Stable tag: 2.0.2' to '2.1.0')
  - webpack.mix.js (change 'style-2.0.2.css' to 'style-2.1.0.css')
  - src/Modules/Assets.php (change 'style-2.0.2.css' to 'style-2.1.0.css')
```

## Output Verification

After running `composer run build-plugin-zip`:

```bash
# Check zip was created with correct version
ls -lh release/oauth-login-*.zip

# Verify contents
unzip -l release/oauth-login-2.1.0.zip | head -20

# Check CSS filename inside zip
unzip -l release/oauth-login-2.1.0.zip | grep "style-"
```

Expected output:
```
oauth-login-2.1.0/assets/build/css/button/style-2.1.0.css
```

## Common Issues

### Issue: "Version mismatch detected"
**Cause:** Version numbers don't match across files
**Solution:** Run `bin/validate-version.sh` to see which files need updating

### Issue: "Unable to determine the version from readme.txt"
**Cause:** `Stable tag:` line not found or malformed
**Solution:** Check `readme.txt` line 8 has format: `Stable tag: X.Y.Z`

### Issue: CSS file not found in built plugin
**Cause:** `webpack.mix.js` and `Assets.php` versions don't match
**Solution:** Ensure both files reference the same versioned CSS filename

### Issue: Build succeeds but zip filename is wrong
**Cause:** `readme.txt` stable tag doesn't match plugin version
**Solution:** Update `readme.txt` to match `oauth-login.php` version

## Integration with Git

The build process is independent of git versioning. However, for releases:

1. Update all version numbers in code
2. Commit changes with message: "Bump version to X.Y.Z"
3. Tag commit: `git tag vX.Y.Z`
4. Run build: `composer run build-plugin-zip`
5. Release zip is ready in `release/` directory

## Asset Caching Strategy

CSS files use version-based cache busting:
- Old version: `style-2.0.2.css`
- New version: `style-2.1.0.css`

When version changes, browsers automatically fetch new CSS because filename changed. No cache headers needed.

## Performance Notes

- Full build takes ~2-3 minutes
- Version validation adds <1 second
- Asset compilation (npm) takes ~1 minute
- Zip creation takes ~30 seconds
- Composer install (first time) takes ~1 minute
