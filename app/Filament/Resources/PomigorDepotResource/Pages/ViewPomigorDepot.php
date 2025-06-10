<?php

namespace App\Filament\Resources\PomigorDepotResource\Pages;

use App\Filament\Resources\PomigorDepotResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPomigorDepot extends ViewRecord
{
    protected static string $resource = PomigorDepotResource::class;

    // Secara default, halaman ViewRecord akan menggunakan skema form() 
    // dari Resource terkait (PomigorDepotResource::form()) dan 
    // menampilkannya dalam mode disabled (read-only). 
    // Ini sudah sesuai dengan kebutuhan kita untuk melihat detail depot POMIGOR.

    // Anda bisa menambahkan atau memodifikasi header actions jika diperlukan.
    // Secara default, tombol EditAction biasanya akan muncul di sini jika
    // resource mengizinkan pengeditan (canEdit() mengembalikan true).
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // Tombol Edit, akan mengarahkan ke halaman EditPomigorDepot
            // Anda bisa menambahkan aksi lain di sini jika perlu,
            // contoh: kembali ke halaman list jika ingin tombol eksplisit
            // Actions\Action::make('backToList')
            //    ->label('Kembali ke Daftar Depot')
            //    ->url($this->getResource()::getUrl('index'))
            //    ->color('gray')
            //    ->icon('heroicon-o-arrow-left-start-on-rectangle'),
        ];
    }

    // Jika Anda ingin mengubah judul halaman view ini secara dinamis
    // public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    // {
    //     return "Detail Depot: " . $this->record->name . " (" . $this->record->depot_code . ")";
    // }
}