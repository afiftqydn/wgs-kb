<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Jika request mengharapkan JSON (misalnya dari API), jangan redirect.
        // Jika tidak, arahkan ke halaman login dari panel admin Filament.
        return $request->expectsJson()
            ? null
            : Filament::getPanel('admin')->getLoginUrl();
    }
}
