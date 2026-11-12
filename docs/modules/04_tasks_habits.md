# Modul 04: Tasks & Habits Tracker
Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Bina **Modul 04: Tasks & Habits Tracker**:
1. **Migrations:**
   - `tasks`: `id`, `user_id` (cascade), `title`, `description` (nullable), `priority` (low/medium/high/urgent), `status` (todo/in_progress/completed/archived), `due_date` (date, nullable), `completed_at` (dateTime, nullable), `timestamps`, index `['user_id', 'status', 'due_date']`.
   - `habits`: `id`, `user_id` (cascade), `name`, `frequency` (daily/weekdays/weekly), `target_per_day` (int, default: 1), `color` (nullable), `is_active` (boolean, default: true), `timestamps`.
   - `habit_logs`: `id`, `habit_id` (cascade), `logged_date` (date), `count` (int, default: 1), `timestamps`, unique `['habit_id', 'logged_date']`.
2. **Models (`Habit.php`):**
   - `isCompletedToday(): bool` dan `currentStreak(): int` (kira hari berturut-turut tanpa putus guna Carbon).
3. **Livewire UI (`app/Livewire/Tasks/TaskHabitIndex.php`):**
   - Penapis Task (Hari Ini / Penting / Semua), toggle checkbox satu-klik, input pantas tekan Enter, dan barisan Habit dengan bulatan status + lencana streak (`🔥 7 hari`).
4. **Seeder:** `TaskHabitSeeder.php` (3 task + 3 habit contoh).