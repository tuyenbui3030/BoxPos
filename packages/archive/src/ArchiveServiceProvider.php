<?php

namespace Packages\Archive;

use Illuminate\Support\ServiceProvider;

class ArchiveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
}
