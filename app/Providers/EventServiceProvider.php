<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Import Event dan Listener Anda
use Illuminate\Auth\Events\Login;
use App\Listeners\LogSuccessfulLogin;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // Daftarkan event Login dan listener-nya di sini
        Login::class => [
            LogSuccessfulLogin::class,
        ],
        // Anda bisa menambahkan listener untuk event Logout di sini juga jika perlu
        // \Illuminate\Auth\Events\Logout::class => [
        //     \App\Listeners\LogUserLogout::class, // Buat listener LogUserLogout jika perlu
        // ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // Defaultnya false, Anda bisa set true jika ingin auto-discovery
    }
}