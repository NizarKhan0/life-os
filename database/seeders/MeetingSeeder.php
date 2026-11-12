<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MeetingSeeder extends Seeder
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

        // 1. Meeting 30 minit dari sekarang
        $soonStart = Carbon::now()->addMinutes(30)->second(0);
        $soonMeeting = Meeting::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'Sesi Semakan Seni Bina LifeOS & Sprint Sync',
            ],
            [
                'description' => 'Semakan kemajuan Modul 01 & Modul 02 bersama ujian integrasi peringatan Telegram.',
                'location_or_link' => 'https://meet.google.com/lifeos-sprint-sync',
                'start_time' => $soonStart,
                'end_time' => $soonStart->copy()->addMinutes(45),
                'status' => 'scheduled',
            ]
        );

        $soonMeeting->reminders()->delete();
        $soonMeeting->reminders()->create([
            'remind_before_minutes' => 15,
            'channel' => 'telegram',
            'is_sent' => false,
            'sent_at' => null,
        ]);

        // 2. Meeting esok
        $tomorrowStart = Carbon::now()->addDay()->setTime(10, 0, 0);
        $tomorrowMeeting = Meeting::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'title' => 'Perbincangan Strategi Kewangan & Bajet Bulanan',
            ],
            [
                'description' => 'Menyusun sasaran aliran tunai, agihan dompet (Wallets), dan semakan langganan bulanan.',
                'location_or_link' => 'Bilik Mesyuarat Utama / Zoom',
                'start_time' => $tomorrowStart,
                'end_time' => $tomorrowStart->copy()->addHour(),
                'status' => 'scheduled',
            ]
        );

        $tomorrowMeeting->reminders()->delete();
        $tomorrowMeeting->reminders()->create([
            'remind_before_minutes' => 30,
            'channel' => 'telegram',
            'is_sent' => false,
            'sent_at' => null,
        ]);
    }
}
