<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | This option controls the default theme for the application.
    | Supported themes: "light", "dark"
    |
    */
    'default' => env('APP_THEME', 'light'),

    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    |
    | List of available themes for the application.
    |
    */
    'themes' => [
        'light' => [
            'name' => 'Light Theme',
            'description' => 'A clean and bright theme',
        ],
        'dark' => [
            'name' => 'Dark Theme', 
            'description' => 'A dark theme that is easy on the eyes',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Storage
    |--------------------------------------------------------------------------
    |
    | How theme preferences should be stored.
    | Supported: "session", "cookie", "database"
    |
    */
    'storage' => env('THEME_STORAGE', 'session'),

    /*
    |--------------------------------------------------------------------------
    | Theme Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Settings for theme cookie when using cookie storage.
    |
    */
    'cookie' => [
        'name' => 'app_theme',
        'expire' => 60 * 24 * 365, // 1 year in minutes
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => true,
    ],
];
