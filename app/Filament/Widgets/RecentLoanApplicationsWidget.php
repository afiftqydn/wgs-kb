<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\LoanApplication; // Import model LoanApplication
use App\Filament\Resources\LoanApplicationResource; // Untuk URL aksi
use Illuminate\Database\Eloquent\Builder; // Untuk type hinting query
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login

class RecentLoanApplicationsWidget extends BaseWidget
{
    protected static ?int $sort = 2; // Urutan widget di dashboard (setelah StatsOverviewWidget jika itu 1)
    protected int | string | array $columnSpan = 'full'; // Widget ini mengambil lebar penuh

    // Judul widget (opsional, defaultnya akan mengambil dari nama kelas)
    public function getTableHeading(): string
    {
        return 'Permohonan Pembiayaan Terbaru / Tugas Saya';
    }

    protected function getTableQuery(): Builder
    {
        // Ambil permohonan yang relevan. Contoh:
        // 1. 5 permohonan terbaru dengan status SUBMITTED atau UNDER_REVIEW
        return LoanApplication::query()
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])
            ->latest() // Urutkan berdasarkan terbaru
            ->limit(5);

        // // 2. Atau, permohonan yang ditugaskan kepada pengguna yang login
        // $user = Auth::user();
        // return LoanApplication::query()
        //     ->where('assigned_to', $user->id)
        //     ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'ESCALATED']) // Status yang memerlukan aksi
        //     ->orderBy('updated_at', 'desc'); // Urutkan berdasarkan kapan terakhir diupdate
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('application_number')
                ->label('No. Permohonan')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('customer.name') // Asumsi relasi customer ada dan di-load
                ->label('Nama Nasabah')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'DRAFT' => 'gray',
                    'SUBMITTED' => 'info',
                    'UNDER_REVIEW' => 'warning',
                    'ESCALATED' => 'primary',
                    'APPROVED' => 'success',
                    'REJECTED' => 'danger',
                    default => 'gray',
                })
                ->sortable(),
            Tables\Columns\TextColumn::make('amount_requested')
                ->label('Jumlah Diminta')
                ->money('IDR')
                ->sortable(),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Update Terakhir')
                ->dateTime('d M Y H:i')
                ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->label('Lihat Detail')
                ->url(fn (LoanApplication $record): string => LoanApplicationResource::getUrl('view', ['record' => $record]))
                ->icon('heroicon-o-eye'),
        ];
    }

    /**
     * Kontrol kapan widget ini harus ditampilkan.
     * Bisa disesuaikan agar hanya muncul untuk peran yang punya "tugas" spesifik.
     */
    // public static function canView(): bool
    // {
    //     return auth()->user()->hasAnyRole(['Admin Unit', 'Analis Unit', 'Kepala Unit', 'Kepala Cabang']);
    // }
}