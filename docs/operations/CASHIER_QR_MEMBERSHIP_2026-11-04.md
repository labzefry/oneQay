# Shared cashier QR orders and member enrollment

Cashiers use the same order screen on web and Windows: scan a keyboard-style QR token (or enter an exact phone number), select a pending order, reduce quantities or remove unavailable products, check the current quote, and explicitly confirm payment. Cancellation only applies to unpaid orders. Public ordering and web session authorization remain unchanged. Current cashiers POST the lookup criteria so tokens and contact details do not enter URL logs; the existing web GET route remains compatible.

On either cashier surface, the directory searches by exact normalized phone or email and enrolls a member by name, phone and email. Records are scoped to the verified business organization and linked to the canonical customer directory; duplicate contacts do not create a second membership. A selected member is attached atomically to a paid QR receipt. Enrollment does not grant consent, verify contact ownership or create a password. Editing linked contacts through the older customer form is blocked until a verified contact-change flow exists.

Windows uses an allowlisted, HTTPS-only native command. It reuses the encrypted cashier credential, verifies the server's business scope and permission, and refuses offline QR settlement. There is no second local payment or stock journal for QR sales. A lost response preserves the exact payment request for retry while the screen remains open; after leaving the screen or signing out, reconcile against the server before receiving payment again. Ordinary offline checkout remains separate.

## Contract and rollout

- Browser: protected page `/pos/business/cashier-members` and POST actions under `/pos/business/menu-orders/`.
- Native: POST `/api/pos/desktop/v3/menu-orders/{lookup,quote,settle,cancel,member-lookup,member-register}`; legacy native APIs remain unchanged. Service verticals are excluded. Snapshot feature fields are additive.
- New additive module migration `2026_11_04_000004_create_cashier_memberships.php` creates two tables (membership identity and immutable sale attribution). Upgrade from the verified 430cbbb oneQayDev deployment requires a fresh predecessor binding, backup and source-bound operator qualification for 114 → 116 tables / 44 → 45 migration receipts. The previous menu ZIP is not compatible with this revision. A source rollback keeps additive tables intact.
- Keep `ONEQAY_CASHIER_MEMBERSHIP_ENABLED` off until migration and pilot verification. QR continues to use `ONEQAY_MENU_GUEST_ORDERING_ENABLED`. Deploy and qualify the web API before publishing a higher-version signed Windows patch. The Windows version is intentionally unchanged in this draft; do not tag the existing version again.
- Exact SQLite/MySQL and Windows build CI, independent security review, cPanel operator rehearsal, and device testing with a physical scanner remain release gates. Local validation does not certify the signed installer or hosted runtime.

Still outside this change: member login/setup email, accrual/redemption and refund-safe point ledger, membership for non-QR sales, receipt/reminder email, printing native QR receipts, and signed Windows publication.

## CI follow-up

The first PR head passed the dedicated SQLite/MySQL menu and membership suite and Windows installer build. Its failed jobs stopped in legacy sprint source-envelope steps, not in those product suites. Membership configuration now uses a dedicated config file so the historical updater and shift-close configuration assertions can remain intact. The remaining historical scope checks accept this successor only for the internal PR #995, its pinned base and branch, and exact Git blobs and file modes in the successor manifest. Original behavior for other events, frozen legacy fixture scripts, operational authorization assertions and independent functional steps remain unchanged.

The next CI run confirmed the menu and Windows builds again; its remaining failures reached older migration horizons, fixture checks or the dependency audit's historical path classifier. This follow-up keeps all 27 canonical migrations and protected fixture/dependency inputs byte-identical. It reuses the existing disposable fixture path for the exact successor and keeps executable regressions, npm/Composer advisory checks and operational checks active.

## Independent review follow-up

The exact `a23fb3b` review found that the cashier could confirm payment before a member lookup returned. A delayed selection could invalidate confirmation while a payment reply was unknown. The shared workspace now blocks confirmation and payment until member actions finish, requires reconfirmation after a selection, and keeps the original member, amount, lines and operation ID for retry. A native rejection before HTTP cannot resolve an earlier uncertain attempt. The desktop UI test delays the member lookup, simulates a lost reply and a local retry rejection, then checks that retry sends the same body.
