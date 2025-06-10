<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue; // <-- TAMBAHKAN INI
use Illuminate\Queue\InteractsWithQueue;   // <-- TAMBAHKAN INI
use Spatie\Activitylog\Facades\Activity;
use App\Models\User;

class LogSuccessfulLogin implements ShouldQueue // <-- IMPLEMENTASIKAN ShouldQueue
{
    use InteractsWithQueue; // <-- GUNAKAN TRAIT INI

    public function __construct()
    {
        //
    }

    public function handle(Login $event): void
    {
        // dd($event->user, get_class($event->user)); // Hapus dd() jika masih ada

        if ($event->user && $event->user instanceof User) {
            $user = $event->user;

            Activity::log("Pengguna '{$user->name}' (ID: {$user->id}, Email: {$user->email}) berhasil login.")
                ->causedBy($user)
                ->performedOn($user)
                ->log('Authentication:Login');
        } else {
            Activity::log("Sebuah event Login terjadi tanpa informasi pengguna yang jelas.")
                ->log('Authentication:LoginAttempt');
        }
    }
}