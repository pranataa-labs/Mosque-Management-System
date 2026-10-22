# 🕌 Sistem Informasi Masjid (SIMasjid)

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

Sistem Informasi Manajemen Masjid (SIMasjid) adalah platform web komprehensif yang dibangun menggunakan **Laravel 12** dan **Tailwind CSS**. Sistem ini dirancang secara khusus untuk memudahkan pengurus masjid (DKM) dalam mengelola administrasi, mempublikasikan kegiatan majelis ilmu, memanajemen laporan arus kas (keuangan) secara transparan kepada jamaah, serta menyajikan antarmuka (UI) yang modern, bersih, dan sangat responsif di semua perangkat.

---

## 📸 Preview Tampilan Aplikasi (HD)

<p align="center">
  <img src="screenshots/landing-preview.png" alt="Beranda Utama Masjid Nurul Iman" width="920">
</p>

---

## ✨ Fitur Utama (Core Features)

### 🧑‍💻 Halaman Publik (Frontend / Landing Page)
- **Beranda Interaktif:** Desain antarmuka modern yang menyajikan profil, visi misi, dan fasilitas masjid.
- **Jadwal Sholat:** Menampilkan waktu sholat harian yang secara dinamis dikonfigurasi melalui sistem terpusat.
- **Transparansi Keuangan:** Menampilkan indikator saldo akhir masjid langsung di halaman depan. Laporan detail pemasukan dan pengeluaran juga dapat diakses oleh publik secara *real-time*.
- **Portal Kajian & Berita:** Modul berita untuk mempublikasikan agenda kajian, jadwal majelis taklim, dan informasi penting lainnya.
- **Program Pembangunan:** Etalase interaktif untuk memonitor perkembangan proyek fisik masjid (status Berjalan / Selesai).
- **Responsif & Animasi Halus:** Tampilan dirancang *mobile-first* (beradaptasi sempurna di layarp HP, Tablet, dan Desktop) dengan animasi navigasi dan *hover* yang lembut.
- **Struktur Halaman Terpadu:** Penggabungan modul layanan, profil pengurus, donasi, dan kontak ke dalam tata letak yang minim klik dan sangat ramah pengguna (UX).

### 🔒 Panel Admin (CMS Backend)
- **Dashboard & Analitik Visual:** Menyajikan Ringkasan Arus Kas beserta visualisasi grafik (Chart.js) yang memetakan tren pengeluaran dan pemasukan secara Harian, Mingguan, dan Tahunan.
- **Manajemen Konten Fleksibel:** Sistem Create, Read, Update, Delete (CRUD) yang mudah digunakan untuk mengelola Kegiatan, Program, dan Keuangan.
- **Pagination Cerdas & UX Terbaik:** Penerapan paginasi presisi di halaman admin yang mempertahankan posisi *scroll* pengguna (tidak melompat ke atas layar) saat berpindah antar halaman tabel yang memiliki banyak data.
- **Notifikasi Anti-Intrusif:** Penggunaan integrasi **SweetAlert2** untuk semua interaksi (peringatan hapus data dan notifikasi sukses) guna menghindari blokir *alert* paksa dari sistem bawaan peramban (browser).

---

## 🛡️ Keamanan & Integritas Data (Security & Reliability)

Aplikasi SIMasjid dibangun dengan standar praktik terbaik arsitektur Laravel untuk memastikan keamanan sistem dari ancaman *cyber* dan mencegah kebocoran data.

1. **Log Aktivitas (Audit Trail)**
   Sistem secara otomatis dan diam-diam (di belakang layar) mencatat seluruh riwayat tindakan sensitif di dalam *dashboard* admin. Mulai dari sesi login yang sukses/gagal, perubahan data, hingga penghapusan informasi. Log ini merekam *Alamat IP*, *User Agent*, dan *Timestamp* guna menjamin akuntabilitas serta kemudahan investigasi (*tracing*) apabila terjadi anomali.
2. **Pencegahan Kehilangan Data (Soft Deletes)**
   Menerapkan paradigma penghapusan lunak (*Soft Deletes*). Saat admin menghapus data krusial seperti Laporan Keuangan, Program, atau Artikel, data tersebut **tidak akan terhapus secara permanen dari *database***. Data disembunyikan dari UI, tetapi sewaktu-waktu masih dapat dipulihkan (*restore*) oleh Database Administrator guna mencegah bencana *human-error*.
3. **Keamanan Eksekusi (CSRF & SQL Injection Protection)**
   - Semua *form submission* dilindungi token anti-CSRF (*Cross-Site Request Forgery*).
   - Penggunaan arsitektur *Eloquent ORM* dari Laravel menjamin 100% semua variabel input (termasuk formulir pencarian) ter-sanitasi secara otomatis (melalui implementasi PDO *Parameter Binding*), membuat sistem mustahil diserang melalui injeksi SQL.
4. **Proteksi Akses (Authentication & Rate Limiting)**
   Modul masuk (*Login*) diawasi dengan *Rate Limiting* yang ketat (membatasi jumlah percobaan *login* yang salah dalam waktu tertentu) untuk memblokir teknik serangan peretasan tipe *Brute-Force*.

---

## ⚙️ Sentralisasi Konfigurasi (Single Source of Truth)

Sistem ini didesain agar sangat ramah bagi pengurus masjid non-programmer. Semua data spesifik institusi diatur dari **satu file tunggal (`config/masjid.php`)**. Jika terjadi pergantian kepengurusan atau perbaikan data, Anda tidak perlu membongkar berbagai file kode HTML.

Variabel yang dapat dikonfigurasi melalui file ini meliputi:
- Nama Masjid, Singkatan, dan Nama Bahasa Arab.
- Kontak, Alamat, Email, dan tautan *embed* Peta Google.
- Tautan Media Sosial.
- Jadwal Waktu Sholat.
- Profil dan Nomor Rekening Bank ZISWAF.
- **Struktur Kepengurusan (Mulai dari Penasehat hingga Marbot).** (Data di halaman "Susunan Pengurus" secara dinamis memetakan file ini).

---

## 🚀 Panduan Instalasi (Lokal)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di perangkat Anda.

### 1. Persyaratan Sistem
Pastikan sistem Anda telah terinstal:
- **PHP** (Minimal versi 8.2)
- **Composer** (Package Manager PHP)
- **Node.js & NPM**
- **MySQL / MariaDB**

### 2. Kloning Repositori
```bash
git clone https://github.com/USERNAME_ANDA/nama-repo-anda.git
cd nama-repo-anda
```

### 3. Instalasi Dependensi Inti
```bash
composer install
npm install
```

### 4. Konfigurasi Database (.env)
Salin file konfigurasi bawaan dan hasilkan kunci aplikasi:
```bash
cp .env.example .env
php artisan key:generate
```
Buka file `.env` menggunakan *text editor* dan atur parameter basis data lokal Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=masjid_muer
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migrasi & Inisialisasi Database
Jalankan perintah ini untuk membangun tabel dan struktur sistem (beserta fitur-fitur keamanan *Soft Delete*):
```bash
php artisan migrate --seed
```

### 6. Storage Link (Manajemen Media)
Buat *symlink* agar folder internal dapat dirender ke halaman peramban publik:
```bash
php artisan storage:link
```

### 7. Kompilasi Aset Visual (Tailwind)
```bash
npm run dev
```

### 8. Menjalankan Sistem
Gunakan terminal baru untuk menjalankan mesin server *local development*:
```bash
php artisan serve
```
- Akses website publik: **`http://localhost:8000`**
- Akses portal admin: **`http://localhost:8000/login`**

---

<p align="center">
  <b>Sistem Informasi Masjid (SIMasjid)</b><br>
  Dibangun dengan ❤️ untuk mendorong literasi teknologi dan kemaslahatan umat. Semoga bermanfaat!
</p>

---

> **⚠️ Disklaimer:**
> Proyek ini dikembangkan secara berkelanjutan. Meskipun standar praktik keamanan dan arsitektur telah diupayakan dengan maksimal, tidak menutup kemungkinan masih terdapat *bugs*, celah keamanan, atau ketidaksempurnaan pada fitur-fitur di dalam aplikasi ini. Harap lakukan pengujian (*testing*) lebih lanjut secara mandiri sebelum mendeploy sistem ini ke lingkungan produksi publik skala besar. Kontribusi, saran, dan perbaikan dari komunitas sangat kami hargai.
