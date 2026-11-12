<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BrainDump;
use App\Models\User;
use App\Models\WeeklyReview;
use App\Services\WeeklyReviewService;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(WeeklyReviewService $reviewService): void
    {
        $user = User::query()->first() ?? User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 1. Rekod review minggu lepas
        $lastWeekStart = $reviewService->weekStart()->subWeek();

        WeeklyReview::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'week_start_date' => $lastWeekStart->toDateString(),
            ],
            [
                'wins' => 'Berjaya menyiapkan Modul 01 hingga 03 LifeOS dan konsisten membaca setiap hari.',
                'challenges' => 'Terlalu banyak gangguan notifikasi telefon semasa sesi deep work petang.',
                'improvements' => 'Aktifkan mod Fokus antara 2-4 petang dan rancang 3 task utama setiap malam.',
                'metrics_snapshot' => $reviewService->buildSnapshot($user->id, $lastWeekStart),
            ]
        );

        // 2. Dua nota Brain Dump
        BrainDump::query()->where('user_id', $user->id)->delete();

        BrainDump::query()->create([
            'user_id' => $user->id,
            'content' => 'Idea: tambah carta trend perbelanjaan mingguan dalam dashboard LifeOS.',
            'is_processed' => false,
        ]);

        BrainDump::query()->create([
            'user_id' => $user->id,
            'content' => 'Ingat semak semula pelan takaful sebelum tarikh pembaharuan bulan depan.',
            'is_processed' => false,
        ]);
    }
}
