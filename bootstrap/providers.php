<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\LocalizationServiceProvider::class,
    Livewire\LivewireServiceProvider::class,
    Packages\Customer\CustomerServiceProvider::class,
    Packages\User\UserServiceProvider::class,
    Packages\SessionManager\SessionManagerServiceProvider::class,
    Packages\Appearance\AppearanceServiceProvider::class,
    Packages\Log\LogServiceProvider::class,
    Packages\Localization\LocalizationServiceProvider::class,
];
