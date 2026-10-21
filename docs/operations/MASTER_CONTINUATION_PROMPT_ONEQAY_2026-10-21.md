# Prompt lanjutan oneQay — UI/UX modern, palet terkunci, deploy tanpa Cloud Browser

> **Update from ongoing implementation on 2026-10-21:** This handoff is the original requirement contract, not evidence that UI modernization was completed. Feature branch `agent/ui-enterprise-palette-feedback-20261021` and PR #978 now contain incremental UI source changes, with CI/review and deployment separately gated. Read `docs/operations/ONEQAY_UI_FOUNDATION_CHECKPOINT_2026-10-21.md` and the live PR head first. Never use the old `production-7efdda7648c2` grant for a new release or assume the hosting source follows the branch.

Author by Lab | zefry. Salin isi bagian **Prompt siap pakai** ke chat baru. Dokumen ini mencatat tugas berikutnya; perubahan UI belum diimplementasikan oleh handoff ini.

## Prompt siap pakai

Anda melanjutkan pengembangan **oneQay — The Future of Intelligent Business Management**, repositori https://github.com/labzefry/oneQay, branch main. Saya Product Owner/pemilik repositori. Ini kelanjutan proyek yang sudah berjalan, bukan membangun ulang dari nol. Kerjakan implementasi sampai hasilnya konkret, teruji, dipublikasikan melalui alur PR yang berlaku dan paket deploy siap digunakan. Otorisasi pengembangan/squash-and-merge yang sudah diberikan tetap berlaku dalam ruang lingkup tugas; jangan meminta konfirmasi berulang untuk keputusan implementasi rutin. Jangan melewati perlindungan, review/check yang benar-benar berlaku atau mengarang persetujuan. Jika suatu gate memang belum terpenuhi, jelaskan tepat gate dan bukti yang kurang, sambil menyelesaikan pekerjaan lain yang tidak bergantung padanya.

### 1. Cara kerja chat biasa dan konteks yang hemat

Saya memakai **chat biasa, tanpa Cloud Browser**. Jangan membuka/menawarkan Cloud Browser, meminta saya login ke browser cloud, mengambil cookie/session atau meminta password/OTP cPanel melalui chat. Gunakan konektor GitHub yang tersedia. Jika chat tidak memiliki kemampuan menulis repo atau membangun artefak, sebutkan batasan secara jujur dan siapkan patch/file/instruksi yang konkret; jangan mengaku sudah commit, merge, build atau deploy.

Baca current main serta catatan terbaru ini terlebih dahulu:

1. `docs/operations/ONEQAYDEV_DEPLOYED_CHECKPOINT_2026-10-21.md` dan tautan bukti produksi di dalamnya.
2. `docs/design/ONEQAY_UI_PALETTE_2026-10-21.json`.
3. `docs/design/ONEQAY_UI_MODERNIZATION_2026-10-21.md`.
4. `UI_GUIDELINE.md`, README, PROJECT_MANIFEST, TASKS, CHANGELOG, ARCHITECTURE, ROADMAP, AI_CONSTITUTION dan CONTRIBUTING. Baca dokumen domain/ADR yang terkait perubahan, bukan seluruh arsip sprint berulang kali.
5. Implementasi sebelumnya: `docs/implementation/MAIL_CATALOG_STOCK_20261021.md`, `docs/implementation/MAIL_STOCK_DEPLOY_20261021.md`, `docs/implementation/POS_ENTERPRISE_OPERATOR_HANDOFF.md`.

Gunakan ringkasan/checkpoint repositori sebagai indeks konteks; itu bukan fine-tuning model atau jaminan cache lintas chat. Catat commit/blob yang sudah dibaca, baca ulang hanya bagian yang berubah atau dibutuhkan. Source truth GitHub/receipt aktual mengalahkan catatan historis yang stale. Jangan mengulang provisioning, audit menyeluruh, setup lama, atau sprint yang sudah ditutup. Pemeriksaan awal harus ringkas dan langsung mengarah ke implementasi.

### 2. Baseline yang wajib dipertahankan

- Aplikasi hosting aktif: `7efdda7648c2e89bf0100b1ac8d76763ec73dd94`, release `production-7efdda7648c2`, deploy21Oktober2026 pukul18:17:29WIB.
- Main sebelum handoff dokumentasi: `afda997f79a866dfd151cc50de46275dd250cd3c`, operator PR#976. **Source aplikasi hosting, main terbaru dan operator berbeda; jangan disamakan.** Mulai dari main aktual, pertahankan perilaku aplikasi yang sudah terpasang.
- PR#975 aplikasi dan PR#976 operator sudah squash-merge. Sertifikasi aplikasi run39532367140/artifact12315947592; 6.395 berkas diverifikasi di hosting.
- Bukti eksekusi: https://github.com/labzefry/oneQay/pull/975#issuecomment-6314727770.
- State aktual BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING, traffic ON_PRESERVED, NATIVE_PRODUCTION_PASS. Database **38 receipt/88 tabel**. Jangan jalankan ulang migrasi historis atau dua migrasi21Oktober yang sudah selesai.
- Fitur terakhir terpasang: SMTP worker terpisah dari health/campaign, laporan status SMTP, retensi log72jam, tambah kategori, stok tracked/unlimited sampai canonical sale/void serta web/desktop source. Worker berjalan tanpa error, sampel antrean0. **SMTP diterima server/inbox/spam placement belum terbukti.** Jangan mengaku email sudah terkirim karena worker sehat.
- Desktop source/build0.2.1 terpisah dari installer yang dipakai user; signed patch updater belum dipromosikan. Uji kas/offline riil dan acceptance business login berotorisasi belum dibuktikan. Jangan menyembunyikan status ini atau mengklaim100% hanya karena UI tampil.
- Backend Laravel/PHP; Vue3+Inertia+Vite; MySQL; Windows POS mengikuti implementasi desktop yang ada. Pertahankan Modular Monolith/Clean Architecture/DDD, schema milik modul, transactional outbox, tenant/outlet/device scoping, session, privileged TOTP, RBAC deny-by-default, idempotency, correlation ID dan integritas angka uang/stok.
- Control plane hanya pemilik aplikasi; SMTP hanya di control plane. Business owner mengelola bisnis/pegawai/kasir sendiri. Windows POS tetap kasir, bukan admin bisnis. Tidak ada generic grant atau data tenant silang.
- UI copyright oneQay. `Author by Lab | zefry` hanya source/docs, bukan teks footer halaman.

### 3. Palet wajib — tidak boleh ditambah atau diganti

Acuan pemilik: lampiran `image(20261021-123331).png`, SHA256 `bc981f5729a5b9e92c0125559deb4a99c9b3dee33d1bd53c33d2b5148f595a51`. Gunakan **HEX tertulis**, bukan perkiraan warna sampel screenshot:

- Navy Blue `#1B1B39`.
- Primary Blue `#3965FA`.
- Soft Blue `#99B7FC`.
- Pale Blue `#E9EBFF`.
- Base White `#FFFFFF`.
- Light Gray `#EDEDED`.
- Dark Gray `#202020`.

Jadikan JSON palet dan UI_GUIDELINE kontrak permanen untuk pengembangan berikutnya. Terapkan token semantik bersama di sistem styling yang ada; jangan menduplikasi CSS per halaman. Hapus drift teal/hijau/merah/amber/ungu lama dalam cakupan UI yang dimigrasikan; jangan membuat tema baru atau menambah HEX/tint solid sendiri. Shadow/overlay hanya alpha dari warna palet dengan kontras komposit diperiksa. Jangan memakai nama/branding pihak ketiga dalam aplikasi oneQay. Logo/foto unggahan bisnis adalah konten pengguna dan tidak direcolor sembarangan.

Success/error/warning ditandai label, icon, border/pola yang jelas dalam palet ini. Grafik memakai label/pola/dash selain warna. Jaga kontras teks normal>=4.5:1 dan kontrol bermakna>=3:1 sesuai konteks. Primary blue di atas pale blue tidak cukup untuk teks normal; gunakan navy/dark. Tidak boleh ada tulisan putih di tombol pucat, angka transparan atau tombol konfigurasi tidak terlihat. Ukur semua state aktual, termasuk hover/focus/selected/disabled.

### 4. Arah desain modern dan morph

Buat UI enterprise modern yang rapi, nyaman, jelas hierarkinya dan konsisten: shared shell/navigation, kartu ringkas, tabel presisi, filter/pencarian mudah ditemukan, konfigurasi terkelompok, whitespace seimbang, alignment bagus, responsive dan micro-interaction halus. Modern berarti kualitas kerja pengguna, bukan sekadar menambah bayangan/gradient atau menu palsu.

Gunakan morph/soft-depth secara terkendali: kartu berlapis, bayangan tipis, inset seperlunya, transisi container/dialog dan state yang natural. Pertahankan batas input/tombol, kontras, solid fallback, keyboard dan fokus. Hindari glass/blur berlebihan, animasi lama, background bergerak terus dan beban GPU yang mengganggu Windows10 dengan spesifikasi ringan. Hormati prefers-reduced-motion; animasi tidak boleh menjadi syarat proses selesai.

Font normal proporsional: body/form16px/1rem; label sekunder operasional minimal14px; tabel14–16px; subjudul20–24px; judul24–32px. Gunakan rem/clamp, line-height nyaman, numeral tabular untuk angka, font existing/system/local yang efisien. Tidak ada shrink-to-fit menjadi tulisan kecil. Pastikan zoom200%, keyboard, layar kecil dan English text expansion tidak memotong fungsi. Ikon Iconify konsisten, accessible name untuk icon-only; bundle offline-safe untuk POS Windows. Semua nominal tetap numeric/decimal canonical dan simbol mata uang tetap mengikuti setting bisnis.

Cakupan mencakup homepage/auth/registration/error, control plane/NOC/SMTP/CMS, business dashboard/configuration/catalog/category/stock/people/reports/import, POS/shift/receipt configuration dan desktop source yang relevan. Perbaiki khusus halaman konfigurasi bisnis/POS yang berantakan dan tombol tidak terlihat. Gunakan shared components sehingga cakupan selesai menyeluruh; jangan mengklaim seluruh aplikasi modern bila baru homepage.

### 5. Loading/progress setiap proses — tidak boleh stuck

Buat komponen feedback proses terpusat dengan operation ID dan state jelas: idle, pending/running, succeeded, failed, cancelled, timed-out, unknown/pending-verification dan offline-queued. Setiap aksi pengguna bermakna mendapat popup/progress dan hasil yang mudah dipahami: save, upload/import, exportPDF/Excel, SMTPtest, registrasi, buka/tutup shift, penyiapan print, sync/update. Delay kemunculan popup sekitar200–300ms boleh mencegah flash pada proses instan; hasil akhir tetap terlihat. Background polling/autosave dan scan cepat POS tidak boleh menimbulkan rentetan modal yang mengganggu.

Wajib:

- Spinner berhenti pada semua terminal state; cleanup finally/onFinish, timer/listener dihentikan, kontrol kembali aktif sesuai state dan fokus dipulihkan.
- HTTP202/queued bukan selesai; ikuti job/operation ID sampai status terminal server. Jangan campurkan SMTP accepted dengan inbox delivered, printer spool accepted dengan kertas tercetak, atau download dimulai dengan file selesai tersimpan.
- Persentase hanya jika progress/total nyata tersedia. Selain itu gunakan indeterminate + label tahap. Dilarang progress100% palsu, timeout yang dianggap sukses atau animasi yang menunggu selamanya.
- Timeout/disconnect mutasi bisa berarti hasil belum diketahui. Tampilkan pending verification, cek operation yang sama dan jangan otomatis submit ulang sebagai transaksi baru. Cegah duplicate click/commit; pertahankan idempotency.
- Cancel/AbortController pada browser tidak membatalkan transaksi yang sudah committed. Abaikan stale response, context mismatch dan callback setelah unmount. Bersihkan state saat route/session berubah tanpa menghilangkan durable offline queue.
- Long job dapat diminimalkan/dipantau ulang bila backend mendukung. Cancel hanya ditampilkan bila benar-benar didukung. Polling bounded/visibility-aware, tidak merusak isian yang belum disimpan dan tidak menghidupkan loader kembali setelah selesai.
- Offline queued, syncing, conflict, failed, unknown dan synced berbeda. Reconnect belum berarti sync selesai. Expired session kembali ke login, bukan rawJSON; error sopan dengan kode/correlation aman untuk NOC.
- Dialog/status/progressbar accessible, aria-busy/aria-live, keyboard, fokus dan reduced-motion berfungsi. Error tidak ditutupi toast sukses.

### 6. Urutan kerja dan validasi yang cukup

Mulai dari token/shell/typography dan primitive async, lalu konfigurasi control plane/business, katalog/report/import/SMTP, cashier/shift/desktop dan public/auth/error. Lanjutkan otomatis sampai cakupan selesai. Jangan mengganti backend/accounting karena tugas visual. Jangan menambah dependency, audit panjang atau tes yang hanya meniru implementation tanpa kebutuhan.

Jalankan build/check repo yang berlaku dan tes bermakna untuk: warna/kontras, responsive320/768/1366/1920, keyboard/focus/zoom/reduced-motion, ID/English, sukses cepat/lambat,422/403/401/419/500, disconnect, timeout unknown, queued-job completion, late response, duplicate submit, unmount dan operasi bersamaan. Pastikan tidak ada spinner tersisa atau write duplikat. Pertahankan sale/void tracked-unlimited, shift, import/export, SMTP retention dan offline sync. Rekam before/after dengan data sintetis/redacted. Klaim hanya check yang benar-benar dijalankan; jangan mengubah gate/test lama agar hijau.

Setiap milestone perbarui PROJECT_MANIFEST, TASKS, CHANGELOG, UI_GUIDELINE, brief/palet bila relevan dan checkpoint lanjutan: perubahan, path/commit/PR, tes, selesai/pending, bukti dan langkah berikutnya. Jangan simpan password, sessionURL, TOTP, token unduh, SQLbackup atau data pelanggan di repo/prompt.

### 7. Deploy untuk chat biasa — cPanel milik saya, tanpa browser cloud

Setelah implementasi selesai, checks/review yang berlaku lulus, squash-merge dan sertifikasi source yang benar selesai, buat **paket ZIP deploy siap pakai**, checksumSHA256, daftar isi, panduan singkat Indonesia, recovery yang kompatibel dan commandcron PHP persis untuk paket tersebut. Build asset/vendor di CI; hosting tidak memerlukan SSH, Node, Composer, Python atau symlink. Hosting terakhir PHP8.3.35 curl/zip/phar/zlib; /usr/bin/python3 tidak tersedia.

Target hanya `https://oneqaydev.n07.my.id/`:

- Private `/home/pekd7254/oneqay-production-test`.
- Public `/home/pekd7254/public_html/oneqaydev.n07.my.id`.
- Pertahankan fixed public bridge, shared runtime/.env privat, sesi, accounts, SMTP, database dan staging `oneqay.n07.my.id`.

Saya membuka cPanel di browser pribadi dan mengoperasikan File Manager/Cron Jobs. Berikan langkah tahap demi tahap yang singkat beserta nama/path file hasil yang harus saya kirim. Contoh bentuk command kompatibel, **bukan command siap jalan untuk rilis baru**:

`/usr/local/bin/php /home/pekd7254/oneqay-production-test/NAMA_PAKET_BARU/update.php update ID_OTORISASI_BARU > /home/pekd7254/oneqay-production-test/NAMA_PAKET_BARU/cron-output.log 2>&1`

Ganti semua placeholder dengan nilai nyata sebelum menyuruh saya menjalankan. Jangan gunakan operator7ef lama untuk source baru: ia terikat predecessorc2e. Siapkan successor yang memverifikasi predecessor7ef, source/artifact baru dan receipt aktual. UI-only semestinya tanpa migrasi; jangan blanket migrate. Pertahankan backup dan recovery: stock schema sudah ada, maka rollback ke code lama yang tidak memahami stock_deducted dilarang; gunakan versi kompatibel/forwardfix.

Jangan memberi link/token yang sudah akan expired selama pengembangan. Sediakan paket durable yang aman; siapkan otorisasi tepat source/hash lalu rekam grant baru ketika paket sudah diupload dan saya siap menjalankan. Bila expired, verifikasi paket/checkpoint lalu refresh grant sesuai kebijakan; jangan menghapus expiry/security. Jangan menyimpan token permanen di cron. Jika File Manager gagal0%, jangan langsung menyalahkan inode; gunakan bukti dan jalur transfer PHP yang terverifikasi bila diperlukan tanpa menurunkan TLS/source integrity.

Setelah receipt benar-benar menunjukkan sukses: hapus cron updater sementara, ganti hanya workeroneQay, verifikasi hasil worker/HTTPS/versi, dan arsipkan expanded release lama/duplicatepayload menjadi ZIP privat terverifikasi setelah grace period. Simpan aktif/backup/shared/database/journal; jangan hapus paksa. Record source, timestampWIB, hashes, receipt, jumlah tabel/receipt, worker, retention dan pekerjaan belum teruji di repo. UploadZIP/queuedcron saja belum berarti deploy berhasil. Web hosting, installerWindows dan signed patch updater harus dilaporkan terpisah.

Mulai sekarang dengan ringkasan checkpoint maksimal10poin, lalu implementasi yang terarah. Tampilkan progres singkat yang menjelaskan hasil, bukan meminta konfirmasi rutin. Tujuannya UI/UX modern konsisten dengan palet, proses tidak stuck, fungsi lama tetap benar, repositori tercatat dan paket deploy chat biasa siap dipakai.
