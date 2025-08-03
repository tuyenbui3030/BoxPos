<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StoreCurrentPath
{
    public function handle(Request $request, Closure $next)
    {
        // Only store path for GET requests that are not Livewire updates
        if ($request->isMethod('GET') && !$request->is('livewire/*')) {
            session(['current_path' => $request->path()]);
        }

        return $next($request);
    }
}