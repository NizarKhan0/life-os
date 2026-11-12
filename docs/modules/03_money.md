# Modul 03: Money & Budget Management
Rujuk AGENTE.md` dan `docs/ARCHITECTURE.md`. Bina **Modul 03: Money Management**:
1. **Migrations:**
   - `wallets`: `id`, `user_id` (cascade), `name`, `type` (bank/ewallet/cash), `starting_balance` (decimal 15,2), `currency` (default: 'MYR'), `timestamps`.
   - `categories`: `id`, `user_id` (nullable, cascade), `name`, `type` (enum: expense/income), `icon` (nullable), `color` (nullable), `timestamps`.
   - `transactions`: `id`, `user_id` (cascade), `wallet_id` (cascade), `category_id` (nullOnDelete), `type` (enum: expense/income/transfer), `amount` (decimal 15,2), `transaction_date` (date), `notes` (nullable), `target_wallet_id` (nullable, nullOnDelete), `timestamps`, index `['user_id', 'transaction_date']`.
   - `budgets`: `id`, `user_id` (cascade), `category_id` (cascade), `amount` (decimal 15,2), `month_year` (string 7, cth: '2026-11'), `timestamps`, unique `['user_id', 'category_id', 'month_year']`.
2. **Models (`Wallet.php`, `Budget.php`):**
   - `Wallet::currentBalance(): float` dan `Budget::spentAmount(): float` / `progressPercentage(): float`.
3. **Livewire UI (`app/Livewire/Money/MoneyIndex.php`):**
   - Grid kad baki setiap dompet & Net Worth, progress bar bajet bulanan (hijau/kuning/merah), jadual transaksi terkini, dan Quick Transaction Modal bawah 10 saat.
4. **Seeder:** `MoneySeeder.php` (3 wallet: Maybank/TNG/Tunai, kategori asas, 5 transaksi, 1 bajet).