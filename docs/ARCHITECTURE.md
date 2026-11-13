# 🏛️ LifeOS — Master Architecture & System Blueprint

Dokumen ini adalah rujukan pelan induk (*master blueprint*) teknikal, skema pangkalan data, reka bentuk arkitektur, dan standard integrasi bagi keseluruhan ekosistem **LifeOS**.

---

## 1. Technical Stack & Foundation
- **Language & Runtime:** PHP 8.3+ (Strict Types: `declare(strict_types=1);` pada semua fail)
- **Framework:** Laravel 13 (Latest Release)
- **Database Engine:** MySQL 8.0+ (InnoDB, Collation: `utf8mb4_unicode_ci`)
- **Frontend Architecture:** Pure Livewire v3/v4 dengan Flux UI (Reaktif, Single-Page App feel, Zero Full-Page Reload)
- **Styling Architecture:** Tailwind CSS v4 (`@import "tailwindcss";` dalam `app.css`, tanpa `tailwind.config.js` lapuk)
- **UI Language Standard:** 100% Professional English merentasi seluruh komponen web (titles, forms, buttons, tables, badges, modals, toasts).
- **Communication & Delivery:** Telegram Bot API (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_DEFAULT_CHAT_ID`) dengan sokongan Smart Simulation Mode.

---

## 2. Complete Entity Relationship Diagram (ERD)

```text
[ User ]
   ├── hasMany ──► [ Goals ] ── hasMany ──► [ Milestones ]
   ├── hasMany ──► [ Meetings ] ── hasMany ──► [ MeetingReminders ]
   ├── hasMany ──► [ Wallets ] ── hasMany ──► [ Transactions ] ◄── belongsTo ── [ Categories ]
   │                                                                               └── hasMany ──► [ Budgets ]
   ├── hasMany ──► [ Tasks ]
   ├── hasMany ──► [ Habits ] ── hasMany ──► [ HabitLogs ]
   ├── hasMany ──► [ Subscriptions ]
   ├── hasMany ──► [ WeeklyReviews ]
   ├── hasMany ──► [ BrainDumps ]
   └── hasMany ──► [ AutomationLogs ]
```

### Jadual Skema & Tanggungjawab Data:
1. `users`: Entiti induk autentikasi & pemilikan akaun.
2. `goals`: Matlamat jangka pendek/panjang beserta nilai sasaran (`target_value`, `target_date`, `status`).
3. `milestones`: Pencapaian kecil bertarikh di bawah sasaran matlamat (`is_completed`, `due_date`).
4. `meetings`: Pengurusan mesyuarat, janji temu, dan pautan panggilan (`location_or_link`, `start_time`, `end_time`).
5. `meeting_reminders`: Rekod amaran mesyuarat automatik Telegram (`remind_before_minutes`, `is_sent`, `sent_at`).
6. `wallets`: Akaun kewangan (Bank, E-Wallet, Tunai) dengan ikon visual emoji (`icon`, `starting_balance`, `currency`).
7. `categories`: Kategori pendapatan/perbelanjaan berasingan mengikut pengguna (`type`, `color`, `icon`).
8. `transactions`: Rekod aliran tunai masuk/keluar/pindahan antara dompet (`amount`, `transaction_date`, `notes`).
9. `budgets`: Had siling perbelanjaan bulanan setiap kategori mengikut bulan/tahun (`amount`, `month_year`).
10. `tasks`: Pengurusan tugasan harian mengikut keutamaan (`priority`: low/medium/high/urgent, `due_date`, `status`).
11. `habits`: Amalan berulang harian dengan pengira streak (`frequency`, `target_per_day`, `color`, `is_active`).
12. `habit_logs`: Rekod tandaan tabiat harian bagi kiraan *streak* berterusan (`logged_date`, `count`).
13. `subscriptions`: Bil berulang dan langganan digital dengan status amaran tamat tempoh (`billing_cycle`, `next_billing_date`).
14. `weekly_reviews`: Rekod retrospektif mingguan (Wins, Challenges, Improvements) berserta *metrics snapshot*.
15. `brain_dumps`: Nota minda pantas yang boleh diproses kepada tugasan atau disimpan sebagai idea (`is_processed`).
16. `automation_logs`: Sejarah penuh mesej proaktif Telegram dan larian cron (`status`: sent/simulated/failed).

---

## 3. Strict Multi-Tenancy & Data Isolation Principle
Semua data di dalam LifeOS adalah terasing sepenuhnya bagi setiap pengguna (*strict tenant boundary*):
- Setiap entiti mesti mempunyai lajur `user_id` berkunci asing (`cascadeOnDelete()`).
- Setiap pertanyaan Eloquent **wajib** menyaring `where('user_id', Auth::id())` atau diikat melalui relasi `Auth::user()->relation()`.
- Tiada sebarang pengguna boleh melihat, menyunting, atau memadam dompet, transaksi, tugasan, mahupun nota pengguna lain.

---

## 4. Full CRUD UI Standard
Setiap modul diwajibkan menyediakan kitaran lengkap CRUD:
- **Create:** Borang/Modal reaktif dengan validasi masa nyata.
- **Read:** Kad KPI ringkasan, bar kemajuan peratusan, dan jadual teratur.
- **Update:** Butang Edit `✏️` yang memuatkan data sedia ada ke dalam modal pengemaskinian tanpa reload halaman.
- **Delete:** Butang Delete `🗑️` yang mencetuskan modal pengesahan keselamatan (*Flux confirmation modal*) sebelum rekod dihapuskan.

---

## 5. Proactive Automation & Scheduler Engine
Semua automasi berjalan mengikut zon masa `Asia/Kuala_Lumpur` dan dikonfigurasikan secara dinamik menerusi `config/lifeos.php` dan fail `.env`:

| Perintah Artisan | Jadual / Kekerapan | Zon Masa | Konfigurasi `.env` | Penerangan Proaktif |
| :--- | :--- | :--- | :--- | :--- |
| `meetings:send-reminders` | **Setiap Minit** (`everyMinute`) | Sistem / Local | - | Menghantar peringatan mesyuarat berserta link Google Meet/Zoom ke Telegram. |
| `lifeos:send-briefing` | **Harian @ 07:30 AM** | `Asia/Kuala_Lumpur` | `SCHEDULE_BRIEFING_TIME` | **Morning Executive Pulse**: Agenda meeting, top 3 urgent tasks, unlogged habits, baki dompet. |
| `goals:send-reminders` | **Harian @ 08:00 AM** | `Asia/Kuala_Lumpur` | `SCHEDULE_GOALS_TIME` | Mengingatkan matlamat aktif yang menghampiri deadline (7 hari) atau progress rendah. |
| `subscriptions:check-due` | **Harian @ 09:00 AM** | `Asia/Kuala_Lumpur` | `SCHEDULE_SUBSCRIPTIONS_TIME` | Amaran bil & komitmen yang due dalam masa 3 hari atau hari ini. |
| `money:check-budgets` | **Harian @ 08:00 PM** | `Asia/Kuala_Lumpur` | `SCHEDULE_BUDGETS_TIME` | Amaran jika penggunaan bajet kategori melebihi 80% (Warning) atau 100% (Overbudget). |
| `tasks:send-reminders` | **Harian @ 09:00 PM** | `Asia/Kuala_Lumpur` | `SCHEDULE_TASKS_TIME` | **Evening Recap**: Tugasan tertunggak hari ini & tabiat belum ditanda. |
| `reviews:send-weekly-reminder`| **Setiap Ahad @ 08:00 PM** | `Asia/Kuala_Lumpur` | `SCHEDULE_REVIEWS_TIME` | Peringatan refleksi mingguan & proses nota Brain Dump. |

### Ciri Khas Automasi:
- **Direct Synchronous HTTP:** Tidak memerlukan sebarang `queue:work` untuk menghantar Telegram.
- **Smart Simulation Mode:** Jika token Telegram tidak dimasukkan, mesej direkodkan ke pangkalan data tanpa mematikan sistem.
- **On-Demand Manual Trigger:** Boleh dicetuskan pada bila-bila masa menerusi `/automation` atau terminal CLI.
