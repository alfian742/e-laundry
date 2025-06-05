# Posyandu Project

Selamat datang di **Posyandu Project**! 🎉 Proyek ini dibangun menggunakan Laravel 12 dan template dashboard **Bootstrap Stisla**.

## 🖥️ Screenshot

1. Login Page
![Login Page](public/img/screenshot/Screenshot_001.png)

2. Dashboard
![Dashboard](public/img/screenshot/Screenshot_002.png)

## ✨ Fitur

1. **Login Multi-User**: Admin, Bidan, Tenaga Medis Puskesmas, Orang Tua (Ibu), Kepala Desa/Lurah, dan Kepala Dusun/Lingkungan.
2. **Kelola Data Orang Tua**.
3. **Kelola Data Anak**.
4. **Kelola Data Lansia**.
5. **Kelola Data Petugas**: Akses hanya untuk Admin.
6. **Kelola Jadwal**.
7. **Kelola Imunisasi**.
8. **Kelola Penimbangan**.
9. **Kelola Pemeriksaan Ibu Hamil**.
10. **Kelola Pemeriksaan Lansia**.
11. **Kelola Persedian Vaksin**.
12. **Kelola Persediaan Obat**.
13. **Kelola Identitas Situs**: Akses hanya untuk Admin.

## 🚀 Teknologi yang Digunakan

- ![Laravel 12](https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel) **Laravel 12**: Framework PHP untuk membangun aplikasi web.
- **Boostrap Stisla**: Template dashboard responsif dan komponen UI base on **Boostrap 4**.

## 📦 Instalasi

### 📝 Prasyarat

Pastikan Anda telah menginstal:

- PHP >= 8.2
- Composer
- MySQL / MariaDB

Ikuti langkah-langkah berikut untuk menjalankan proyek Laravel **Posyandu** di servel lokal.

### 1. Clone Repository
Clone repositori dari GitHub ke direktori lokal:
```bash
git clone https://github.com/alfian742/posyandu.git
```

### 2. Masuk ke Direktori Proyek
Pindah ke folder proyek:
```bash
cd posyandu
```

### 3. Install Dependensi
Pastikan Anda sudah menginstall Composer. Lalu jalankan:
```bash
composer update
```

### 4. Konfigurasi File ENV
Laravel menggunakan file `.env` untuk konfigurasi lingkungan.

1. Duplikat file `.env.example` dan ubah namanya menjadi `.env`:
    ```bash
    cp .env.example .env
    ```

2. Buka file `.env` dan sesuaikan konfigurasi berikut:
    - `DB_DATABASE`: Nama database yang akan digunakan
    - `DB_USERNAME`: Username database
    - `DB_PASSWORD`: Password database
    - `APP_TIMEZONE`: Zona waktu aplikasi, sesuaikan dengan wilayah Anda (misalnya `Asia/Makassar`)

    Contoh:
    ```
    DB_DATABASE=posyandu
    DB_USERNAME=root
    DB_PASSWORD=
    APP_TIMEZONE=Asia/Makassar
    ```

3. Generate application key:
    ```bash
    php artisan key:generate
    ```

### 5. Jalankan Migrasi dan Seeder
**Migrasi** digunakan untuk membuat struktur tabel database:
```bash
php artisan migrate
```

**Seeder** digunakan untuk mengisi data awal:
```bash
php artisan db:seed
```

### 6. Jalankan Aplikasi
Gunakan perintah berikut untuk menjalankan server development:
```bash
php artisan serve
```

Aplikasi akan berjalan di:
```
http://localhost:8000
```

---

## 📖 Panduan Penggunaan

1. **Login**:
   - Gunakan kredensial berikut untuk login ke aplikasi:
   - Admin
     - **Username**: `admin`
     - **Password**: `123`

2. **Login Selain Admin**:
   - Anda dapat login dengan menggunakan **Username**: `nik yang terdaftar pada aplikasi` dan **Password**: `123` untuk login dengan role Bidan, Petugas, dan Orang Tua (Ibu).

3. **Kelola Semua Menu**:
   - Menu dapat dikelola oleh semua user dengan batasan akses tertentu.

4. **Demo**:
   - Demo aplikasi dapat diakses melalui link berikut: [Demo Aplikasi Posyandu](http://my-public-project.infinityfreeapp.com/posyandu/public/)

---

Terima kasih telah mengunjungi repositori ini! Jika ada pertanyaan atau saran, jangan ragu untuk menghubungi saya.
