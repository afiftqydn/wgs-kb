<?php

namespace App\Filament\Pages\Auth; // PASTIKAN NAMESPACE INI SESUAI DENGAN LOKASI FILE

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class LoginPage extends BaseLogin // Nama kelas bisa CustomLoginPage atau LoginPage
{
    /**
     * Override method ini untuk menggunakan brand logo kustom Anda
     * yang spesifik untuk halaman login.
     */
    protected function getBrandLogo(): ?Htmlable
    {
        dd('Custom Login Page - getBrandLogo() executed'); // Tes di sini
        return view('filament.auth.custom_login_brand'); 
    }

    /**
     * Override method ini jika Anda ingin mengubah atau menghilangkan 
     * heading default "Sign in".
     */
    public function getHeading(): string | Htmlable
    {
        // Contoh: Menghilangkan heading
        return ''; 

        // Contoh: Mengubah heading
        // return 'Selamat Datang di Aplikasi WGS';
    }

    // Anda juga bisa meng-override subheading jika perlu
    // public function getSubheading(): string | Htmlable | null
    // {
    //     return 'Silakan masuk untuk melanjutkan.';
    // }
}