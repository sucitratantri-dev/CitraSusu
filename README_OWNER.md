# CITRA SUSU — Paket Owner

Paket ini melanjutkan project Laravel yang sudah ada. Fokus perubahan adalah dashboard dan menu pengawasan Owner (role database `admin`).

## Menu Owner
- Dashboard Owner
- Laporan penjualan
- Laporan produk
- Monitoring stok dan kedaluwarsa per batch
- Laporan penerimaan barang dan supplier
- Persetujuan retur
- Monitoring pesanan, pembayaran, dan pengiriman
- Kinerja karyawan
- Notifikasi dan profil

Owner tidak mendapatkan akses ke halaman operasional produk, supplier, penerimaan barang, pemrosesan pesanan, maupun verifikasi pembayaran. Menu chat dan pengaturan sistem tidak ditampilkan pada sidebar Owner.

## Database
File `citra_susu.sql` berada di folder terpisah di samping ZIP. Import ke database baru bernama `citra_susu` melalui phpMyAdmin. SQL ini membuat ulang tabel (memiliki DROP TABLE), jadi jangan impor ke database berisi data penting tanpa backup.

Akun demo:
- Owner: admin@citrasusu.test
- Karyawan: karyawan@citrasusu.test
- Kurir: kurir@citrasusu.test
- Pembeli: pembeli@citrasusu.test
- Password demo: `password`

## Menjalankan
1. Konfigurasikan `.env` ke MySQL `citra_susu`.
2. Jalankan `composer install`.
3. Jalankan `php artisan key:generate`.
4. Jalankan `php artisan optimize:clear`.
5. Jalankan `php artisan serve`.

Jangan menjalankan `php artisan migrate:fresh` setelah import SQL.

## Catatan versi Owner
- Role `admin` diperlakukan sebagai Owner pada tampilan dan hak akses.
- Owner diarahkan ke dashboard pengawasan.
- Menu operasional pengelolaan produk, stok, penerimaan, supplier, dan pemrosesan pesanan dibatasi untuk role `employee`.
- Owner tetap dapat memeriksa pengajuan retur dan memberi keputusan.
- Chat tidak ditampilkan dan endpoint chat dibatasi untuk buyer/employee.
- Skema database tidak berubah pada paket Owner ini; SQL disediakan untuk instalasi database awal.
