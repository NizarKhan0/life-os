<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('meetings:send-reminders')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('lifeos:send-briefing')
    ->dailyAt((string) config('lifeos.schedules.briefing', '07:30'))
    ->timezone((string) config('lifeos.timezone', 'Asia/Kuala_Lumpur'));

Schedule::command('goals:send-reminders')
    ->dailyAt((string) config('lifeos.schedules.goals', '08:00'))
    ->timezone((string) config('lifeos.timezone', 'Asia/Kuala_Lumpur'));

Schedule::command('subscriptions:check-due')
    ->dailyAt((string) config('lifeos.schedules.subscriptions', '09:00'))
    ->timezone((string) config('lifeos.timezone', 'Asia/Kuala_Lumpur'));

Schedule::command('money:check-budgets')
    ->dailyAt((string) config('lifeos.schedules.budgets', '20:00'))
    ->timezone((string) config('lifeos.timezone', 'Asia/Kuala_Lumpur'));

Schedule::command('tasks:send-reminders')
    ->dailyAt((string) config('lifeos.schedules.tasks', '21:00'))
    ->timezone((string) config('lifeos.timezone', 'Asia/Kuala_Lumpur'));

Schedule::command('reviews:send-weekly-reminder')
    ->weeklyOn((int) config('lifeos.schedules.reviews_day', 0), (string) config('lifeos.schedules.reviews', '20:00'))
    ->timezone((string) config('lifeos.timezone', 'Asia/Kuala_Lumpur'));
