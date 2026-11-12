<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MoneySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 1. 3 Wallet: Maybank / TNG / Tunai
        $maybank = Wallet::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Maybank'],
            [
                'type' => 'bank',
                'icon' => '🏦',
                'starting_balance' => 3500.00,
                'currency' => 'MYR',
            ]
        );

        $tng = Wallet::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'TNG eWallet'],
            [
                'type' => 'ewallet',
                'icon' => '📱',
                'starting_balance' => 250.00,
                'currency' => 'MYR',
            ]
        );

        $tunai = Wallet::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Tunai'],
            [
                'type' => 'cash',
                'icon' => '💵',
                'starting_balance' => 150.00,
                'currency' => 'MYR',
            ]
        );

        // 2. Kategori Asas (Expense & Income)
        $makananCategory = Category::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Makanan & Minuman', 'type' => 'expense'],
            ['icon' => '🍽️', 'color' => 'amber']
        );

        $pengangkutanCategory = Category::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Pengangkutan & Tol', 'type' => 'expense'],
            ['icon' => '🚗', 'color' => 'sky']
        );

        $utilitiCategory = Category::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Utiliti & Internet', 'type' => 'expense'],
            ['icon' => '⚡', 'color' => 'violet']
        );

        $gajiCategory = Category::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Gaji & Freelance', 'type' => 'income'],
            ['icon' => '💼', 'color' => 'emerald']
        );

        // 3. 5 Transaksi Contoh
        Transaction::query()->where('user_id', $user->id)->delete();

        $today = Carbon::now('Asia/Kuala_Lumpur');

        // Transaksi 1: Pendapatan (Gaji masuk Maybank)
        Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $maybank->id,
            'category_id' => $gajiCategory->id,
            'type' => 'income',
            'amount' => 4500.00,
            'transaction_date' => $today->copy()->startOfMonth()->addDays(1)->toDateString(),
            'notes' => 'Gaji bulanan & projek pembangunan sistem',
            'target_wallet_id' => null,
        ]);

        // Transaksi 2: Perbelanjaan (Makanan guna TNG)
        Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $tng->id,
            'category_id' => $makananCategory->id,
            'type' => 'expense',
            'amount' => 45.50,
            'transaction_date' => $today->copy()->subDays(3)->toDateString(),
            'notes' => 'Makan tengah hari & kopi mesyuarat',
            'target_wallet_id' => null,
        ]);

        // Transaksi 3: Perbelanjaan (Barangan dapur & makanan guna Maybank)
        Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $maybank->id,
            'category_id' => $makananCategory->id,
            'type' => 'expense',
            'amount' => 380.00,
            'transaction_date' => $today->copy()->subDays(2)->toDateString(),
            'notes' => 'Belanja barang dapur mingguan',
            'target_wallet_id' => null,
        ]);

        // Transaksi 4: Perbelanjaan (Minyak & tol guna Tunai)
        Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $tunai->id,
            'category_id' => $pengangkutanCategory->id,
            'type' => 'expense',
            'amount' => 60.00,
            'transaction_date' => $today->copy()->subDay()->toDateString(),
            'notes' => 'Isi minyak RON95',
            'target_wallet_id' => null,
        ]);

        // Transaksi 5: Pindahan / Transfer (Topup TNG daripada Maybank)
        Transaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $maybank->id,
            'category_id' => null,
            'type' => 'transfer',
            'amount' => 200.00,
            'transaction_date' => $today->toDateString(),
            'notes' => 'Topup baki TNG eWallet',
            'target_wallet_id' => $tng->id,
        ]);

        // 4. 1 Bajet Bulanan (Makanan & Minuman)
        Budget::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $makananCategory->id,
                'month_year' => $today->format('Y-m'),
            ],
            [
                'amount' => 600.00,
            ]
        );
    }
}
