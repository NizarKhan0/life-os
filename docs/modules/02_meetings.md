# Modul 02: Meetings & Time Management
Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Bina **Modul 02: Meetings & Reminders**:
1. **Migrations:**
   - `meetings`: `id`, `user_id` (cascade), `title`, `description` (nullable), `location_or_link` (nullable), `start_time` (dateTime), `end_time` (dateTime, nullable), `status` (default: 'scheduled'), `timestamps`, index `['user_id', 'start_time']`.
   - `meeting_reminders`: `id`, `meeting_id` (cascade), `remind_before_minutes` (int, default: 15), `channel` (default: 'telegram'), `is_sent` (boolean, default: false), `sent_at` (dateTime, nullable), `timestamps`, index `['is_sent', 'remind_before_minutes']`.
2. **Services & Scheduler:**
   - `app/Services/TelegramService.php`: HTTP Client ke Telegram `sendMessage` guna `TELEGRAM_BOT_TOKEN` & `TELEGRAM_DEFAULT_CHAT_ID`.
   - `app/Console/Commands/SendMeetingReminders.php` (`meetings:send-reminders`): Semak reminder belum dihantar yang masuk tetingkap masa `start_time`, hantar Telegram & set `is_sent = true`. Daftar `everyMinute()` di `routes/console.php`.
3. **Livewire UI (`app/Livewire/Meetings/MeetingIndex.php`):**
   - Tab Upcoming & Past Meetings, lencana status reminder (`🔔 Sent` / `⏳ Pending`), modal Add Meeting, dan butang "Test Telegram Alert".
4. **Seeder:** `MeetingSeeder.php` (1 meeting 30 minit dari sekarang + 1 meeting esok).