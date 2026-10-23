# Website & Sistem Informasi Masjid (Laravel + Tailwind)

**Tech Stack:** Laravel, Tailwind CSS, Blade, MySQL, Filament/Custom Dashboard.  
**Standards & Operating Workflow:** Terintegrasi penuh dengan [Workflow Master](.workflow/README.md).

---

## 1. Operating Discipline & Engineering Workflow
Semua pekerjaan rekayasa pada repositori ini WAJIB mematuhi 9 tahapan alur kerja di `.workflow/workflows/00-engineering-workflow-ai-coding.md`:
1. **Triage:** Pahami tujuan, klasifikasikan tipe tugas dan tingkat risiko (Low / Medium / High).
2. **Discover:** Periksa file terkait, model kas/keuangan, agenda, dan migrasi sebelum perubahan.
3. **Design:** Rancang acceptance criteria, alur data transaksional, dan boundary error.
4. **Plan:** Tentukan file dan urutan modifikasi; hindari overengineering.
5. **Implement:** Tulis kode bersih, aman, dan modular sesuai konvensi Laravel.
6. **Verify:** Jalankan test suite secara nyata (`php artisan test` atau `./vendor/bin/phpunit`).
7. **Review:** Periksa diff, otorisasi akses admin vs jamaah, dan integritas laporan kas.
8. **Release:** Verifikasi migrasi skema dan kesehatan aplikasi.
9. **Report:** Laporkan hasil verifikasi nyata dengan jujur (Passed / Failed / Skipped).

---

## 2. Aturan Domain Khusus Sistem Masjid
- **Transparansi & Integritas Keuangan Kas:** Seluruh pencatatan kas masuk dan kas keluar WAJIB memverifikasi tanggal, kategori, dan nominal. Laporan saldo akhir harus terakumulasi secara atomik dan akurat.
- **Role-Based Access Control:** Fitur penginputan kas, agenda, dan postingan hanya dapat diakses oleh Pengurus/Admin terautentikasi. Jamaah umum hanya memiliki izin baca (*read-only*).
- **Testing (Pola AAA):** Pengujian automated wajib menggunakan SQLite in-memory (`RefreshDatabase`), mengikuti standar `.workflow/rules/04-testing-qa-rules.md`.
