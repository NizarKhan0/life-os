# Modul 07: Automation & Morning Briefing Hub

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Modul ini bertindak sebagai pusat kawalan (*Command Center*) bagi memantau, menguji, dan menguruskan semua automasi berjadual serta notifikasi proaktif Telegram dalam LifeOS.

---

## 1. Migrations & Schema
- `automation_logs`:
  - `id`, `user_id` (foreignId constrained cascade), `type` (string: cth 'morning_briefing', 'meetings:send-reminders', dll.), `channel` (string: 'telegram'), `status` (enum: 'sent', 'simulated', 'failed'), `triggered_by` (enum: 'scheduler', 'manual'), `message_preview` (text, nullable), `sent_at` (timestamp), `timestamps`.
  - Indeks kompaun: `['user_id', 'sent_at']`, `['user_id', 'type']`.

---

## 2. Services & Architecture
- `BriefingService.php`:
  - Mengumpul data eksekutif merentas modul: tarikh semasa, mesyuarat hari ini, top 3 urgent/high tasks, tabiat belum dilog berserta streak, belanja semalam, dan baki setiap dompet.
  - Memformatkan laporan HTML kemas untuk dihantar ke Telegram.
  - Merekodkan hasil penghantaran ke `automation_logs`.
- `TelegramService.php`:
  - Berkomunikasi secara terus (*direct synchronous HTTP*) ke Telegram Bot API.
  - Menyokong **Smart Simulation Mode** (tiada ralat jika token tidak dikonfigurasi).
- `config/lifeos.php`:
  - Membaca waktu jadual daripada fail `.env` (`SCHEDULE_BRIEFING_TIME`, `SCHEDULE_GOALS_TIME`, dsb.) mengikut zon masa `Asia/Kuala_Lumpur`.

---

## 3. UI/UX & Livewire Implementation (`app/Livewire/Automation/AutomationHub.php`)
- **UI Language:** **100% Professional English** (tiada teks Melayu pada antaramuka).
- **Core Hub Features:**
  1. **Executive Banner:** Memaparkan status sambungan bot Telegram (**Connected** / **Simulation Mode**), waktu jadual Morning Briefing semasa yang dinamik, dan butang **"⚡ Dispatch Test Briefing to Telegram"**.
  2. **Daily Pulse KPI Cards:** Kad metrik pautan pantas ke Meetings, Top Tasks, Net Balance, dan Active Goals.
  3. **Live Message Preview:** Pratonton format sebenar mesej Telegram yang akan diterima pada telefon pengguna.
  4. **Active Schedules Control Panel:** Senarai 7 arahan berjadual berserta butang **"▶ Run Now"** untuk ujian on-demand tanpa perlu menunggu masa cron.
  5. **Automation Dispatch Logs:** Jadual sejarah larian automasi lengkap dengan status lencana (*Sent*, *Simulated*, *Failed*), butang semak butiran mesej, dan butang padam rekod log dengan **Flux Confirmation Modal**.

---

## 4. Scheduling & Execution (`routes/console.php`)
- Menjalankan kesemua 7 perintah berjadual mengikut pemboleh ubah persekitaran dalam `Asia/Kuala_Lumpur`.
- Tiada keperluan `queue:work` untuk penghantaran mesej Telegram kerana dilaksanakan secara *direct synchronous HTTP*.
- Untuk maklumat terperinci mengenai persediaan crontab server dan Docker, rujuk `docs/AUTOMATION_SETUP_GUIDE.md`.
