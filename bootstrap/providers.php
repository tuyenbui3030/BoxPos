<?php

return [
    App\Providers\AppServiceProvider::class,
    Packages\Customer\CustomerServiceProvider::class,
    Packages\User\UserServiceProvider::class,
    Packages\SessionManager\SessionManagerServiceProvider::class,
    Packages\Appearance\AppearanceServiceProvider::class,
    Packages\Log\LogServiceProvider::class,
];
