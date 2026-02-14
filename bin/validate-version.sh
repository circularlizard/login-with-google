#!/usr/bin/env bash

# Version Validation Script
# Ensures all version references are consistent across the codebase

set -e

SCRIPT_DIR=$(dirname "$0")
cd "$SCRIPT_DIR/.." || exit 1

echo "🔍 Validating version consistency..."

# Extract versions from all sources
PLUGIN_VERSION=$(grep "Version:" oauth-login.php | head -1 | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")
README_VERSION=$(grep "Stable tag:" readme.txt | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")
WEBPACK_VERSION=$(grep "style-.*\.css" webpack.mix.js | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+" | head -1)
ASSETS_VERSION=$(grep "style-.*\.css" src/Modules/Assets.php | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+" | head -1)

# Display found versions
echo "Found versions:"
echo "  oauth-login.php:        $PLUGIN_VERSION"
echo "  readme.txt:             $README_VERSION"
echo "  webpack.mix.js:         $WEBPACK_VERSION"
echo "  Assets.php:             $ASSETS_VERSION"

# Validate all versions match
ERRORS=0

if [[ "$PLUGIN_VERSION" != "$README_VERSION" ]]; then
    echo "❌ ERROR: oauth-login.php ($PLUGIN_VERSION) != readme.txt ($README_VERSION)"
    ERRORS=$((ERRORS + 1))
fi

if [[ "$PLUGIN_VERSION" != "$WEBPACK_VERSION" ]]; then
    echo "❌ ERROR: oauth-login.php ($PLUGIN_VERSION) != webpack.mix.js ($WEBPACK_VERSION)"
    ERRORS=$((ERRORS + 1))
fi

if [[ "$PLUGIN_VERSION" != "$ASSETS_VERSION" ]]; then
    echo "❌ ERROR: oauth-login.php ($PLUGIN_VERSION) != Assets.php ($ASSETS_VERSION)"
    ERRORS=$((ERRORS + 1))
fi

if [[ $ERRORS -eq 0 ]]; then
    echo "✅ All versions match: $PLUGIN_VERSION"
    exit 0
else
    echo ""
    echo "❌ Version mismatch detected! Please update all files to use version: $PLUGIN_VERSION"
    echo ""
    echo "Files to update:"
    [[ "$PLUGIN_VERSION" != "$README_VERSION" ]] && echo "  - readme.txt (change 'Stable tag: $README_VERSION' to '$PLUGIN_VERSION')"
    [[ "$PLUGIN_VERSION" != "$WEBPACK_VERSION" ]] && echo "  - webpack.mix.js (change 'style-$WEBPACK_VERSION.css' to 'style-$PLUGIN_VERSION.css')"
    [[ "$PLUGIN_VERSION" != "$ASSETS_VERSION" ]] && echo "  - src/Modules/Assets.php (change 'style-$ASSETS_VERSION.css' to 'style-$PLUGIN_VERSION.css')"
    exit 1
fi
