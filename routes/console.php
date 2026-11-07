<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Console Schedule Configuration
|--------------------------------------------------------------------------
| Prune audit logs exceeding the 90-day retention window daily at 2:00 AM.
*/
Schedule::command('stic:prune-audit-logs')->daily()->at('02:00');

