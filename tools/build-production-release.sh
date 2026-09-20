#!/usr/bin/env bash
set -euo pipefail
source_sha="${1:-}"
[[ "$source_sha" =~ ^[0-9a-f]{40}$ ]] || { echo "A full 40-character Git SHA is required." >&2; exit 1; }
test "$(git rev-parse HEAD)" = "$source_sha" || { echo "Source mismatch." >&2; exit 1; }
release_id="production-${source_sha:0:12}"
source_epoch="$(git show -s --format=%ct "$source_sha")"
application_tree_sha="$(git rev-parse "$source_sha:apps/web")"
build_provenance="${ONEQAY_BUILD_PROVENANCE:-local://source/${source_sha}}"
stage_root="dist/stage/${release_id}"
app_root="${stage_root}/apps/web"
archive="dist/${release_id}.tar.gz"
checksum="${archive}.sha256"
manifest="dist/${release_id}.manifest.json"
latest="0000_00_00_000027_create_pos_shift_close_evidence_foundation.php"
for f in apps/web/artisan apps/web/bootstrap/app.php apps/web/composer.lock apps/web/public/index.php apps/web/public/.htaccess apps/web/public/build/manifest.json apps/web/vendor/autoload.php "apps/web/database/migrations/$latest" release/production-manifest-v1.schema.json tools/validate-production-release-manifest.php ops/final-shift-close/PRODUCTION_RELEASE_DEPLOYMENT_GOVERNANCE_CONTRACT.json; do test -f "$f" || { echo "Missing $f" >&2; exit 1; }; done
test ! -e apps/web/.env
test ! -e apps/web/bootstrap/cache/config.php
test "$(find apps/web/database/migrations -maxdepth 1 -type f -name '*.php' | wc -l | tr -d '[:space:]')" -eq 27
rm -rf dist/stage "$archive" "$checksum" "$manifest"
mkdir -p "${stage_root}/apps" "${stage_root}/release-contract"
cp -a apps/web "$app_root"
rm -rf "$app_root/node_modules" "$app_root/tests" "$app_root/storage/logs"/* "$app_root/storage/framework/cache"/* "$app_root/storage/framework/sessions"/* "$app_root/storage/framework/views"/*
cp release/production-manifest-v1.schema.json "${stage_root}/release-contract/"
cp ops/final-shift-close/PRODUCTION_RELEASE_DEPLOYMENT_GOVERNANCE_CONTRACT.json "${stage_root}/release-contract/"
if find "$stage_root" -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' -o -name '*.p12' -o -name '*.pfx' \) -print -quit | grep -q .; then exit 1; fi
cat > "${stage_root}/RELEASE.json" <<JSON
{"payload_metadata_version":1,"product":"oneQay","release_id":"${release_id}","environment":"PRODUCTION","required_runtime_class":"production","production":true,"production_data_allowed":true,"source_commit":"${source_sha}","application_tree_sha":"${application_tree_sha}","governed_business_runtime_default_enabled":false,"migration_source_included":true,"migration_count":27,"latest_migration":"${latest}","migration_execution_state":"NOT_PERFORMED_BY_ARTIFACT_BUILD","migration_execution_authorized":false,"deployment_authority":"NOT_GRANTED","environment_deployment":"NOT_PERFORMED","production_traffic_activation":"NOT_AUTHORIZED","production_activation":"NOT_AUTHORIZED","updater_activation":"INACTIVE","target_selection":"NOT_PERFORMED","selected_target":null,"producer_dispatch":"NOT_PERFORMED","attribution":"Lab | zefry"}
JSON
mkdir -p dist
tar --sort=name --mtime="@${source_epoch}" --owner=0 --group=0 --numeric-owner -C dist/stage -cf - "$release_id" | gzip -n > "$archive"
sha="$(sha256sum "$archive" | awk '{print $1}')"
size="$(wc -c < "$archive" | tr -d '[:space:]')"
printf '%s  %s\n' "$sha" "${release_id}.tar.gz" > "$checksum"
cat > "$manifest" <<JSON
{"manifest_version":1,"schema_id":"oneqay.production-release-manifest.v1","product":{"name":"oneQay","repository":"labzefry/oneQay"},"release":{"id":"${release_id}","channel":"PRODUCTION_CANDIDATE","environment":"PRODUCTION","production":true,"production_data_allowed":true},"source":{"commit_sha":"${source_sha}","application_tree_sha":"${application_tree_sha}"},"build":{"provider":"GITHUB_ACTIONS_OR_EQUIVALENT_TRUSTED_CI","source_date_epoch":${source_epoch},"provenance_reference":"${build_provenance}"},"artifact":{"filename":"${release_id}.tar.gz","format":"tar.gz","media_type":"application/gzip","size_bytes":${size},"sha256":"${sha}"},"runtime":{"required_runtime_class":"production","governed_business_runtime_default_enabled":false,"php_constraint":"^8.2","build_php":"8.3","build_node":"24.19.0","runtime_build_tools_required":false},"migration":{"source_included":true,"expected_count":27,"latest_migration":"${latest}","execution_state":"NOT_PERFORMED_BY_ARTIFACT_BUILD","execution_authorized":false},"promotion_gate":{"verified_durable_staging_evidence_required":true,"same_source_commit_required":true,"production_traffic_activation_authorized":false},"operational_boundary":{"environment_deployment":"NOT_PERFORMED","migration27_execution":"NOT_PERFORMED","permission_provisioning":"NONE","feature_activation":"INACTIVE","deployment_authority":"NOT_GRANTED","technical_preview_activation":"NOT_AUTHORIZED","production_activation":"NOT_AUTHORIZED","updater_activation":"INACTIVE","target_selection":"NOT_PERFORMED","selected_target":null,"producer_dispatch":"NOT_PERFORMED"},"attribution":"Lab | zefry"}
JSON
php tools/validate-production-release-manifest.php "$manifest" "$archive"
tar -tzf "$archive" > /tmp/oneqay-production-list.txt
! grep -E '(^|/)(\.env|\.git)(/|$)|(^|/)(node_modules|tests)(/|$)' /tmp/oneqay-production-list.txt
test "$(grep -Ec '/apps/web/database/migrations/[^/]+\.php$' /tmp/oneqay-production-list.txt)" -eq 27
if [[ -n "${GITHUB_OUTPUT:-}" ]]; then echo "release_id=$release_id" >> "$GITHUB_OUTPUT"; fi
printf 'Production candidate built; deployment NOT PERFORMED; traffic activation NOT AUTHORIZED.\nAuthor by Lab | zefry\n'
