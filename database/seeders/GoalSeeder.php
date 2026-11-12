<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class GoalSeeder extends Seeder
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

        // 1. Matlamat Kewangan (Financial Goal)
        $financialGoal = Goal::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'Tabung Dana Kecemasan 6 Bulan',
            ],
            [
                'description' => 'Mengumpul simpanan kecemasan berjumlah RM 15,000 di dalam akaun berisiko rendah.',
                'category' => 'financial',
                'target_date' => Carbon::now()->addMonths(6)->toDateString(),
                'target_value' => 15000.00,
                'current_value' => 6500.00,
                'unit' => 'RM',
                'status' => 'in_progress',
            ]
        );

        $financialGoal->milestones()->delete();
        $financialGoal->milestones()->createMany([
            [
                'title' => 'Capai RM 3,000 pertama (Asas Kecemasan)',
                'due_date' => Carbon::now()->subMonth()->toDateString(),
                'is_completed' => true,
                'completed_at' => Carbon::now()->subMonth(),
            ],
            [
                'title' => 'Capai RM 7,500 (50% Sasaran)',
                'due_date' => Carbon::now()->addMonth()->toDateString(),
                'is_completed' => false,
                'completed_at' => null,
            ],
            [
                'title' => 'Lengkapkan RM 15,000 penuh',
                'due_date' => Carbon::now()->addMonths(6)->toDateString(),
                'is_completed' => false,
                'completed_at' => null,
            ],
        ]);

        // 2. Matlamat Peribadi (Personal Goal)
        $personalGoal = Goal::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'Siapkan Sistem LifeOS & Baca 12 Buku Teknikal',
            ],
            [
                'description' => 'Meningkatkan kemahiran kejuruteraan perisian Laravel 13, Livewire v4, dan amalan produktiviti harian.',
                'category' => 'personal',
                'target_date' => Carbon::now()->addMonths(3)->toDateString(),
                'target_value' => null,
                'current_value' => 0,
                'unit' => null,
                'status' => 'in_progress',
            ]
        );

        $personalGoal->milestones()->delete();
        $personalGoal->milestones()->createMany([
            [
                'title' => 'Selesaikan Modul 01: Goals & Milestones',
                'due_date' => Carbon::now()->addDays(3)->toDateString(),
                'is_completed' => true,
                'completed_at' => Carbon::now(),
            ],
            [
                'title' => 'Integrasi penuh notifikasi Telegram Bot API',
                'due_date' => Carbon::now()->addDays(7)->toDateString(),
                'is_completed' => false,
                'completed_at' => null,
            ],
            [
                'title' => 'Habiskan bacaan 4 buku seni bina perisian pertama',
                'due_date' => Carbon::now()->addMonth()->toDateString(),
                'is_completed' => false,
                'completed_at' => null,
            ],
        ]);
    }
}
