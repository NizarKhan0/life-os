# Modul 02: Meetings & Time Management

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Modul ini menguruskan jadual mesyuarat, janji temu penting, dan peringatan proaktif sebelum bermula ke Telegram.

---

## 1. Migrations & Schema
- `meetings`:
  - `id`, `user_id` (foreignId constrained cascade), `title`, `description` (nullable text), `location_or_link` (nullable string: Google Meet / Zoom / Lokasi Fizikal), `start_time` (dateTime), `end_time` (dateTime, nullable), `status` (enum: 'scheduled', 'completed', 'cancelled', default: 'scheduled'), `timestamps`.
  - Indeks kompaun: `['user_id', 'start_time']`.
- `meeting_reminders`:
  - `id`, `meeting_id` (foreignId constrained cascade), `remind_before_minutes` (int, default: 15), `channel` (string, default: 'telegram'), `is_sent` (boolean, default: false), `sent_at` (dateTime, nullable), `timestamps`.
  - Indeks kompaun: `['is_sent', 'remind_before_minutes']`.

---

## 2. Services & Automation
- `TelegramService.php`:
  - Menguruskan panggilan HTTP synchronous ke Telegram Bot API (`sendMessage` dengan format HTML/Markdown).
  - Menyokong mod simulasi sekiranya `TELEGRAM_BOT_TOKEN` tiada.
- Artisan Command `meetings:send-reminders`:
  - Berjalan **setiap minit** (`everyMinute()->withoutOverlapping()`).
  - Mengimbas mesyuarat berstatus `scheduled` yang bakal bermula dalam tempoh amaran (15 minit sebelum) dan menghantar pautan mesyuarat ke Telegram.

---

## 3. UI/UX & Livewire Implementation (`app/Livewire/Meetings/MeetingIndex.php`)
- **UI Language:** **100% Professional English** (tiada istilah Bahasa Melayu pada UI).
- **Full CRUD Requirements:**
  - **Create:** Modal "Schedule Meeting" dengan input tajuk, link/lokasi, tarikh, waktu mula & waktu tamat.
  - **Read:** Tab penapis "Upcoming Meetings" dan "Past/Completed Meetings", lencana status reminder (`🔔 Sent` / `⏳ Pending`), dan kiraan undur masa.
  - **Update:** Butang Edit untuk menukar waktu, pautan, atau butiran mesyuarat.
  - **Delete:** Butang Delete berserta **Flux Confirmation Modal** sebelum mesyuarat dihapuskan.
  - **Quick Test:** Butang "Test Telegram Alert" untuk mencuba penghantaran amaran secara langsung.

---

## 4. Seeder
- `MeetingSeeder.php`: Menyediakan 1 mesyuarat 30 minit dari sekarang (untuk ujian peringatan) dan 1 mesyuarat pada hari esok.
