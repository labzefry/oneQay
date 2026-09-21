# Sprint224 Final Shift Close Migration27 cPanel Local DB Binding Probe

Author by Lab | zefry

## Purpose

Sprint224 fixes one concrete live compatibility defect proven by workflow run \`35604174103\`.

The selected durable-staging runtime, protected GitHub Environment, authenticated runtime-side DB-binding attestation, and canonical selected-target verification all succeeded. The producer failed only when the GitHub-hosted runner attempted to open a direct MySQL PDO connection to the shared-hosting database:

\`SQLSTATE[HY000] [2002] Connection refused\`

This is a transport incompatibility between a GitHub-hosted runner and the selected cPanel database boundary. It is not evidence of a bad selected target, bad runtime attestation, Cloudflare HTTP blocking, or migration #27 execution.

Sprint224 preserves the independent database-readback trust requirement instead of deleting or weakening it.

## Exact bounded source envelope

Sprint224 changes exactly:

1. \`.github/workflows/final-shift-close-migration27-selected-target-db-binding.yml\`
2. \`.github/workflows/sprint103-final-shift-close-migration27-execution-evidence.yml\`
3. \`.github/workflows/sprint117-final-shift-close-migration27-selected-target-binding.yml\`
4. \`.github/workflows/sprint118-final-shift-close-migration27-selected-target-db-binding.yml\`
5. \`.github/workflows/sprint224-final-shift-close-migration27-cpanel-local-db-binding-probe.yml\`
6. \`docs/SPRINT224_FINAL_SHIFT_CLOSE_MIGRATION27_CPANEL_LOCAL_DB_BINDING_PROBE.md\`
7. \`ops/final-shift-close/MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT.json\`
8. \`ops/final-shift-close/cpanel-migration27-local-db-binding-probe.php\`

Sorted-newline path-set SHA-256:

\`74238c669332056df20c094e88435e6830c19b213688aa31aa0590abd115e420\`

No application source, migration source, runtime route/provider, \`STATE.json\`, target-selection state, migration executor, permission policy, feature activation, deployment, Production source, or updater source is changed.

## Live failure evidence

Workflow:

\`Final Shift Close Migration27 Selected Target DB Binding\`

Run:

\`35604174103\`

Exact successful stages before the failure:

- checkout canonical dispatch source;
- current canonical main validation;
- selected durable target validation;
- protected control-channel and credential-shape validation;
- authenticated selected-runtime DB-binding attestation fetch;
- authenticated runtime binding versus canonical selected-target verification.

Failure occurred at:

\`Independently read database identity and verify migration27 is absent\`

PDO constructor result:

\`SQLSTATE[HY000] [2002] Connection refused\`

No binding artifact and no success evidence status were published.

## Preserved primary path

Direct database verification remains the primary mode:

\`GITHUB_HOSTED_PDO_PRIMARY\`

When the GitHub-hosted runner can establish the protected PDO connection, the existing read-only verification remains authoritative:

- \`SELECT DATABASE(), @@hostname, @@port\`;
- canonical \`migrations\` table must exist;
- \`oneqay_pos_shift_close_evidence\` must not exist;
- migration \`0000_00_00_000027_create_pos_shift_close_evidence_foundation\` must not be recorded;
- the independently calculated database-binding SHA-256 must equal the authenticated runtime-side database-binding SHA-256.

A successful direct PDO connection never falls back because a later identity, migration-state, or fingerprint check fails.

## cPanel-local independent PDO fallback

When and only when the GitHub-hosted runner cannot establish the direct PDO connection, the producer may consume a short-lived signed cPanel-local probe through the protected Environment secret:

\`ONEQAY_MIGRATION27_BINDING_LOCAL_PROBE_B64\`

Probe source:

\`ops/final-shift-close/cpanel-migration27-local-db-binding-probe.php\`

The probe is CLI-only and runs on the selected cPanel host. It independently opens PDO using the private runtime database configuration and performs the same read-only database checks.

It does not bootstrap Laravel and does not use the application database reader. This preserves a separate database-readback code path from the authenticated application runtime attestation.

## Probe trust binding

The cPanel-local payload is accepted only when all of the following hold:

- evidence type is exactly \`CPANEL_LOCAL_INDEPENDENT_PDO_READBACK\`;
- payload is bound to the exact current canonical \`main\` SHA;
- environment ID exactly matches the selected target;
- runtime class exactly matches the selected target;
- exact running source commit exactly matches the selected target;
- exact running artifact SHA-256 exactly matches the selected target;
- readiness attestation SHA-256 exactly matches the selected target;
- selection fingerprint SHA-256 exactly matches the selected target;
- database-binding algorithm remains \`SHA256_CANONICAL_JSON_DATABASE_HOSTNAME_PORT_V1\`;
- migration #27 state is \`NOT_EXECUTED\`;
- mode is \`READ_ONLY\`;
- \`secrets_embedded = false\`;
- payload lifetime is at most 900 seconds and has not expired;
- nonce is structurally valid;
- protected GitHub DB host, port, database, and username match the probe credential-binding SHA-256;
- HMAC-SHA256 verifies using a key derived from the protected DB password and fixed context \`oneqay-migration27-cpanel-local-probe-v1\`;
- the probe database-binding SHA-256 exactly equals the authenticated runtime-side database-binding SHA-256.

Any mismatch fails closed before artifact publication.

## Secret handling

The probe reads the existing private runtime \`.env\`.

It never prints:

- DB host;
- DB name;
- DB username;
- DB password;
- runtime bearer token;
- GitHub secret values.

The output file contains a Base64-encoded, signed evidence payload and is written with mode \`0600\`.

The Base64 payload is transferred only into the protected GitHub Environment secret:

\`ONEQAY_MIGRATION27_BINDING_LOCAL_PROBE_B64\`

It is not committed, posted to issues, or pasted into chat.

## Post-merge cPanel execution shape

After Sprint224 is merged and current \`main\` is reverified, copy the probe source to a private operator path, for example:

\`/home/pekd7254/oneqay-operator/cpanel-migration27-local-db-binding-probe.php\`

Run it through one-shot cPanel Cron using the exact current post-merge main SHA:

\`\`\`text
<PHP83_CLI> /home/pekd7254/oneqay-operator/cpanel-migration27-local-db-binding-probe.php \
  --runtime-env=/home/pekd7254/oneqay-staging/releases/durable-staging-5be28a3c0017/apps/web/.env \
  --runtime-manifest=/home/pekd7254/oneqay-staging/releases/durable-staging-5be28a3c0017/apps/web/storage/app/private/final-shift-close-runtime-binding.json \
  --canonical-main=<POST_MERGE_MAIN_SHA> \
  --output=/home/pekd7254/oneqay-operator/output/migration27-local-db-binding-probe.b64
\`\`\`

Expected safe output includes:

- \`RESULT=SUCCESS\`;
- \`MODE=CPANEL_LOCAL_INDEPENDENT_PDO_READBACK\`;
- \`MIGRATION27=NOT_EXECUTED\`;
- expiry timestamp;
- database-binding SHA-256.

The Base64 payload expires after 900 seconds. Copy only the content of the generated \`.b64\` file to the protected GitHub Environment secret \`ONEQAY_MIGRATION27_BINDING_LOCAL_PROBE_B64\`, then dispatch the producer from current \`main\` within the validity window.

The existing eight Environment secrets remain required. Sprint224 adds only the short-lived ninth probe-evidence secret.

## Evidence artifact compatibility

The trusted producer artifact remains unchanged:

\`final-shift-close-migration27-selected-target-db-binding\`

It still contains exactly:

- \`binding.json\`;
- \`execution.json\`.

The downstream migration executor therefore requires no schema change.

Sprint224 does not grant migration execution authority.

## Historical compatibility

Sprint103, Sprint117, and Sprint118 regression workflows are updated only to recognize the exact Sprint224 successor envelope and the already-persisted current selected target.

Their original migration execution, selected-target, database-binding, and no-go security properties remain preserved.

## Current canonical operational boundary

\`RUN_35604174103 = FAILED_REMOTE_PDO_CONNECTION_REFUSED\`

\`SPRINT224_CPANEL_LOCAL_DB_BINDING_PROBE = MATERIALIZED_SOURCE_ONLY\`

\`MIGRATION27_SELECTED_TARGET_BINDING_EVIDENCE = NOT_YET_PRODUCED_SUCCESSFULLY\`

\`MIGRATION27_EXECUTION = NOT_PERFORMED\`

\`MIGRATION27_EXECUTION_AUTHORITY = NOT_GRANTED\`

\`PERMISSION_PROVISIONING = NONE\`

\`FINAL_SHIFT_CLOSE = INACTIVE\`

\`TECHNICAL_PREVIEW = NOT_AUTHORIZED\`

\`PRODUCTION = NOT_AUTHORIZED\`

\`UPDATER = INACTIVE\`

\`APPLICATION_SOURCE_MODIFICATION_ON_DURABLE_STAGING = NO\`

\`DATABASE_MUTATION = NO\`

\`TARGET_RESELECTION = NO\`

After a successful producer run, the next operational gate must be derived from the current canonical migration #27 contract. A successful DB-binding producer does not itself authorize migration #27.

Author by Lab | zefry
