<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SubscriptionSeeder extends Seeder
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

        $maybank = Wallet::query()->where('user_id', $user->id)->where('name', 'Maybank')->first();
        $tng = Wallet::query()->where('user_id', $user->id)->where('name', 'TNG eWallet')->first();

        $today = Carbon::today();

        // 1. Langganan Bulanan 1 (Hampir tamat tempoh: 2 hari lagi)
        Subscription::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'TIME Fibre Home 500Mbps',
            ],
            [
                'category' => 'utility',
                'amount' => 139.00,
                'billing_cycle' => 'monthly',
                'next_billing_date' => $today->copy()->addDays(2)->toDateString(),
                'wallet_id' => $maybank?->id,
                'auto_renew' => true,
                'is_active' => true,
                'notes' => 'Jalur lebar internet kediaman (Auto-debit Maybank).',
            ]
        );

        // 2. Langganan Bulanan 2 (Tamat tempoh: Hari Ini!)
        Subscription::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'ChatGPT Plus / Claude Pro AI',
            ],
            [
                'category' => 'digital',
                'amount' => 95.00,
                'billing_cycle' => 'monthly',
                'next_billing_date' => $today->toDateString(),
                'wallet_id' => $maybank?->id,
                'auto_renew' => true,
                'is_active' => true,
                'notes' => 'Langganan produktiviti pembangunan perisian & AI.',
            ]
        );

        // 3. Langganan Bulanan 3 (15 hari lagi)
        Subscription::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Takaful Perubatan & Hayat',
            ],
            [
                'category' => 'insurance',
                'amount' => 220.00,
                'billing_cycle' => 'monthly',
                'next_billing_date' => $today->copy()->addDays(15)->toDateString(),
                'wallet_id' => $maybank?->id,
                'auto_renew' => true,
                'is_active' => true,
                'notes' => 'Perlindungan kad perubatan bulanan.',
            ]
        );

        // 4. Komitmen Tahunan (Roadtax & Insurans Kenderaan / Domain Server)
        Subscription::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Cukai Jalan (Roadtax) & Insurans Kereta Tahunan',
            ],
            [
                'category' => 'government',
                'amount' => 1200.00,
                'billing_cycle' => 'yearly',
                'next_billing_date' => $today->copy()->addMonths(4)->toDateString(),
                'wallet_id' => $tng?->id,
                'auto_renew' => false,
                'is_active' => true,
                'notes' => 'Pembaharuan tahunan melalui MyJPJ / BJAK.',
            ]
        );
    }
}

