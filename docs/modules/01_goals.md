# Modul 01: Goals & Milestones
Rujuk `CLAUDE.md` dan `docs/ARCHITECTURE.md`. Bina **Modul 01: Goals & Milestones**:
1. **Migrations:**
   - `goals`: `id`, `user_id` (cascade), `title`, `description` (nullable), `category` (default: 'personal'), `target_date` (nullable), `target_value` (decimal 15,2, nullable), `current_value` (decimal 15,2, default: 0), `unit` (nullable), `status` (default: 'in_progress'), `timestamps`, index `['user_id', 'status']`.
   - `milestones`: `id`, `goal_id` (cascade), `title`, `due_date` (nullable), `is_completed` (boolean, default: false), `completed_at` (dateTime, nullable), `timestamps`, index `['goal_id', 'is_completed']`.
2. **Models (`Goal.php`, `Milestone.php`):**
   - `progressPercentage(): float`: kira `(current_value / target_value) * 100` jika ada `target_value`, atau peratus milestone siap.
   - `daysRemaining(): ?int`: baki hari hingga `target_date` guna Carbon.
3. **Livewire UI (`app/Livewire/Goals/GoalIndex.php`):**
   - Paparan Grid Cards dengan progress bar Tailwind v4, Quick Progress Updater reaktif, Milestone toggle checkbox satu-klik (`toggleMilestone`), dan modal Create Goal.
4. **Seeder:** `GoalSeeder.php` (1 matlamat kewangan + 1 matlamat peribadi).
