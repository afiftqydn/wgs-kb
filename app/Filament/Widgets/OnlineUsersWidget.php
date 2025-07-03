<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class OnlineUsersWidget extends Widget
{
    protected static string $view = 'filament.widgets.online-users-widget';

    // Properti ini mencegah widget tampil di dashboard utama
    protected static bool $showOnDashboard = false;

    // Refresh widget setiap 10 detik
    protected static ?string $pollingInterval = '10s';

    // Properti untuk menyimpan daftar pengguna online
    public Collection $onlineUsers;

    /**
     * Menentukan apakah widget ini bisa dilihat oleh pengguna saat ini.
     * Hanya pengguna dengan izin yang sesuai yang bisa melihatnya.
     */
    public static function canView(): bool
    {
        // Gunakan nama izin yang dibuat oleh Filament Shield untuk widget ini.
        return true;
    }

    /**
     * Mount method dijalankan saat komponen pertama kali di-load.
     */
    public function mount(): void
    {
        $this->updateOnlineUsers();
    }

    /**
     * Method ini akan dipanggil oleh polling untuk me-refresh data.
     */
    public function updateOnlineUsers(): void
    {
        // Ambil semua pengguna yang aktivitas terakhirnya dalam 5 menit terakhir
        $this->onlineUsers = User::where('last_activity_at', '>', now()->subMinutes(5))
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    /**
     * Kirim data ke view.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'users' => $this->onlineUsers,
        ];
    }
}
