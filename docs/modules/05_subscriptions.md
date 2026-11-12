# Modul 05: Subscriptions & Commitments Vault
Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Bina **Modul 05: Subscriptions & Commitments Vault**:
1. **Migration (`subscriptions`):**
   - `id`, `user_id` (cascade), `name`, `category` (digital/utility/insurance/government), `amount` (decimal 15,2), `billing_cycle` (monthly/yearly), `next_billing_date` (date), `wallet_id` (nullable, nullOnDelete), `auto_renew` (boolean, default: true), `is_active` (boolean, default: true), `notes` (nullable), `timestamps`, index `['user_id', 'next_billing_date', 'is_active']`.
2. **Model (`Subscription.php`):** `daysUntilDue(): int` dan `isDueSoon(int $days = 3): bool`.
3. **Artisan Command (`subscriptions:check-due`):** Semak komitmen luput dalam 3 hari atau hari ini, hantar amaran via `TelegramService`.
4. **Livewire UI (`app/Livewire/Subscriptions/SubscriptionIndex.php`):** Kad jumlah komitmen bulanan, jadual dengan lencana amaran tamat tempoh, dan form tambah komitmen.
5. **Seeder:** `SubscriptionSeeder.php` (3 langganan bulanan + 1 komitmen tahunan).