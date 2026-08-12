<?php

use App\Http\Controllers\TeacherServiceMarkPrintController;
use App\Livewire\ServiceMarks\CalculationCreate;
use App\Livewire\ServiceMarks\CalculationShow;
use App\Livewire\ServiceMarks\MarkSettings;
use App\Livewire\ServiceMarks\RankingIndex;
use App\Livewire\ServiceMarks\RankingShow;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('teacher-service-marks')
    ->name('service-marks.')
    ->group(function () {
        Route::get('/', RankingIndex::class)
            ->name('index');

        Route::get('/ranking/{zone}/{employee}', RankingShow::class)
            ->name('ranking.show');

        Route::get('/create', CalculationCreate::class)
            ->name('create');

        Route::get('/settings', MarkSettings::class)
            ->name('settings');

        /*
         * Saved calculation snapshot routes.
         */
        Route::get('/calculations/{calculation}', CalculationShow::class)
            ->whereNumber('calculation')
            ->name('show');

        Route::get(
            '/calculations/{calculation}/print',
            TeacherServiceMarkPrintController::class
        )
            ->whereNumber('calculation')
            ->name('print');
    });
