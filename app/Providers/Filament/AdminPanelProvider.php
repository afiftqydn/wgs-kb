<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Illuminate\Support\Carbon;
use Filament\Support\Colors\Color;
use Filament\Navigation\UserMenuItem;
use Illuminate\Contracts\View\View;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Filament\Widgets\RecentLoanApplicationsWidget;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Filament\Pages\Auth\LoginPage as AppCustomLoginPage;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
// use App\Filament\Widgets\OnlineUsersWidget;

use App\Http\Middleware\UpdateUserLastActivity;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->sidebarCollapsibleOnDesktop()
            ->path('admin')
            ->login(AppCustomLoginPage::class)
            ->brandLogo(asset('images/Logo.png'))
            ->brandLogoHeight('63px')
            ->breadcrumbs(false)
            // ->spa()
            ->sidebarWidth('20rem')
            ->navigationGroups([
                'Manajemen Pengajuan',
                'Data Master',
                'Manajemen POMIGOR',
                'Administrasi Sistem',
            ])

            // 1. Kustomisasi User Menu Item
            ->userMenuItems([
            //     'last_login' => UserMenuItem::make()
            //         ->label(function () {
            //             /** @var \App\Models\User $user */
            //             $user = auth()->user();

            //             // Pastikan kolom last_login_at ada dan tidak null
            //             if ($user && $user->last_login_at) {
            //                 return 'Login ' . Carbon::parse($user->last_login_at)->diffForHumans();
            //             }
            //             return 'Login baru saja';
            //         })
            //         ->icon('heroicon-o-clock')
            //         // Item ini tidak akan memiliki action, hanya sebagai display
            //         ->url(null), 
                
            //     // 2. Tombol Logout (Filament akan menanganinya secara otomatis)
                // 'logout' => UserMenuItem::make()->label('Sign Out'),
            ])
            
            ->renderHook(
                // Nama hook untuk posisi sebelum menu pengguna
                'panels::user-menu.before', 
                // Arahkan ke Blade view yang telah kita buat
                fn () => view('filament.hooks.user_name') 
            )


            // 3. Menambahkan ikon toggle tema sebelum menu pengguna
            ->renderHook(
                // Hook ini menempatkan view sebelum komponen user menu di-render
                'panels::user-menu.before',
                fn (): View => view('filament.custom.theme-toggle'),
            )


            ->colors([
                'primary' => [
                    50 =>  '#f2fbf3',
                    100 => '#e1f7e2',
                    200 => '#c4eec8',
                    300 => '#95e09c',
                    400 => '#5fc96a',
                    500 => '#39ae45',
                    600 => '#2a8f34',
                    700 => '#24712d',
                    800 => '#215a28',
                    900 => '#1d4a23',
                    950 => '#0b280f',
                ],
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                RecentLoanApplicationsWidget::class,
                // OnlineUsersWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \Illuminate\Session\Middleware\AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                UpdateUserLastActivity::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ]);
    }
}
