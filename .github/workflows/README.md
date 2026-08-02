# Workflow Directory

GitHub Actions workflows will be added after the technology stack and quality-tool ADRs are approved.

Any workflow added here must:

- use least-privilege `permissions`;
- pin third-party actions according to the supply-chain policy;
- avoid untrusted-code secret exposure;
- run documentation, test, security, and build gates appropriate to its scope;
- never print credentials or sensitive data;
- produce traceable artifacts linked to the commit;
- be documented in DEPLOYMENT.md, TESTING.md, TASKS.md, and CHANGELOG.md.

No placeholder CI is activated because the application stack has not yet been selected.
