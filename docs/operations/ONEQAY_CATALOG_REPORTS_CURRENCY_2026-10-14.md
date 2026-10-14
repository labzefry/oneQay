# oneQay catalog, report downloads and persistent currency

## 2026-10-14 — Catalog, reporting downloads and persistent currency successor

Author by Lab | zefry. Target: oneqaydev.n07.my.id; predecessor application f5a037ce60aa05c61e0b9c197f2e171c4d01fa8c, host update-result SHA256 73f0176fb4e07713ffe6aa619e03964ef5d351db44961ede2286bab9cc9a7cf3.

- Delivered source: reference-driven blue header and white grouped sidebar; product catalog/search/category/brand/variant/SKU/barcode/price fields; guarded CSV/XLSX import preview (500 rows) and all-or-nothing canonical catalog/opening-stock commit; PDF and real XLSX downloads across filtered pages (10,000-row bound); persistent organization currency/symbol/name settings; exact integer decimal formatting across POS and business views; dated sales trend from scoped correction-aware POS facts.
- Currency defaults apply to new records. Existing financial/product currency is preserved; no FX conversion or mass relabelling. Wholesale/cost fields are stored catalog metadata, not an automatic checkout discount engine.
- Only one new additive business-module migration creates preferences and product profiles. Historical migrations, merchant identities, credentials, existing stock and records must remain unchanged.
- Local verification: 50 merchandising/SQL/file checks, 73 existing integrated-business checks, and 20 canonical role-administration checks passed on SQLite. Native MySQL and compiled-browser qualification are pending; no hosting deployment of this successor is claimed yet.
- Olsera is a visual/navigation reference. External marketplace/GoFood services, payroll/tax automation, loyalty redemption, coupon pricing, and commission settlement are not delivered merely by adding a menu. Existing owner SMTP/worker setup and authenticated host acceptance remain pending.

## Operator handoff

The last verified host update applied exactly two business/platform migrations, retaining the already-installed replenishment migration. The database now has the exact 30-receipt baseline. A successor must validate that baseline and existing module schema before applying only the merchandising migration. Never reuse the receipt recovery guard, reset reports, delete backups, or replay prior DDL.

Exports require current scoped authority on every request; all records are scoped using the authenticated context. CSV/XLSX import accepts the downloadable single-sheet template; fields are strings and exact decimals, formulas and external entities are rejected. Invalid or duplicate rows prevent commit; stable retries use the original operation and payload. Existing products must be edited explicitly and opening stock must never be re-established. Additional stock continues through canonical receiving.

A currency update uses optimistic versioning and a durable audit event. It changes the default for new records and the display symbol for matching currency only. Existing amounts and currency codes remain immutable. Numeric currency codes, barcode/SKU identifiers, and monetary values are distinct: barcodes retain leading zeroes; money is stored as integer atomic units, never binary floating point.

PDFs embed the licensed DejaVu Sans font, repeat headers and include page numbers and Lab | zefry attribution. XLSX files are OOXML, use text cells for untrusted content, numeric cells for representable values, and text for values beyond Excel's 15-digit precision. XLSX imports require PHP DOM and zlib; the hosting operator must qualify those capabilities before promotion.
