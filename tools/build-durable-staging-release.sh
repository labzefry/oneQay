#!/usr/bin/env bash
set -euo pipefail

source_sha="${1:-}"

if [[ ! "$source_sha" =~ ^[0-9a-f]{40}$ ]]; then
  echo "A full 40-character Git SHA is required." >&2
  exit 1
fi

resolved_sha="$(git rev-parse HEAD)"
if [[ "$resolved_sha" != "$source_sha" ]]; then
  echo "Checked-out source does not match requested durable staging release SHA." >&2
  exit 1
fi

release_id="durable-staging-${source_sha:0:12}"
source_epoch="$(git show -s --format=%ct "$source_sha")"
build_provenance="${ONEQAY_BUILD_PROVENANCE:-local://source/${source_sha}}"
stage_root="dist/stage/${release_id}"
application_root="${stage_root}/apps/web"
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
  "apps/web/app/Providers/PosOperationsHubServiceProvider.php"
  "apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php"
  "release/durable-staging-manifest-v1.schema.json"
  "tools/validate-durable-staging-release-manifest.php"
  "ops/final-shift-close/DURABLE_STAGING_RELEASE_ARTIFACT_CONTRACT.json"
)

for required_file in "${required_files[@]}"; do
  if [[ ! -f "$required_file" ]]; then
    echo "Missing required durable staging release input: $required_file" >&2
    exit 1
  fi
done

if [[ -e apps/web/.env ]]; then
  echo "Tracked or generated .env is forbidden in the durable staging release input." >&2
  exit 1
fi

if [[ -e apps/web/bootstrap/cache/config.php ]]; then
  echo "Cached Laravel configuration is forbidden in the durable staging release input." >&2
  exit 1
fi

mapfile -t migration_files < <(find apps/web/database/migrations -maxdepth 1 -type f -name '*.php' -printf '%f\n' | LC_ALL=C sort)
if [[ "${#migration_files[@]}" -ne 27 ]]; then
  echo "Durable staging release requires the exact canonical 27-migration source set." >&2
  exit 1
fi

expected_latest_migration="0000_00_00_000027_create_pos_shift_close_evidence_foundation.php"
if [[ "${migration_files[-1]}" != "$expected_latest_migration" ]]; then
  echo "Durable staging release migration tail does not match canonical migration #27." >&2
  exit 1
fi

rm -rf dist/stage "$archive_path" "$checksum_path" "$manifest_path"
mkdir -p "${stage_root}/apps" "${stage_root}/release-contract"

cp -a apps/web "$application_root"
rm -rf "${application_root}/node_modules" "${application_root}/tests"
rm -rf "${application_root}/storage/logs"/*
rm -rf "${application_root}/storage/framework/cache"/*
rm -rf "${application_root}/storage/framework/sessions"/*
rm -rf "${application_root}/storage/framework/views"/*
cp release/durable-staging-manifest-v1.schema.json "${stage_root}/release-contract/"
cp ops/final-shift-close/DURABLE_STAGING_RELEASE_ARTIFACT_CONTRACT.json "${stage_root}/release-contract/"

if [[ ! -d "${application_root}/database/migrations" ]]; then
  echo "Durable migration source is missing from staging payload." >&2
  exit 1
fi

packaged_migration_count="$(find "${application_root}/database/migrations" -maxdepth 1 -type f -name '*.php' | wc -l | tr -d '[:space:]')"
if [[ "$packaged_migration_count" != "27" ]]; then
  echo "Durable staging payload migration count drifted from canonical 27." >&2
  exit 1
fi

if [[ ! -f "${application_root}/database/migrations/${expected_latest_migration}" ]]; then
  echo "Durable staging payload is missing canonical migration #27 source." >&2
  exit 1
fi

if find "$stage_root" -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' -o -name '*.p12' -o -name '*.pfx' \) -print -quit | grep -q .; then
  echo "Forbidden secret-bearing file shape found in durable staging payload." >&2
  exit 1
fi

if [[ -d "${application_root}/node_modules" || -d "${application_root}/tests" ]]; then
  echo "Build-only node_modules or test suite must not enter durable staging payload." >&2
  exit 1
fi

cat > "${stage_root}/RELEASE.json" <<JSON
{
  "payload_metadata_version": 1,
  "product": "oneQay",
  "release_id": "${release_id}",
  "environment": "DURABLE_STAGING",
  "required_runtime_class": "durable-staging",
  "production": false,
  "synthetic_fixture_runtime": false,
  "production_data_allowed": false,
  "source_commit": "${source_sha}",
  "migration_source_included": true,
  "migration_count": 27,
  "latest_migration": "${expected_latest_migration}",
  "migration_execution_state": "NOT_EXECUTED_BY_ARTIFACT_BUILD",
  "migration_execution_authorized": false,
  "deployment_authority": "NOT_GRANTED",
  "environment_deployment": "NOT_PERFORMED",
  "feature_activation": "INACTIVE",
  "technical_preview_activation": "NOT_AUTHORIZED",
  "production_activation": "NOT_AUTHORIZED",
  "updater_activation": "INACTIVE",
  "target_selection": "BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET",
  "selected_target": null,
  "producer_dispatch": "NOT_PERFORMED",
  "readiness_endpoint": "/internal/oneqay/durable-runtime/readiness",
  "running_source_commit_environment_key": "ONEQAY_RUNNING_SOURCE_COMMIT",
  "running_artifact_sha256_environment_key": "ONEQAY_RUNNING_ARTIFACT_SHA256",
  "attestation_token_environment_key": "ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN",
  "environment_values_embedded": false,
  "attribution": "Lab | zefry"
}
JSON

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
  "schema_id": "oneqay.durable-staging-release-manifest.v1",
  "product": {
    "name": "oneQay",
    "repository": "labzefry/oneQay"
  },
  "release": {
    "id": "${release_id}",
    "channel": "STAGING",
    "environment": "DURABLE_STAGING",
    "production": false,
    "synthetic_fixture_runtime": false,
    "production_data_allowed": false
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
    "required_runtime_class": "durable-staging",
    "php_constraint": "^8.2",
    "build_php": "8.3",
    "build_node": "24.19.0",
    "runtime_build_tools_required": false
  },
  "migration": {
    "source_included": true,
    "expected_count": 27,
    "latest_migration": "${expected_latest_migration}",
    "execution_state": "NOT_EXECUTED_BY_ARTIFACT_BUILD",
    "execution_authorized": false
  },
  "attestation_binding": {
    "readiness_endpoint": "/internal/oneqay/durable-runtime/readiness",
    "source_commit_environment_key": "ONEQAY_RUNNING_SOURCE_COMMIT",
    "artifact_sha256_environment_key": "ONEQAY_RUNNING_ARTIFACT_SHA256",
    "attestation_token_environment_key": "ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN",
    "environment_values_embedded": false
  },
  "operational_boundary": {
    "environment_creation": "NOT_PERFORMED",
    "environment_deployment": "NOT_PERFORMED",
    "migration27_execution": "NOT_PERFORMED",
    "permission_provisioning": "NONE",
    "feature_activation": "INACTIVE",
    "deployment_authority": "NOT_GRANTED",
    "technical_preview_activation": "NOT_AUTHORIZED",
    "production_activation": "NOT_AUTHORIZED",
    "updater_activation": "INACTIVE",
    "target_selection": "BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET",
    "selected_target": null,
    "producer_dispatch": "NOT_PERFORMED"
  },
  "attribution": "Lab | zefry"
}
JSON

php tools/validate-durable-staging-release-manifest.php "$manifest_path" "$archive_path"

tar -tzf "$archive_path" > /tmp/oneqay-durable-staging-release-contents.txt

if grep -E '(^|/)(\.env|\.git)(/|$)|(^|/)(node_modules|tests)(/|$)' /tmp/oneqay-durable-staging-release-contents.txt; then
  echo "Forbidden path found in durable staging release." >&2
  exit 1
fi

if ! grep -Fq "/apps/web/database/migrations/${expected_latest_migration}" /tmp/oneqay-durable-staging-release-contents.txt; then
  echo "Durable staging release archive does not contain canonical migration #27 source." >&2
  exit 1
fi

archive_migration_count="$(
  grep -E "/apps/web/database/migrations/[^/]+\.php$" /tmp/oneqay-durable-staging-release-contents.txt |
    wc -l |
    tr -d '[:space:]'
)"
if [[ "$archive_migration_count" != "27" ]]; then
  echo "Durable staging archive migration count is not exactly 27." >&2
  exit 1
fi

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
  {
    echo "release_id=$release_id"
    echo "archive_path=$archive_path"
    echo "checksum_path=$checksum_path"
    echo "manifest_path=$manifest_path"
    echo "artifact_sha256=$artifact_sha256"
  } >> "$GITHUB_OUTPUT"
fi

printf 'Durable staging governed artifact built: %s\n' "$archive_name"
printf 'SHA-256: %s\n' "$artifact_sha256"
printf 'Migration source: INCLUDED / NOT EXECUTED\n'
printf 'Deployment authority: NOT GRANTED\n'
printf 'Author by Lab | zefry\n'
