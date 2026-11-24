<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('meta:dispatch')->everyMinute()->withoutOverlapping();
Schedule::call(function () {
    \App\Models\MetaConversionEvent::where('created_at', '<', now()->subDays(config('meta.retention_days')))->update(['payload' => null]);
    \App\Models\Order::where('created_at', '<', now()->subDays(config('meta.retention_days')))->whereNotNull('tracking_context')->update(['tracking_context' => null]);
})->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
