<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | LifeOS Timezone for Automations
    |--------------------------------------------------------------------------
    */
    'timezone' => env('LIFEOS_TIMEZONE', 'Asia/Kuala_Lumpur'),

    /*
    |--------------------------------------------------------------------------
    | LifeOS Scheduled Reminders & Briefing Timings (24-hour format HH:MM)
    |--------------------------------------------------------------------------
    */
    'schedules' => [
        'briefing' => env('SCHEDULE_BRIEFING_TIME', '07:30'),
        'goals' => env('SCHEDULE_GOALS_TIME', '08:00'),
        'subscriptions' => env('SCHEDULE_SUBSCRIPTIONS_TIME', '09:00'),
        'budgets' => env('SCHEDULE_BUDGETS_TIME', '20:00'),
        'tasks' => env('SCHEDULE_TASKS_TIME', '21:00'),
        'reviews' => env('SCHEDULE_REVIEWS_TIME', '20:00'),
        'reviews_day' => (int) env('SCHEDULE_REVIEWS_DAY', 0), // 0 = Sunday
    ],
];
