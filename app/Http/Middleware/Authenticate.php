<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        $path = ltrim($request->getPathInfo(), '/');

        if (
            $request->expectsJson() ||
            $request->wantsJson() ||
            $request->is('api/*') ||
            str_contains($path, '/api/') ||
            str_starts_with($path, 'api/')
        ) {
            return null;
        }

        return route('login');
    }
}
