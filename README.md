# CITRA SUSU — Sistem Informasi UMKM

Aplikasi Laravel 12 untuk katalog dan pemesanan produk, pengelolaan stok berbasis batch, monitoring kedaluwarsa, penerimaan barang, retur, pengantaran internal, notifikasi, laporan, dan chat pembeli–toko.

## Teknologi
- PHP 8.2+
- Laravel 12
- MySQL 8+ / MariaDB 10.5+
- Blade, CSS responsif, JavaScript polling untuk chat

## Instalasi Windows (XAMPP)
1. Ekstrak folder `CitraSusu` ke `C:\xampp\htdocs\CitraSusu`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka `http://localhost/phpmyadmin`.
4. Buat database bernama `citra_susu` (collation `utf8mb4_unicode_ci`).
5. Pilih database tersebut → **Import** → pilih `database/citra_susu.sql` → Go. SQL sudah memuat struktur tabel, relasi, akun demo, kategori awal, dan pengaturan toko. Migration history juga sudah dicatat, jadi jangan menjalankan `migrate:fresh` setelah impor.
6. Salin `.env.example` menjadi `.env`, lalu pastikan konfigurasi database:

```env
APP_NAME="Citra Susu"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost/CitraSusu/public
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=citra_susu
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

7. Buka terminal/CMD di folder project, jalankan:

```bash
composer install
php artisan key:generate
php artisan storage:link
php artisan optimize:clear
```

8. Buka `http://localhost/CitraSusu/public`.

> Alternatif: jika ingin membangun database dari migration, buat database kosong lalu jalankan `php artisan migrate --seed`. Jangan impor SQL sekaligus menjalankan migration pada database yang sama.

## Akun demo
Semua akun menggunakan password `password`.

| Role | Email |
|---|---|
| Admin | admin@citrasusu.test |
| Karyawan | karyawan@citrasusu.test |
| Kurir | kurir@citrasusu.test |
| Pembeli | pembeli@citrasusu.test |

Ganti password demo sebelum dipakai pada lingkungan publik.

## Chat box
- Pembeli membuka chat melalui tombol bulat customer service di kanan bawah atau menu Chat.
- Admin/karyawan membuka menu **Chat Pembeli**, memilih percakapan, lalu membalas.
- Pesan disimpan pada tabel `messages`, percakapan pada `conversations`.
- Halaman chat memperbarui pesan baru dengan polling AJAX setiap 3 detik; riwayat tetap tersimpan setelah halaman ditutup.
- Akses percakapan dibatasi ke pemilik percakapan dan staf toko. Karyawan memerlukan flag `chat_enabled=1`.

## Database dan migrasi
- SQL siap impor: `database/citra_susu.sql`.
- Struktur sumber Laravel: `database/migrations/`.
- Data demo melalui seeder: `database/seeders/DatabaseSeeder.php`.

## Catatan ruang lingkup dan verifikasi
Project ini merupakan kelanjutan dari source ZIP yang diberikan. Modul dan route yang sudah tersedia dipertahankan serta paket ini menyertakan SQL untuk database MySQL. Pengujian otomatis `php artisan test` pada lingkungan build ini belum dapat dijalankan karena ekstensi PHP `dom`, `mbstring`, dan `xmlwriter` tidak tersedia. Setelah instalasi di XAMPP, aktifkan ekstensi tersebut bila ingin menjalankan PHPUnit. Jalankan `php artisan route:list` untuk memeriksa route aplikasi.

Referensi UI/UX 42 halaman yang diberikan disertakan di `docs/CITRA-SUSU-UIUX-REFERENCE.pdf` sebagai acuan tampilan.


## Import database via XAMPP / phpMyAdmin (MySQL or MariaDB)

1. Start Apache and MySQL in XAMPP.
2. Open `http://localhost/phpmyadmin`.
3. Create database `citra_susu` (collation `utf8mb4_unicode_ci`).
4. Select the database, choose **Import**, then upload `database/sql/citra_susu.sql`.
5. The SQL quotes `key` and `value` in Laravel cache tables for MariaDB compatibility.

The standalone SQL file is also supplied beside this ZIP as `citra_susu_FIXED.sql`.
