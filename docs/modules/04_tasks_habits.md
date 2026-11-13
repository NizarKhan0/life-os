# Modul 04: Tasks & Habits Tracker

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Modul ini menguruskan pengurusan tugasan harian mengikut prioriti serta pembentukan tabiat konsisten dengan kiraan *streak*.

---

## 1. Migrations & Schema
- `tasks`:
  - `id`, `user_id` (foreignId constrained cascade), `title` (string), `description` (nullable text), `priority` (enum: 'low', 'medium', 'high', 'urgent', default: 'medium'), `status` (enum: 'todo', 'in_progress', 'completed', 'archived', default: 'todo'), `due_date` (date, nullable), `completed_at` (dateTime, nullable), `timestamps`.
  - Indeks kompaun: `['user_id', 'status', 'due_date']`.
- `habits`:
  - `id`, `user_id` (foreignId constrained cascade), `name` (string), `frequency` (enum: 'daily', 'weekdays', 'weekly', default: 'daily'), `target_per_day` (int, default: 1), `color` (string, nullable: cth 'indigo', 'emerald', 'rose'), `is_active` (boolean, default: true), `timestamps`.
- `habit_logs`:
  - `id`, `habit_id` (foreignId constrained cascade), `logged_date` (date), `count` (int, default: 1), `timestamps`.
  - Kekunci unik kompaun: `['habit_id', 'logged_date']`.

---

## 2. Models & Streak Logic
- `Task.php`:
  - `isOverdue(): bool` : Memeriksa sama ada tugasan belum selesai dan tarikh akhir telah lepas.
- `Habit.php`:
  - `isCompletedToday(): bool` : Memeriksa sama ada rekod `habit_logs` wujud untuk tarikh hari ini.
  - `currentStreak(): int` : Mengira jumlah hari berturut-turut tabiat ini ditanda tanpa putus menggunakan manipulasi Carbon.

---

## 3. UI/UX & Livewire Implementation (`app/Livewire/Tasks/TaskHabitIndex.php`)
- **UI Language:** **100% Professional English** (tiada teks Melayu pada antaramuka).
- **MANDATORY Full CRUD On Tasks & Habits:**
  1. **Tasks (Full CRUD):**
     - Quick inline input (taip tajuk tekan Enter terus tambah).
     - Checkbox reaktif satu klik untuk tukar status `completed` / `todo`.
     - Modal Edit Task untuk menukar keutamaan (*priority*), tarikh akhir (*due date*), dan huraian.
     - Butang Delete Task berserta **Flux Confirmation Modal**.
     - Penapis pantas: Today, Urgent/High Priority, Pending, dan All.
  2. **Habits (Full CRUD):**
     - Modal Add Habit (nama tabiat, kekerapan, pilihan warna lencana).
     - Butang tanda check-in hari ini berserta visual badge streak berapi (`🔥 7 days`).
     - Butang Edit Habit untuk menukar nama dan kekerapan tabiat.
     - Butang Delete Habit berserta **Flux Confirmation Modal**.

---

## 4. Automation & Daily Recap
- Command Artisan: `tasks:send-reminders` (didaftarkan di `routes/console.php` mengikut `SCHEDULE_TASKS_TIME` dalam `Asia/Kuala_Lumpur`).
- Menghantar notifikasi Telegram petang (**Evening Wrap-up**) untuk mengingatkan tugasan tertunggak yang belum siap dan tabiat harian yang belum dilog sebelum hari berakhir.

---

## 5. Seeder
- `TaskHabitSeeder.php`: Menyediakan contoh tugasan dengan pelbagai tahap keutamaan dan tabiat harian dengan beberapa log streak sedia ada.
