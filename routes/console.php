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
    ->dailyAt('07:30')
    ->timezone('Asia/Kuala_Lumpur');

Schedule::command('goals:send-reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Kuala_Lumpur');

Schedule::command('money:check-budgets')
    ->dailyAt('20:00')
    ->timezone('Asia/Kuala_Lumpur');

Schedule::command('tasks:send-reminders')
    ->dailyAt('21:00')
    ->timezone('Asia/Kuala_Lumpur');
