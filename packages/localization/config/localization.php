<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | List of locales supported by the application.
    |
    */
    'supported_locales' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr',
        ],
        'vi' => [
            'name' => 'Vietnamese', 
            'native' => 'Tiếng Việt',
            'flag' => '🇻🇳',
            'direction' => 'ltr',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale to use when no locale is specified.
    |
    */
    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale to use when a translation is not found.
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | The session key used to store the current locale.
    |
    */
    'session_key' => 'locale',

    /*
    |--------------------------------------------------------------------------
    | Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Settings for storing locale preference in cookies.
    |
    */
    'cookie' => [
        'name' => 'locale',
        'expire' => 60 * 24 * 365, // 1 year in minutes
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => false,
    ],
];
