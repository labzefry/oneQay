#!/usr/bin/env bash
set -euo pipefail

# Author by Lab | zefry

source_sha="${1:-}"
[[ "$source_sha" =~ ^[0-9a-f]{40}$ ]] || { echo "A full 40-character Git SHA is required." >&2; exit 1; }
test "$(git rev-parse HEAD)" = "$source_sha" || { echo "Checked-out source does not match requested Production operator kit SHA." >&2; exit 1; }

source_epoch="$(git show -s --format=%ct "$source_sha")"
kit_id="production-operator-kit-${source_sha:0:12}"
stage_parent="dist/production-kit-stage"
stage_root="${stage_parent}/${kit_id}"
zip_path="dist/${kit_id}.zip"
manifest_path="dist/${kit_id}.manifest.json"
checksum_path="${zip_path}.sha256"
contract="ops/final-shift-close/PRODUCTION_OPERATOR_KIT_PUBLICATION_CONTRACT.json"

required_files=(
  "$contract"
  "ops/final-shift-close/PRODUCTION_DARK_DEPLOYMENT_EXECUTION_CONTRACT.json"
  "ops/final-shift-close/PRODUCTION_RELEASE_DEPLOYMENT_GOVERNANCE_CONTRACT.json"
  "tools/deployment/production-operator-target-input.schema.json"
  "tools/deployment/production-operator-target-profile.schema.json"
  "tools/deployment/production-target-candidate.schema.json"
  "tools/deployment/production-deployment-authority-request.schema.json"
  "tools/deployment/production-deployment-authority.schema.json"
  "tools/deployment/production-deployment-plan.schema.json"
  "tools/deployment/production-deployment-evidence.schema.json"
  "tools/inspect-production-operator-target.php"
  "tools/prepare-production-operator-target-candidate.php"
  "tools/prepare-production-deployment-authority-request.php"
  "tools/qualify-production-deployment-authority.php"
  "tools/prepare-production-deployment-plan.php"
  "tools/execute-production-dark-deployment.php"
  "tools/qualify-production-deployment-evidence.php"
)

for file in "${required_files[@]}"; do
  test -f "$file" || { echo "Missing Production operator kit input: $file" >&2; exit 1; }
done
jq -e . "$contract" >/dev/null

release_run_id="$(jq -r '.production_release_reference.publication_run_id' "$contract")"
release_artifact_id="$(jq -r '.production_release_reference.actions_artifact_id' "$contract")"
release_artifact_name="$(jq -r '.production_release_reference.actions_artifact_name' "$contract")"
release_outer_digest="$(jq -r '.production_release_reference.actions_outer_digest' "$contract")"
release_expires_at="$(jq -r '.production_release_reference.expires_at' "$contract")"
release_id="$(jq -r '.production_release_reference.release_id' "$contract")"
release_source="$(jq -r '.production_release_reference.source_commit' "$contract")"
release_filename="$(jq -r '.production_release_reference.artifact_filename' "$contract")"
release_size="$(jq -r '.production_release_reference.artifact_size_bytes' "$contract")"
release_sha="$(jq -r '.production_release_reference.artifact_sha256' "$contract")"
release_manifest_sha="$(jq -r '.production_release_reference.manifest_sha256' "$contract")"
staging_artifact_id="$(jq -r '.staging_prerequisite_reference.actions_artifact_id' "$contract")"
staging_source="$(jq -r '.staging_prerequisite_reference.source_commit' "$contract")"
staging_release_id="$(jq -r '.staging_prerequisite_reference.release_id' "$contract")"
staging_sha="$(jq -r '.staging_prerequisite_reference.artifact_sha256' "$contract")"

[[ "$release_run_id" =~ ^[0-9]+$ ]]
[[ "$release_artifact_id" =~ ^[0-9]+$ ]]
[[ "$release_artifact_name" =~ ^oneqay-production-[0-9a-f]{12}-operator-bundle$ ]]
[[ "$release_outer_digest" =~ ^sha256:[0-9a-f]{64}$ ]]
[[ "$release_expires_at" =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z$ ]]
[[ "$release_id" =~ ^production-[0-9a-f]{12}$ ]]
[[ "$release_source" =~ ^[0-9a-f]{40}$ ]]
[[ "$release_id" == "production-${release_source:0:12}" ]]
[[ "$release_filename" == "$release_id.tar.gz" ]]
[[ "$release_size" =~ ^[0-9]+$ ]]
[[ "$release_sha" =~ ^[0-9a-f]{64}$ ]]
[[ "$release_manifest_sha" =~ ^[0-9a-f]{64}$ ]]
[[ "$staging_artifact_id" =~ ^[0-9]+$ ]]
[[ "$staging_source" == "$release_source" ]]
[[ "$staging_release_id" == "durable-staging-${release_source:0:12}" ]]
[[ "$staging_sha" =~ ^[0-9a-f]{64}$ ]]

rm -rf "$stage_parent" "$zip_path" "$manifest_path" "$checksum_path"
mkdir -p "$stage_root"

for file in "${required_files[@]}"; do
  mkdir -p "$stage_root/$(dirname "$file")"
  cp "$file" "$stage_root/$file"
done

cat > "$stage_root/KIT.json" <<JSON
{
  "schema_version": 1,
  "kit_state": "PRODUCTION_DARK_DEPLOYMENT_OPERATOR_KIT_NOT_DEPLOYED_NOT_ACTIVATED",
  "kit_id": "$kit_id",
  "kit_source_commit": "$source_sha",
  "production_release_reference": {
    "publication_run_id": $release_run_id,
    "actions_artifact_id": $release_artifact_id,
    "actions_artifact_name": "$release_artifact_name",
    "actions_outer_digest": "$release_outer_digest",
    "expires_at": "$release_expires_at",
    "release_id": "$release_id",
    "source_commit": "$release_source",
    "artifact_filename": "$release_filename",
    "artifact_size_bytes": $release_size,
    "artifact_sha256": "$release_sha",
    "manifest_sha256": "$release_manifest_sha"
  },
  "staging_prerequisite_reference": {
    "actions_artifact_id": $staging_artifact_id,
    "release_id": "$staging_release_id",
    "source_commit": "$staging_source",
    "artifact_sha256": "$staging_sha",
    "verified_deployment_evidence_required": true
  },
  "deployment_authority": "NOT_GRANTED",
  "migration27_execution": "NOT_PERFORMED",
  "production_traffic_activation": "NOT_AUTHORIZED",
  "selected_target": null,
  "producer_dispatch": "NOT_PERFORMED",
  "secret_values_embedded": false,
  "attribution": "Lab | zefry"
}
JSON

cat > "$stage_root/target-input.template.json" <<JSON
{
  "schema_version": 1,
  "target_class": "OPERATOR_MANAGED_FILESYSTEM",
  "execution_channel": "PHP_CLI",
  "environment_id": "<real-production-environment-id>",
  "runtime_class": "production",
  "production": true,
  "production_data_allowed": true,
  "isolated_environment": true,
  "release_binding": {
    "release_id": "$release_id",
    "source_commit": "$release_source",
    "artifact_sha256": "$release_sha"
  },
  "filesystem": {
    "deployment_root": "<absolute-private-production-deployment-root>",
    "release_root": "<absolute-private-production-release-root>",
    "shared_runtime_root": "<absolute-private-production-shared-runtime-root>",
    "active_release_pointer": "<absolute-production-active-release-pointer>",
    "document_root": "<active-release-pointer>/apps/web/public"
  },
  "health": {"url": "https://<production-host>/health/live", "path": "/health/live"},
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

cat > "$stage_root/private-bindings.template.json" <<JSON
{
  "ONEQAY_PRODUCTION_ATTESTATION_TOKEN": "<private-production-attestation-token>",
  "ONEQAY_PRODUCTION_ENVIRONMENT_ID": "<real-production-environment-id>",
  "ONEQAY_RUNNING_ARTIFACT_SHA256": "$release_sha",
  "ONEQAY_RUNNING_SOURCE_COMMIT": "$release_source",
  "ONEQAY_RUNTIME_CLASS": "production"
}
JSON

cat > "$stage_root/README.md" <<'MARKDOWN'
# oneQay Production Dark Deployment Operator Kit

Author by Lab | zefry

Purpose: deploy an exact Production candidate in dark mode only. This kit contains governance and operator tools, not oneQay application bytes, secrets, deployment authority, database credentials, migration authority, or Production traffic activation authority.

Preconditions:
1. Verified durable-staging deployment evidence exists for the exact source in KIT.json.
2. Retrieve the exact Production candidate recorded in KIT.json.
3. Materialize a real isolated Production target.
4. Keep target input, private bindings, runtime environment, authority material, and evidence outside the public document root.
5. Private files use owner-only permissions 0600.
6. Production business/traffic activation remains separately unauthorized.

Execution order:
1. Inspect target with tools/inspect-production-operator-target.php.
2. Prepare target candidate with tools/prepare-production-operator-target-candidate.php.
3. Prepare authority request with tools/prepare-production-deployment-authority-request.php using exact Production manifest/archive, verified staging evidence, and target candidate.
4. Qualify separately issued short-lived authority with tools/qualify-production-deployment-authority.php.
5. Build exact plan with tools/prepare-production-deployment-plan.php.
6. Execute dark deployment with tools/execute-production-dark-deployment.php.
7. Qualify evidence with tools/qualify-production-deployment-evidence.php.

Dark-deployment executor arguments:
  production-deployment-plan.json
  target-profile.json
  production-artifact.tar.gz
  private-bindings.json
  private-runtime-env
  production-deployment-evidence-candidate.json

Success before traffic activation:
  PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED

The executor never runs migration #27 and never authorizes or activates Production business traffic. Stop on any identity, digest, target, authority, filesystem, health, rollback, or runtime readback mismatch.

MARKDOWN

python3 - "$stage_root" "$manifest_path" "$kit_id" "$source_sha" "$release_run_id" "$release_artifact_id" "$release_artifact_name" "$release_outer_digest" "$release_expires_at" "$release_id" "$release_source" "$release_filename" "$release_size" "$release_sha" "$release_manifest_sha" "$staging_artifact_id" "$staging_release_id" "$staging_source" "$staging_sha" <<'PY'
import hashlib, json, pathlib, sys
stage=pathlib.Path(sys.argv[1]); manifest_path=pathlib.Path(sys.argv[2])
kit_id=sys.argv[3]; source_sha=sys.argv[4]
release_run_id=int(sys.argv[5]); release_artifact_id=int(sys.argv[6]); release_artifact_name=sys.argv[7]
release_outer_digest=sys.argv[8]; release_expires_at=sys.argv[9]; release_id=sys.argv[10]; release_source=sys.argv[11]
release_filename=sys.argv[12]; release_size=int(sys.argv[13]); release_sha=sys.argv[14]; release_manifest_sha=sys.argv[15]
staging_artifact_id=int(sys.argv[16]); staging_release_id=sys.argv[17]; staging_source=sys.argv[18]; staging_sha=sys.argv[19]
files=[]
for p in sorted(x for x in stage.rglob("*") if x.is_file()):
    data=p.read_bytes()
    files.append({"path":p.relative_to(stage).as_posix(),"sha256":hashlib.sha256(data).hexdigest(),"size_bytes":len(data)})
manifest={
 "schema_version":1,"kit_state":"PRODUCTION_DARK_DEPLOYMENT_OPERATOR_KIT_PUBLISHED_SOURCE",
 "kit_id":kit_id,"kit_source_commit":source_sha,
 "production_release_reference":{"publication_run_id":release_run_id,"actions_artifact_id":release_artifact_id,
  "actions_artifact_name":release_artifact_name,"actions_outer_digest":release_outer_digest,"expires_at":release_expires_at,
  "release_id":release_id,"source_commit":release_source,"artifact_filename":release_filename,
  "artifact_size_bytes":release_size,"artifact_sha256":release_sha,"manifest_sha256":release_manifest_sha},
 "staging_prerequisite_reference":{"actions_artifact_id":staging_artifact_id,"release_id":staging_release_id,
  "source_commit":staging_source,"artifact_sha256":staging_sha,"verified_deployment_evidence_required":True},
 "files":files,"secret_values_embedded":False,"application_release_bytes_embedded":False,
 "deployment_authority":"NOT_GRANTED","migration27_execution":"NOT_PERFORMED",
 "production_traffic_activation":"NOT_AUTHORIZED","selected_target":None,"producer_dispatch":"NOT_PERFORMED",
 "attribution":"Lab | zefry"}
encoded=json.dumps(manifest,indent=2,sort_keys=True,ensure_ascii=False)+"\n"
manifest_path.write_text(encoded,encoding="utf-8")
(stage/"kit.manifest.json").write_text(encoded,encoding="utf-8")
PY

python3 - "$stage_root" "$zip_path" "$source_epoch" <<'PY'
import datetime,pathlib,sys,zipfile
stage=pathlib.Path(sys.argv[1]); output=pathlib.Path(sys.argv[2]); epoch=int(sys.argv[3])
dt=datetime.datetime.fromtimestamp(epoch,datetime.timezone.utc); second=dt.second-(dt.second%2)
zt=(min(max(dt.year,1980),2107),dt.month,dt.day,dt.hour,dt.minute,second)
with zipfile.ZipFile(output,"w",compression=zipfile.ZIP_DEFLATED,compresslevel=9) as zf:
    for p in sorted(x for x in stage.rglob("*") if x.is_file()):
        rel=(pathlib.Path(stage.name)/p.relative_to(stage)).as_posix()
        info=zipfile.ZipInfo(rel,zt); info.create_system=3; info.external_attr=(0o100600<<16); info.compress_type=zipfile.ZIP_DEFLATED
        zf.writestr(info,p.read_bytes())
PY

zip_sha256="$(sha256sum "$zip_path" | awk '{print $1}')"
printf '%s  %s\n' "$zip_sha256" "$(basename "$zip_path")" > "$checksum_path"

if find "$stage_root" -type f \( -name '.env' -o -name '*.pem' -o -name '*.key' -o -name '*.p12' -o -name '*.pfx' \) -print -quit | grep -q .; then
  echo "Forbidden secret-bearing file shape found in Production operator kit." >&2
  exit 1
fi
if find "$stage_root" -type f -path '*/apps/web/*' -print -quit | grep -q .; then
  echo "Production operator kit must not contain application runtime bytes." >&2
  exit 1
fi
if grep -RInE --exclude='*.schema.json' --exclude='*.php' '(BEGIN (RSA|OPENSSH|EC|DSA) PRIVATE KEY|ghp_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,}|AKIA[0-9A-Z]{16})' "$stage_root"; then
  echo "Potential secret pattern detected." >&2
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

printf 'Production dark-deployment operator kit built: %s\n' "$(basename "$zip_path")"
printf 'SHA-256: %s\n' "$zip_sha256"
printf 'Application bytes embedded: NO\n'
printf 'Deployment authority: NOT GRANTED\n'
printf 'Production traffic activation: NOT AUTHORIZED\n'
printf 'Author by Lab | zefry\n'
