<?php

use App\Livewire\Goals\GoalIndex;
use App\Livewire\Meetings\MeetingIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('goals', GoalIndex::class)->name('goals.index');
    Route::livewire('meetings', MeetingIndex::class)->name('meetings.index');
});

require __DIR__.'/settings.php';
