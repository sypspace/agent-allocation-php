<?php

use App\Jobs\AssignAgent;
use App\Jobs\FallbackRoomAssignment;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::job(FallbackRoomAssignment::class)->withoutOverlapping()->everyFiveMinutes();
