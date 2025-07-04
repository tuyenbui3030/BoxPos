<?php

namespace Packages\Employee;

use Illuminate\Support\ServiceProvider;

class EmployeeServiceProvider extends ServiceProvider
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
