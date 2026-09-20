#!/usr/bin/env bash
set -euo pipefail

# Author by Lab | zefry

source_sha="${1:-}"

if [[ ! "$source_sha" =~ ^[0-9a-f]{40}$ ]]; then
  echo "A full 40-character Git SHA is required." >&2
  exit 1
fi

resolved_sha="$(git rev-parse HEAD)"
if [[ "$resolved_sha" != "$source_sha" ]]; then
  echo "Checked-out source does not match requested cPanel operator kit SHA." >&2
  exit 1
fi

source_epoch="$(git show -s --format=%ct "$source_sha")"
kit_id="cpanel-no-ssh-operator-kit-${source_sha:0:12}"
stage_parent="dist/cpanel-kit-stage"
stage_root="${stage_parent}/${kit_id}"
zip_path="dist/${kit_id}.zip"
manifest_path="dist/${kit_id}.manifest.json"
checksum_path="${zip_path}.sha256"
contract_path="ops/final-shift-close/DURABLE_STAGING_CPANEL_NO_SSH_OPERATOR_KIT_PUBLICATION_CONTRACT.json"

required_files=(
  "$contract_path"
  "ops/final-shift-close/DURABLE_STAGING_CPANEL_NO_SSH_TARGET_QUALIFICATION_CONTRACT.json"
  "ops/final-shift-close/DURABLE_STAGING_CPANEL_NO_SSH_GUARDED_DEPLOYMENT_EXECUTION_CONTRACT.json"
  "tools/cpanel/inspect-durable-staging-cpanel-no-ssh-target.php"
  "tools/cpanel/prepare-durable-staging-cpanel-no-ssh-target-candidate.php"
  "tools/cpanel/execute-durable-staging-cpanel-no-ssh-deployment.php"
  "tools/cpanel/fixed-public-document-root-bridge.php"
  "tools/deployment/cpanel-no-ssh-durable-staging-target-profile.schema.json"
  "tools/deployment/durable-staging-deployment-handoff.schema.json"
  "tools/deployment/durable-staging-operator-target-candidate.schema.json"
  "tools/prepare-durable-staging-deployment-authority-request.php"
  "tools/deployment/durable-staging-deployment-authority-request.schema.json"
  "tools/qualify-durable-staging-deployment-authority.php"
  "tools/deployment/durable-staging-deployment-authority.schema.json"
  "tools/deployment/durable-staging-operator-target.schema.json"
  "tools/prepare-durable-staging-operator-deployment-plan.php"
  "tools/deployment/durable-staging-operator-deployment-plan.schema.json"
  "tools/qualify-durable-staging-deployment-evidence.php"
  "tools/deployment/durable-staging-deployment-evidence.schema.json"
)

for file in "${required_files[@]}"; do
  if [[ ! -f "$file" ]]; then
    echo "Missing required cPanel operator kit input: $file" >&2
    exit 1
  fi
done

jq -e . "$contract_path" >/dev/null

release_run_id="$(jq -r '.application_release_reference.publication_run_id' "$contract_path")"
release_artifact_id="$(jq -r '.application_release_reference.actions_artifact_id' "$contract_path")"
release_artifact_name="$(jq -r '.application_release_reference.actions_artifact_name' "$contract_path")"
release_outer_digest="$(jq -r '.application_release_reference.actions_outer_digest' "$contract_path")"
release_expires_at="$(jq -r '.application_release_reference.expires_at' "$contract_path")"
release_id="$(jq -r '.application_release_reference.release_id' "$contract_path")"
release_source="$(jq -r '.application_release_reference.source_commit' "$contract_path")"
release_artifact_filename="$(jq -r '.application_release_reference.artifact_filename' "$contract_path")"
release_artifact_size="$(jq -r '.application_release_reference.artifact_size_bytes' "$contract_path")"
release_artifact_sha="$(jq -r '.application_release_reference.artifact_sha256' "$contract_path")"
release_manifest_sha="$(jq -r '.application_release_reference.manifest_sha256' "$contract_path")"
release_handoff_sha="$(jq -r '.application_release_reference.handoff_sha256' "$contract_path")"
handoff_state="$(jq -r '.application_release_reference.handoff_state' "$contract_path")"

[[ "$release_run_id" =~ ^[0-9]+$ ]]
[[ "$release_artifact_id" =~ ^[0-9]+$ ]]
[[ "$release_artifact_name" =~ ^oneqay-durable-staging-[0-9a-f]{12}-operator-bundle$ ]]
[[ "$release_outer_digest" =~ ^sha256:[0-9a-f]{64}$ ]]
[[ "$release_expires_at" =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$ ]]
[[ "$release_id" =~ ^durable-staging-[0-9a-f]{12}$ ]]
[[ "$release_source" =~ ^[0-9a-f]{40}$ ]]
[[ "$release_artifact_filename" == "$release_id.tar.gz" ]]
[[ "$release_artifact_size" =~ ^[0-9]+$ ]]
[[ "$release_artifact_sha" =~ ^[0-9a-f]{64}$ ]]
[[ "$release_manifest_sha" =~ ^[0-9a-f]{64}$ ]]
[[ "$release_handoff_sha" =~ ^[0-9a-f]{64}$ ]]
[[ "$handoff_state" == "VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED" ]]
[[ "$release_id" == "durable-staging-${release_source:0:12}" ]]

rm -rf "$stage_parent" "$zip_path" "$manifest_path" "$checksum_path"
mkdir -p "$stage_root"

for file in "${required_files[@]}"; do
  mkdir -p "$stage_root/$(dirname "$file")"
  cp "$file" "$stage_root/$file"
done

cat > "$stage_root/KIT.json" <<JSON
{
  "schema_version": 1,
  "kit_state": "CPANEL_NO_SSH_OPERATOR_QUALIFICATION_KIT_NOT_DEPLOYED",
  "kit_id": "$kit_id",
  "kit_source_commit": "$source_sha",
  "application_release_reference": {
    "publication_run_id": $release_run_id,
    "actions_artifact_id": $release_artifact_id,
    "actions_artifact_name": "$release_artifact_name",
    "actions_outer_digest": "$release_outer_digest",
    "expires_at": "$release_expires_at",
    "release_id": "$release_id",
    "source_commit": "$release_source",
    "artifact_filename": "$release_artifact_filename",
    "artifact_size_bytes": $release_artifact_size,
    "artifact_sha256": "$release_artifact_sha",
    "manifest_sha256": "$release_manifest_sha",
    "handoff_sha256": "$release_handoff_sha",
    "handoff_state": "$handoff_state"
  },
  "operator_channel": "CPANEL_FILE_MANAGER_PLUS_ONE_SHOT_CRON_PHP_CLI",
  "deployment_authority": "NOT_GRANTED",
  "migration27_execution": "NOT_PERFORMED",
  "target_selection": "BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET",
  "selected_target": null,
  "producer_dispatch": "NOT_PERFORMED",
  "secret_values_embedded": false,
  "attribution": "Lab | zefry"
}
JSON

cat > "$stage_root/target-input.template.json" <<JSON
{
  "schema_version": 1,
  "target_class": "CPANEL_NO_SSH",
  "execution_channel": "CPANEL_CRON_PHP_CLI_NO_SSH",
  "environment_id": "<real-environment-id>",
  "runtime_class": "durable-staging",
  "production": false,
  "production_data_allowed": false,
  "synthetic_fixture_runtime": false,
  "release_binding": {
    "source_commit": "$release_source",
    "artifact_sha256": "$release_artifact_sha"
  },
  "filesystem": {
    "deployment_root": "<absolute-private-deployment-root>",
    "release_root": "<absolute-private-release-root>",
    "shared_runtime_root": "<absolute-private-shared-runtime-root>",
    "active_release_pointer": "<absolute-active-release-pointer>",
    "document_root": "<active-release-pointer>/apps/web/public",
    "document_root_mode": "ACTIVE_RELEASE_PUBLIC"
  },
  "operator_assertions": {
    "durable_database_persistence": false,
    "durable_session": false,
    "authorization": false,
    "transaction_durability": false,
    "pos_durability": false,
    "authenticated_configuration_channel": false,
    "read_before_write": false,
    "read_after_write": false,
    "non_mutating_health_attestation": false,
    "verified_rollback": false
  },
  "attribution": "Lab | zefry"
}
JSON

cat > "$stage_root/private-bindings.template.json" <<JSON
{
  "ONEQAY_RUNTIME_CLASS": "durable-staging",
  "ONEQAY_RUNNING_SOURCE_COMMIT": "$release_source",
  "ONEQAY_RUNNING_ARTIFACT_SHA256": "$release_artifact_sha",
  "ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID": "<real-environment-id>",
  "ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN": "<private-attestation-token>",
  "ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED": "true"
}
JSON

cat > "$stage_root/README.md" <<'MARKDOWN'
# oneQay cPanel No-SSH Operator Qualification Kit

Document-root modes:

- ACTIVE_RELEASE_PUBLIC: use when cPanel can point the domain directly to <active-release-pointer>/apps/web/public.
- FIXED_PUBLIC_BRIDGE: use when cPanel keeps a fixed public document root outside the private deployment tree. Set filesystem.document_root to the real public root and filesystem.document_root_mode to FIXED_PUBLIC_BRIDGE. Qualification requires a writable public root, existing rewrite-to-index .htaccess, strict public/private path separation, and atomic file/directory rename support.

The fixed-public executor writes only a minimal public index bridge plus build assets. Laravel source, vendor, runtime environment, bindings, and secrets remain under the private deployment tree. When PHP symlink() is available, the established active-pointer path is preserved. When the hosting provider disables PHP symlink() but PHP hard links are available, FIXED_PUBLIC_BRIDGE may use an authority-bound direct-release public bridge plus a private hard-link runtime binding; the provider disable_functions policy is never bypassed. Rollback rehearsal restores the previous public surface before reactivating the candidate. ACTIVE_RELEASE_PUBLIC still requires PHP symlink support.

Author by Lab | zefry

This kit is for a real isolated non-production cPanel/shared-hosting target where SSH is unavailable. It is a qualification/governance toolkit only. It is **not** deployment authority and does not contain the oneQay application release.

## Required companion bundle

Use only the application release identity recorded in `KIT.json`. Retrieve that exact separately published durable-staging operator bundle and keep its archive, manifest, SHA-256 sidecar, and deployment handoff together. Do not substitute an older bundle merely because it was used by an earlier Sprint.

## cPanel preparation

1. Use File Manager to create a private operator workspace outside the domain document root.
2. Upload and extract this ZIP in that private workspace.
3. Copy `target-input.template.json` to a private working JSON file and replace only real observed target values.
4. Copy `private-bindings.template.json` to a private bindings JSON file, replace placeholders with real host values, and set permissions to `0600` (no group/world access).
5. Never place approval tokens, database passwords, application keys, or other secrets inside the kit or repository.
6. Use cPanel Cron Jobs as one-shot commands. Remove the temporary Cron entry after each step.

The PHP CLI path is hosting-specific. Replace `<PHP_CLI>` with the actual cPanel PHP CLI binary approved by the provider.

## Step 1 — Observe the real cPanel target

```text
<PHP_CLI> tools/cpanel/inspect-durable-staging-cpanel-no-ssh-target.php \
  <target-input.json> \
  <private-bindings.json> \
  <target-profile.json>
```

Success state:

`CPANEL_NO_SSH_TARGET_PROFILE_OBSERVED`

The inspector fails closed if PHP/runtime/filesystem/binding/document-root requirements are not met.

## Step 2 — Prepare the canonical Sprint208 target candidate

```text
<PHP_CLI> tools/cpanel/prepare-durable-staging-cpanel-no-ssh-target-candidate.php \
  <target-profile.json> \
  <target-candidate.json>
```

Expected state:

`OPERATOR_TARGET_CANDIDATE`

## Step 3 — Prepare the Sprint208 authority request

Use the deployment handoff from the separately published application bundle:

```text
<PHP_CLI> tools/prepare-durable-staging-deployment-authority-request.php \
  <deployment-handoff.json> \
  <target-candidate.json> \
  <deployment-authority-request.json>
```

Expected state:

`DURABLE_STAGING_DEPLOYMENT_AUTHORITY_REQUEST_PENDING_APPROVAL`

This request does not grant authority.

## Step 4 — Qualify separately issued authority

The external authority must be exact-request/target/release bound and valid for at most 900 seconds. Never put the approval token value in a Cron command.

Place the approval token temporarily in a private `0600` file outside the document root. Pipe that file to STDIN:

```text
cat <private-approval-token-file> | <PHP_CLI> tools/qualify-durable-staging-deployment-authority.php \
  <deployment-handoff.json> \
  <target-candidate.json> \
  <deployment-authority-request.json> \
  <deployment-authority.json> \
  <current-unix-time> \
  <qualified-operator-target.json>
```

Delete the private approval-token file immediately after the qualification attempt.

## Step 5 — Generate the Sprint207 plan

While authority remains current:

```text
<PHP_CLI> tools/prepare-durable-staging-operator-deployment-plan.php \
  <deployment-handoff.json> \
  <qualified-operator-target.json> \
  <deployment-plan.json>
```

Expected state:

`QUALIFIED_FOR_EXTERNAL_OPERATOR_EXECUTION_NOT_EXECUTED`

The plan itself performs no deployment.

## Step 6 — Guarded cPanel no-SSH deployment execution

Before authority expires, upload the exact durable-staging archive identified in `KIT.json` into the private operator workspace and prepare a private Laravel runtime environment file under the qualified shared-runtime root. The runtime environment file must be owner-only (0600) and must not be stored in the application archive.

Run the Sprint214 executor:

```text
<PHP_CLI> tools/cpanel/execute-durable-staging-cpanel-no-ssh-deployment.php \
  <deployment-plan.json> \
  <target-profile.json> \
  <durable-staging-archive.tar.gz> \
  <private-bindings.json> \
  <private-runtime-env> \
  <https-readiness-url> \
  <deployment-evidence.json>
```

The executor revalidates the plan fingerprint and current authority, verifies the exact archive SHA-256, extracts the release only after authority validation, binds the private runtime environment without exposing its value, atomically activates the immutable release, fetches the authenticated HTTPS readiness attestation, rehearses rollback to the previous serving state, reactivates the new release, fetches readiness again, and writes Sprint209-compatible deployment evidence only if every check succeeds. ACTIVE_RELEASE_PUBLIC uses the existing symlink pointer model. FIXED_PUBLIC_BRIDGE uses the same model when PHP symlink() is available; if the provider disables PHP symlink(), the governed fallback requires PHP hard-link support for the private runtime binding and makes the fixed public bridge itself the authority-checked atomic activation surface.

For ACTIVE_RELEASE_PUBLIC and symlink-capable FIXED_PUBLIC_BRIDGE targets, the existing absent/previous-symlink rollback semantics remain unchanged. For the FIXED_PUBLIC_BRIDGE no-PHP-symlink fallback, rollback rehearsal restores the previous public index/build surface and then reactivates the exact immutable candidate release; the private active pointer is not mutated by that fallback.

If execution fails after active-pointer mutation, the executor attempts to restore the previous active state and emits no success evidence.

## Step 7 — Sprint209 evidence qualification

If Step 6 succeeds, the Sprint214 executor has already produced real deployment evidence from the authority-bound execution, runtime readback, HTTPS health checks, and rollback rehearsal.

Qualify that evidence:

```text
<PHP_CLI> tools/qualify-durable-staging-deployment-evidence.php \
  <deployment-evidence.json> \
  <environment-id> \
  durable-staging \
  <running-source-commit> \
  <running-artifact-sha256> \
  <deployment-plan-fingerprint> \
  <deployment-authority-sha256>
```

Expected state:

`QUALIFIED_DEPLOYMENT_EVIDENCE_NOT_SELECTED`

## Fail-closed rule

Stop if any real host fact, required capability, PHP feature, filesystem operation, document-root isolation, authority field, digest, runtime readback, or rollback evidence cannot be verified.

The kit does not authorize migration #27, permission provisioning, Final Shift Close activation, target selection, producer dispatch, Technical Preview, Production, or updater activation.
MARKDOWN

python3 - "$stage_root" "$manifest_path" "$kit_id" "$source_sha" "$release_run_id" "$release_artifact_id" "$release_artifact_name" "$release_outer_digest" "$release_expires_at" "$release_id" "$release_source" "$release_artifact_filename" "$release_artifact_size" "$release_artifact_sha" "$release_manifest_sha" "$release_handoff_sha" "$handoff_state" <<'PY'
import hashlib
import json
import pathlib
import sys

stage = pathlib.Path(sys.argv[1])
manifest_path = pathlib.Path(sys.argv[2])
kit_id = sys.argv[3]
source_sha = sys.argv[4]
release_run_id = int(sys.argv[5])
release_artifact_id = int(sys.argv[6])
release_artifact_name = sys.argv[7]
release_outer_digest = sys.argv[8]
release_expires_at = sys.argv[9]
release_id = sys.argv[10]
release_source = sys.argv[11]
release_artifact_filename = sys.argv[12]
release_artifact_size = int(sys.argv[13])
release_artifact_sha = sys.argv[14]
release_manifest_sha = sys.argv[15]
release_handoff_sha = sys.argv[16]
handoff_state = sys.argv[17]

files = []
for path in sorted(p for p in stage.rglob("*") if p.is_file()):
    rel = path.relative_to(stage).as_posix()
    data = path.read_bytes()
    files.append({
        "path": rel,
        "sha256": hashlib.sha256(data).hexdigest(),
        "size_bytes": len(data),
    })

manifest = {
    "schema_version": 1,
    "kit_state": "CPANEL_NO_SSH_OPERATOR_QUALIFICATION_KIT_PUBLISHED_SOURCE",
    "kit_id": kit_id,
    "kit_source_commit": source_sha,
    "application_release_reference": {
        "publication_run_id": release_run_id,
        "actions_artifact_id": release_artifact_id,
        "actions_artifact_name": release_artifact_name,
        "actions_outer_digest": release_outer_digest,
        "expires_at": release_expires_at,
        "release_id": release_id,
        "source_commit": release_source,
        "artifact_filename": release_artifact_filename,
        "artifact_size_bytes": release_artifact_size,
        "artifact_sha256": release_artifact_sha,
        "manifest_sha256": release_manifest_sha,
        "handoff_sha256": release_handoff_sha,
        "handoff_state": handoff_state,
    },
    "files": files,
    "secret_values_embedded": False,
    "deployment_authority": "NOT_GRANTED",
    "migration27_execution": "NOT_PERFORMED",
    "selected_target": None,
    "producer_dispatch": "NOT_PERFORMED",
    "attribution": "Lab | zefry",
}
encoded = json.dumps(manifest, indent=2, sort_keys=True, ensure_ascii=False) + "\n"
manifest_path.write_text(encoded, encoding="utf-8")
(stage / "kit.manifest.json").write_text(encoded, encoding="utf-8")
PY

python3 - "$stage_root" "$zip_path" "$source_epoch" <<'PY'
import datetime
import pathlib
import sys
import zipfile

stage = pathlib.Path(sys.argv[1])
output = pathlib.Path(sys.argv[2])
epoch = int(sys.argv[3])
dt = datetime.datetime.fromtimestamp(epoch, datetime.timezone.utc)
year = min(max(dt.year, 1980), 2107)
second = dt.second - (dt.second % 2)
zip_time = (year, dt.month, dt.day, dt.hour, dt.minute, second)

with zipfile.ZipFile(output, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=9) as zf:
    for path in sorted(p for p in stage.rglob("*") if p.is_file()):
        rel = pathlib.Path(stage.name) / path.relative_to(stage)
        info = zipfile.ZipInfo(rel.as_posix(), zip_time)
        info.create_system = 3
        info.external_attr = (0o100600 << 16)
        info.compress_type = zipfile.ZIP_DEFLATED
        data = path.read_bytes()
        zf.writestr(info, data)
PY

zip_sha256="$(sha256sum "$zip_path" | awk '{print $1}')"
printf '%s  %s\n' "$zip_sha256" "$(basename "$zip_path")" > "$checksum_path"

python3 - "$zip_path" "$stage_root" <<'PY'
import pathlib
import sys
import zipfile

archive = pathlib.Path(sys.argv[1])
stage = pathlib.Path(sys.argv[2])
expected = sorted(
    (pathlib.Path(stage.name) / p.relative_to(stage)).as_posix()
    for p in stage.rglob("*")
    if p.is_file()
)
with zipfile.ZipFile(archive) as zf:
    actual = sorted(zf.namelist())
if actual != expected:
    raise SystemExit("zip_content_mismatch")
PY

if grep -RInE   --exclude='*.schema.json'   --exclude='*.php'   '(BEGIN (RSA|OPENSSH|EC|DSA) PRIVATE KEY|ghp_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,}|AKIA[0-9A-Z]{16})'   "$stage_root"; then
  echo "Potential secret pattern detected in cPanel operator kit." >&2
  exit 1
fi

if find "$stage_root" -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' -o -name '*.p12' -o -name '*.pfx' \) -print -quit | grep -q .; then
  echo "Forbidden secret-bearing file shape found in cPanel operator kit." >&2
  exit 1
fi

if grep -RIFq '"deployment_authority": "GRANTED"' "$stage_root"; then
  echo "Kit must not embed granted deployment authority." >&2
  exit 1
fi

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
  {
    echo "kit_id=$kit_id"
    echo "zip_path=$zip_path"
    echo "manifest_path=$manifest_path"
    echo "checksum_path=$checksum_path"
    echo "zip_sha256=$zip_sha256"
  } >> "$GITHUB_OUTPUT"
fi

printf 'cPanel no-SSH operator qualification kit built: %s\n' "$(basename "$zip_path")"
printf 'SHA-256: %s\n' "$zip_sha256"
printf 'Application bytes embedded: NO\n'
printf 'Deployment authority: NOT GRANTED\n'
printf 'Author by Lab | zefry\n'
