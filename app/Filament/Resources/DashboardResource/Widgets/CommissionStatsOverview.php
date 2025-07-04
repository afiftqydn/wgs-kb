<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\Region;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CommissionStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s'; // Refresh setiap menit

    protected function getStats(): array
    {
        $user = auth()->user();
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Query dasar untuk menghitung total komisi
        $query = LoanApplication::query()
            ->leftJoin('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->leftJoin('product_type_rules as rules', 'product_types.id', '=', 'rules.product_type_id')
            ->where('loan_applications.status', 'APPROVED')
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate]);

        // Terapkan batasan wilayah
        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                $query->whereIn('loan_applications.input_region_id', $accessibleRegionIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                $query->where('loan_applications.input_region_id', $user->region_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Hitung total komisi menggunakan database
        $totalCommission = $query->sum(DB::raw('
            CASE
                WHEN rules.type = "percentage" THEN (loan_applications.amount_requested * rules.value / 100)
                WHEN rules.type = "flat" THEN rules.value
                ELSE 0
            END
        '));

        return [
            Stat::make('Total Komisi Bulan Ini', 'Rp ' . number_format($totalCommission, 0, ',', '.'))
                ->description('Total komisi dari pengajuan yang disetujui')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
