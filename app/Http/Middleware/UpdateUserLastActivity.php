<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- INI BAGIAN YANG PENTING
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah ada pengguna yang sedang login
        if (Auth::check()) {
            $user = Auth::user();
            // Update timestamp aktivitas terakhir
            $user->last_activity_at = now();
            $user->save();
        }

        return $next($request);
    }
}
