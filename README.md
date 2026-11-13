# Bengkel Masman

Aplikasi manajemen usaha berbasis web untuk CV Masman Sejahtera. Aplikasi ini dikembangkan menggunakan Laravel untuk membantu pengelolaan data pelanggan, barang dan jasa, transaksi, inventori, serta pencatatan aktivitas aplikasi.

## Fitur Utama

- **Manajemen Pelanggan:** pengelolaan data pelanggan.
- **Manajemen Barang dan Jasa:** pengelolaan informasi barang dan jasa.
- **Manajemen Transaksi:** pencatatan dan pengelolaan transaksi usaha.
- **Manajemen Inventori:** pengelolaan stok barang.
- **Audit Trail:** pencatatan aktivitas dan perubahan data tertentu dalam aplikasi.

## Teknologi

- PHP 8.3
- Laravel 13
- MySQL 8
- Laravel Breeze
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- Chart.js

## Persyaratan Sistem

Pastikan perangkat sudah memiliki:

- PHP 8.3 atau versi yang sesuai dengan kebutuhan proyek.
- Composer.
- Node.js dan NPM.
- MySQL 8.
- Web server lokal, misalnya Laragon.

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/albayyyz/bengkel-masman.git
cd bengkel-masman
```

### 2. Instal Dependensi PHP

```bash
composer install
```

### 3. Instal Dependensi Frontend

```bash
npm install
```

### 4. Konfigurasi Environment

Salin file konfigurasi:

```bash
cp .env.example .env
```

Pada Windows, file `.env.example` juga dapat disalin secara manual menjadi `.env`.

Sesuaikan konfigurasi database pada `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bengkel_masman
DB_USERNAME=root
DB_PASSWORD=
```

Buat database kosong dengan nama `bengkel_masman` melalui MySQL sebelum menjalankan migrasi.

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Jalankan Migrasi Database

```bash
php artisan migrate
```

Pastikan perintah migrasi dijalankan pada database lokal yang memang disiapkan untuk instalasi baru.

### 7. Build Frontend

```bash
npm run build
```

### 8. Jalankan Aplikasi

```bash
php artisan serve
```

Buka alamat berikut melalui browser:

```text
http://127.0.0.1:8000
```

## Pengujian

Pengujian otomatis dapat dijalankan menggunakan:

```bash
php artisan test
```

Pastikan konfigurasi database pengujian menggunakan database terpisah agar tidak mengganggu database aplikasi.

## Struktur Modul

Aplikasi mencakup pengelolaan pelanggan, barang dan jasa, transaksi, inventori, serta pencatatan aktivitas. Implementasi dan detail setiap modul dapat ditinjau langsung melalui source code pada repository.

## Tujuan Pengembangan

Proyek ini dikembangkan sebagai studi kasus pengelolaan usaha pada CV Masman Sejahtera, dengan pendekatan pengembangan Rapid Application Development (RAD).

---

**Repository:** https://github.com/albayyyz/bengkel-masman