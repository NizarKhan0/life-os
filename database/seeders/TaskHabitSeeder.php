<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Habit;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TaskHabitSeeder extends Seeder
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

        $today = Carbon::today();

        // 1. 3 Task Contoh
        Task::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'Lengkapkan ujian komponen Livewire Modul 04 (Tasks & Habits)',
            ],
            [
                'description' => 'Pastikan fungsi toggle satu-klik, penapis Hari Ini / Penting / Semua, dan pengiraan streak berjalan lancar.',
                'priority' => 'urgent',
                'status' => 'todo',
                'due_date' => $today->toDateString(),
                'completed_at' => null,
            ]
        );

        Task::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'Semak penyata perbelanjaan bulanan & kemas kini bajet LifeOS',
            ],
            [
                'description' => 'Bandingkan transaksi Maybank dan TNG eWallet bagi bulan semasa.',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => $today->toDateString(),
                'completed_at' => null,
            ]
        );

        Task::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'Susun nota dokumentasi teknikal dalam folder docs/',
            ],
            [
                'description' => 'Kemaskan panduan aliran kerja CI/CD dan seni bina LifeOS.',
                'priority' => 'medium',
                'status' => 'completed',
                'due_date' => $today->copy()->subDay()->toDateString(),
                'completed_at' => Carbon::now()->subHours(3),
            ]
        );

        // 2. 3 Habit Contoh + HabitLogs untuk paparan Streak
        $habitBaca = Habit::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Baca Buku / Artikel Teknikal (20 Minit)',
            ],
            [
                'frequency' => 'daily',
                'target_per_day' => 1,
                'color' => 'emerald',
                'is_active' => true,
            ]
        );

        // Bina 7 hari berturut-turut (termasuk hari ini) => 🔥 7 hari
        $habitBaca->logs()->delete();
        for ($i = 0; $i < 7; $i++) {
            $habitBaca->logs()->create([
                'logged_date' => $today->copy()->subDays($i)->toDateString(),
                'count' => 1,
            ]);
        }

        $habitSenaman = Habit::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Senaman & Regangan Pagi (30 Minit)',
            ],
            [
                'frequency' => 'daily',
                'target_per_day' => 1,
                'color' => 'sky',
                'is_active' => true,
            ]
        );

        // Bina 3 hari berturut-turut sehingga semalam (belum tanda hari ini) => 🔥 3 hari
        $habitSenaman->logs()->delete();
        for ($i = 1; $i <= 3; $i++) {
            $habitSenaman->logs()->create([
                'logged_date' => $today->copy()->subDays($i)->toDateString(),
                'count' => 1,
            ]);
        }

        $habitDeepWork = Habit::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Sesi Deep Work Tanpa Gangguan (2 Jam)',
            ],
            [
                'frequency' => 'weekdays',
                'target_per_day' => 1,
                'color' => 'violet',
                'is_active' => true,
            ]
        );

        $habitDeepWork->logs()->delete();
        $habitDeepWork->logs()->create([
            'logged_date' => $today->copy()->subDay()->toDateString(),
            'count' => 1,
        ]);
    }
}
