<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class LoginPage extends BaseLogin
{
    /**
     * Menampilkan logo custom di halaman login.
     */
    // protected function getBrandLogo(): ?Htmlable
    // {
    //     // Mengembalikan view sebagai Htmlable (HtmlString)
    //     return new HtmlString(view('filament.auth.custom_login_brand')->render());
    // }

    /**
     * Menampilkan heading pada halaman login.
     * Anda bisa kosongkan atau ganti teksnya sesuai kebutuhan.
     */
    public function getHeading(): string | Htmlable
    {
        // Kosongkan heading
        return "Assalamu'alaikum";

        // Atau ubah teks heading seperti contoh berikut:
        // return 'Selamat Datang di Aplikasi WGS';
    }

    public function getSubheading(): string | Htmlable | null
{
    return 'Masukkan akun Anda untuk melanjutkan.';
}
    

    /**
     * (Opsional) Menampilkan subheading di halaman login.
     */
    // public function getSubheading(): string | Htmlable | null
    // {
    //     return 'Silakan masuk untuk melanjutkan.';
    // }
}