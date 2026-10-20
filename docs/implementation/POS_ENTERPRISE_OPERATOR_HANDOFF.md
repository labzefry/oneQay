# Panduan oneQay POS Windows 0.2

Pembaruan ini memerlukan backend enterprise POS dan installer Windows 0.2 yang sesuai. Jangan memasang paket dari checkpoint pengembangan ke hosting. Paket final harus menyebutkan commit, checksum, hasil sertifikasi sumber, serta perintah cron yang sudah terikat ke rilis tersebut.

## Urutan pemasangan

1. Pada aplikasi 0.1, selesaikan shift dan sinkronkan seluruh transaksi. Database lama dipertahankan, tetapi isinya tidak otomatis dipindahkan ke profil kasir baru.
2. Pasang pembaruan hosting menggunakan ZIP dan cron yang disertakan pada paket final. Hasil `update-result.json` harus menyatakan `BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING` dan `business_traffic: ON_PRESERVED`.
3. Ganti cron worker ke direktori rilis baru setelah hasil pemasangan berhasil. Jalankan setiap menit dan periksa `worker-result.json`. Worker lama tidak boleh dihapus sebelum penggantinya berhasil.
4. Rilis lama dan salinan payload dikompresi menjadi ZIP privat setelah masa tunggu 120 detik. Salinan aktif, sesi, konfigurasi, serta arsip pemulihan tetap dipertahankan. Jika kompresi/verifikasi gagal, folder sumber tetap disimpan dan laporan menyebutkan penyebabnya.
5. Pasang installer 0.2 tanpa menghapus aplikasi atau folder datanya. Server sudah tertanam: `https://oneqaydev.n07.my.id`.

## Pengaturan pemilik bisnis

Buka `/pos/business/desktop` setelah login melalui web bisnis.

| Menu | Pengaturan |
| --- | --- |
| Akun kasir | Email login unik, nama petugas, kata sandi awal, PIN enam angka, status aktif. Setiap petugas memakai akunnya sendiri. |
| Struk pelanggan | Logo PNG/JPG/WebP maksimal 2 MB, pilihan tampilkan/sembunyikan, dan footer maksimal 240 karakter. Logo otomatis diperkecil untuk printer. |
| Shift & keamanan | Auto-lock, termasuk pilihan mati; penerima laporan dipisahkan koma, titik koma, atau baris baru; pengesahan penutupan shift. |
| Dapur & routing | Tambahkan beberapa dapur dan kategori produk yang dikirim ke masing-masing dapur. Nama kategori harus sesuai katalog. |

Mata uang mengikuti pengaturan bisnis. SMTP hanya diatur pada control plane pemilik aplikasi. Alamat penerima laporan tidak berarti SMTP sudah siap: periksa konfigurasi, antrean, kegagalan, dan hasil pengiriman pada control plane.

## Pemakaian kasir

Login pertama memerlukan internet dan katalog siap jual. Isi kas awal sebelum transaksi. Setelah itu kasir dapat melakukan penjualan, mencantumkan nomor meja/order, mencetak bill pelanggan dan tiket dapur, serta melihat ringkasan transaksi.

Pilih **Kunci** untuk mengunci layar; buka kembali dengan PIN yang dikelola pemilik bisnis. Auto-lock dapat dimatikan di web. Mengganti kasir memerlukan penutupan shift lokal terlebih dahulu.

Saat internet terputus, transaksi disimpan di SQLite pada komputer yang sama. Akun yang sudah pernah login dapat dipakai kembali dengan kredensial tersimpan selama izin offline delapan jam masih berlaku. Jangan menghapus folder data atau memindahkannya ke pengguna Windows lain; proteksi DPAPI terikat pada pengguna Windows tersebut. Penolakan akun oleh server tidak diganti dengan login offline.

Saat tutup kas, masukkan kas yang benar-benar dihitung dan catatan selisih. Laporan dapat dicetak tanpa logo. Laporan awal berstatus menunggu pengesahan. Pemilik/petugas berwenang yang berbeda dari pembuka shift mengesahkan penutupan melalui web; selisih kas mengikuti alur rekonsiliasi independen. Sinkronisasi dilakukan berurutan agar penjualan tidak terduplikasi. Email laporan awal dan final masuk ke antrean platform dengan kunci deduplikasi terpisah.

## Printer dan perangkat

- USB/Bluetooth: pasangkan dan instal printer pada Windows terlebih dahulu, kemudian pilih antrean printer Windows di POS.
- LAN/WLAN: gunakan alamat dan port printer ESC/POS yang sesuai pada jaringan lokal.
- Pilih kertas 58 atau 80 mm. Hubungkan cash drawer yang kompatibel ke printer struk; laci hanya dipicu pada cetak transaksi asli.
- Hubungkan setiap dapur ke printer yang sesuai. Tiket dapur ringkas, tanpa harga atau logo. Bill pelanggan memuat petugas, waktu, nomor struk, serta footer yang disimpan di web.
- Pemindai barcode beroperasi sebagai input keyboard Windows. Akun dengan izin opname dapat menggunakan fitur hitung stok; temuan konflik diperiksa melalui web.
- Jika aplikasi berhenti ketika mencetak, status keluaran menjadi tidak pasti. Periksa kertas fisik sebelum memilih cetak salinan agar pesanan tidak dikerjakan dua kali.

## Patch aplikasi

Aplikasi memeriksa versi yang tersedia. Instalasi dilakukan setelah shift ditutup dan antrean transaksi diselesaikan. Installer harus memiliki tanda tangan yang cocok dengan kunci publik tertanam, versi lebih baru, dan alamat rilis yang sesuai. Semua profil SQLite dicadangkan sebelum pemasangan; kegagalan unduh atau verifikasi menghentikan pemasangan.

Pemilik repositori perlu memasang `TAURI_SIGNING_PRIVATE_KEY` pada Actions secrets menggunakan kunci privat yang telah diberikan secara terpisah. Jangan memasukkan kunci itu ke repositori, cPanel, atau `public_html`. Kanal patch belum tersedia sebelum workflow **Signed Windows POS release** berhasil menerbitkan rilis bertanda tangan. Sertifikat Authenticode Windows belum disediakan.

Pengujian otomatis membuktikan perilaku kode dalam lingkungan uji. Windows 10 fisik, printer, laci, pemindai, transaksi uang nyata, dan email yang benar-benar diterima tetap perlu dibuktikan pada perangkat operasional.
