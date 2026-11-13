# Modul 01: Goals & Milestones

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Modul ini menguruskan matlamat jangka masa pendek/panjang serta pencapaian pecahan (*milestones*).

---

## 1. Migrations & Schema
- `goals`:
  - `id`, `user_id` (foreignId constrained cascade), `title`, `description` (nullable text), `category` (default: 'personal'), `target_date` (nullable date), `target_value` (decimal 15,2, nullable), `current_value` (decimal 15,2, default: 0), `unit` (nullable string), `status` (enum: 'in_progress', 'completed', 'archived', default: 'in_progress'), `timestamps`.
  - Indeks kompaun: `['user_id', 'status']`.
- `milestones`:
  - `id`, `goal_id` (foreignId constrained cascade), `title`, `due_date` (nullable date), `is_completed` (boolean, default: false), `completed_at` (dateTime, nullable), `timestamps`.
  - Indeks kompaun: `['goal_id', 'is_completed']`.

---

## 2. Models & Business Logic
- `Goal.php`:
  - `progressPercentage(): float` : Mengira peratusan kemajuan berdasarkan `current_value / target_value * 100`, atau peratusan milestone yang siap.
  - `daysRemaining(): ?int` : Menghitung baki hari sehingga `target_date` (pulang nilai negatif jika tertunggak).
  - `isOverdue(): bool` : Memeriksa jika tarikh sasaran telah lepas tetapi belum selesai.
- `Milestone.php`:
  - Relasi `belongsTo(Goal::class)`.

---

## 3. UI/UX & Livewire Implementation (`app/Livewire/Goals/GoalIndex.php`)
- **UI Language:** **100% Professional English** (tiada teks Melayu pada butang, label, header, atau toast).
- **Zero Full-Page Reload:** Semua interaksi berlaku secara reaktif menggunakan Livewire v3/v4 & Flux UI.
- **Full CRUD Requirements:**
  - **Create:** Modal "New Goal" dengan input tajuk, kategori, tarikh sasaran, unit & nilai sasaran.
  - **Read:** Grid kad interaktif dengan bar kemajuan Tailwind v4, lencana status (*In Progress*, *Completed*), dan senarai milestone.
  - **Update:** Butang Edit pada kad Goal untuk mengemaskini maklumat matlamat, serta butang kemas kini nilai semasa pantas (*Quick Value Increment*).
  - **Delete:** Butang Delete berserta **Flux Confirmation Modal** sebelum matlamat dipadamkan.
  - **Milestone Operations:** Tambah milestone baharu, tanda siap dengan satu klik (`toggleMilestone`), dan padam milestone.

---

## 4. Automation & Reminders
- Command Artisan: `goals:send-reminders` (didaftarkan di `routes/console.php` mengikut `SCHEDULE_GOALS_TIME` dalam `Asia/Kuala_Lumpur`).
- Memeriksa matlamat yang menghampiri tarikh akhir dalam tempoh 7 hari atau peratusan kemajuan di bawah sasaran dan menghantar amaran proaktif ke Telegram.

---

## 5. Seeder
- `GoalSeeder.php`: Menyediakan contoh sekurang-kurangnya 1 matlamat kewangan (cth: "Emergency Fund RM10k") dan 1 matlamat peribadi (cth: "Read 12 Books") berserta beberapa milestone.
