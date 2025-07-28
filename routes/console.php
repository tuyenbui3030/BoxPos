<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register BoxPos seeding command
// Artisan::command('boxpos:seed', \App\Console\Commands\SeedingCommand::class);
