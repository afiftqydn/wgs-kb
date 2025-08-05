<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection; // Ganti ke Eloquent Collection

class OnlineUsersWidget extends Widget
{
    protected static string $view = 'filament.widgets.online-users-widget';
    protected static ?int $sort = 1; // <-- DIUBAH: Urutan pertama
    protected int | string | array $columnSpan = 'full'; // <-- DIUBAH: Lebar penuh
    protected static ?string $pollingInterval = '15s';
    public Collection $onlineUsers;

    public function mount(): void
    {
        $this->updateOnlineUsers();
    }

    public function updateOnlineUsers(): void
    {
        // Ambil pengguna yang aktivitas terakhirnya dalam 5 menit terakhir
        // Eager load relasi 'roles' untuk efisiensi
        $this->onlineUsers = User::with('roles')
            ->where('last_activity_at', '>', now()->subMinutes(5))
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    // Tidak perlu getViewData() karena properti public $onlineUsers
    // akan otomatis tersedia di view.
}
