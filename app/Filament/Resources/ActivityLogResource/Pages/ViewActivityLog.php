<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    // Secara default, halaman ViewRecord akan menggunakan skema form() 
    // dari Resource terkait (ActivityLogResource::form()) dan menampilkannya dalam mode disabled.
    // Ini sudah sesuai dengan kebutuhan kita untuk melihat detail log.

    // Anda bisa menambahkan header actions jika diperlukan,
    // misalnya tombol kembali ke daftar atau aksi kustom lainnya.
    // Tombol Edit biasanya muncul otomatis jika resource mengizinkannya,
    // tapi karena log tidak untuk diedit, form kita sudah di-set disabled semua.
    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(), // Biasanya tidak ada Edit untuk log aktivitas
            // Anda bisa menambahkan aksi lain di sini jika perlu,
            // contoh: kembali ke halaman list jika ingin tombol eksplisit
            Actions\Action::make('backToList')
               ->label('Kembali ke Daftar')
               ->url($this->getResource()::getUrl('index'))
               ->color('gray'),
        ];
    }

    // Jika Anda ingin mengubah judul halaman view ini
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Detail Log Aktivitas: #' . $this->record->id;
    }
}