# Windows POS 0.2.4 signing and Program Files recovery

Author by Lab | zefry. Product Owner explicitly authorized a new signing key and one manual recovery install on23October2026, after confirming no previous private signing key was ever created by them. This is a new trust generation, not a signature compatible with installed old clients.

Old NSIS config used currentUser, hence `%LOCALAPPDATA%/oneQay POS`. New config uses perMachine: default `C:\Program Files\oneQay POS`, admin/UAC required. The identifier remains `id.oneqay.pos`; databases remain in the same Windows user's `%LOCALAPPDATA%\id.oneqay.pos`, never Program Files. Different Windows accounts have separate local queues.

A new password-protected Tauri signing pair was generated privately with the official pinned CLI, with a signing probe. Only its public key is in source. Private key/password must never enter git, Actions artifacts, release assets, logs, cPanel or chat. Owner receives a private key backup and configures the two Actions secrets. The stable-v2 endpoint is separate from the old stable endpoint: old clients are not offered an incompatible signature.

## Operator sequence

1. Qualify and merge this PR; repository main must contain the exact new public key before private secret provisioning.
2. Owner runs the private configure script with GitHub CLI authenticated as labzefry. It compares main's public key, writes the two Actions secrets through stdin, and dispatches Signed Windows POS release. No password pasted into chat. Preserve a private offline backup of both files.
3. Verify Windows build/core/signature checks and release publication. Download immutable `pos-windows-v0.2.4` installer and compare SHA256SUMS. Do not use a PR CI installer as the signed production release.
4. On each existing PC, synchronize online if possible and close POS normally. Run `apps/pos-desktop/scripts/backup-before-recovery.ps1` as the existing cashier Windows user. It copies all profiles and WAL/SHM files, verifies hashes, rejects links or a running POS; never clears or restores data automatically.
5. Run the official recovery installer, accept UAC, select default Program Files. An old per-user binary may remain because the install scope changed. Open the new Program Files shortcut under the original Windows user. Confirm pending count, cashier profile and shift before removing an obsolete shortcut/application. Never select remove app data. Do not copy a stale backup over newer transactions.
6. Future newer releases on stable-v2 are signature-verified with this key before backup/install. Updates may show UAC because binaries are in Program Files. Private key provisioning/release/real-device acceptance must be recorded separately.

## Limits

Updater minisign signatures are not Microsoft Authenticode certificates. No Microsoft publisher certificate or absence of SmartScreen warning is claimed. No bypass of Windows security prompts is provided. No old-key compatible automatic update, real PC migration, offline queue synchronization, or SMTP delivery is claimed until observed.

Web source d63cf4dd44af was deployed successfully earlier; this desktop-only recovery does not require another cPanel deployment. Live receipts are in PR984 comment6356615103 and PR985 comment6356654615. Source/channels/version changes must be tested; original key absence is documented in the prior account-flow checkpoint.
