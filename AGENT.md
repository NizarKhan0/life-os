# ANTIGRAVITY AGENT RULES & AUTONOMOUS BOUNDARIES

## 1. Project Context & Initialization State
- Projek Laravel ini SUDAH SIAP DIPASANG dalam direktori ini.
- Tugas anda: Membina ciri dan modul baharu mengikut spesifikasi dalam `docs/modules/`[cite: 3, 5, 8].
- DILARANG menjalankan `laravel new`, memadam fail teras, atau mengubah konfigurasi asas `.env` tanpa kebenaran pengguna[cite: 3, 5, 8].

## 2. Technical Stack & Version Lock (DILARANG UBAH)
- **Framework:** Laravel 13 / Terkini (PHP 8.3+, Strict Types)[cite: 7, 8].
- **Frontend:** Pure Livewire v4 / Terkini[cite: 7, 8].
- **DILARANG SAMA SEKALI:** Memasang Filament, Vue, React, Inertia, atau Bootstrap[cite: 3, 5, 8].
- **Styling:** Pure Tailwind CSS v4 (Gunakan `@import "tailwindcss";` dalam `resources/css/app.css`. DILARANG menjana fail `tailwind.config.js` lapuk)[cite: 3, 5, 8].
- **Database:** MySQL (InnoDB). Wajib menggunakan `foreignId()->constrained()->cascadeOnDelete()` dan `index()` pada kolum carian[cite: 3, 5, 8].

## 3. Autonomous Execution & Terminal Guardrails
- **Package Installation Lock:** DILARANG menjalankan `composer require` atau `npm install` secara automatik tanpa meminta pengesahan daripada pengguna terlebih dahulu[cite: 5].
- **Command Verification:** Selepas selesai menjana fail modul, anda dibenarkan menjalankan `php artisan migrate` dan `php artisan db:seed` untuk mengesahkan tiada ralat sintaks.
- **Database Reset Lock:** DILARANG menjalankan `php artisan migrate:fresh` atau `db:wipe` tanpa izin eksplisit pengguna.

## 4. Coding & Architecture Standards
1. Sentiasa baca `docs/ARCHITECTURE.md` sebelum menulis sebarang fail baharu[cite: 3, 5, 8].
2. Ikut turutan wajib (Formula 4 Lapisan): **Migration & Model -> Service/Logic -> Livewire Component & Blade View -> Seeder**[cite: 3, 5, 8].
3. **Lean Livewire:** Pindahkan logik API luar (seperti Telegram Bot) atau pengiraan berat ke dalam `app/Services/`[cite: 3, 5, 8].
4. **Zero Full-Page Reload:** Pastikan semua operasi tambah, edit, dan toggle berjalan secara reaktif tanpa memuat semula halaman pelayar[cite: 3, 5, 8].