<?php

use App\Livewire\Goals\GoalIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('goals', GoalIndex::class)->name('goals.index');
});

require __DIR__.'/settings.php';
