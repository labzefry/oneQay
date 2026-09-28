#!/usr/bin/env bash
set -euo pipefail

# Author by Lab | zefry

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
out="${1:-$repo_root/build/production-fixed-public-cpanel-no-ssh-operator-kit}"
rm -rf "$out"
mkdir -p "$out/tools/production/cpanel" "$out/tools/cpanel" "$out/tools/deployment" "$out/input" "$out/private" "$out/output"

copy() { install -m 0644 "$repo_root/$1" "$out/$1"; }
copy tools/production/cpanel/inspect-production-fixed-public-cpanel-no-ssh-target.php
copy tools/production/cpanel/prepare-production-fixed-public-cpanel-no-ssh-target-candidate.php
copy tools/production/cpanel/prepare-production-fixed-public-deployment-authority-request.php
copy tools/production/cpanel/prepare-production-fixed-public-deployment-plan.php
copy tools/production/cpanel/execute-production-fixed-public-cpanel-no-ssh-dark-deployment.php
copy tools/production/cpanel/qualify-production-fixed-public-deployment-evidence.php
copy tools/qualify-production-deployment-authority.php
copy tools/cpanel/fixed-public-document-root-bridge.php
copy tools/deployment/production-fixed-public-cpanel-no-ssh-target-input.schema.json
copy tools/deployment/production-fixed-public-cpanel-no-ssh-target-profile.schema.json
copy tools/deployment/production-fixed-public-cpanel-no-ssh-target-candidate.schema.json
copy ops/final-shift-close/PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_SUCCESSOR_CONTRACT.json

cat > "$out/input/target-input.template.json" <<'JSON'
{
  "schema_version": 1,
  "target_class": "CPANEL_NO_SSH",
  "execution_channel": "CPANEL_CRON_PHP_CLI_NO_SSH",
  "environment_id": "replace-production-environment-id",
  "runtime_class": "production",
  "production": true,
  "production_data_allowed": true,
  "isolated_environment": true,
  "release_binding": {
    "release_id": "production-505518f79e8a",
    "source_commit": "505518f79e8a70f789b94f5074a040eae785aeb0",
    "artifact_sha256": "906fb0dd630fc1de95e23a5505999567e2f287b53da8cb8ec346f9d44e311695"
  },
  "filesystem": {
    "deployment_root": "/replace/private/production-root",
    "release_root": "/replace/private/production-root/releases",
    "shared_runtime_root": "/replace/private/production-root/shared",
    "active_release_pointer": "/replace/private/production-root/current",
    "document_root": "/replace/public/document-root",
    "document_root_mode": "FIXED_PUBLIC_BRIDGE"
  },
  "health": {"url":"https://replace-production-host/health/live","path":"/health/live"},
  "operator_assertions": {
    "durable_database_persistence": true,
    "durable_session": true,
    "authorization": true,
    "transaction_durability": true,
    "pos_durability": true,
    "authenticated_configuration_channel": true,
    "read_before_write": true,
    "read_after_write": true,
    "non_mutating_health_attestation": true,
    "verified_rollback": true
  },
  "attribution": "Lab | zefry"
}
JSON

cat > "$out/private/private-bindings.template.json" <<'JSON'
{
  "ONEQAY_PRODUCTION_ATTESTATION_TOKEN": "REPLACE_WITH_PRIVATE_TOKEN_MINIMUM_32_CHARACTERS",
  "ONEQAY_PRODUCTION_ENVIRONMENT_ID": "replace-production-environment-id",
  "ONEQAY_RUNNING_ARTIFACT_SHA256": "906fb0dd630fc1de95e23a5505999567e2f287b53da8cb8ec346f9d44e311695",
  "ONEQAY_RUNNING_SOURCE_COMMIT": "505518f79e8a70f789b94f5074a040eae785aeb0",
  "ONEQAY_RUNTIME_CLASS": "production"
}
JSON
chmod 0600 "$out/private/private-bindings.template.json"

cat > "$out/README.md" <<'MARKDOWN'
# oneQay Production Fixed-Public cPanel No-SSH Operator Kit

Author by Lab | zefry

This kit supports **qualification and separately authorized dark deployment only** for a real Production cPanel target with a fixed public document root and no SSH.

It does not grant Production deployment authority or Production business-traffic authority. Migration #27 replay, permission reprovisioning, Final Shift Close reactivation, target reselection, producer dispatch, and updater activation remain forbidden.

## Stable cPanel execution rule

Use one simple absolute PHP invocation per Cron entry and redirect to one absolute log path. Do not chain `cd`, `&&`, `;`, shell conditionals, or child-process execution.

## Qualification

1. Replace only real observed target values in `input/target-input.template.json`.
2. Create the real private binding file under the private shared-runtime tree; mode must be `0600`.
3. Run the inspector, then target-candidate preparation.
4. Only after exact target qualification may an authority request be prepared against the exact Production manifest/archive and exact same-source durable-staging evidence.
5. A separately issued authority may live at most 900 seconds and still cannot activate Production business traffic.

When PHP `symlink()` is disabled by the provider, the target qualifies only if PHP hard links are actually supported. The successor never bypasses `disable_functions` and never invokes shell/SSH from PHP.
MARKDOWN

find "$out" -type f -not -path '*/private/*' -exec touch -t 202609280000 {} +
if command -v zip >/dev/null 2>&1; then
  (cd "$out" && find . -type f -print | LC_ALL=C sort | zip -X -q "../oneqay-production-fixed-public-cpanel-no-ssh-operator-kit.zip" -@)
  printf '%s\n' "$out/../oneqay-production-fixed-public-cpanel-no-ssh-operator-kit.zip"
else
  printf '%s\n' "$out"
fi
