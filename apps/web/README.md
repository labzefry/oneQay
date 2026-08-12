# oneQay Web — M7.1 Application Skeleton

This directory is the bounded first-party oneQay Web/PWA application boundary
authorized for M7.1 — Application Skeleton & Configuration Boundary.

It is intentionally additive to the repository-root framework-agnostic Platform
Foundation. It contains no POS business capability, database business schema,
real payment integration, Production data, deployment automation, or Production
authority.

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
| `vite` | `8.1.5` | MIT | Approved frontend build tool |
| `laravel-vite-plugin` | `3.1.3` | MIT | Laravel/Vite integration |
| `@vitejs/plugin-vue` | `6.0.8` | MIT | Vue SFC integration |
| `typescript` | `7.0.2` | Apache-2.0 | TypeScript-first frontend |
| `vue-tsc` | `3.3.8` | MIT | Vue/TypeScript static checking |

Exact dependency locks are generated and then committed before the Draft PR is
eligible for independent review. CI fails on unresolved High/Critical dependency
advisories.

Attribution: Lab | zefry
