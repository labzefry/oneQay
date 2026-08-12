# oneQay Web — M7.1 Application Skeleton

This directory is the bounded first-party oneQay Web/PWA application boundary
authorized for M7.1 — Application Skeleton & Configuration Boundary.

It is intentionally additive to the repository-root framework-agnostic Platform
Foundation. It contains no POS business capability, database business schema,
real payment integration, Production data, deployment automation, or Production
authority.

## Configuration template

`environment.example` is the tracked placeholder-only equivalent of a Laravel
`.env.example`. Copy it to an untracked local `.env` only in an authorized
Local/Test/CI environment and replace placeholders with local/test values. Real
credentials and Production secrets must never be committed.

## Runtime baseline

- PHP: `>=8.2` within the governed runtime boundary.
- Laravel: `12.64.0` (selected to preserve PHP 8.2 compatibility).
- Node.js Local/Test/CI toolchain: `24.19.0` LTS.
- npm: provided by that Node.js release.

## Direct dependency manifest

| Package | Version | License | M7.1 necessity |
| --- | --- | --- | --- |
| `laravel/framework` | `12.64.0` | MIT | Approved backend framework |
| `inertiajs/inertia-laravel` | `3.3.1` | MIT | First-party Web delivery integration |
| `vue` | `3.5.40` | MIT | Approved Web frontend framework |
| `@inertiajs/vue3` | `3.6.1` | MIT | Vue/Inertia delivery adapter |
| `axios` | `1.18.1` | MIT | Required HTTP/type dependency exposed by the Inertia client stack |
| `vite` | `8.1.5` | MIT | Approved frontend build tool |
| `laravel-vite-plugin` | `3.1.3` | MIT | Laravel/Vite integration |
| `@vitejs/plugin-vue` | `6.0.8` | MIT | Vue SFC integration |
| `typescript` | `5.9.3` | Apache-2.0 | Stable TypeScript compiler compatible with the M7.1 Vue type-checker |
| `vue-tsc` | `3.3.8` | MIT | Vue/TypeScript static checking |
| `@types/node` | `24.13.3` | MIT | Node 24 toolchain types required by Vite configuration |

Exact dependency locks are committed before the Draft PR is eligible for
independent review. CI fails on unresolved High/Critical dependency advisories.

Attribution: Lab | zefry
