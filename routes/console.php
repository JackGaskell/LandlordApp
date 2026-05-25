<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks (requires cron: * * * * * php artisan schedule:run)
|--------------------------------------------------------------------------
*/
Schedule::command('rent:run-collection-cycle')
    ->dailyAt(config('landlord.collection.cycle_time', '07:00'))
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->description('Sync rent periods and statuses for active tenants');

Schedule::command('rent:dispatch-reminders')
    ->dailyAt(config('landlord.reminders.dispatch_time', '08:00'))
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->description('Queue rent reminder emails for landlords');
