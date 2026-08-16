# AI M7.6 Rehearsal Preparation State

## State

- Milestone: **M7.6 — Preview Deployment / Recovery Rehearsal**
- Preparation package: **IMPLEMENTED ON BOUNDED BRANCH / NOT YET PUBLISHED**
- Real qualified-target execution: **NOT EXECUTED**
- Target mutation authority: **NOT CREATED BY THIS FILE OR PACKAGE**
- M7.7: **NOT AUTHORIZED**
- Release: **NOT AUTHORIZED**
- Production: **NOT AUTHORIZED**
- Production readiness: **NO-GO**

Attribution: **Lab | zefry**

## Purpose

This checkpoint records only the source-side preparation required to execute a future separately authorized M7.6 Preview deployment/recovery rehearsal against an already-qualified target.

The package introduces:

- Preview/synthetic-only target qualification binding;
- fresh rehearsal authorization bound to the target qualification fingerprint;
- `NO_SCHEMA_CHANGE` rehearsal plan semantics;
- deployment/recovery driver port with no provider-specific implementation;
- deterministic rehearsal phase sequencing;
- candidate health and deliberate rollback exercise;
- baseline health verification;
- atomic sanitized evidence persistence;
- dedicated CI regression and operator runbook.

## Non-authority boundary

This preparation does not introduce a cPanel, SSH, SFTP, FTP, shell, arbitrary HTTP, or provider-specific deployment adapter. It does not wire the rehearsal runner into the service container, HTTP routes, Web UI, updater install endpoint, or background scheduler.

It does not enable `install_enabled`, edit `.env`, expose secret values, manage database credentials, mutate database/schema/migrations, publish a GitHub Release, deploy to Production, or create M7.7 authority.

A future real-target rehearsal requires a fresh exact-target execution authorization and must be evidenced separately. Until then, M7.6 must not be represented as completed merely because this preparation package and synthetic CI regression exist.
