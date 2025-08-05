<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\LoanApplication;
use App\Filament\Resources\LoanApplicationResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Region;
use Filament\Tables\Columns\TextColumn;

class RecentLoanApplicationsWidget extends BaseWidget
{
    protected static ?int $sort = 4; // <-- DIUBAH: Jadikan urutan terakhir
    protected int | string | array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return 'Permohonan Pembiayaan Terbaru / Tugas Saya';
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        
        // Query dasar dengan relasi yang dibutuhkan (eager loading)
        // PERBAIKAN: Mengubah 'assignedTo' menjadi 'assignee' agar sesuai dengan nama relasi di model.
        $query = LoanApplication::with(['customer', 'assignee']);

        // --- Logika Batasan Wilayah ---
        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                $query->whereIn('input_region_id', $accessibleRegionIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                $query->where('input_region_id', $user->region_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filter spesifik untuk menampilkan tugas yang relevan
        // Jika pengguna adalah analis/admin, hanya tampilkan yang ditugaskan padanya.
        // Jika kepala unit/cabang, bisa melihat semua di wilayahnya.
        if ($user->hasAnyRole(['Analis Unit', 'Admin SubUnit'])) {
             $query->where('assigned_to', $user->id);
        }
       
        $query->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'ESCALATED']) // Fokus pada tugas yang masih aktif
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
            Tables\Columns\TextColumn::make('customer.name')
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
            // Kolom baru untuk melihat siapa yang ditugaskan
            // PERBAIKAN: Mengubah 'assignedTo.name' menjadi 'assignee.name'
            Tables\Columns\TextColumn::make('assignee.name')
                ->label('Ditugaskan Kepada')
                ->sortable()
                ->default('Belum Ditugaskan'),
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
}
