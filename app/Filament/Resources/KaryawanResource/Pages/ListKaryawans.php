<?php

namespace App\Filament\Resources\KaryawanResource\Pages;
use Fibtegis\FilamentInfiniteScroll\Concerns\InteractsWithInfiniteScroll;
use App\Filament\Resources\KaryawanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKaryawans extends ListRecords
{
    use InteractsWithInfiniteScroll;
    protected static string $resource = KaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Data Karyawan')
                ->icon('heroicon-s-plus-circle')
                ->color('success'),
        ];
    }
}
