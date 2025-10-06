<?php

namespace App\Filament\Resources\MigrasiDataResource\Pages;

use App\Filament\Resources\MigrasiDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Imports\MigrasiDataImport;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class ListMigrasiData extends ListRecords
{
    protected static string $resource = MigrasiDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Baris di bawah ini yang diubah
            Actions\CreateAction::make()
                ->label('Input Data')
                ->icon('heroicon-s-plus-circle')
                ->color('success'),
            Actions\Action::make('import')
                ->label('Impor Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('amber') // Anda mengubah warnanya menjadi amber, saya biarkan
                ->form([
                    FileUpload::make('attachment')
                        ->label('Pilih File Excel')
                        ->required()
                        ->disk('local')
                        ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ])
                ->action(function (array $data) {
                    try {
                        Excel::import(new MigrasiDataImport, $data['attachment']);
                        Notification::make()->title('Impor Berhasil')->success()->send();
                    } catch (\Exception $e) {
                        Notification::make()->title('Impor Gagal')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}