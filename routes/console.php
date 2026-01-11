<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly Reports - Generate every Sunday at 8:00 AM
Schedule::command('reports:generate-weekly')
    ->weeklyOn(0, '08:00') // 0 = Sunday
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/weekly-reports.log'));
