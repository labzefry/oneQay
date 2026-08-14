# M7.5 P2 Evidence Acquisition Checklist

Attribution: **Lab | zefry**

## Purpose

This provider-neutral checklist defines the sanitized evidence required to evaluate a P2 Managed/Hardened VPS or Server target under DEC-009 and DEC-005R.

It does not select a provider, purchase infrastructure, provision a host, deploy oneQay, install certificates, create production credentials, or authorize M7.6.

## Evidence handling rules

Evidence must be sanitized and non-secret.

Never capture or commit:

- passwords;
- private keys;
- database credentials;
- API tokens;
- session secrets;
- raw `.env` contents;
- customer/production data.

Evidence may use redacted screenshots, non-secret command output, configuration summaries, release metadata, or repository/evidence identifiers.

Every evidence item should identify the target, observation time, evidence source, and result.

## Runtime and request boundary

Collect evidence for:

- supported PHP version;
- PHP CLI availability;
- required PHP extensions;
- web-server/request runtime;
- document root mapped exactly to the public application surface;
- effective front-controller rewrite/routing;
- bounded request/upload/time limits;
- trusted-proxy behavior where applicable.

## TLS and network

Collect evidence for:

- valid Preview HTTPS termination;
- HTTP-to-HTTPS redirect behavior;
- secure-cookie suitability;
- certificate lifecycle capability;
- DNS resolution;
- outbound HTTPS access required by authorized runtime integrations;
- restricted inbound exposure.

Do not perform DNS or certificate mutation merely to satisfy this checklist unless separately authorized.

## Scheduler, worker, and queue

Collect evidence for:

- one-minute scheduler capability where required;
- actual scheduler execution behavior;
- persistent supervised worker capability or a proven safe bounded alternative;
- queue execution model;
- retry/failure behavior;
- idempotency compatibility;
- process restart/recovery behavior;
- concurrency/resource limits.

## Filesystem and secrets

Collect evidence for:

- private writable application storage;
- public/private path separation;
- non-public secrets/configuration storage;
- file ownership and permission boundaries;
- temporary-file isolation;
- log rotation;
- backup coverage for persistent files where applicable.

## Relational database profile

For the selected DEC-005R profile collect sanitized evidence for:

- engine family and exact version;
- application connectivity;
- least-privilege application account behavior;
- externalized credentials;
- topology-appropriate TLS where required;
- connection-limit visibility;
- transaction semantics;
- tenant-isolation semantics;
- backup/export capability;
- successful isolated restore;
- controlled migration boundary;
- DEC-005R Database Portability Contract conformance.

MariaDB, MySQL, or PostgreSQL identity alone is insufficient.

## Backup and recovery

Collect evidence for:

- database backup mechanism;
- persistent-file backup where applicable;
- schedule and coverage;
- retention behavior;
- off-host copy where appropriate;
- integrity verification;
- isolated restore target;
- successful restore rehearsal;
- recovery metadata;
- access control/audit.

Backup success without restore evidence is not recoverability.

## Release and rollback boundary

Collect evidence for:

- trusted/versioned release artifact identity;
- release/commit identity;
- recoverable publication mechanism;
- previous-release retention;
- configuration rollback boundary;
- application rollback behavior;
- compatibility of database evolution with rollback/recovery;
- post-publication health verification capability.

Direct live overwrite without a recoverable release boundary is not acceptable.

## Observability

Collect evidence for:

- application/runtime logs;
- correlation-ID lookup;
- security-event visibility;
- health/readiness visibility;
- scheduler/worker visibility where applicable;
- database/storage dependency visibility;
- log rotation;
- operator access suitable for Preview troubleshooting.

## Resource and quota visibility

Collect evidence for:

- CPU allocation/limits;
- memory allocation/limits;
- storage capacity/quota;
- process limits;
- database connection limits;
- request/upload limits;
- alerting or observable thresholds where available.

## Preview isolation and security

Collect evidence proving:

- environment is Preview, not Production;
- only synthetic data is used;
- no production/customer credential exists;
- public paths cannot serve private source/configuration/log/backup material;
- tenant-context and tenant-isolation tests can execute safely;
- secrets are externalized;
- privileged operational actions are controlled and auditable.

## Machine-verifiable package target

When the evidence exists, convert it into the schema consumed by:

`tools/runtime-qualification.php`

Use a sanitized target identifier and selected relational profile.

Only controls with direct sufficient evidence may be `VERIFIED`. Unknown, incomplete, unavailable, or absent capabilities remain `PARTIAL`, `UNVERIFIED`, `UNAVAILABLE`, or `NOT_SUPPLIED`.

## Current boundary

No actual sanitized P2 target evidence is presently represented by this checklist.

Therefore:

- P2 is an **EVIDENCE ACQUISITION TARGET**, not a selected deployment target;
- M7.5 qualification remains **BLOCKED / WAITING FOR REQUIRED EXTERNAL RUNTIME EVIDENCE**;
- deployment remains **NOT AUTHORIZED**.
