# Modul 05: Subscriptions & Commitments Vault

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Modul ini berfungsi sebagai peti besi (*vault*) bagi menjejaki langganan digital, bil utiliti bulanan, dan komitmen berulang agar tiada caj tersembunyi atau tunggakan berlaku.

---

## 1. Migrations & Schema
- `subscriptions`:
  - `id`, `user_id` (foreignId constrained cascade), `name` (string), `category` (enum: 'digital', 'utility', 'insurance', 'government', 'rent', 'other', default: 'digital'), `amount` (decimal 15,2), `billing_cycle` (enum: 'monthly', 'yearly', default: 'monthly'), `next_billing_date` (date), `wallet_id` (foreignId nullable nullOnDelete), `auto_renew` (boolean, default: true), `is_active` (boolean, default: true), `notes` (nullable text), `timestamps`.
  - Indeks kompaun: `['user_id', 'next_billing_date', 'is_active']`.

---

## 2. Models & Calculations
- `Subscription.php`:
  - `daysUntilDue(): int` : Mengira baki hari sehingga tarikh pembaharuan seterusnya (`next_billing_date`).
  - `isDueSoon(int $days = 3): bool` : Menentukan sama ada bil bakal tiba dalam tempoh 3 hari atau hari ini.
  - `monthlyEquivalent(): float` : Menormalkan kos tahunan ke nilai bulanan (`amount / 12` jika `yearly`).

---

## 3. UI/UX & Livewire Implementation (`app/Livewire/Subscriptions/SubscriptionIndex.php`)
- **UI Language:** **100% Professional English** (tiada teks Melayu pada antaramuka).
- **MANDATORY Full CRUD On Subscriptions:**
  - **Create:** Modal "New Subscription / Commitment" dengan nama, kategori, kitaran bil, tarikh seterusnya, dan dompet pembayaran yang dipautkan.
  - **Read:** Kad ringkasan metrik (Total Monthly Commitment, Total Active Subscriptions, Subscriptions Due This Week), dan jadual terperinci dengan lencana amaran (*Due Soon*, *Active*, *Inactive*).
  - **Update:** Butang Edit untuk mengubah kos, tarikh kitaran, atau status langganan.
  - **Delete:** Butang Delete berserta **Flux Confirmation Modal** sebelum rekod dipadam.
  - **Quick Toggle:** Butang suis aktif/tidak aktif (`toggleActive`).

---

## 4. Automation & Due Alerts
- Command Artisan: `subscriptions:check-due` (didaftarkan di `routes/console.php` mengikut `SCHEDULE_SUBSCRIPTIONS_TIME` dalam `Asia/Kuala_Lumpur`).
- Mengimbas komitmen aktif yang bakal tamat tempoh dalam masa 3 hari atau hari ini dan menghantar amaran proaktif ke Telegram.

---

## 5. Seeder
- `SubscriptionSeeder.php`: Menyediakan contoh langganan bulanan (cth: Spotify, Netflix, Sewa Rumah) dan komitmen tahunan (cth: Cukai Jalan / Domain).
