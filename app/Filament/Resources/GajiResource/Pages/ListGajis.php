<?php

namespace App\Filament\Resources\GajiResource\Pages;

use App\Filament\Resources\GajiResource;
use App\Models\Gaji;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

// Import yang dibutuhkan untuk Aksi Download ZIP
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ListGajis extends ListRecords
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol "Download Semua Slip" sekarang di posisi pertama (kiri)
            Action::make('downloadAllSlipGaji')
                ->label('Download Semua Slip')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation() // Tambahkan konfirmasi sebelum proses
                ->modalHeading('Konfirmasi Download')
                ->modalDescription('Anda akan mengunduh semua slip gaji yang tampil di tabel dalam format ZIP. Proses ini mungkin memakan waktu beberapa saat tergantung jumlah data.')
                ->modalSubmitActionLabel('Ya, Lanjutkan')
                ->action(function () {
                    // Ambil semua record gaji yang sedang ditampilkan (sudah ter-filter)
                    $records = $this->getFilteredTableQuery()->get();

                    if ($records->isEmpty()) {
                        // Tampilkan notifikasi jika tidak ada data untuk diunduh
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak Ada Data')
                            ->body('Tidak ada data gaji untuk diunduh.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Buat nama file ZIP yang unik
                    $zipFileName = 'semua_slip_gaji_' . now()->format('Y-m-d_His') . '.zip';
                    $zipPath = storage_path('app/temp/' . $zipFileName);

                    // Pastikan direktori sementara ada
                    if (!is_dir(dirname($zipPath))) {
                        mkdir(dirname($zipPath), 0755, true);
                    }

                    $zip = new ZipArchive;

                    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                        // Loop setiap record gaji
                        foreach ($records as $record) {
                            // Generate PDF untuk setiap record
                            $pdf = Pdf::loadView('pdf.slip-gaji', ['gaji' => $record]);
                            
                            // Buat nama file PDF yang unik di dalam ZIP
                            $pdfFileName = "slip-gaji-{$record->karyawan->nama_lengkap}-{$record->periode_bulan}-{$record->periode_tahun}.pdf";
                            
                            // Tambahkan PDF ke dalam file ZIP
                            $zip->addFromString($pdfFileName, $pdf->output());
                        }
                        $zip->close();
                    }

                    // Kirim file ZIP untuk di-download dan hapus file sementara setelah selesai
                    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
                }),
            
            // Tombol "Input Gaji" sekarang di posisi kedua (kanan)
            Actions\CreateAction::make()
                ->label('Input Gaji')
                ->icon('heroicon-s-plus-circle')
                ->color('success'),
        ];
    }
}
