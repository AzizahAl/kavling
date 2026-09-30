# TR-DMS — Tectona Residen

Sistem manajemen proyek kavling (versi web dari Excel *TR-DMS Master 2026*). Laravel 12, PHP 8.2, MySQL/MariaDB (XAMPP), Blade + Tailwind + Alpine.js + Chart.js (semua aset lokal, tidak butuh internet).

## Menjalankan di XAMPP

```bash
composer install
cp .env.example .env        # atur DB_DATABASE=tectona_residence, APP_LOCALE=id
php artisan key:generate
php artisan migrate --seed  # 14 kavling, 5 tahap harga, pengaturan, RAB, kas awal, 1 admin
```

Buka `http://localhost/tectona/public` lalu masuk dengan akun admin dari `database/seeders/AdminSeeder.php`
(bisa diatur lewat `.env`: `ADMIN_EMAIL`, `ADMIN_PASSWORD`). Segera ganti kata sandi di menu **Profil**.

Aset tampilan sudah ter-build di `public/build`. Setelah mengubah file di `resources/` jalankan `npm install` (sekali) lalu `npm run build`.

## Struktur penting

| Bagian | Lokasi |
|---|---|
| Aturan bisnis (harga, transaksi, angsuran, alokasi, komisi, lead, dokumen) | `app/Services/` |
| Angka yang bisa diubah tanpa kode | menu **Pengaturan Proyek** (`app/Services/Pengaturan.php`) |
| Komponen tampilan (kartu, tabel, badge, modal, input Rupiah) | `resources/views/components/` |
| Format Rupiah & tanggal Indonesia | `app/Support/helpers.php` |
| Hak akses (admin / agen) | `routes/web.php`, `app/Http/Middleware/Peran.php` |

## Tes

Tes memakai database MySQL terpisah `tectona_test` (buat sekali di phpMyAdmin), lalu:

```bash
php artisan test
```
