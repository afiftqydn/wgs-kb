<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\LoanApplication; // Import model LoanApplication
use App\Filament\Resources\LoanApplicationResource; // Untuk URL aksi
use Illuminate\Database\Eloquent\Builder; // Untuk type hinting query
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login
use App\Models\Region;

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
        $user = Auth::user();
        
        // Mulai dengan query dasar
        $query = LoanApplication::query();

        // --- Terapkan Logika Batasan Wilayah ---

        // 1. Untuk peran level UNIT
        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                $query->whereIn('input_region_id', $accessibleRegionIds);
            } else {
                // Jika user UNIT tidak punya region_id, jangan tampilkan apa-apa
                $query->whereRaw('1 = 0');
            }
        }
        // 2. Untuk peran level SUBUNIT
        elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                $query->where('input_region_id', $user->region_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        // 3. Untuk peran global seperti Tim IT, Kepala Cabang, dll., jangan terapkan filter wilayah
        // jadi $query akan mengambil semua data.

        // --- Tambahkan Filter Spesifik Widget ---
        // Setelah difilter berdasarkan wilayah, filter lagi untuk menampilkan tugas yang relevan
        // Contoh: menampilkan permohonan yang ditugaskan ke user saat ini
        $query->where('assigned_to', $user->id)
              ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'ESCALATED', 'APPROVED', 'REJECTED']) // Tampilkan juga yang perlu direview
              ->orderBy('updated_at', 'desc');

        return $query;
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