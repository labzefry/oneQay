# Workflow Directory

GitHub Actions application build, test, release, and deployment workflows remain
deferred until the technology stack and quality-tool ADRs are approved.

The repository currently permits a narrowly scoped governance workflow:

- `.github/workflows/governance-required-checks.yml`

This workflow restores the stable required-check producers used by the protected
default branch:

- `governance-validation`;
- `markdown-lint`;
- `secret-scan`.

The workflow:

- runs for pull requests targeting `main`;
- supports `workflow_dispatch` for manual diagnostics;
- uses least-privilege `contents: read`;
- pins `actions/checkout` to a full commit SHA;
- does not access repository secrets;
- does not build, publish, release, migrate, or deploy the application;
- does not select or imply an application technology stack;
- does not grant source-code, Phase 0 exit, release, deployment, or merge
  authority.

Any workflow added here must:

- use least-privilege `permissions`;
- pin third-party actions according to the supply-chain policy;
- avoid untrusted-code secret exposure;
- run documentation, test, security, and build gates appropriate to its scope;
- never print credentials or sensitive data;
- produce traceable results linked to the commit;
- be documented in `DEPLOYMENT.md`, `TESTING.md`, `TASKS.md`, and
  `CHANGELOG.md`.

Application CI remains inactive until the relevant ADRs and separate Product
Owner authority are available.

Attribution: Lab | zefry
