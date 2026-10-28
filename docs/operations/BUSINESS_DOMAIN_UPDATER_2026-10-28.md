# Separate business domain and Windows updater repair — 28 October 2026

Author by Lab | zefry.

## Problem and implementation

The canonical website and tenant address previously shared `APP_URL`. Add optional
`ONEQAY_BUSINESS_DOMAIN_URL` as a validated HTTPS base, retaining APP_URL for
website, activation email and central session handoff. Empty new configuration
preserves existing behavior. Existing business IDs, slugs, data, credentials and
permissions are unchanged; URLs are resolved against the configured base.

Development configuration, after routing and TLS acceptance:

```dotenv
APP_URL=https://oneqaydev.n07.my.id
ONEQAY_BUSINESS_DOMAIN_URL=https://n07.my.id
ONEQAY_BUSINESS_DOMAIN_RESERVED=jakon,magetan,mail,oneqay,oneqaydev
ONEQAY_BUSINESS_DOMAINS_ENABLED=false
```

The last flag remains false until wildcard DNS, cPanel document-root mapping,
origin TLS and browser TLS are verified. DNS alone is not activation. Tenant
URLs then become `https://<existing-slug>.n07.my.id`. The exact canonical website
host is exempt from tenant lookup. Existing infrastructure names and the
canonical website's label are unavailable for new claims; conflicting existing
claims fail closed and require platform-owner resolution, never reassignment.
Set the reserved list from the actual hosting/DNS inventory before activation.

Production relocation uses the same two settings with the new owned domain.
Review collisions, verify routing/TLS, notify affected business owners, drain
old handoffs and reauthenticate; do not rename tenant IDs or copy their data.
Rollback disables the domain flag and restores the previous base. Sessions use
host-only Secure cookies, never a shared parent-domain session cookie.

## Updater incident and prevention

The published0.2.6 stable manifest referenced `oneQay%20POS_0.2.6_x64-setup.exe`,
which returned HTTP404. GitHub published the uploaded asset as
`oneQay.POS_0.2.6_x64-setup.exe`. Installed0.2.4 and0.2.6 have identical public
trust keys and stable-v2 endpoints: no new key, password or reinstall is needed
to repair this incident.

The0.2.7 publication workflow reads back authoritative release metadata, downloads
the actual public installer and signature, checks SHA256/size, verifies the
signature against the embedded key, and builds latest.json from that published
URL. Any failure leaves the stable manifest untouched. No unsigned fallback,
key rotation, arbitrary URL or downgrade is introduced. The immutable0.2.6
asset is retained;0.2.7 is a new patch. Existing local backup/freeze remains.

## Threat model and validation

High risk: tenant host selection/session handoff and executable publication.
Controls retained: membership enforcement, wrong-tenant rejection, state/host
bound45-second single-use encrypted ticket, reserved sibling hosts, canonical
login, strict HTTPS root validation, exact release source and installer signature.
New tests cover split-root lookup, existing slug preservation, infrastructure
reservations, central handoff target, invalid/nested/unknown hosts, legacy
fallback, GitHub filename normalization, wrong URL/digest/version, missing
signature and downloaded tamper.

Validation and publication/deployment evidence must be added below when observed.
This source record alone does not assert hosting activation or a successful
installed-device update. Never reset merchant data or disable signature checks.
