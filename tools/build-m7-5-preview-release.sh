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
build_provenance="${ONEQAY_BUILD_PROVENANCE:-local://source/${source_sha}}"
stage_root="dist/stage/${release_id}"
private_app_root="${stage_root}/apps/web"
public_surface="${stage_root}/public-surface"
archive_name="${release_id}.tar.gz"
archive_path="dist/${archive_name}"
checksum_path="${archive_path}.sha256"
manifest_path="dist/${release_id}.manifest.json"

required_files=(
  "apps/web/artisan"
  "apps/web/bootstrap/app.php"
  "apps/web/composer.lock"
  "apps/web/public/index.php"
  "apps/web/public/.htaccess"
  "apps/web/public/build/manifest.json"
  "apps/web/vendor/autoload.php"
  "release/manifest-v1.schema.json"
  "tools/validate-release-manifest.php"
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

if [[ -e apps/web/bootstrap/cache/config.php ]]; then
  echo "Cached Laravel configuration is forbidden in the governed release input." >&2
  exit 1
fi

rm -rf dist/stage "$archive_path" "$checksum_path" "$manifest_path"
mkdir -p "${stage_root}/apps" "$public_surface"

cp -a apps/web "$private_app_root"
rm -rf "${private_app_root}/node_modules"

# Sprint 19 durable migrations are canonical Local/Test/CI source, but Technical Preview
# remains NO_SCHEMA_CHANGE until a separate Preview persistence gate exists.
rm -rf "${private_app_root}/database/migrations"
if [[ -d "${private_app_root}/database/migrations" ]]; then
  echo "Durable migration directory must not enter the Technical Preview release payload." >&2
  exit 1
fi

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
\$sharedRoot = \$accountHome.'/oneqay-preview/shared';
\$sharedEnvironmentRoot = \$sharedRoot.'/runtime';
\$sharedEnvironmentFile = \$sharedEnvironmentRoot.'/.env';

if (!is_file(\$appRoot.'/vendor/autoload.php') || !is_file(\$appRoot.'/bootstrap/app.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'oneQay Technical Preview unavailable';
    exit;
}

if (
    !is_dir(\$sharedRoot)
    || is_link(\$sharedRoot)
    || !is_dir(\$sharedEnvironmentRoot)
    || is_link(\$sharedEnvironmentRoot)
    || !is_file(\$sharedEnvironmentFile)
    || is_link(\$sharedEnvironmentFile)
    || !is_readable(\$sharedEnvironmentFile)
) {
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
\$app->useEnvironmentPath(\$sharedEnvironmentRoot);
\$app->loadEnvironmentFrom('.env');

\$app->handleRequest(Request::capture());

// Author by Lab | zefry
PHP

cat > "${stage_root}/RELEASE.json" <<JSON
{
  "payload_metadata_version": 1,
  "product": "oneQay",
  "environment": "TECHNICAL_PREVIEW",
  "production": false,
  "synthetic_data_only": true,
  "source_commit": "${source_sha}",
  "release_id": "${release_id}",
  "governed_manifest_sidecar": "${release_id}.manifest.json",
  "migration_classification": "NO_SCHEMA_CHANGE",
  "updater_activation": "DISABLED",
  "private_application_relative_path": "oneqay-preview/releases/${release_id}/apps/web",
  "public_surface_source": "public-surface",
  "shared_runtime_environment_profile": "PRIVATE_SHARED_DOTENV_V1",
  "shared_runtime_environment_relative_path": "oneqay-preview/shared/runtime/.env",
  "shared_runtime_secret_values_embedded": false,
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

artifact_sha256="$(sha256sum "$archive_path" | awk '{print $1}')"
artifact_size="$(wc -c < "$archive_path" | tr -d '[:space:]')"
printf '%s  %s\n' "$artifact_sha256" "$archive_name" > "$checksum_path"

cat > "$manifest_path" <<JSON
{
  "manifest_version": 1,
  "schema_id": "oneqay.release-manifest.v1",
  "product": {
    "name": "oneQay",
    "repository": "labzefry/oneQay"
  },
  "release": {
    "id": "${release_id}",
    "version": null,
    "channel": "PREVIEW",
    "environment": "TECHNICAL_PREVIEW",
    "production": false,
    "synthetic_data_only": true
  },
  "source": {
    "commit_sha": "${source_sha}"
  },
  "build": {
    "provider": "GITHUB_ACTIONS_OR_EQUIVALENT_TRUSTED_CI",
    "source_date_epoch": ${source_epoch},
    "provenance_reference": "${build_provenance}"
  },
  "artifact": {
    "filename": "${archive_name}",
    "format": "tar.gz",
    "media_type": "application/gzip",
    "size_bytes": ${artifact_size},
    "sha256": "${artifact_sha256}"
  },
  "runtime": {
    "php_constraint": "^8.2",
    "build_php": "8.3",
    "build_node": "24.19.0",
    "runtime_build_tools_required": false
  },
  "compatibility": {
    "supported_current_release_policy": "GOVERNED_PREVIEW_NO_SCHEMA_CHANGE",
    "allow_downgrade": false,
    "migration_classification": "NO_SCHEMA_CHANGE",
    "rollback_compatibility": "APPLICATION_POINTER_ROLLBACK_COMPATIBLE",
    "public_surface_compatibility": "M7_5_PREVIEW_PUBLIC_SURFACE_V1",
    "private_storage_layout_version": 1,
    "updater_activation": "DISABLED"
  },
  "release_notes_reference": "UPDATER.md#release-manifest-v1",
  "attribution": "Lab | zefry"
}
JSON

php tools/validate-release-manifest.php "$manifest_path" "$archive_path"

tar -tzf "$archive_path" > /tmp/oneqay-preview-release-contents.txt

if grep -E '(^|/)(\.env|\.git)(/|$)|(^|/)(node_modules)(/|$)' /tmp/oneqay-preview-release-contents.txt; then
  echo "Forbidden path found in packaged release." >&2
  exit 1
fi

if grep -F '/apps/web/database/migrations/' /tmp/oneqay-preview-release-contents.txt; then
  echo "Technical Preview release unexpectedly contains durable migration source." >&2
  exit 1
fi

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
  {
    echo "release_id=$release_id"
    echo "archive_path=$archive_path"
    echo "checksum_path=$checksum_path"
    echo "manifest_path=$manifest_path"
    echo "artifact_sha256=$artifact_sha256"
    echo "artifact_size=$artifact_size"
  } >> "$GITHUB_OUTPUT"
fi

printf 'Prepared governed release bundle %s from %s (%s bytes, sha256 %s)\n' \
  "$release_id" "$source_sha" "$artifact_size" "$artifact_sha256"

# Author by Lab | zefry