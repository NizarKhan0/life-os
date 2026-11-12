# LifeOS — Complete Architecture & System Blueprint

Dokumen ini adalah rujukan pelan induk (*master blueprint*) teknikal, skema data, integrasi, dan reka bentuk arkitektur bagi projek **LifeOS**.

## 1. Technical Stack
- **Framework:** PHP 8.3+ / Laravel 13 (Strict Types)
- **Database:** MySQL (InnoDB, utf8mb4)
- **Frontend:** Pure Livewire v4 (Zero-page reload)
- **Styling:** Tailwind CSS v4 (`@import "tailwindcss";`)
- **Integrations:** Telegram Bot API (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_DEFAULT_CHAT_ID`)

## 2. Entity Relationship Diagram (ASCII ERD)
[ User ]
   ├── hasMany ──► [ Goals ] ── hasMany ──► [ Milestones ]
   ├── hasMany ──► [ Meetings ] ── hasMany ──► [ MeetingReminders ]
   ├── hasMany ──► [ Wallets ] ── hasMany ──► [ Transactions ] ◄── belongsTo ── [ Categories ]
   │                                                                               └── hasMany ──► [ Budgets ]
   ├── hasMany ──► [ Tasks ]
   ├── hasMany ──► [ Habits ] ── hasMany ──► [ HabitLogs ]
   └── hasMany ──► [ Subscriptions ]

## 3. Scheduler (`routes/console.php`)
- `meetings:send-reminders` -> `everyMinute()->withoutOverlapping()`
- `lifeos:send-briefing` -> `dailyAt('07:30')->timezone('Asia/Kuala_Lumpur')`
- `subscriptions:check-due` -> `dailyAt('09:00')->timezone('Asia/Kuala_Lumpur')`