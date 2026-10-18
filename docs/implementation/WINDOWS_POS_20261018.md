# Windows POS and friendly error experience — 18 October 2026

Owner request: Windows 10+ POS-only application, local offline transactions, reconnect synchronization, printer/cash drawer/scanner setup, friendly browser error pages and session expiry login redirects. This explicit owner request authorizes a bounded Windows O2 implementation under DEC_008 / ADR-006, extending the earlier Android-first implementation preference. It does not change server authority, RBAC, tenant isolation, real-money validation, or deployment certification requirements.

## Implemented direction

Tauri 2, the system WebView2 runtime, Vue 3 and bundled Iconify/Lucide icons; SQLite WAL with FULL synchronization. Native credentials use Windows user-bound DPAPI, never JavaScript or localStorage. No native business administration. Pairing is a five-minute, single-use capability minted by an authenticated POS session. Native requests revalidate the canonical session authority, credential/TOTP epochs, business eligibility, tenant membership, organizational relationships and POS permission. Reauthentication retains the local queue.

Offline sales are provisional CASH records, bounded to an eight-hour catalog/shift lease. No offline shift creation, provider settlement, silent price adjustment or inventory overwrite. The server reuses CompleteSale and its canonical durable repository. Operation ID and payload identity survive retries. Server shift/price/stock changes become review conflicts. Stock counts are review proposals; they do not directly mutate sellable stock. SQLite retains accepted, pending, rejected and conflict receipts. No queue-clearing action is provided.

USB and paired Bluetooth printers use Windows RAW spooler queues supporting ESC/POS. LAN and WLAN use TCP/IP, normally port 9100. Cash drawer pulses use the receipt printer; scanner input uses USB/Bluetooth HID keyboard mode. Printer delivery is not proof that paper physically emerged; uncertain jobs require explicit duplicate-marked reprint.

Normal HTML requests receive self-contained illustrated bilingual error pages and NOC references. Expired HTML/Inertia sessions navigate to the fixed business or owner login route. Native/API JSON contracts retain their status and safe envelope. Illustrations and Iconify data are bundled, so errors and offline POS do not depend on a public image or icon service.

## Validation and release status

Development branch: experience-pos-desktop-20261018, predecessor main eb9a02d00a8ca11d6287d9be149efdd99f25967a. Initial local validation: 13 SQLite offline tests and 131 assertions in browser-error-experience (including existing platform/control-plane regression assertions) pass; desktop Vue typecheck and bundle build pass. Server synchronization integration passes 83 assertions including 65 predecessor regression assertions. Windows CI compiled all Rust code; a Windows spooler import-library name was corrected after the first linker failure. The next installer build is in progress. Not installed on hosting. No physical printer, Windows 10 hardware, real-cash pilot or production sync acceptance is claimed.

Production remains production-78202bb5fe59. New desktop module is additive, forward-only; installation needs a newly certified application source and a bound 782 successor cPanel operator with existing verified ZIP retention. Do not reuse the old 14e-to-782 update package for this successor.
