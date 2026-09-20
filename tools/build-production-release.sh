#!/usr/bin/env bash
set -euo pipefail

# Author by Lab | zefry
source_sha="${1:-}"

if [[ ! "$source_sha" =~ ^[0-9a-f]{40}$ ]]; then echo "A full 40-character Git SHA is required." >&2; exit 1; fi
resolved_sha="$(git rev-parse HEAD)"
if [[ "$resolved_sha" != "$source_sha" ]]; then echo "Checked-out source does not match requested production release SHA." >&2; exit 1; fi

release_id="production-${source_sha:0:12}"
source_epoch="$(git show -s --format=%ct "$source_sha")"
build_provenance="${ONEQAY_BUILD_PROVENANCE:-local://source/${source_sha}}"
stage_root="dist/stage/${release_id}"
application_root="${stage_root}/apps/web"
archive_name="${release_id}.tar.gz"
archive_path="dist/${archive_name}"
checksum_path="${archive_path}.sha256"
manifest_path="dist/${release_id}.manifest.json"

required_files=("apps/web/artisan" "apps/web/bootstrap/app.php" "apps/web/composer.lock" "apps/web/public/index.php" "apps/web/public/.htaccess" "apps/web/public/build/manifest.json" "apps/web/vendor/autoload.php" "apps/web/app/Providers/PosOperationsHubServiceProvider.php" "apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php" "release/production-manifest-v1.schema.json" "tools/validate-production-release-manifest.php" "ops/final-shift-close/PRODUCTION_RELEASE_DEPLOYMENT_GOVERNANCE_CONTRACT.json")
for required_file in "${required_files[@]}"; do [[ -f "$required_file" ]] || { echo "Missing required production release input: $required_file" >&2; exit 1; }; done
[[ ! -e apps/web/.env && ! -e apps/web/bootstrap/cache/config.php ]] || { echo "Production release input must not contain .env or cached Laravel configuration." >&2; exit 1; }

mapfile -t migration_files < <(find apps/web/database/migrations -maxdepth 1 -type f -name '*.php' -printf '%f\n' | LC_ALL=C sort)
[[ "${#migration_files[@]}" -eq 27 ]] || { echo "Production release requires exact canonical 27 migrations." >&2; exit 1; }
expected_latest_migration="0000_00_00_000027_create_pos_shift_close_evidence_foundation.php"
[[ "${migration_files[-1]}" = "$expected_latest_migration" ]] || { echo "Production migration tail mismatch." >&2; exit 1; }

rm -rf dist/stage "$archive_path" "$checksum_path" "$manifest_path"
mkdir -p "${stage_root}/apps" "${stage_root}/release-contract"
cp -a apps/web "$application_root"
rm -rf "${application_root}/node_modules" "${application_root}/tests"
rm -rf "${application_root}/storage/logs"/* "${application_root}/storage/framework/cache"/* "${application_root}/storage/framework/sessions"/* "${application_root}/storage/framework/views"/*
cp release/production-manifest-v1.schema.json "${stage_root}/release-contract/"
cp ops/final-shift-close/PRODUCTION_RELEASE_DEPLOYMENT_GOVERNANCE_CONTRACT.json "${stage_root}/release-contract/"
if find "$stage_root" -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' -o -name '*.p12' -o -name '*.pfx' \) -print -quit | grep -q .; then echo "Forbidden secret-bearing file shape found in production payload." >&2; exit 1; fi
[[ ! -d "${application_root}/node_modules" && ! -d "${application_root}/tests" ]] || exit 1

application_payload_sha256="$(cd "$application_root"; while IFS= read -r -d '' path; do rel="${path#./}"; printf '%s\0' "$rel"; sha256sum "$path" | awk '{printf "%s\\0", $1}'; done < <(find . -type f -print0 | LC_ALL=C sort -z) | sha256sum | awk '{print $1}')"

cat > "${stage_root}/RELEASE.json" <<JSON
{"payload_metadata_version":1,"product":"oneQay","release_id":"${release_id}","environment":"PRODUCTION","required_runtime_class":"production","production":true,"synthetic_fixture_runtime":false,"production_data_allowed":true,"source_commit":"${source_sha}","application_payload_sha256":"${application_payload_sha256}","staging_payload_equivalence_required":true,"migration_source_included":true,"migration_count":27,"latest_migration":"${expected_latest_migration}","migration_execution_state":"NOT_EXECUTED_BY_ARTIFACT_BUILD","migration_execution_authorized":false,"deployment_authority":"NOT_GRANTED","environment_deployment":"NOT_PERFORMED","feature_activation":"INACTIVE","technical_preview_activation":"NOT_AUTHORIZED","production_activation":"NOT_AUTHORIZED","updater_activation":"INACTIVE","selected_target":null,"producer_dispatch":"NOT_PERFORMED","readiness_endpoint":"/health/ready","running_source_commit_environment_key":"ONEQAY_RUNNING_SOURCE_COMMIT","running_artifact_sha256_environment_key":"ONEQAY_RUNNING_ARTIFACT_SHA256","attestation_token_environment_key":"ONEQAY_PRODUCTION_RUNTIME_ATTESTATION_TOKEN","environment_values_embedded":false,"attribution":"Lab | zefry"}
JSON

mkdir -p dist
tar --sort=name --mtime="@${source_epoch}" --owner=0 --group=0 --numeric-owner -C dist/stage -cf - "$release_id" | gzip -n > "$archive_path"
artifact_sha256="$(sha256sum "$archive_path" | awk '{print $1}')"
artifact_size="$(wc -c < "$archive_path" | tr -d '[:space:]')"
printf '%s  %s\n' "$artifact_sha256" "$archive_name" > "$checksum_path"

cat > "$manifest_path" <<JSON
{
  "manifest_version":1,"schema_id":"oneqay.production-release-manifest.v1",
  "product":{"name":"oneQay","repository":"labzefry/oneQay"},
  "release":{"id":"${release_id}","channel":"PRODUCTION","environment":"PRODUCTION","production":true,"synthetic_fixture_runtime":false,"production_data_allowed":true},
  "source":{"commit_sha":"${source_sha}"},
  "build":{"provider":"GITHUB_ACTIONS_OR_EQUIVALENT_TRUSTED_CI","source_date_epoch":${source_epoch},"provenance_reference":"${build_provenance}"},
  "artifact":{"filename":"${archive_name}","format":"tar.gz","media_type":"application/gzip","size_bytes":${artifact_size},"sha256":"${artifact_sha256}"},
  "application_payload":{"digest_algorithm":"PATH_AND_CONTENT_SHA256_V1","sha256":"${application_payload_sha256}","staging_equivalence_required":true},
  "runtime":{"required_runtime_class":"production","php_constraint":"^8.2","build_php":"8.3","build_node":"24.19.0","runtime_build_tools_required":false},
  "migration":{"source_included":true,"expected_count":27,"latest_migration":"${expected_latest_migration}","execution_state":"NOT_EXECUTED_BY_ARTIFACT_BUILD","execution_authorized":false},
  "attestation_binding":{"readiness_endpoint":"/health/ready","source_commit_environment_key":"ONEQAY_RUNNING_SOURCE_COMMIT","artifact_sha256_environment_key":"ONEQAY_RUNNING_ARTIFACT_SHA256","attestation_token_environment_key":"ONEQAY_PRODUCTION_RUNTIME_ATTESTATION_TOKEN","environment_values_embedded":false},
  "operational_boundary":{"environment_creation":"NOT_PERFORMED","environment_deployment":"NOT_PERFORMED","migration27_execution":"NOT_PERFORMED","permission_provisioning":"NONE","feature_activation":"INACTIVE","deployment_authority":"NOT_GRANTED","technical_preview_activation":"NOT_AUTHORIZED","production_activation":"NOT_AUTHORIZED","updater_activation":"INACTIVE","selected_target":null,"producer_dispatch":"NOT_PERFORMED"},
  "attribution":"Lab | zefry"
}
JSON
php tools/validate-production-release-manifest.php "$manifest_path" "$archive_path"
tar -tzf "$archive_path" > /tmp/oneqay-production-release-contents.txt
! grep -E '(^|/)(\.env|\.git)(/|$)|(^|/)(node_modules|tests)(/|$)' /tmp/oneqay-production-release-contents.txt
[[ "$(grep -Ec '/apps/web/database/migrations/[^/]+\.php$' /tmp/oneqay-production-release-contents.txt)" -eq 27 ]]
if [[ -n "${GITHUB_OUTPUT:-}" ]]; then { echo "release_id=$release_id"; echo "archive_path=$archive_path"; echo "checksum_path=$checksum_path"; echo "manifest_path=$manifest_path"; echo "artifact_sha256=$artifact_sha256"; echo "application_payload_sha256=$application_payload_sha256"; } >> "$GITHUB_OUTPUT"; fi
printf 'Production governed release candidate built: %s\nSHA-256: %s\nApplication payload SHA-256: %s\nProduction deployment authority: NOT GRANTED\nProduction activation: NOT AUTHORIZED\nAuthor by Lab | zefry\n' "$archive_name" "$artifact_sha256" "$application_payload_sha256"
