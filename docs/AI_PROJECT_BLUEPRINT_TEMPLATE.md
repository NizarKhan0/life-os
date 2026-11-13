# 📄 MASTER AI AGENT PROJECT BLUEPRINT & SPECIFICATION TEMPLATE
> **Format Sedia Guna untuk Projek Baharu (Laravel 13 • Livewire v3/v4 • Flux UI • Tailwind CSS v4)**

Fail Word (.docx) rasmi telah dijana di:
📄 **[docs/AI_PROJECT_BLUEPRINT_TEMPLATE.docx](file:///c:/laragon/www/z/life-os/docs/AI_PROJECT_BLUEPRINT_TEMPLATE.docx)**

---

## 📑 Isi Kandungan
1. [Lima Hukum Emas (5 Golden Rules) Arahan AI](#1-lima-hukum-emas-5-golden-rules)
2. [Templat 1: AGENT.md (System Instructions & Rules)](#2-templat-1-agentmd)
3. [Templat 2: docs/ARCHITECTURE.md (Pelan Induk Sistem)](#3-templat-2-docsarchitecturemd)
4. [Templat 3: docs/modules/XX_module_name.md (Spesifikasi Modul)](#4-templat-3-docsmodulesxx_module_namemd)
5. [Senarai Semak Emas (Golden 10-Point Checklist)](#5-senarai-semak-emas-golden-10-point-checklist)

---

## 1. Lima Hukum Emas (5 Golden Rules)

| No | Hukum Emas | Mengapa Wajib Dinyatakan Kepada AI? |
| :--- | :--- | :--- |
| **1** | **100% English Web UI Standard** | Walaupun prompt ditulis dalam Bahasa Melayu, WAJIB arahkan bahawa SEMUA teks antaramuka web (header, label, button, badge, modal, toast) ditulis dalam Bahasa Inggeris profesional untuk mengelakkan bahasa rojak pada UI. |
| **2** | **Mandatori Full CRUD** | AI hanya membina apa yang dispesifikasikan. Jika anda tidak sebut Edit dan Delete, AI hanya akan buat borang Create dan jadual Read. Wajibkan Create, Read, Update, Delete untuk **SEMUA** entiti dan sub-entiti. |
| **3** | **Strict Multi-Tenancy Isolation** | Wajibkan kolum `user_id` pada setiap jadual utama dan tegaskan `where('user_id', Auth::id())` pada setiap query agar data antara pengguna tidak bercampur. |
| **4** | **Skema Lengkap Dari Awal** | Masukkan medan visual UX (icon emoji, warna badge, status, susunan) terus ke dalam migrasi asas pertama bagi mengelakkan fail migrasi patch bertindih. |
| **5** | **Environment-Driven Automation** | Sebarang jadual masa atau konfigurasi cron jangan di-hardcode dalam PHP. Pautkan ke `.env` dan `config/` supaya pengguna boleh ubah masa bila-bila masa. |

---

## 2. Templat 1: `AGENT.md`

Salin teks di bawah dan simpan sebagai fail `AGENT.md` di direktori punca (*root*) projek baharu anda:

```markdown
# 🤖 ANTIGRAVITY AGENT RULES & AUTONOMOUS BOUNDARIES: [NAMA_PROJEK]

## 1. Project Context & State
- Projek Laravel ini SUDAH SIAP DIPASANG dalam direktori ini.
- DILARANG menjalankan `laravel new`, memadam direktori teras projek, atau memadam fail `.env`.
- Bina dan selenggara ciri mengikut spesifikasi di `docs/modules/` dan `docs/ARCHITECTURE.md`.

## 2. Technical Stack Lock (DILARANG UBAH)
- Framework: PHP 8.3+ / Laravel 13 (Strict Types: `declare(strict_types=1);` pada semua fail PHP).
- Frontend: Pure Livewire v3/v4 dengan Flux UI (Reaktif, Zero Full-Page Reload).
- DILARANG SAMA SEKALI: Memasang Filament, Inertia, Vue, React, atau Bootstrap.
- Styling: Pure Tailwind CSS v4 (`@import "tailwindcss";` dalam `resources/css/app.css`).
- Database: MySQL (InnoDB, utf8mb4_unicode_ci). Wajib `foreignId()->constrained()->cascadeOnDelete()`.

## 3. Web UI Language: 100% Professional English
- SEMUA tajuk, label borang, teks butang, lencana status (badges), kepala jadual, placeholder, modal, mesej ralat validasi, dan toast WAJIB 100% Bahasa Inggeris.
- DILARANG mencampurkan Bahasa Melayu ke dalam antaramuka web.
- Clean Assets: Padam favicon lalai. Gunakan `<link rel="icon" href="data:,">`.

## 4. Mandatory Full CRUD on Every Entity
- SETIAP modul dan sub-entiti WAJIB menyokong 4 operasi penuh:
  1. Create: Modal borang reaktif pantas.
  2. Read: Kad metrik KPI & jadual data teratur.
  3. Update: Butang Edit yang mengisi borang modal tanpa reload halaman.
  4. Delete: Butang Delete berserta **Flux Confirmation Modal** keselamatan.

## 5. Strict Multi-Tenancy & Data Isolation
- Setiap entiti utama mesti mempunyai lajur `user_id`.
- Setiap Eloquent query WAJIB dibatasi: `Model::query()->where('user_id', Auth::id())`.
- DILARANG menggunakan `Model::all()` tanpa tapisan `user_id`.

## 6. Automation & Schedulers
- Jadual masa WAJIB dipautkan ke `config/[nama_projek].php` dan boleh diubah via `.env`.
- Notifikasi Telegram/Emel dihantar secara terus (direct synchronous HTTP) tanpa queue overhead melainkan diarahkan.
- Smart Simulation: Rekod ke database log jika token API belum dikonfigurasi tanpa melontarkan fatal error.

## 7. Quality & Verification Guardrails
- Selepas menjana fail modul, sahkan tiada ralat:
  - `composer lint:check`   (Laravel Pint)
  - `composer types:check`  (PHPStan)
  - `php artisan test`      (PHPUnit)
```

---

## 3. Templat 2: `docs/ARCHITECTURE.md`

Salin teks di bawah dan simpan sebagai fail `docs/ARCHITECTURE.md`:

```markdown
# 🏛️ [NAMA_PROJEK] — Master Architecture & System Blueprint

## 1. Technical Overview
- Framework: Laravel 13, PHP 8.3+, Livewire v3/v4, Flux UI, Tailwind CSS v4.
- Database: MySQL 8.0+ (InnoDB, utf8mb4_unicode_ci).
- Multi-Tenancy: Single Database Tenant Boundary (Semua entiti terikat kepada user_id).
- Integrations: [Telegram Bot API / Resend / Stripe dsb.]

## 2. Entity Relationship Diagram (ASCII ERD)
[ User ]
   ├── hasMany ──► [ Modul_Utama_1 ] ── hasMany ──► [ Sub_Entiti_1 ]
   ├── hasMany ──► [ Modul_Utama_2 ] ── hasMany ──► [ Sub_Entiti_2 ]
   └── hasMany ──► [ AutomationLogs ]

## 3. Skema Data & Tanggungjawab Entiti
1. users: Data autentikasi & profil pengguna.
2. [tabel_1]: [Huraian fungsi dan kolum-kolum penting].
3. [tabel_2]: [Huraian fungsi dan kolum-kolum penting].
4. automation_logs: Rekod audit status penghantaran notifikasi proaktif (sent/simulated/failed).

## 4. Jadual Automasi Konsol (routes/console.php)
- [command_1] -> [kekerapan] -> Mengikut pemboleh ubah .env: SCHEDULE_[NAMA]_TIME
- Zon Masa Lalai: Asia/Kuala_Lumpur.
```

---

## 4. Templat 3: `docs/modules/XX_module_name.md`

Gunakan struktur seragam ini untuk setiap modul yang ingin dibina:

```markdown
# Modul [Nombor]: [Nama Modul]

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Bina modul ini mengikut spesifikasi:

## 1. Migrations & Schema
- `[nama_table_1]`:
  - `id`, `user_id` (foreignId constrained cascade), `name` (string), `icon` (string 10, nullable: emoji), `status` (enum: 'active', 'inactive', default: 'active'), `timestamps`.
  - Indeks kompaun: `['user_id', 'status']`.
- `[nama_table_sub_entiti]`:
  - `id`, `[parent]_id` (foreignId constrained cascade), `amount` (decimal 15,2), `timestamps`.

## 2. Models & Business Logic
- `[ModelName].php`:
  - Definisikan `$fillable` dan casts lengkap.
  - Relasi `belongsTo(User::class)`.
  - Kaedah Pengiraan / Scope:
    - `calculateProgress(): float` : Pengiraan formula logik.
    - `isOverdue(): bool` : Pemeriksaan tarikh matang.

## 3. Livewire UI Implementation (app/Livewire/[Folder]/[Component].php)
- UI Language: 100% Professional English.
- Sediakan MANDATORY Full CRUD:
  1. Create: Modal reaktif berserta validasi masa nyata.
  2. Read: Kad metrik ringkasan KPI, bar kemajuan peratusan, dan jadual/grid data.
  3. Update: Butang Edit yang mengisi borang modal tanpa reload halaman.
  4. Delete: Butang Delete berserta Flux Confirmation Modal keselamatan.

## 4. Automation & Reminders (Jika Berkenaan)
- Artisan Command: `[module]:send-reminders`.
- Didaftarkan di `routes/console.php` mengikut pemboleh ubah `.env`.
- Hantar amaran proaktif berserta mod simulasi jika token API tiada.

## 5. Database Seeder
- `[Module]Seeder.php`: Wajib menyediakan rekod contoh lengkap untuk ujian segera.
```

---

## 5. Senarai Semak Emas (Golden 10-Point Checklist)

Sebelum menekan Enter untuk mengarahkan Ejen AI, semak 10 kriteria ini:

- [ ] **1. Standard Bahasa UI**: Adakah anda menegaskan "100% Professional English UI"?
- [ ] **2. Hak CRUD Penuh**: Adakah setiap entiti & sub-entiti mempunyai arahan Edit dan Delete?
- [ ] **3. Modal Pengesahan**: Adakah anda menetapkan syarat confirmation modal sebelum sebarang delete?
- [ ] **4. Kolum user_id**: Adakah setiap jadual utama mempunyai `foreignId('user_id')->constrained()`?
- [ ] **5. Medan Visual UX**: Adakah kolum `icon`, `color`, atau `sort_order` dimasukkan dalam migrasi asal?
- [ ] **6. Indeks Carian**: Adakah indeks kompaun ditentukan untuk carian pantas?
- [ ] **7. Zero Page Reload**: Adakah reaktif Livewire tanpa reload halaman ditegaskan?
- [ ] **8. Konfigurasi .env**: Adakah masa automasi dan token disambungkan ke `.env` (bukan hardcoded)?
- [ ] **9. Mod Simulasi**: Adakah automasi menyokong simulasi log jika API luar belum disambung?
- [ ] **10. Seeder Ujian**: Adakah seeder data disertakan untuk pengesahan segera selepas migrasi?

