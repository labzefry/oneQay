# DEC-010 — Product License and Third-Party Notice Policy Decision Record

- Status: Approved / Substantive Decision Complete
- Decision owner: Product Owner `labzefry`
- Product: oneQay
- Developer & Product Engineering Entity: Lab | zefry
- Decision baseline: `5cc572675dd7871a3ca841cedf06fbc8ea74f839`
- Verified baseline tree: `72704fffb97aa053092ba20271728bd2b0198982`
- Published predecessor: DEC-009 / PR #83
- Scope: product and governance policy only
- Legal-document status: **LEGAL REVIEW REQUIRED**
- Publication authority: documentation representation only; no implementation, distribution, deployment, release, or production authority

## Decision

DEC-010 establishes **PROPRIETARY / ALL RIGHTS RESERVED** as the default oneQay product/source licensing direction.

DEC-010 does not declare oneQay OSI open-source, open-core, dual-licensed, or a general source-available commercial product. Those models remain possible future decisions only through separate Product Owner authority and qualified legal review.

The existing `LICENSE` remains the current default protection baseline pending formal legal review. DEC-010 approves product and governance policy; it does not approve final lawyer-grade software-license, CLA, EULA, trademark, reseller/OEM, or jurisdiction-specific contract text.

## D-010 dispositions

### D-010-01 — Product license model

**Approved:** Proprietary / All Rights Reserved is the default oneQay product/source policy.

### D-010-02 — Repository visibility versus rights

Repository visibility and product-license rights are separate. A public GitHub repository does not by itself make oneQay OSI open-source. Future legal text must respect platform-specific rights, applicable law, and other binding agreements. DEC-010 does not authorize a repository-visibility change.

### D-010-03 — Commercial use

No general right is granted to third parties to commercially host oneQay for others, resell it, offer it as their own SaaS, white-label/OEM it, sublicense it, or commercially redistribute it unless a separately authorized written agreement expressly grants those rights.

### D-010-04 — Modification and fork boundary

DEC-010 grants no additional general off-platform modification or redistribution right. Platform-specific rights, mandatory law, and separately executed agreements remain respected. Commercial redistribution of derivatives requires explicit authorization.

### D-010-05 — Distribution

Source, binary, mobile, plugin, marketplace, OEM, reseller, and bundled distribution require an explicit rights basis. DEC-010 itself grants no distribution authority.

### D-010-06 — Trademark and brand

Software copyright/license rights do not automatically grant rights to the oneQay name, oneQay logo, Lab | zefry identity, product branding, or related marks. Final trademark terms remain subject to qualified legal review.

### D-010-07 — Contributor policy

External contributions are legally gated until an adequate contributor-rights framework is separately approved. The preferred future direction is a lawyer-approved Contributor Agreement / CLA suitable for a proprietary commercial product. DCO/sign-off may supplement provenance evidence but is not the sole proprietary contributor-right mechanism approved by DEC-010. Internal employee/contractor rights require an applicable employment, contract, IP-assignment, or equivalent rights basis.

DEC-010 does not create or approve final CLA/DCO legal text.

### D-010-08 — Third-party dependency policy

| License class | Default disposition |
| --- | --- |
| Permissive | ALLOW WITH COMPLIANCE |
| Weak / file-level copyleft | CONDITIONAL / REVIEW REQUIRED |
| Strong copyleft | BLOCKED BY DEFAULT |
| Network copyleft | BLOCKED BY DEFAULT |
| Proprietary / commercial | CONDITIONAL |
| Source-available / non-OSI custom | BLOCK PENDING REVIEW |
| Unknown / custom / no-license | BLOCK |

Permissive intake still requires provenance, exact license/version, required notices, attribution, applicable patent/trademark/license obligations, and transitive review. Strong or network copyleft requires explicit qualified legal review and a separately governed exception before adoption. Proprietary/commercial components require sufficient commercial use/deployment/distribution rights. Unknown licensing fails closed.

### D-010-09 — License compatibility gate

License compatibility is a **PRE-ADOPTION** gate, not only a release-time check. Review covers the exact package/version, direct/transitive status, license, provenance, runtime/build/dev classification, server/distributed/mobile/browser usage, modification, notice/attribution duties, source-offer obligations, and commercial rights.

### D-010-10 — Notice and attribution

Future authorized implementation/publication must maintain an auditable third-party license inventory and create `THIRD_PARTY_NOTICES.md` when actual resolved components or assets require such notices. No fictional or empty notice artifact is required by DEC-010.

Relevant inventory fields include component, version, copyright holder where required, exact license, SPDX identifier where available, attribution, license-text requirement, NOTICE requirement, source-offer requirement, and distribution context.

### D-010-11 — SBOM relationship

SBOM and legal notice are separate artifacts derived from a shared component inventory. Target traceability is:

`dependency lock -> resolved component inventory -> SBOM -> license review -> notice/attribution artifact -> release evidence`

SBOM does not substitute for license or notice compliance.

### D-010-12 — Cross-ecosystem dependencies

The same license-intake governance applies to PHP/Composer, JavaScript package ecosystems, Vue/frontend libraries, Android/Gradle, Kotlin/Java, native libraries, browser scripts, fonts, icons, images, templates, SDKs, binaries, and other distributed assets. Exact obligations depend on the actual license, modification, and distribution context.

### D-010-13 — Plugins and marketplace

First-party oneQay plugins are proprietary by default unless separately decided. Future third-party plugins require explicit publisher, provenance, license, distribution-right, dependency/license, attribution/notice, and compatibility metadata. No valid licensing basis means no future marketplace distribution.

No marketplace or plugin implementation authority is created.

### D-010-14 — AI, model, and data licensing

Future AI provider/model/dataset adoption requires licensing and terms review, including commercial-use rights, model-weight license, API/service terms, output-use rights, fine-tuning, redistribution, dataset provenance, attribution, derivative restrictions, and material change/termination risk where applicable.

No AI provider/model/dataset is selected. Privacy, data residency, retention, and jurisdiction remain under DEC-011.

### D-010-15 — Documentation, media, and assets

Documentation, images, icons, fonts, templates, demo data, media, and marketing assets require known provenance and a valid rights basis. Unknown provenance is not acceptable.

### D-010-16 — Private / enterprise modules

Future private/proprietary enterprise modules may coexist with oneQay. DEC-010 does not establish open-core. A future move to dual licensing, open-core, or an OSI open-source core requires a new substantive decision and compatibility review.

### D-010-17 — Security / license exception

A component that would otherwise be blocked requires a bounded exception identifying the exact component/version/license, use/distribution context, justification, alternatives, Product Owner, security review, qualified legal review where applicable, mitigation, removal/replacement strategy, and expiry/review date. Convenience is not a permanent exception basis.

### D-010-18 — Future license change

Any material future oneQay product-license change requires separate Product Owner substantive authority, copyright/contributor-right audit, third-party compatibility review, qualified legal review, and governed publication. Existing contracts, previously granted rights, public forks, distributions, and third-party obligations must not be silently treated as erasable.

### D-010-19 — Legal-review boundary

Product Owner authority governs product/commercial policy direction. Qualified legal review remains required for final externally relied-upon software-license text, public-repository/platform-terms reconciliation, CLA/contributor agreements, trademark/brand terms, customer EULA/SaaS terms, reseller/OEM terms, material copyleft compatibility determinations, and jurisdiction-specific enforceability.

DEC-010 substantive approval is not formal legal advice.

### D-010-20 — Explicit non-scope

DEC-010 does not authorize:

- material modification of `LICENSE` legal wording;
- creation of fictional `THIRD_PARTY_NOTICES` entries;
- repository visibility changes;
- dependency/package installation or adoption;
- application, Laravel, Vue/PWA, or Android implementation;
- AI provider/model adoption;
- commercial launch or customer/source/binary distribution;
- marketplace or plugin implementation;
- CLA execution or trademark registration;
- hosting procurement or infrastructure provisioning;
- deployment, release, or production promotion;
- DEC-011 or DEC-012 work;
- Sprint 14.

## Existing repository facts at decision baseline

At the verified decision baseline:

- the repository is public;
- GitHub license detection reports `Other / NOASSERTION`;
- top-level `LICENSE` is the `oneQay Proprietary License Notice` and identifies itself as a default protection baseline pending formal legal review;
- `PROJECT_MANIFEST.md` still represented `Proprietary / All Rights Reserved` as Proposed before DEC-010 publication;
- `composer.json` declares `"license": "proprietary"` and has no application/framework dependency in `require` beyond PHP `>=8.2`;
- no top-level `NOTICE` or `THIRD_PARTY_NOTICES` artifact was present;
- `CODING_STANDARDS.md`, `SECURITY.md`, and `RELEASE.md` already required dependency-license/supply-chain review and/or SBOM/license-notice release evidence at the appropriate maturity.

These facts do not create package adoption, external distribution, or final legal-text authority.

## Current legal-document disposition

The current top-level `LICENSE` remains unchanged by DEC-010 publication preparation and remains:

**DEFAULT PROTECTION BASELINE / LEGAL REVIEW PENDING**.

A material rewrite addressing platform terms, governing law, disclaimer/warranty, limitation of liability, termination, jurisdiction, commercial distribution, contributor rights, or similar lawyer-grade provisions requires qualified legal review and separate authority.

## Supersession and change control

This decision may be superseded only through a later Product Owner substantive decision with the required rights audit, dependency compatibility assessment, legal review, and governed publication lifecycle.

## Authority boundary

Publication of this decision record is policy publication only. It does not authorize implementation, package installation/adoption, distribution, commercial release, marketplace/plugin launch, infrastructure provisioning, deployment, release, production, DEC-011, DEC-012, or Sprint 14.

Attribution: **Lab | zefry**
