<?php

namespace App\Filament\Resources\MigrasiDataResource\Pages;

use App\Filament\Resources\MigrasiDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Imports\MigrasiDataImport;
use App\Exports\MigrasiDataExport; // <-- TAMBAH INI
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ListMigrasiData extends ListRecords
{
    protected static string $resource = MigrasiDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Input Data')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),  
            // Import Action  
            Actions\Action::make('import')
                ->label('Impor Excel')
                ->icon('heroicon-o-arrow-up-on-square')
                ->color('blue')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Pilih File Excel')
                        ->required()
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ])
                        ->maxSize(10240) // 10MB
                ])
                ->action(function (array $data) {
                    try {
                        Excel::import(new MigrasiDataImport, $data['attachment']);
                        Notification::make()
                            ->title('Impor Berhasil')
                            ->body('Data nasabah berhasil diimpor ke sistem.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Impor Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->tooltip('Upload data dari file Excel'),
                 // Export Action
            Actions\Action::make('export')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-folder-arrow-down')
                ->color('gray')
                ->action(function () {
                    $fileName = 'data-nasabah-' . date('Y-m-d-H-i-s') . '.xlsx';
                    
                    try {
                        return Excel::download(new MigrasiDataExport, $fileName);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ekspor Gagal')
                            ->body('Terjadi kesalahan saat mengekspor data: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->tooltip('Download data dalam format Excel'),
        ];
    }
}