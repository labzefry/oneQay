# Integrated business management

Author by Lab | zefry

This change adds operational supplier, purchasing, customer, finance, and people workflows to the existing POS application. It is a source delivery; merging it does not establish that these functions are deployed or accepted on the hosting account.

## User workflows

| Workspace | Usable flow | Authorization |
| --- | --- | --- |
| Suppliers | Create/edit supplier contact records and active status | Supplier view/manage |
| Purchasing | Create order, submit, approve/reject/cancel, receive partial or complete delivery | Separate manage, approve, receive grants; receiving also requires POS inventory replenishment |
| Customers | Create/edit customer, record or withdraw marketing consent, log interactions and follow-up dates | Customer view/manage |
| Finance | Match payable to goods receipt, issue customer receivable, record partial/full settlement, post balanced journals, reverse manual journals, view trial balance | Separate view, post, settle grants |
| People | Employee records, attendance and corrections, leave requests and decisions | Separate view, manage, approve grants |
| Activity log | Paginated immutable command records and filtered CSV page export | Audit view |
| Business access | Assign/revoke predefined roles to an existing eligible account for the current outlet | Existing protected policy administrator, with configured MFA and fresh step-up |

Indonesia remains the default language; English uses the existing language switcher. Existing translations, credentials, session verification, authorization policy, POS mutation logic, and the 27 historical migrations are preserved.

## Activating team access

An authorized tenant administrator opens `/pos/business/access`, verifies the privileged session when enabled, and selects an existing member identity and role. Presets define business manager, purchasing, finance, customer, people, or read-only business access. They do not grant protected administrator authority. Opening `/pos` refreshes the available navigation.

Preset definition requires tenant-level policy authority. Outlet assignments do not grant organization membership or outlet eligibility. Those prerequisites continue through the existing governed identity administration flow. Revoking a preset removes its assignment; grants from other roles remain effective.

## Integration and data integrity

Goods receipt updates the existing POS stock journal and writes the corresponding purchasing receipt and balanced inventory accrual in one database transaction. Supplier invoices clear the goods-received accrual into accounts payable; settlements clear payable or receivable balances against the selected cash/bank account. The settlement form records a payment already made; it does not initiate an external transfer.

Operations use server-verified tenant, organization, outlet, device and actor. Stable operation identifiers prevent duplicate stock and payment postings. Authorization is checked again on replay. Stale versions, excess receiving, excess payment, unbalanced journals, cross-tenant references and invalid dates are rejected. Reports keep currency and scale separate. CSV cells are formula-neutralized, and sensitive responses are not cached.

The additive migration `database/module-migrations/business/2026_10_12_000001_create_integrated_business_management.php` creates twelve tables. Historical migrations are not replayed. Its destructive down migration is intentionally unavailable; recovery must use a verified backup and the appropriate application release.

## Qualification

`Integrated Business Management` executes relational SQLite and MySQL business tests, real SQLite role administration tests, existing policy/MFA and POS regressions, TypeScript checks, the production frontend build, and browser interaction checks on the compiled output. Browser fixtures establish UI behavior only; SQL tests establish persistence behavior. CI artifacts contain the compiled frontend and mobile screenshots.

Historical workflow edits are confined to source compatibility steps. `tools/qa-business-management-source-envelope.sh` checks the exact predecessor, explicit path set, unchanged protected foundation, additive provider registration, preserved translations, and byte-identical executable regression steps. These source checks do not authorize deployment.

## Hosting and remaining product scope

The latest verified hosting evidence in the project context runs bilingual source `e93b7fef42cb36840b7fca6b79435c6188a13cd5`. This new backend and schema require a certified successor release, host preflight, backup, guarded additive migration, route/cache rebuild and authenticated acceptance. The previous presentation-only updater is not suitable. No hosting execution is asserted by this document.

The enterprise vision is broader than this delivery. Payroll, statutory accounting/tax, automatic POS-to-ledger posting and costing, manufacturing, warehouse transfers, campaigns/loyalty, full support ticketing/SLA, and external live payment integrations are not implemented by these new workspaces. The earlier enterprise capability matrix remains the inventory baseline; this document records the concrete business-core successor and must not be interpreted as 100% enterprise completion.
