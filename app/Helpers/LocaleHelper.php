<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('localized_route')) {
    /**
     * Generate a localized route URL
     */
    function localized_route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $locale = app()->getLocale();

        // Check if localized route exists
        $localizedName = "locale.$name";
        if (Route::has($localizedName)) {
            $parameters = array_merge(['locale' => $locale], $parameters);
            return route($localizedName, $parameters, $absolute);
        }

        // Fallback to regular route
        if (Route::has($name)) {
            return route($name, $parameters, $absolute);
        }

        // If neither exists, return a localized URL manually
        $url = url("/$locale/" . ltrim($name, '/'));
        return $absolute ? $url : parse_url($url, PHP_URL_PATH);
    }
}

if (!function_exists('is_current_route')) {
    /**
     * Check if current route matches (including localized versions)
     */
    function is_current_route(string $routeName): bool
    {
        $currentRoute = request()->route();
        if (!$currentRoute) {
            return false;
        }

        $currentRouteName = $currentRoute->getName();

        // Check exact match
        if ($currentRouteName === $routeName) {
            return true;
        }

        // Check localized version
        if ($currentRouteName === "locale.$routeName") {
            return true;
        }

        // Check if current is localized and we're checking non-localized
        if (str_starts_with($currentRouteName, 'locale.') &&
            substr($currentRouteName, 7) === $routeName) {
            return true;
        }

        return false;
    }
}
