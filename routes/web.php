<?php

use App\Livewire\Automation\AutomationHub;
use App\Livewire\Dashboard;
use App\Livewire\Goals\GoalIndex;
use App\Livewire\Meetings\MeetingIndex;
use App\Livewire\Money\MoneyIndex;
use App\Livewire\Reviews\WeeklyReviewIndex;
use App\Livewire\Subscriptions\SubscriptionIndex;
use App\Livewire\Tasks\TaskHabitIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('automation', AutomationHub::class)->name('automation.index');
    Route::livewire('goals', GoalIndex::class)->name('goals.index');
    Route::livewire('meetings', MeetingIndex::class)->name('meetings.index');
    Route::livewire('money', MoneyIndex::class)->name('money.index');
    Route::livewire('tasks', TaskHabitIndex::class)->name('tasks.index');
    Route::livewire('subscriptions', SubscriptionIndex::class)->name('subscriptions.index');
    Route::livewire('reviews', WeeklyReviewIndex::class)->name('reviews.index');
});

require __DIR__.'/settings.php';
