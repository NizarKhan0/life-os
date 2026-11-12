<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AutomationLog;
use App\Models\User;
use App\Services\BriefingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AutomationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            return;
        }

        $briefingService = app(BriefingService::class);
        $now = Carbon::now('Asia/Kuala_Lumpur');

        foreach ($users as $user) {
            $previewMessage = $briefingService->generateForUser($user);

            AutomationLog::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => 'morning_briefing',
                    'triggered_by' => 'scheduler',
                ],
                [
                    'channel' => 'telegram',
                    'status' => 'simulated',
                    'message_preview' => $previewMessage,
                    'sent_at' => $now->copy()->setTime(7, 30, 0),
                ]
            );

            AutomationLog::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => 'morning_briefing',
                    'triggered_by' => 'manual',
                ],
                [
                    'channel' => 'telegram',
                    'status' => 'simulated',
                    'message_preview' => $previewMessage,
                    'sent_at' => $now->copy()->subMinutes(15),
                ]
            );
        }
    }
}
