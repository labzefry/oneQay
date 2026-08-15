# M7.5 Technical Preview Release Artifact

## Purpose

This document defines the governed release-artifact path for the oneQay
Technical Preview on shared cPanel hosting where interactive shell access is
not available.

The model preserves two separate boundaries:

- the cPanel-managed Git clone under `oneqay-preview/repository` is source and
  provenance evidence;
- the runtime package is built by GitHub Actions from an exact governed Git SHA
  and is extracted under `oneqay-preview/releases/<release-id>` outside
  `public_html`.

The public document root remains provider-controlled. Only the generated
`public-surface` contents may be copied into the Technical Preview document
root.

## Release identity

The release identifier is derived from the exact source SHA:

`m75-preview-<first-12-characters-of-source-sha>`

The package contains `RELEASE.json` with the full source commit and Technical
Preview classification. It does not contain credentials or Production
authority.

## Build boundary

The M7.5 release workflow:

1. checks out the exact governed source;
2. uses PHP 8.3 for the Preview build target;
3. installs locked Composer production dependencies;
4. rejects High/Critical Composer advisories;
5. installs locked frontend build dependencies;
6. rejects High/Critical npm advisories;
7. type-checks and builds Vite assets;
8. runs the established oneQay Web regression set;
9. creates a private application payload and separate public surface;
10. rejects `.env`, Git metadata, `node_modules`, and private application paths
    from the public surface;
11. emits a deterministic `tar.gz` payload and SHA-256 checksum;
12. uploads the result as a short-retention GitHub Actions artifact.

## Shared-hosting split root

Expected Technical Preview shape:

```text
/home/<cpanel-account>/
├── oneqay-preview/
│   ├── repository/
│   └── releases/
│       └── <release-id>/
│           ├── apps/web/
│           ├── public-surface/
│           └── RELEASE.json
└── public_html/
    └── oneqay.n07.my.id/
        └── <public-surface only>
```

The generated public entry point resolves the account home directory at runtime
and then targets the exact release ID under `oneqay-preview/releases`. It does
not embed the cPanel username, password, origin IP, token, database credential,
or Production hostname.

## Safety constraints

- Technical Preview only.
- Synthetic data only.
- `oneqay.com` is not touched.
- No real customer, participant, BPJS, payment, or personal data.
- No `.env` or credentials in GitHub Actions artifacts.
- No `vendor`, `storage`, tests, Composer manifests, package manifests, or
  application source in the public surface.
- A runtime package is not considered deployed merely because CI built it.
- M7.5 remains blocked until actual target runtime evidence verifies the
  mandatory controls.

Attribution: Lab | zefry
