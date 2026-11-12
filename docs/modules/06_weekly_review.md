# Modul 06: Weekly Review & Brain Dump
Rujuk `CLAUDE.md` dan `docs/ARCHITECTURE.md`. Bina **Modul 06: Weekly Review & Brain Dump**:
1. **Migrations:**
   - `weekly_reviews`: `id`, `user_id` (cascade), `week_start_date` (date), `wins` (text, nullable), `challenges` (text, nullable), `improvements` (text, nullable), `metrics_snapshot` (json, nullable), `timestamps`, unique `['user_id', 'week_start_date']`.
   - `brain_dumps`: `id`, `user_id` (cascade), `content` (text), `is_processed` (boolean, default: false), `timestamps`.
2. **Livewire UI (`app/Livewire/Reviews/WeeklyReviewIndex.php`):**
   - Brain Dump Quick Bar (taip nota pantas tekan Enter terus simpan), Weekly Snapshot Card (metrik automatik task siap & belanja minggu semasa), dan borang refleksi 3 soalan.
3. **Seeder:** `ReviewSeeder.php` (1 rekod review minggu lepas + 2 nota brain dump).