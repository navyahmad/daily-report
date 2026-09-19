# Daily Report — Sistem Pelaporan Pekerjaan Harian Kantor

Aplikasi web internal kantor berbasis **Laravel 11** yang dirancang untuk mempermudah karyawan dalam mengisi laporan pekerjaan harian secara terstruktur tanpa proses login, serta menyediakan dashboard monitoring dan pengelolaan laporan terpusat bagi tim **Admin & HRD**.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
  - [1. Portal Karyawan (Tanpa Login)](#1-portal-karyawan-tanpa-login)
  - [2. Dashboard Monitoring Admin & HRD](#2-dashboard-monitoring-admin--hrd)
  - [3. Manajemen & Moderasi Laporan](#3-manajemen--moderasi-laporan)
  - [4. Manajemen Master Karyawan](#4-manajemen-master-karyawan)
  - [5. Sistem Autentikasi & Hak Akses (Role-Based)](#5-sistem-autentikasi--hak-akses-role-based)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Struktur Database Utama](#struktur-database-utama)
- [Panduan Instalasi & Setup](#panduan-instalasi--setup)
- [Akun Default (Development)](#akun-default-development)
- [Menjalankan Pengujian (Testing)](#menjalankan-pengujian-testing)
- [Format & Standar Kode](#format--standar-kode)
- [Keamanan & Praktik Terbaik](#keamanan--praktik-terbaik)
- [Lisensi](#lisensi)

---

## Fitur Utama

### 1. Portal Karyawan (Tanpa Login)
- **Akses Langsung**: Karyawan mengakses formulir laporan publik (`/`) tanpa perlu proses login.
- **Pemilihan Tanggal Fleksibel**: Tanggal pelaporan dapat disesuaikan dengan kebutuhan operasional pekerjaan.
- **Dropdown Reaktif**: Pilihan divisi memuat daftar nama karyawan aktif secara dinamis via asynchronous request.
- **Formulir Spesifik Divisi (Dynamic Forms)**:
  - **Teknisi**: Multi-select jenis pekerjaan (*Instalasi, Maintenance, Troubleshooting, Survey, Remote Support, Yang lain*), textarea rincian per pekerjaan, status pengerjaan independen (*Selesai / Progres / Pending*), kendala, dan rencana besok.
  - **Admin Sales**: Pilihan pekerjaan hari ini & rencana besok (*Follow Up, Membuat Penawaran, Meeting, Yang lain*) dengan detail wajib per aktivitas yang dipilih, metrik numerik (*customer dihubungi, quotation dibuat, closing*), dan kendala.
  - **Admin Project**: Pemrosesan dokumen (*SOW, BAST, Report, Yang lain*) dengan textarea rincian per dokumen, input jumlah project yang memunculkan daftar baris proyek dan progres persentase secara dinamis, kendala, dan rencana besok.
  - **Admin Procurement**: Pilihan kategori pekerjaan (*Cari Barang, Cari Teknisi, PO*) dengan textarea rincian kondisional, input jumlah PO dan rincian vendor, catatan barang diterima/dikirim, kendala, dan rencana besok.
  - **System Informasi**: Deskripsi pekerjaan hari ini, status pengerjaan, kendala teknis, dan rencana besok.
  - **Finance**: Deskripsi aktivitas harian, daftar dinamis invoice yang dibuat (*count & detail items*), pencatatan jurnal harian, rekap kas/bank, kendala, dan rencana besok.
- **Integritas Data & Snapshot**:
  - Validasi ketat `UNIQUE(employee_id, report_date)` guna mencegah pengiriman ganda pada tanggal yang sama.
  - Penyimpanan `form_version: 2` dengan snapshot nama karyawan, nama divisi, dan kode divisi agar riwayat historis tetap konsisten meskipun master data diperbarui di kemudian hari.

### 2. Dashboard Monitoring Admin & HRD
- **Filter Tanggal & Divisi**: Mengubah tanggal atau unit kerja secara langsung memutakhirkan seluruh data laporan yang ditampilkan.
- **Kartu Metrik**: Total Karyawan Aktif, Laporan Masuk pada Tanggal Terpilih, Total Arsip Laporan (All Time), dan Total Unit Divisi.
- **Rekap Laporan Per Divisi**: Menampilkan jumlah laporan masuk pada tanggal terpilih, total staf aktif, dan total arsip per unit kerja, dilengkapi tautan filter langsung.
- **Daftar Laporan Terbaru**: Menampilkan 10 pengiriman laporan terbaru dengan status aktif/dibatalkan serta akses cepat ke detail laporan.

### 3. Manajemen & Moderasi Laporan
- **Filter Komprehensif**: Pencarian berdasarkan rentang tanggal (`start_date` – `end_date`), divisi, status laporan (*Aktif/Dibatalkan*), dan pencarian nama.
- **Tampilan Detail Laporan**: Penyajian terstruktur untuk seluruh data spesifik tiap divisi tanpa menampilkan format JSON mentah.
- **Koreksi & Edit**: Admin/HRD dapat membantu mengoreksi konten laporan jika terdapat kesalahan input dari karyawan.
- **Pembatalan & Pemulihan**: Status laporan dapat diubah menjadi `cancelled` atau `active` (soft-status) untuk menjaga integritas data.
- **Ekspor CSV / Excel**: Ekspor data laporan berformat CSV dengan *UTF-8 Byte Order Mark (BOM)* untuk kompatibilitas penuh dengan Microsoft Excel.

### 4. Manajemen Master Karyawan
- **Master Karyawan**: Penambahan, pengubahan data, dan penugasan divisi.
- **Toggle Status Aktif**: Menonaktifkan karyawan yang sudah tidak aktif tanpa menghapus riwayat pelaporan terdahulu. Karyawan non-aktif secara otomatis disembunyikan dari pilihan formulir publik.

### 5. Sistem Autentikasi & Hak Akses (Role-Based)
- **Custom Authentication Guard**: Menggunakan guard `admin_hrd` berbasis tabel internal `admin_hrd_users`.
- **Tanpa Registrasi Publik**: Hanya akun yang didaftarkan oleh Administrator yang dapat mengakses panel dashboard.
- **Login Khusus Kantor**: Form login di `/admin/login` dengan label *"Email Kantor"* & *"Password"*, opsi remember-me, dan penanganan autentikasi aman.
- **2 Tingkatan Peran (Role)**:
  - **Administrator (`admin`)**: Hak akses menyeluruh ke dashboard, laporan, master karyawan, ekspor data, serta **Kelola Akun Staf Admin/HRD**.
  - **HRD Team (`hrd`)**: Hak akses operasional ke dashboard, moderasi laporan, master karyawan, dan ekspor data (dibatasi dari menu kelola akun).
- **Keamanan Akun**:
  - Middleware aktivasi akun: Akun non-aktif ditolak saat login, dan sesi aktif langsung diputus jika akun dinonaktifkan di tengah sesi.
  - Perlindungan *self-deactivation*: Pengguna tidak dapat menonaktifkan akun miliknya sendiri.

---

## Teknologi yang Digunakan

- **Backend**: [PHP 8.3+](https://www.php.net/) & [Laravel 11](https://laravel.com/)
- **Frontend**: [Blade Templates](https://laravel.com/docs/blade), [Tailwind CSS 3](https://tailwindcss.com/), dan [Alpine.js](https://alpinejs.dev/)
- **Database**: SQLite (Development) / MySQL / PostgreSQL (Production)
- **Asset Bundler**: [Vite](https://vitejs.dev/)
- **Testing**: [PHPUnit](https://phpunit.de/)
- **Code Style**: [Laravel Pint](https://laravel.com/docs/pint)

---

## Struktur Database Utama

```
├── divisions                # Master data divisi kantor
├── employees                # Master data karyawan (relasi ke divisions)
├── daily_reports            # Header laporan, snapshot histori, dan form_data (JSON)
└── admin_hrd_users          # Akun pengguna terotentikasi (role: admin / hrd)
```

---

## Panduan Instalasi & Setup

### Prasyarat
- PHP >= 8.3 (dengan ekstensi `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `bcmath`)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) & NPM

### Langkah-langkah

1. **Clone Repository**:
   ```bash
   git clone <URL_REPOSITORY_ANDA> daily-report
   cd daily-report
   ```

2. **Install Dependensi PHP**:
   ```bash
   composer install
   ```

3. **Install Dependensi Frontend**:
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**:
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   *Sesuaikan konfigurasi database dan variabel aplikasi di dalam file `.env`.*

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi & Seeder Database**:
   ```bash
   php artisan migrate --seed
   ```

7. **Build Aset Frontend**:
   ```bash
   npm run build
   ```
   *(Atau gunakan `npm run dev` untuk mode pengembangan).*

8. **Jalankan Web Server**:
   ```bash
   php artisan serve
   ```
   Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## Akun Default (Development)

> **Catatan**: Akun di bawah ini dihasilkan dari database seeder untuk keperluan pengujian lokal. Harap ubah password atau sesuaikan akun sebelum penerapan ke lingkungan produksi.

| Role | Email Kantor | Password Default | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@kantor.com` | `password` | Akses Penuh + Kelola Akun Admin/HRD |
| **HRD Team** | `hrd@kantor.com` | `password` | Monitoring, Laporan, Karyawan, Ekspor |

---

## Menjalankan Pengujian (Testing)

Proyek ini dilengkapi dengan rangkaian automated test suite menggunakan PHPUnit:

```bash
php artisan test
```

Cakupan pengujian mencakup:
- Pengiriman formulir publik & validasi input dinamis per divisi.
- Proteksi pencegahan laporan ganda per karyawan per tanggal.
- Integritas snapshot data pada laporan.
- Autentikasi guard `admin_hrd` & hak akses berbasis role (`admin` vs `hrd`).
- Ringkasan metrik dashboard laporan & rekap per divisi.
- Pembatalan, pemulihan, pengeditan, dan ekspor laporan ke format CSV.

---

## Format & Standar Kode

Pemeriksaan dan formatting standar penulisan kode PHP:

```bash
vendor/bin/pint
```

---

## Keamanan & Praktik Terbaik

- **Tanpa Registrasi Terbuka**: Sistem autentikasi hanya dapat diakses melalui penambahan manual oleh Administrator.
- **Enkripsi Password**: Seluruh password disimpan menggunakan hashing aman bawaan Laravel (*Bcrypt / Argon2id*).
- **Proteksi CSRF**: Seluruh formulir web terlindungi dari serangan *Cross-Site Request Forgery*.
- **Rate Limiting**: Endpoint login dan pengiriman laporan dibatasi untuk mencegah percobaan brute-force atau spam.
- **Isolasi Sesi**: Akun yang dinonaktifkan akan langsung kehilangan hak akses pada request berikutnya.

---

## Lisensi

Aplikasi ini bersifat internal dan berlisensi privat untuk kebutuhan operasional kantor.


