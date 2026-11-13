# Modul 03: Money & Expense Management

Rujuk `AGENT.md` dan `docs/ARCHITECTURE.md`. Modul ini menguruskan pelbagai akaun dompet kewangan (*multi-wallet*), aliran tunai transaksi masuk/keluar, dan kawalan bajet bulanan setiap kategori.

---

## 1. Migrations & Schema
- `wallets`:
  - `id`, `user_id` (foreignId constrained cascade), `name` (string), `icon` (string 10, nullable: emoji cth: `🏦`, `📱`, `💵`), `type` (enum: 'bank', 'ewallet', 'cash', 'investment', default: 'bank'), `starting_balance` (decimal 15,2, default: 0), `currency` (string 3, default: 'MYR'), `timestamps`.
- `categories`:
  - `id`, `user_id` (foreignId constrained cascade), `name` (string), `type` (enum: 'expense', 'income'), `icon` (string, nullable), `color` (string, nullable), `timestamps`.
  - Terasing mengikut pengguna (`where('user_id', Auth::id())`).
- `transactions`:
  - `id`, `user_id` (foreignId constrained cascade), `wallet_id` (foreignId constrained cascade), `category_id` (foreignId nullable nullOnDelete), `type` (enum: 'expense', 'income', 'transfer'), `amount` (decimal 15,2), `transaction_date` (date), `notes` (nullable text), `target_wallet_id` (foreignId nullable nullOnDelete untuk transaksi pindahan), `timestamps`.
  - Indeks kompaun: `['user_id', 'transaction_date']`.
- `budgets`:
  - `id`, `user_id` (foreignId constrained cascade), `category_id` (foreignId constrained cascade), `amount` (decimal 15,2), `month_year` (string 7, format: 'YYYY-MM', cth: '2026-11'), `timestamps`.
  - Kekunci unik kompaun: `['user_id', 'category_id', 'month_year']`.

---

## 2. Models & Financial Calculations
- `Wallet.php`:
  - `currentBalance(): float` : Mengira `starting_balance` + (semua income + transfer masuk) - (semua expense + transfer keluar).
  - `displayIcon(): string` : Memulangkan ikon emoji pilihan atau default mengikut jenis akaun.
- `Budget.php`:
  - `spentAmount(): float` : Jumlah perbelanjaan kategori tersebut dalam bulan `month_year`.
  - `progressPercentage(): float` : Peratusan bajet yang telah dibelanjakan.
  - `isOverbudget(): bool` : Melebihi 100% daripada had siling.
  - `isWarning(): bool` : Mencapai 80% sehingga 100% daripada bajet.

---

## 3. UI/UX & Livewire Implementation (`app/Livewire/Money/MoneyIndex.php`)
- **UI Language:** **100% Professional English** (tiada teks Melayu seperti "Dompet", "Baki", "Perbelanjaan", "Transaksi").
- **MANDATORY Full CRUD On All 4 Entities:**
  1. **Wallets (Full CRUD):**
     - Add Wallet (dengan emoji picker/icon), kad baki, Edit Wallet, dan Delete Wallet (berserta Flux confirmation modal).
  2. **Categories (Full CRUD):**
     - Add Category, senarai kategori kustom pengguna, Edit Category, dan Delete Category (dengan Flux confirmation modal).
  3. **Budgets (Full CRUD):**
     - Set Budget Bulanan, kad visual progress bar (hijau <80%, kuning 80-99%, merah >=100%), Edit Budget, dan Delete Budget (dengan Flux confirmation modal).
  4. **Transactions (Full CRUD):**
     - Quick Transaction modal pantas, jadual transaksi terkini dengan lencana jenis, butang Edit Transaction, dan butang Delete Transaction (dengan Flux confirmation modal).

---

## 4. Automation & Budget Alerts
- Command Artisan: `money:check-budgets` (didaftarkan di `routes/console.php` mengikut `SCHEDULE_BUDGETS_TIME` dalam `Asia/Kuala_Lumpur`).
- Mengimbas penggunaan bajet semasa dan menghantar amaran Telegram sekiranya kategori mencecah **80% (Yellow Alert)** atau **100% (Red/Overbudget Alert)**.

---

## 5. Seeder
- `MoneySeeder.php`: Menyediakan 3 dompet contoh (Maybank `🏦`, Touch 'n Go `📱`, Tunai `💵`), kategori perbelanjaan & pendapatan asas, contoh transaksi aliran tunai, dan 1 bajet bulanan.
