# oneQay Localization & Language Policy

## Status

- Product: **oneQay**
- Capability: **Localization / Internationalization (i18n)**
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Documentation base: `c3f64c10d476e422d6bf49117e68e595479f98e4`
- Capability status: **APPROVED DIRECTIONAL PRODUCT REQUIREMENT / DOCUMENTED**
- Source implementation authority: **NOT CREATED BY THIS DOCUMENT**
- Deployment authority: **NOT AUTHORIZED**
- Release authority: **NOT AUTHORIZED**
- Production authority: **NOT AUTHORIZED**
- Production readiness: **NO-GO**

This document records the Product Owner direction that the oneQay application must be designed and delivered as a bilingual product with **Bahasa Indonesia** and **English** user interfaces.

This publication is documentation-only. It does not authorize current source implementation, dependency installation, geolocation-provider integration, database/schema/migration mutation, deployment, Release/GitHub Release, or Production use.

Attribution: **Lab | zefry**

## Product requirement

oneQay must support exactly two initial product languages:

1. **Bahasa Indonesia** — locale `id-ID`;
2. **English** — locale `en` / `en-US`-compatible presentation where a regional English locale is required by the implementation stack.

The user-facing application must automatically choose the initial display language using the country/location of the access request:

- access resolved to **Indonesia (`ID`)** → default interface language **Bahasa Indonesia**;
- access resolved to any country **outside Indonesia** → default interface language **English**.

The automatic selection is an initial/default experience only. A user must always be able to change the language manually, and a valid explicit user choice must take precedence over automatic country detection on subsequent use.

## Scope of bilingual presentation

When this capability is later authorized for implementation, bilingual coverage must apply consistently to applicable user-facing surfaces, including:

- authenticated Web application;
- PWA;
- Native Android/mobile surfaces where implemented;
- tenant administration surfaces;
- platform administration surfaces where applicable;
- public/customer-facing pages;
- installer/onboarding surfaces where user-facing;
- Help Center and Knowledge Base;
- Customer Service & Support surfaces;
- Live Chat system UI;
- Ticketing customer UI;
- validation and safe error messages;
- navigation, forms, buttons, labels, empty states, confirmations, and accessibility text;
- user-facing release/update notices;
- user-facing notification templates where localization is applicable;
- transactional email/message templates where localization is applicable;
- downloadable/user-visible reports where localized labels are appropriate.

Machine identifiers, immutable audit event types, database identifiers, API field names, stable error codes, correlation IDs, release IDs, ticket IDs, incident IDs, and other technical identifiers must remain language-neutral and stable.

## Canonical language-selection behavior

### First or unauthenticated access

For a user without a previously saved language preference:

1. determine the access country using an approved server-side country-resolution boundary based on request IP/coarse network geolocation or equivalent trusted infrastructure metadata;
2. if the resolved country code is `ID`, select `id-ID`;
3. if the resolved country code is any other valid country, select English;
4. if country resolution is unavailable or indeterminate, use a safe fallback limited to the two supported languages and default to English when no reliable supported preference can be established;
5. present a visible language switcher so the user can immediately override the automatic selection.

### Authenticated access

A persisted explicit user language preference must take precedence over automatic IP/location detection.

Recommended precedence for future implementation:

1. explicit authenticated user language preference;
2. explicit language choice stored in the current trusted session/client preference boundary;
3. server-side access-country detection;
4. supported browser language hint only as a fallback when country resolution is unavailable;
5. English as the deterministic final fallback.

Tenant administrators may later define a tenant default language, but a tenant default must not silently remove the user's ability to select either supported language unless a separate governed product decision explicitly authorizes such a restriction.

## Country/location detection boundary

The localization requirement is based on **country-level access location**, not precise physical tracking.

The future implementation should prefer coarse server-side country resolution derived from the request network boundary or trusted infrastructure/CDN metadata. It must not require GPS, precise latitude/longitude, continuous location tracking, or device-location permission solely to select the interface language.

Required direction:

- use only the minimum geographic precision necessary: normally country code;
- Indonesia is identified by canonical country code `ID`;
- all resolved countries other than `ID` map to English for this initial bilingual requirement;
- IP/country detection is advisory for language defaulting and must never be treated as proof of citizenship, residence, legal jurisdiction, tenant ownership, user identity, tax location, or authorization;
- VPN, proxy, corporate network, roaming, mobile carrier routing, satellite connection, or geolocation-database errors may produce an incorrect country result, therefore manual override is mandatory;
- localization detection must never grant or deny security permissions.

## Privacy requirements

Language auto-selection must follow oneQay privacy and data-minimization principles.

Future implementation must not retain raw IP addresses solely because they were used to choose a language when such retention is unnecessary for an independently authorized security, audit, anti-abuse, or operational purpose.

Where an infrastructure provider supplies a resolved country code, oneQay should consume the minimum safe country metadata required for localization rather than copying unnecessary geolocation detail into application state.

The following must not be introduced merely for language selection:

- precise GPS history;
- background device location collection;
- permanent location profiles;
- cross-tenant location datasets;
- third-party advertising/tracking identifiers;
- silent location-based profiling beyond the documented localization purpose.

Any future external IP-geolocation service or provider requires its own privacy, security, retention, failure, cost, availability, and data-processing review before adoption.

## Trusted request metadata

A future implementation must not trust arbitrary client-supplied forwarding or country headers as authoritative.

If reverse-proxy/CDN country metadata is used, only headers established by a known and configured trusted proxy boundary may influence locale selection.

Untrusted values such as spoofed `X-Forwarded-For` or arbitrary client-provided country fields must not override trusted server-side request interpretation.

Failure to determine country safely must degrade to the documented fallback rather than failing the application request.

## Manual language override

A language selector must remain available at an appropriate user-facing location.

Required behavior:

- options are clearly presented as **Bahasa Indonesia** and **English**;
- selecting a language updates the interface without changing tenant identity, authorization, currency, timezone, or business data;
- an authenticated user preference may be persisted in the governed profile/preferences boundary;
- an unauthenticated preference may be stored using a bounded first-party session/cookie/client preference mechanism when separately authorized;
- the application must not repeatedly overwrite an explicit user selection merely because the apparent IP country changes;
- a user must be able to change the preference again at any time.

## Separation of locale from business configuration

Interface language is not the same as business geography.

The following concerns must remain independently governed:

- tenant country;
- legal jurisdiction;
- tax rules;
- currency;
- accounting policy;
- timezone;
- outlet location;
- date/time business semantics;
- payment availability;
- data residency;
- regulatory obligations.

For example, a user physically accessing from Indonesia may choose English, and an Indonesian tenant may be accessed by a user outside Indonesia. Neither case may mutate tenant business configuration merely because the UI language changes.

## Formatting direction

Localization should eventually include appropriate presentation formatting while retaining canonical business values.

Examples:

- translated user-facing text;
- locale-aware date presentation;
- locale-aware number presentation;
- locale-aware currency formatting based on the actual business currency, not inferred from language;
- translated relative-time labels;
- translated accessibility labels;
- pluralization rules supported by the selected i18n framework.

Canonical timestamps, monetary minor-unit values, database values, API contracts, IDs, and audit facts remain locale-independent.

## Translation architecture direction

Future implementation must avoid scattered hard-coded bilingual strings.

Recommended architecture:

`UI / Notification / Support Surface`

→ `Localization Contract`

→ `Translation Key + Locale`

→ `id-ID Catalog` or `English Catalog`

The localization layer should support:

- stable translation keys;
- namespaced catalogs by module/capability;
- deterministic fallback;
- interpolation with safe escaping;
- pluralization;
- test coverage for missing keys;
- untranslated-string detection where practical;
- compatibility with Web/PWA and future mobile channels;
- version-aware Help Center/Knowledge Base localization where applicable.

Business logic must not depend on translated display text.

## API and error boundary

API contracts must remain language-neutral.

Recommended direction:

- stable machine error code remains unchanged across languages;
- correlation ID remains unchanged;
- optional user-facing safe message may be localized at the interface boundary;
- validation field identifiers remain stable;
- API consumers must not parse localized human-readable error text to determine behavior.

## Customer Service & Support integration

The previously documented oneQay Customer Service & Support capability must honor this bilingual policy.

Directional behavior includes:

- Help Center article localization;
- Knowledge Base language/version metadata;
- AI Support response language aligned with the user's current supported locale where policy permits;
- Human Handoff preserving the current conversation language;
- Live Chat routing may consider language skill without changing authorization;
- Ticket UI and customer-facing templates localized independently from stable ticket data;
- Incident customer notices available in the supported languages where applicable;
- CSAT prompts and customer-facing support analytics labels localized where applicable.

AI Support must not infer security or tenant decisions from language or detected country.

## Caching and tenant isolation

Future localized rendering must not weaken tenant isolation.

Any cache used for localized content must include all required isolation dimensions, including locale and tenant/access scope where applicable.

A cached Indonesian page for one tenant must not be served as another tenant's authorized content, and localized cache keys must never become a substitute for tenant authorization.

## Search and Knowledge Base direction

Search must be designed to understand the selected language without merging authorization boundaries.

Future Help Center/Knowledge Base search should support:

- language-tagged content;
- language-aware indexing/analyzers where justified;
- fallback to another supported language only when the product experience explicitly permits it;
- visibility and tenant authorization before search-result disclosure.

## Accessibility and UX

Both supported languages must target equivalent functional quality.

A feature must not be considered complete merely because one locale is fully usable while the other has missing navigation, inaccessible labels, broken layouts, or untranslated critical actions.

UI design must anticipate different text lengths between Bahasa Indonesia and English, including responsive layout, buttons, tables, dialogs, mobile/PWA surfaces, and accessibility names.

## Quality gates for future implementation

Before bilingual functionality is considered complete for an authorized product slice, tests should demonstrate at minimum:

1. `ID` access-country result defaults to Bahasa Indonesia;
2. non-`ID` resolved country defaults to English;
3. country-resolution failure uses the defined safe fallback;
4. explicit user language selection overrides automatic detection;
5. explicit preference remains stable when apparent country changes;
6. language switching does not alter tenant or authorization context;
7. stable error codes and correlation IDs remain identical across locales;
8. required translation keys exist in both supported catalogs;
9. user-facing critical flows contain no accidental untranslated placeholders;
10. localized cache behavior remains tenant-safe;
11. no precise GPS/device-location permission is required solely for localization;
12. raw IP is not retained solely for language selection without another governed purpose;
13. spoofed client country/forwarding metadata cannot bypass the trusted request boundary;
14. Web/PWA/mobile surfaces preserve equivalent feature semantics across languages;
15. Help Center/Support surfaces follow the selected locale where implemented.

## Development sequencing

This requirement is intentionally documented **before implementation**.

Recommended sequencing:

### L10N-0 — Localization architecture and catalog foundation

Can begin when a future Product Owner authority explicitly permits localization source work.

Outcome:

- stable locale contract;
- `id-ID` and English catalogs;
- language selector;
- deterministic fallback;
- translation-key testing;
- no IP/geolocation dependency required yet if the slice is limited to manual locale selection and architecture foundation.

### L10N-1 — Country-aware automatic default

Begin only after the trusted request/proxy/privacy boundary for country resolution is explicitly approved.

Outcome:

- trusted server-side country resolution;
- `ID` → Bahasa Indonesia;
- non-`ID` → English;
- safe failure behavior;
- explicit user override persistence.

### L10N-2 — Full product-surface localization

Roll out alongside each authorized product capability rather than postponing translation until the end of the project.

Each new user-facing module should ship with both supported languages as part of its Definition of Done once the localization foundation is active.

### L10N-3 — Support/content/notification localization maturity

Extend bilingual quality across Help Center, Knowledge Base, support workflows, notifications, release notices, templates, analytics labels, and other externally visible content as those capabilities are implemented.

## Recommended timing

Localization architecture should be established **before broad business-module UI expansion**, because retrofitting translation keys, locale-safe formatting, notification templates, Help Center content, and mobile surfaces after many modules exist creates unnecessary rework.

However, this document does not change the current post-Sprint-14 successor authority. Localization source implementation is **not started by this publication**.

When the project reaches the appropriate future UI/platform-foundation milestone, the Product Owner may authorize L10N-0 as a bounded cross-cutting workstream. Country/IP auto-detection can then follow as L10N-1 after its trusted-network and privacy boundary is verified.

## Explicit non-scope of this publication

This documentation publication does not authorize or perform:

- Vue/TypeScript localization code;
- Laravel localization code;
- Android localization resources;
- package/dependency installation;
- translation catalog source files;
- IP geolocation provider integration;
- Cloudflare/CDN country-header integration;
- location permission prompts;
- cookie/profile persistence implementation;
- schema/SQL/migration changes;
- database writes;
- deployment;
- Release/GitHub Release;
- Production/customer data;
- Production readiness promotion.

## Canonical requirement summary

The intended future oneQay behavior is:

`Access Request`

→ `Resolve supported explicit preference if present`

→ otherwise `Resolve coarse access country`

→ country `ID` → **Bahasa Indonesia (`id-ID`)**

→ country other than `ID` → **English**

→ country unavailable → **safe supported-language fallback**

→ user may manually select **Bahasa Indonesia** or **English** at any time.

This requirement applies as a cross-cutting product capability and must be implemented only under a later explicit bounded authority.

Attribution: **Lab | zefry**