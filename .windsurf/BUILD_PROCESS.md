# Build Process Documentation

## Overview
The OAuth Login plugin uses a multi-stage build process that combines PHP, JavaScript, and CSS compilation with version management and release packaging.

## Build Pipeline

### Stage 0: Version Validation (Mandatory)
**Script:** `bin/validate-version.sh` (called from `bin/build-plugin-zip.sh` line 12)

**Mandatory pre-build check** - Build cannot proceed without passing this stage.

Validates that all version references are consistent:

```
oauth-login.php (Version header)
    ↓
readme.txt (Stable tag)
webpack.mix.js (CSS filename)
Assets.php (CSS registration)
```

**What it checks:**
- All four files contain the same version number (X.Y.Z format)
- Extracts versions using grep and regex patterns
- Reports specific file locations with mismatches

**Exit behavior:**
- ✅ Success: Displays matched versions, continues to build
- ❌ Failure: Lists files to update, aborts build with exit code 1

**Example output:**
```
🔍 Validating version consistency...
Found versions:
  oauth-login.php:        2.1.0
  readme.txt:             2.1.0
  webpack.mix.js:         2.1.0
  Assets.php:             2.1.0
✅ All versions match: 2.1.0
```

### Stage 1: Frontend Asset Build
**Command:** `npm run production` (executed in temp directory)

Compiles frontend assets using `bin/build-assets.sh`:

1. **Composer Install** (production only)
   - Command: `composer install --no-dev --prefer-dist --optimize-autoloader`
   - Installs: `pimple/pimple` (3.5.*), `psr/container`
   - Excludes dev dependencies to reduce package size

2. **NPM Install**
   - Installs Node.js dependencies from `package.json`
   - Installs with `--silent` flag for cleaner output

3. **Laravel Mix** processes SCSS and JavaScript
   - Input: `assets/src/scss/button/style.scss`
   - Output: `assets/build/css/button/style-X.Y.Z.css` (versioned filename)
   - Minifies and optimizes for production

4. **WordPress Scripts** builds Gutenberg blocks
   - Input: `assets/src/blocks/login-button/`
   - Output: `assets/build/blocks/`
   - Generates block manifest

5. **Block Manifest Generation**
   - Creates `assets/build/blocks/blocks-manifest.php`
   - Registers all blocks for WordPress

6. **Cleanup**
   - Removes `node_modules` directory
   - Removes `package-lock.json`
   - Reduces final package size

### Stage 2: Release Packaging
**Script:** `bin/build-plugin-zip.sh` (lines 19-79)

Creates distributable plugin package:

1. **Temp Directory Setup** (line 41)
   - Copies all project files to temporary location
   - Excludes: `node_modules`, `vendor`, `.git`, `.idea`, `temp`
   - Uses rsync for efficient copying

2. **Asset Building in Temp Directory** (line 56)
   - Changes to temp directory
   - Runs `bin/build-assets.sh`
   - Installs composer dependencies (--no-dev)
   - Installs npm dependencies
   - Runs npm production build
   - Cleans up node_modules and package-lock.json

3. **Distribution Filtering** (line 67)
   - Uses `.distignore` to exclude development files
   - Copies filtered files to final release directory
   - Keeps only production-ready files

4. **Zip Creation** (line 71)
   - Creates: `release/oauth-login-X.Y.Z.zip`
   - Directory structure: `oauth-login-X.Y.Z/` (inside zip)
   - Uses `zip -r` for recursive compression

5. **Cleanup** (lines 75-76)
   - Removes temporary build directory
   - Removes final release directory (after zipping)
   - Leaves only final zip file in `release/`

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

### Development Build (Frontend Only)
```bash
npm run development
```
- Unminified assets
- Source maps included
- Faster compilation
- Does NOT validate versions
- Does NOT create release zip

### Production Build (Frontend Only)
```bash
npm run production
```
- Minified assets
- Optimized for distribution
- Versioned CSS filename
- Does NOT validate versions
- Does NOT create release zip

### Full Plugin Build (Complete)
```bash
composer run build-plugin-zip
```
- **Validates versions** (mandatory, fails if mismatch)
- Builds all assets (npm production)
- Installs PHP dependencies (composer --no-dev)
- Creates release zip
- Cleans up temporary files
- **This is the command for releases**

### Watch Mode (Development)
```bash
npm run watch
```
- Continuous compilation
- Auto-rebuild on file changes
- Useful for development
- Does NOT validate versions
- Does NOT create release zip

## Validation Workflow

**Validation is mandatory and happens first** - Build aborts if validation fails.

The validation script (`bin/validate-version.sh`) checks:

```
✅ oauth-login.php Version header exists and is valid (X.Y.Z)
✅ readme.txt Stable tag exists and is valid (X.Y.Z)
✅ webpack.mix.js CSS filename includes version (X.Y.Z)
✅ Assets.php CSS filename includes version (X.Y.Z)
✅ All versions match exactly
```

**Success case:**
```
🔍 Validating version consistency...
Found versions:
  oauth-login.php:        2.1.0
  readme.txt:             2.1.0
  webpack.mix.js:         2.1.0
  Assets.php:             2.1.0
✅ All versions match: 2.1.0
```

**Failure case:**
```
❌ Version mismatch detected! Please update all files to use version: 2.1.0

Files to update:
  - readme.txt (change 'Stable tag: 2.0.2' to '2.1.0')
  - webpack.mix.js (change 'style-2.0.2.css' to 'style-2.1.0.css')
  - src/Modules/Assets.php (change 'style-2.0.2.css' to 'style-2.1.0.css')
```

Build aborts with exit code 1 if any mismatch is found.

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

### Issue: "Build aborted: Version mismatch detected"
**Cause:** Version numbers don't match across files
**Solution:** Run `bin/validate-version.sh` to see which files need updating
**Example:**
```bash
$ ./bin/validate-version.sh
❌ Version mismatch detected! Please update all files to use version: 2.1.0
Files to update:
  - readme.txt (change 'Stable tag: 2.0.2' to '2.1.0')
  - webpack.mix.js (change 'style-2.0.2.css' to 'style-2.1.0.css')
```

### Issue: "Unable to determine the version from readme.txt"
**Cause:** `Stable tag:` line not found or malformed
**Solution:** Check `readme.txt` line 8 has format: `Stable tag: X.Y.Z`
**Verify:**
```bash
grep "Stable tag:" readme.txt
# Should output: Stable tag: 2.1.0
```

### Issue: CSS file not found in built plugin
**Cause:** `webpack.mix.js` and `Assets.php` versions don't match
**Solution:** Ensure both files reference the same versioned CSS filename
**Verify:**
```bash
grep "style-" webpack.mix.js
grep "style-" src/Modules/Assets.php
# Both should show the same version number
```

### Issue: Build succeeds but zip filename is wrong
**Cause:** `readme.txt` stable tag doesn't match plugin version
**Solution:** Update `readme.txt` to match `oauth-login.php` version
**Verify:**
```bash
grep "Version:" oauth-login.php
grep "Stable tag:" readme.txt
# Both should show the same version number
```

## Integration with Git

The build process is independent of git versioning. However, for releases:

1. Update all version numbers in code (4 files)
2. Run validation: `./bin/validate-version.sh`
3. Commit changes with message: "Bump version to X.Y.Z"
4. Tag commit: `git tag vX.Y.Z`
5. Run build: `composer run build-plugin-zip`
6. Release zip is ready in `release/` directory
7. Upload zip to WordPress.org or distribution channel

**Important:** Always validate before committing version changes.

## Asset Caching Strategy

CSS files use version-based cache busting:
- Old version: `style-2.0.2.css`
- New version: `style-2.1.0.css`

When version changes, browsers automatically fetch new CSS because filename changed. No cache headers needed.

## Performance Notes

- **Full build takes ~2-3 minutes**
  - Version validation: <1 second
  - Composer install (--no-dev): ~30 seconds
  - NPM install: ~20 seconds
  - Asset compilation (npm production): ~1 minute
  - Node_modules cleanup: ~5 seconds
  - Distribution filtering (rsync): ~10 seconds
  - Zip creation: ~30 seconds
  - Cleanup: ~5 seconds

**Note:** First build takes longer due to dependency installation. Subsequent builds are faster if dependencies haven't changed.
