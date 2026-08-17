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

This document records the Product Owner direction that oneQay must be designed and delivered as a bilingual product with **Bahasa Indonesia** and **English** user interfaces.

This publication is documentation-only. It does not authorize current source implementation, dependency installation, geolocation-provider integration, database/schema/migration mutation, deployment, Release/GitHub Release, or Production use.

Attribution: **Lab | zefry**

## Product requirement

oneQay must support two initial product languages:

1. **Bahasa Indonesia** — locale `id-ID`;
2. **English** — locale `en`, with `en-US`-compatible presentation where a regional English locale is required by the implementation stack.

For a user without a valid saved language preference, the application must automatically select the initial interface language using the country/location of the access request:

- access resolved to **Indonesia (`ID`)** → default interface language **Bahasa Indonesia**;
- access resolved to any country **outside Indonesia** → default interface language **English**.

Automatic detection establishes a default only. A user must always be able to switch language manually, and a valid explicit choice must take precedence over later country detection.

## Bilingual surface coverage

When this capability is later authorized for implementation, both languages must be supported consistently across applicable user-facing surfaces, including:

- authenticated Web application;
- PWA;
- Native Android/mobile surfaces where implemented;
- tenant and platform administration surfaces;
- public/customer-facing pages;
- installer/onboarding screens;
- Help Center and Knowledge Base;
- Customer Service & Support;
- Live Chat and Ticketing customer UI;
- navigation, forms, buttons, labels, dialogs, empty states, confirmations, and accessibility text;
- validation and safe error messages;
- release/update notices;
- notification and transactional message templates where localization applies;
- user-visible report labels where localization applies.

Machine identifiers, immutable audit event types, database identifiers, API fields, stable error codes, correlation IDs, release IDs, ticket IDs, incident IDs, and other technical identifiers must remain language-neutral and stable.

## Canonical language-selection behavior

### First or unauthenticated access

For a user without a previously saved supported-language preference:

1. resolve the access country using an approved server-side country-resolution boundary based on request IP, coarse network geolocation, or equivalent trusted infrastructure metadata;
2. if the resolved country code is `ID`, select `id-ID`;
3. if the resolved country code is any other valid country, select English;
4. if country resolution is unavailable or indeterminate, use a safe fallback limited to the two supported languages and default to English when no reliable supported preference can be established;
5. expose a visible language switcher so the user can immediately override the automatic selection.

### Authenticated access

Recommended precedence for future implementation:

1. explicit authenticated user language preference;
2. explicit language choice stored in the current trusted session/client preference boundary;
3. server-side access-country detection;
4. supported browser language hint only when country resolution is unavailable;
5. English as the deterministic final fallback.

An explicit user selection must not be repeatedly overwritten because the apparent IP country changes.

## Country/location detection boundary

The localization requirement is based on **country-level access location**, not precise physical tracking.

The future implementation should prefer coarse server-side country resolution derived from the request network boundary or trusted infrastructure/CDN metadata. It must not require GPS, precise latitude/longitude, continuous location tracking, or device-location permission solely to select the interface language.

Required direction:

- use only the minimum geographic precision necessary, normally country code;
- Indonesia is identified by canonical country code `ID`;
- all resolved countries other than `ID` map to English for this initial bilingual requirement;
- IP/country detection is advisory for language defaulting only;
- country detection must never be treated as proof of citizenship, residence, legal jurisdiction, tenant ownership, identity, tax location, or authorization;
- VPN, proxy, corporate network, roaming, carrier routing, or geolocation-database errors may produce an incorrect result, therefore manual override is mandatory;
- localization detection must never grant or deny security permissions.

## Privacy and data minimization

Future localization implementation must follow oneQay privacy principles.

It must not retain raw IP addresses solely because they were used to choose a language when such retention is unnecessary for another independently authorized security, audit, anti-abuse, or operational purpose.

Where infrastructure supplies a resolved country code, oneQay should consume only the minimum safe country metadata required for localization rather than copying unnecessary geolocation detail into application state.

The following must not be introduced merely for language selection:

- precise GPS history;
- background device-location collection;
- permanent location profiles;
- cross-tenant location datasets;
- third-party advertising/tracking identifiers;
- silent location profiling beyond the documented localization purpose.

Any future external IP-geolocation provider requires separate privacy, security, retention, failure, cost, availability, and data-processing review before adoption.

## Trusted request metadata

A future implementation must not trust arbitrary client-supplied forwarding or country headers as authoritative.

If reverse-proxy/CDN country metadata is used, only values established by a known and configured trusted proxy boundary may influence locale selection.

Spoofed `X-Forwarded-For`, arbitrary client country fields, or other untrusted headers must not override trusted server-side interpretation.

Failure to resolve a country safely must degrade to the documented fallback rather than failing the application request.

## Manual language override

A language selector must remain available at an appropriate user-facing location.

Required behavior:

- options are clearly presented as **Bahasa Indonesia** and **English**;
- selecting a language changes presentation without changing tenant identity, authorization, currency, timezone, or business data;
- an authenticated preference may later be persisted in the governed profile/preferences boundary;
- an unauthenticated preference may later use a bounded first-party session/cookie/client preference mechanism;
- a user can change the preference again at any time.

## Separation from business configuration

Interface language is not the same as business geography.

The following concerns remain independent from display language:

- tenant country;
- legal jurisdiction;
- tax rules;
- currency;
- accounting policy;
- timezone;
- outlet location;
- payment availability;
- data residency;
- regulatory obligations.

A user in Indonesia may choose English, and an Indonesian tenant may be accessed from outside Indonesia. Neither case may mutate tenant business configuration merely because the UI language changes.

## Formatting direction

Localization should eventually include appropriate presentation formatting while retaining canonical business values.

Examples:

- translated user-facing text;
- locale-aware date and number presentation;
- currency formatting based on actual business currency, not inferred from language;
- translated relative-time labels;
- translated accessibility labels;
- pluralization supported by the selected i18n framework.

Canonical timestamps, monetary values, database values, API contracts, IDs, and audit facts remain locale-independent.

## Translation architecture direction

Future implementation must avoid scattered hard-coded bilingual strings.

Recommended architecture:

`UI / Notification / Support Surface`

→ `Localization Contract`

→ `Translation Key + Locale`

→ `id-ID Catalog` or `English Catalog`

The localization layer should support stable translation keys, namespaced catalogs, deterministic fallback, safe interpolation, pluralization, missing-key testing, untranslated-string detection where practical, and compatibility across Web/PWA/mobile surfaces.

Business logic must not depend on translated display text.

## API and error boundary

API contracts remain language-neutral.

Recommended direction:

- stable machine error codes remain unchanged across languages;
- correlation IDs remain unchanged;
- optional safe user-facing messages may be localized at the interface boundary;
- validation field identifiers remain stable;
- API consumers must not parse localized human-readable error text to determine behavior.

## Customer Service & Support integration

The documented oneQay Customer Service & Support capability must honor this bilingual policy.

Directional behavior includes:

- localized Help Center articles;
- Knowledge Base language/version metadata;
- AI Support responding in the user's selected supported language where policy permits;
- Human Handoff preserving the current conversation language;
- Live Chat routing optionally considering language skill without changing authorization;
- localized customer-facing Ticket and Incident UI/templates;
- localized CSAT prompts and support analytics labels where applicable.

AI Support must never infer security or tenant decisions from language or detected country.

## Caching and tenant isolation

Localized rendering must not weaken tenant isolation.

Any localized cache must include all required isolation dimensions, including locale and tenant/access scope where applicable.

Locale-aware cache keys never replace tenant authorization.

## Accessibility and UX

Both supported languages must target equivalent functional quality.

A feature is not complete merely because one locale works while the other has missing navigation, inaccessible labels, broken layouts, or untranslated critical actions.

UI design must anticipate different text lengths between Bahasa Indonesia and English across responsive Web, tables, dialogs, PWA, and mobile surfaces.

## Quality gates for future implementation

Before bilingual functionality is complete for an authorized product slice, tests should demonstrate at minimum:

1. `ID` access-country result defaults to Bahasa Indonesia;
2. non-`ID` resolved country defaults to English;
3. country-resolution failure uses the defined safe fallback;
4. explicit user selection overrides automatic detection;
5. explicit preference remains stable when apparent country changes;
6. language switching does not alter tenant or authorization context;
7. stable error codes and correlation IDs remain identical across locales;
8. required translation keys exist in both supported catalogs;
9. critical user flows do not contain accidental untranslated placeholders;
10. localized cache behavior remains tenant-safe;
11. no precise GPS/device-location permission is required solely for localization;
12. raw IP is not retained solely for language selection without another governed purpose;
13. spoofed client country/forwarding metadata cannot bypass the trusted request boundary;
14. Web/PWA/mobile surfaces preserve equivalent feature semantics across languages;
15. Help Center and Support surfaces follow the selected locale where implemented.

## Development sequencing

This requirement is intentionally documented **before implementation**.

### L10N-0 — Localization architecture and catalog foundation

May begin only under a later explicit Product Owner source authority.

Outcome direction:

- stable locale contract;
- `id-ID` and English catalogs;
- language selector;
- deterministic fallback;
- translation-key testing.

### L10N-1 — Country-aware automatic default

May begin only after trusted request/proxy/privacy boundaries for country resolution are explicitly approved.

Outcome direction:

- trusted server-side country resolution;
- `ID` → Bahasa Indonesia;
- non-`ID` → English;
- safe failure behavior;
- explicit user override persistence.

### L10N-2 — Full product-surface localization

Localization should roll out alongside each authorized user-facing product capability rather than being postponed until the end of the project.

Once the localization foundation is active, new user-facing modules should ship with both supported languages as part of their Definition of Done.

### L10N-3 — Support/content/notification localization maturity

Extend bilingual coverage across Help Center, Knowledge Base, support workflows, notifications, release notices, templates, analytics labels, and other externally visible content as those capabilities are implemented.

## Recommended timing

Localization architecture should be established **before broad business-module UI expansion** to avoid expensive retrofitting of translation keys, locale-aware formatting, content, notifications, and mobile surfaces.

This document does not alter the current post-Sprint-14 successor authority. Localization source implementation is **not started by this publication**.

## Explicit non-scope of this publication

This documentation publication does not authorize or perform:

- Vue/TypeScript localization code;
- Laravel localization code;
- Android localization resources;
- package/dependency installation;
- translation catalog source files;
- IP-geolocation provider integration;
- Cloudflare/CDN country-header integration;
- location permission prompts;
- cookie/profile persistence implementation;
- schema/SQL/migration changes;
- database writes;
- deployment;
- Release/GitHub Release;
- Production/customer data;
- Production-readiness promotion.

## Canonical requirement summary

The intended future oneQay behavior is:

`Access Request`

→ `Resolve supported explicit preference if present`

→ otherwise `Resolve coarse access country`

→ country `ID` → **Bahasa Indonesia (`id-ID`)**

→ country other than `ID` → **English**

→ country unavailable → **safe supported-language fallback**

→ user may manually select **Bahasa Indonesia** or **English** at any time.

This is a cross-cutting product requirement and must be implemented only under a later explicit bounded authority.

Attribution: **Lab | zefry**
