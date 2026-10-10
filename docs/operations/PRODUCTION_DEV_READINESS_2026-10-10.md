# oneQayDev Production Configuration Readiness — 2026-10-10

Author by Lab | zefry

## Verified prior host evidence
The cPanel oneQayDev source-certified release production-0400241bd83d on isolated domain oneqaydev.n07.my.id was manually staged, followed by a health-only public bridge installation. The host-reported readiness probe had 20/20 PASS, and the browser reported /health/live status=ok (oneqay-web); /health/ready reported status=unavailable. Private 0600 bindings, 27 core+1 POS migration schema reconciliation, staging protection and the remaining placeholder are preserved; no migration should be replayed.

## Source defect and fix
The old CriticalConfiguration readiness allowlist was Local/Test/CI/Preview only. The source rejected an authentic Production runtime even when PHP and the front controller were functional. The new contract accepts Production ONLY if APP_ENV is production, APP_DEBUG is false, and the APP_KEY is a canonical base64-encoded 32-byte key. It preserves historical preview and CI readiness semantics and rejects missing, placeholder, short, nonbase64 or unsafe Production configuration. The existing /health/ready response remains the only public result, without exposing a key or exact failure details.

This readiness has the same definition as the existing endpoint: it is *application configuration readiness*, not a live database transaction health check. It is also not a merchant, cashier or cash sale GO-LIVE authorization. The independent ProductionBusinessRuntimeGate source/release, environment, persistence, session and explicit production transaction/traffic checks remain untouched. No fallback to runtime=ci or preview. Historical Local/Test/CI-only merchant bootstrap remains denied in Production.

## Safe follow-up
Publication on main produces a **new** source SHA, and thus requires a new certified production source archive and exact-source host installation. Do not edit the immutable source archive production-0400241bd83d in place, manipulate private runtime secrets, reuse an old release manifest, rerun migration #27 or replace the separately qualified staging document root. The installed health-only bridge is independent; the separately authorized Production merchant-provisioning and business-traffic/cPanel operator stages are still required before a final cash POS GO-LIVE. Do not claim final deployment from passing /health/ready alone.
