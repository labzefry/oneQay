#!/usr/bin/env bash
set -euo pipefail

source_sha="${1:-}"

if [[ ! "$source_sha" =~ ^[0-9a-f]{40}$ ]]; then
  echo "A full 40-character Git SHA is required." >&2
  exit 1
fi

resolved_sha="$(git rev-parse HEAD)"
if [[ "$resolved_sha" != "$source_sha" ]]; then
  echo "Checked-out source does not match requested release SHA." >&2
  exit 1
fi

release_id="m75-preview-${source_sha:0:12}"
source_epoch="$(git show -s --format=%ct "$source_sha")"
stage_root="dist/stage/${release_id}"
private_app_root="${stage_root}/apps/web"
public_surface="${stage_root}/public-surface"
archive_name="${release_id}.tar.gz"
archive_path="dist/${archive_name}"

required_files=(
  "apps/web/artisan"
  "apps/web/bootstrap/app.php"
  "apps/web/composer.lock"
  "apps/web/public/index.php"
  "apps/web/public/.htaccess"
  "apps/web/public/build/manifest.json"
  "apps/web/vendor/autoload.php"
)

for required_file in "${required_files[@]}"; do
  if [[ ! -f "$required_file" ]]; then
    echo "Missing required release input: $required_file" >&2
    exit 1
  fi
done

if [[ -e apps/web/.env ]]; then
  echo "Tracked or generated .env is forbidden in the release input." >&2
  exit 1
fi

rm -rf dist/stage "$archive_path" "${archive_path}.sha256"
mkdir -p "${stage_root}/apps" "$public_surface"

cp -a apps/web "$private_app_root"
rm -rf "${private_app_root}/node_modules"

if find "$private_app_root" -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' \) -print -quit | grep -q .; then
  echo "Forbidden secret-bearing file shape found in private release payload." >&2
  exit 1
fi

cp -a apps/web/public/. "$public_surface/"

cat > "${public_surface}/index.php" <<PHP
<?php

use Illuminate\\Foundation\\Application;
use Illuminate\\Http\\Request;

define('LARAVEL_START', microtime(true));

\$releaseId = '${release_id}';
\$accountHome = dirname(__DIR__, 2);
\$appRoot = \$accountHome.'/oneqay-preview/releases/'.\$releaseId.'/apps/web';

if (!is_file(\$appRoot.'/vendor/autoload.php') || !is_file(\$appRoot.'/bootstrap/app.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'oneQay Technical Preview unavailable';
    exit;
}

if (file_exists(\$maintenance = \$appRoot.'/storage/framework/maintenance.php')) {
    require \$maintenance;
}

require \$appRoot.'/vendor/autoload.php';

/** @var Application \$app */
\$app = require_once \$appRoot.'/bootstrap/app.php';

\$app->handleRequest(Request::capture());

// Author by Lab | zefry
PHP

cat > "${stage_root}/RELEASE.json" <<JSON
{
  "product": "oneQay",
  "environment": "TECHNICAL_PREVIEW",
  "production": false,
  "synthetic_data_only": true,
  "source_commit": "${source_sha}",
  "release_id": "${release_id}",
  "private_application_relative_path": "oneqay-preview/releases/${release_id}/apps/web",
  "public_surface_source": "public-surface",
  "attribution": "Lab | zefry"
}
JSON

if find "$public_surface" -mindepth 1 \( -name '.env' -o -name '.git' -o -name 'vendor' -o -name 'storage' -o -name 'tests' -o -name 'composer.json' -o -name 'composer.lock' -o -name 'package.json' -o -name 'package-lock.json' -o -name 'artisan' \) -print -quit | grep -q .; then
  echo "Public surface contains a forbidden private application path." >&2
  exit 1
fi

mkdir -p dist

tar \
  --sort=name \
  --mtime="@${source_epoch}" \
  --owner=0 \
  --group=0 \
  --numeric-owner \
  -C dist/stage \
  -cf - "$release_id" | gzip -n > "$archive_path"

(
  cd dist
  sha256sum "$archive_name" > "${archive_name}.sha256"
)

tar -tzf "$archive_path" > /tmp/oneqay-preview-release-contents.txt

if grep -E '(^|/)(\.env|\.git)(/|$)|(^|/)(node_modules)(/|$)' /tmp/oneqay-preview-release-contents.txt; then
  echo "Forbidden path found in packaged release." >&2
  exit 1
fi

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
  {
    echo "release_id=$release_id"
    echo "archive_path=$archive_path"
    echo "checksum_path=${archive_path}.sha256"
  } >> "$GITHUB_OUTPUT"
fi

printf 'Prepared %s from %s\n' "$release_id" "$source_sha"

# Author by Lab | zefry
