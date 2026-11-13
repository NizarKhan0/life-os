# Modul 06: Weekly Review & Brain Dump

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Modul ini menguruskan sesi retrospektif mingguan untuk menilai kejayaan, cabaran, dan penambahbaikan, serta menyediakan ruang *Brain Dump* untuk mencatat lintasan fikiran secara spontan.

---

## 1. Migrations & Schema
- `weekly_reviews`:
  - `id`, `user_id` (foreignId constrained cascade), `week_start_date` (date), `wins` (text, nullable), `challenges` (text, nullable), `improvements` (text, nullable), `metrics_snapshot` (json, nullable: bilangan task siap, jumlah perbelanjaan minggu itu), `timestamps`.
  - Kekunci unik kompaun: `['user_id', 'week_start_date']`.
- `brain_dumps`:
  - `id`, `user_id` (foreignId constrained cascade), `content` (text), `is_processed` (boolean, default: false), `timestamps`.
  - Indeks kompaun: `['user_id', 'is_processed']`.

---

## 2. Models & Reflection Helpers
- `WeeklyReview.php`:
  - Relasi `belongsTo(User::class)`.
  - Cast `metrics_snapshot` ke `array`.
- `BrainDump.php`:
  - Relasi `belongsTo(User::class)`.

---

## 3. UI/UX & Livewire Implementation (`app/Livewire/Reviews/WeeklyReviewIndex.php`)
- **UI Language:** **100% Professional English** (tiada perkataan Melayu pada UI).
- **MANDATORY Full CRUD On Reviews & Brain Dump:**
  1. **Brain Dump (Full CRUD):**
     - Quick Input Bar (taip idea/tugas pantas tekan Enter terus simpan).
     - Senarai kad nota dengan butang Edit Brain Dump (mengisi semula input bar dengan petunjuk pembatalan).
     - Butang Delete Brain Dump berserta **Flux Confirmation Modal**.
     - Butang toggle tanda proses (`toggleProcessed`).
  2. **Weekly Review (Full CRUD):**
     - Weekly Snapshot Card (merangkumkan metrik tugasan selesai dan jumlah perbelanjaan minggu semasa secara automatik).
     - Borang refleksi berpandukan 3 soalan utama (Wins, Challenges, Improvements).
     - Pengemaskinian ulasan minggu sedia ada tanpa reload halaman.
     - Butang Delete Review minggu tertentu berserta confirmation modal.

---

## 4. Automation & Weekly Reflection Prompt
- Command Artisan: `reviews:send-weekly-reminder` (didaftarkan di `routes/console.php` mengikut `SCHEDULE_REVIEWS_TIME` dan `SCHEDULE_REVIEWS_DAY` dalam `Asia/Kuala_Lumpur`).
- Menghantar notifikasi Telegram setiap hari Ahad malam untuk mendorong pengguna meluangkan 10 minit membuat refleksi mingguan dan memproses nota *Brain Dump*.

---

## 5. Seeder
- `ReviewSeeder.php`: Menyediakan contoh ulasan mingguan lepas berserta beberapa nota Brain Dump contoh.
