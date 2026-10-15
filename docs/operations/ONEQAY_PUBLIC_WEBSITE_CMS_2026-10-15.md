# Public website, visual editor and business registration

Author by Lab | zefry

## Delivered scope

The public root becomes a bilingual product website inspired by the requested POS marketing structure. Indonesian is the default. Twenty default page/menu documents describe actual oneQay operations; custom pages can also be created. The owner can change headings, rich text, imagery, calls to action, feature cards, FAQ content, navigation and SEO descriptions. Icons are Lucide assets retrieved from the Iconify public API and bundled locally, preserving the existing CSP and avoiding a runtime network dependency.

`/platform/site` is owner-only and uses the canonical active-session, verified organization context, MFA configuration and fresh policy step-up boundaries. Drafts, immutable publication revisions and media persist in module-owned database tables. Edits support drag-and-drop and keyboard reorder, live canvas, mobile/desktop review, Indonesia/English content, validated image upload, optimistic versions and stable request replay. Publishing binds the reviewed document digest and version. Restoring a revision changes only the draft. Public visitors never receive unpublished custom pages or unused draft media.

`/register` accepts a business name, owner email, phone, type, language and consent. It records a pending application in the existing platform business directory. Duplicate email submissions receive the same acknowledgement without creating another business. No role, tenant identifier or password is accepted publicly; no unapproved invitation is sent. The platform owner approves the application, then the established encrypted mail outbox sends the activation link. Activation uses existing tenant provisioning, canonical credential storage, protected business-admin roles and authenticator enrollment. SMTP configuration remains platform-owner-only.

The initial, hosting-provisioned owner uses `/owner-login` with the existing private credential. Existing business owners use `/business-login`. The public root no longer emits the initial owner's merchant context. The merchant sidebar retains an exact server-provided platform-owner destination while continuing to reject unrelated external navigation.

## Operational contract

One additive module migration adds four tables. It preserves all 31 predecessor migration receipts, identity tables, credentials, sessions, business data and SMTP settings. The current host predecessor is production-ec48e3fffdbb. Source qualification is separate from deployment and authenticated host acceptance. Do not execute a generic migration command or reuse the previous catalog updater for this release.

Media: JPG/PNG/WebP only, maximum 3 MiB and 20 million pixels; no SVG or executable content. Stored media is limited to 100 MiB; pages to 100; each language to 24 sections and each section to 16 cards. Rich HTML uses an attribute-free allowlist with first-party relative links. Registration has CSRF and minute/daily throttles. Draft writes and publication use owner audit records and a serialized idempotency transaction.

## Product and asset accuracy

No copied Olsera testimonials, customer statistics, partner claims or certifications. Illustrative workspace figures are labeled as sample data. No stock photograph is required for this product-focused layout; owners can upload their licensed photos. External payment, marketplace, courier and capital-provider integrations are explicitly described as requiring separate integration work and provider accounts. This delivery does not claim that every third-party Olsera capability is already implemented.

Icon source: <https://api.iconify.design/lucide.json>; documentation: <https://iconify.design/docs/api/icon-data.html>. Licenses accompany the assets in `apps/web/resources/website/LUCIDE-LICENSE.txt`.

## Verification

`apps/web/tests/website-cms-registration.php` exercises real SQL persistence, sanitization, revision publication/restore, stale and idempotent requests, media visibility, business registration through canonical activation, tenant-owner separation and middleware boundaries. Native CI runs SQLite and MySQL plus the compiled Vue browser workflow for navigation, responsive layout, form submission and editor review/publication.
