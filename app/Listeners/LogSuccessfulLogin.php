<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue; // Opsional: jika Anda ingin listener berjalan di antrian
use Illuminate\Queue\InteractsWithQueue;   // Opsional: jika Anda ingin listener berjalan di antrian
use Spatie\Activitylog\Facades\Activity;   // Import Facade Activity
use App\Models\User;                       // Import model User jika perlu type-hinting lebih spesifik

class LogSuccessfulLogin // Hapus "implements ShouldQueue" jika tidak ingin menggunakan antrian untuk listener ini
{
    // Hapus "use InteractsWithQueue;" jika tidak mengimplementasikan ShouldQueue

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event): void
    {
        if ($event->user && $event->user instanceof User) { // Pastikan user ada dan merupakan instance dari model User Anda
            $user = $event->user;
            Activity::log("Pengguna '{$user->name}' (ID: {$user->id}, Email: {$user->email}) berhasil login.")
                ->causedBy($user) // Pengguna yang melakukan aksi (login)
                ->performedOn($user) // Aktivitas dilakukan pada pengguna itu sendiri (opsional, bisa juga pada objek lain jika relevan)
                ->log('Authentication:Login'); // Nama log yang lebih spesifik
                // Anda juga bisa menambahkan properti kustom jika perlu:
                // ->withProperties(['ip_address' => request()->ip()]) 
        } else {
            // Kasus di mana event Login mungkin tidak memiliki user yang diharapkan
            // (jarang terjadi untuk event Login standar, tapi baik untuk pencegahan)
            Activity::log("Sebuah event Login terjadi tanpa informasi pengguna yang jelas.")
                ->log('Authentication:LoginAttempt');
        }
    }
}