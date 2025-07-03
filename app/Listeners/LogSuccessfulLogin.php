<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
// `activity()` adalah helper global, jadi tidak perlu di-import secara spesifik.

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        // Constructor bisa dikosongkan
    }

    /**
     * Handle the user login event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            /** @var \App\Models\User $user */
            $user = $event->user;

            // 1. Update waktu login terakhir
            $user->last_login_at = now();
            $user->save();

            // 2. Catat aktivitas dengan cara yang benar
            activity()
               ->causedBy($user) // Siapa yang menyebabkan aktivitas ini
               ->performedOn($user) // Aktivitas ini dilakukan terhadap siapa/apa
               ->log("Pengguna '{$user->name}' (ID: {$user->id}) berhasil login."); // Deskripsi aktivitas
        }
    }
}
