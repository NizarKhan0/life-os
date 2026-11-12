# Modul 07: Automation & Morning Briefing Hub
Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Bina sistem automasi Morning Briefing Telegram:
1. **Service (`app/Services/BriefingService.php`):**
   - Jana laporan Telegram berformat kemas: Header tarikh, Jadual Meeting hari ini, Top 3 Urgent/High Tasks, Peringatan Tabiat belum dilog hari ini, dan Ringkasan Poket (belanja semalam & baki dompet).
2. **Artisan Command (`lifeos:send-briefing`):**
   - Panggil `BriefingService::generateForUser($user)` dan hantar via `TelegramService`. Daftar di `routes/console.php` jam `07:30` (`Asia/Kuala_Lumpur`).
3. **Livewire Testing Action:**
   - Sediakan butang `[⚡ Hantar Ujian Briefing ke Telegram]` pada Dashboard utama untuk ujian manual bila-bila masa.