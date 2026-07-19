<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Recover payments that were never confirmed in-app — a lost webhook, or a
 * teacher who closed the tab instead of returning. Every 10 minutes, because a
 * paying customer locked out of the app is the one failure that cannot wait.
 * withoutOverlapping so a slow gateway cannot stack runs on top of each other.
 */
Schedule::command('subscriptions:reconcile')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
