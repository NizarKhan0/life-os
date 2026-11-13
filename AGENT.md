# 🤖 ANTIGRAVITY AGENT RULES & AUTONOMOUS BOUNDARIES

Dokumen ini adalah peraturan mutlak (*absolute rules*) dan panduan piawaian kejuruteraan perisian bagi mana-mana ejen AI atau pembangun yang membina atau menyelenggara modul dalam projek **LifeOS**.

---

## 1. Project Context & Initialization State
- Projek Laravel ini **SUDAH SIAP DIPASANG** dalam direktori ini.
- Tugas anda: Membina, menambah baik, dan menyelenggara modul mengikut spesifikasi terperinci di `docs/modules/` dan pelan induk di `docs/ARCHITECTURE.md`.
- **DILARANG** menjalankan `laravel new`, memadam direktori teras projek, atau memadam fail `.env` tanpa kebenaran eksplisit pengguna.

---

## 2. Technical Stack & Version Lock (DILARANG UBAH)
- **Framework:** PHP 8.3+ / Laravel 13 (Strict Types: `declare(strict_types=1);` pada setiap fail PHP).
- **Frontend Engine:** Pure Livewire v3/v4 dengan Flux UI (Reaktif, Zero Full-Page Reload).
- **DILARANG SAMA SEKALI:** Memasang atau menggunakan Filament, Inertia, Vue, React, atau Bootstrap.
- **Styling:** Pure Tailwind CSS v4 (Gunakan `@import "tailwindcss";` dalam `resources/css/app.css`. DILARANG menjana fail konfigurasi lapuk `tailwind.config.js`).
- **Database Engine:** MySQL (InnoDB, `utf8mb4_unicode_ci`). Wajib menggunakan `foreignId()->constrained()->cascadeOnDelete()` dan indeks kompaun pada kolum carian/penapis.

---

## 3. Peraturan UI & Bahasa (100% English Web UI Standard)
- **Bahasa Antaramuka Web (UI):** **WAJIB 100% BAHASA INGGERIS PROFESIONAL**.
  - Semua tajuk (titles), label input, teks butang, lencana status (*badges*), kepala jadual (*table headers*), *placeholder*, *empty states*, mesej ralat validasi, dan notifikasi (*toast*) mestilah dalam Bahasa Inggeris (cth: "Save Transaction", "Delete Wallet", "Due Soon", "Completed", "Pending Check-in").
  - Elakkan mencampurkan perkataan Bahasa Melayu ke dalam UI web (cth: DILARANG guna "Dompet", "Perbelanjaan", "Baki", "Kemaskini" pada antaramuka web).
- **Bahasa Komunikasi & Dokumentasi:** Perbualan dalam chat dan fail dokumentasi/panduan boleh diterangkan dalam Bahasa Melayu, tetapi kod dan UI kekal Bahasa Inggeris sepenuhnya.
- **Clean Assets:** Elakkan favicon bawaan lalai (*default Laravel favicon*). Gunakan RFC data URI `<link rel="icon" href="data:,">` agar tiada paparan favicon yang tidak diingini.

---

## 4. Peraturan Wajib: Full CRUD Pada Setiap Modul & Entiti
Setiap modul tidak boleh terhad kepada fungsi "Tambah" dan "Papar" sahaja. **SEMUA ENTITI** (termasuk sub-entiti seperti Wallets, Categories, Budgets, Habits, Milestones, Subscriptions, Brain Dumps) **WAJIB MENYOKONG FULL CRUD**:
1. **Create (C):** Modal atau input pantas yang lancar dengan validasi mantap.
2. **Read (R):** Paparan kad metrik KPI, senarai reaktif, penapis (*filter*), atau jadual teratur.
3. **Update (U):** Butang Edit yang membuka modal/form berisi data sedia ada untuk dikemaskini tanpa memuat semula halaman.
4. **Delete (D):** Butang Delete berserta **modal pengesahan keselamatan (*Flux confirmation modal*)** untuk mengelakkan pemadaman tidak sengaja.

---

## 5. Pengasingan Data Pengguna (Strict Multi-Tenancy Isolation)
Setiap pengguna mesti mempunyai ruang data yang terasing sepenuhnya (*isolated data boundary*):
- Setiap migrasi entiti utama **wajib** mempunyai kolum `user_id`:
  ```php
  $table->foreignId('user_id')->constrained()->cascadeOnDelete();
  ```
- **Setiap Query & Eloquent Operation Wajib Dibatasi**:
  ```php
  // BETUL:
  Wallet::query()->where('user_id', Auth::id())->get();
  
  // DILARANG:
  Wallet::all(); // Merbahaya! Boleh membocorkan data pengguna lain.
  ```
- Semasa menyimpan rekod baharu, sentiasa ikat dengan `Auth::id()`.

---

## 6. Piawaian Skema Pangkalan Data Lengkap Sejak Awal
Jangan abaikan medan visual UX dalam migrasi awal untuk mengelakkan fail migrasi bertindih (*patch migrations*):
- Kolum visual seperti `icon` (emoji/string), `color` (hex/badge color), `sort_order`, atau `is_active` wajib dimasukkan ke dalam fail migrasi penciptaan jadual yang pertama.
- Elakkan mencipta pelbagai migrasi kecil yang boleh digabungkan ke dalam satu fail migrasi modular yang kemas.

---

## 7. Sistem Automasi, Jadual & Telegram
- **Konfigurasi Jadual Berasaskan Persekitaran (`.env` Driven):**
  - DILARANG menetapkan waktu cron secara *hardcoded* terus di dalam kod tanpa pautan ke konfigurasi.
  - Sambungkan semua masa jadual ke `config/lifeos.php` dan sediakan pemboleh ubah di `.env` (cth: `SCHEDULE_BRIEFING_TIME="07:30"`).
- **Direct Synchronous Delivery:**
  - Peringatan Telegram dihantar secara langsung (*direct HTTP request*). JANGAN gunakan *queue overhead* yang tidak perlu melainkan melibatkan *heavy background processing*.
  - Automasi mesti menyokong **Smart Simulation Mode**: Jika token Telegram tiada, rekod ke `automation_logs` sebagai `simulated` tanpa melontarkan ralat sistem.

---

## 8. Autonomous Execution & Terminal Guardrails
- **Package Installation Lock:** DILARANG menjalankan `composer require` atau `npm install` secara melulu tanpa keperluan eksplisit.
- **Database Reset Lock:** DILARANG menjalankan `php artisan migrate:fresh` atau `db:wipe` pada fasa produksi.
- **Verification Routine:** Selepas sebarang pengemaskinian kod penting, sentiasa sahkan integriti sistem:
  ```bash
  composer lint:check   # Semakan format Laravel Pint
  composer types:check  # Semakan ketat PHPStan
  php artisan test      # Ujian unit & fitur PHPUnit
  ```
