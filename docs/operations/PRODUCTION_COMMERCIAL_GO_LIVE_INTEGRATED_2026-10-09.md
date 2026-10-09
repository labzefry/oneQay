# oneQay — Integrated Production Merchant Commercial Delivery (2026-10-09)

Author by Lab | zefry

## Business outcome and deployment target
One coherent delivery for authenticated cash POS transactions, not a separate administrative sprint.
The existing qualified staging is https://oneqay.n07.my.id, running source a5e833bce4baa22c21a144c558a0ca123f1d0b1e.
The customer-prepared isolated validation hostname is https://oneqaydev.n07.my.id with a separate cPanel document root /home/pekd7254/public_html/oneqaydev.n07.my.id and private runtime /home/pekd7254/oneqay-production-test. Operator-reported schema finalization established 27 core and one module migration receipts; no repeat migrations. Staging data and document root remain immutable in this engineering PR.

## Integrated engineering
- Add a native ProductionBusinessRuntimeGate to the shipped Laravel app, not a CI runtime projection or a special developer bypass.
- Preserve existing Local/Test/CI code checks, deny preview/staging in this new gate, and retain explicit separately authorized Production runtime admission with exact source, artifact and environment lineage.
- Integrate login/session, tenant/org/outlet/device context, authorization, catalog, cashier, shift open/close, sale/idempotent receipt, inventory, void/refund and reporting only through the gate plus existing permission and feature checks.
- Restrict initial commercial tender processing to CASH. MANUAL_EXTERNAL remains unavailable for real Production checkout until a separately proven settlement/evidence mechanism exists.
- Preserve special Local/Test/CI-only first-merchant bootstrap, privileged controls, updater, staging close bridge, and Technical Preview controls. In particular, production first-merchant setup is not yet authorized/implemented by this PR.
- Prevent an unrelated cPanel workflow from accidentally allowing schema mutation: require a separate exact app-path list and preserve all existing branch-specific path rules.

## Certification boundary
This engineering change is NOT evidence that checkout is already accepted at the oneqaydev host. The official Production RC build remains explicitly **dark-only** and its manifest MUST NOT be relabelled to business-ready without evidence. Neither the old dark executor nor the existing operator ZIP can activate transactions. The Production native gate remains closed against that release.

For a business-ready release, a successor signed and exact-source build + distinct Production business-traffic authority + compatible cPanel deployment/activation operator and host transaction proof are required. The host operator will need to initialize the first authorized merchant without exposing credentials and qualify app-specific state on the segregated DB.

## Deployment contract for the next combined phase
1. Merge only after exact-head owner authority, full CI and the integrated cash POS suite pass.
2. Produce exact-source production candidate with deterministic archive + transactional proof. Do not mutate a previously published dark archive. If not transaction-certified, refuse business traffic rather than manually flip RELEASE.json or an env switch.
3. Deploy through cPanel no SSH to oneqaydev isolated public/private roots, with short-lived operator authority, database binding, rollback, and no migration #27 replay. Remove the temporary index.html only under an authorized atomic serving swap.
4. Exercise login (and TOTP for privileged), merchant scope, catalog, opening cash, shift opening, cash sale, receipt, idempotent replay, stock movement, closing cash, Final Shift Close and reporting on authorized test fixtures in that host. Retain evidence without leaking secrets.
5. Only then seek separate live-business and domain-cutover approval for oneqay.n07.my.id; the existing qualified staging must not be overwritten early.

No operational Production deployment, traffic activation, or real monetary charge has been performed by this PR.
