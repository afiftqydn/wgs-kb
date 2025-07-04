<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\Region; // 1. Tambahkan ini
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UnitCommissionChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Komisi per Unit';
    protected static ?string $pollingInterval = null;
    public ?string $filter = 'this_month';

    protected function getChartDataEmptyMessage(): string
    {
        return 'Tidak ada data komisi untuk periode ini.';
    }

    protected function getFilters(): ?array
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }
        $months['this_month'] = 'Bulan Ini';
        return $months;
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $user = auth()->user(); // Ambil pengguna yang sedang login

        if ($activeFilter === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $startDate = Carbon::createFromFormat('Y-m', $activeFilter)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $activeFilter)->endOfMonth();
        }

        // --- QUERY DASAR ---
        $query = LoanApplication::query()
            ->select(
                'regions.name as unit_name',
                DB::raw('SUM(
                    CASE
                        WHEN rules.type = "percentage" THEN (loan_applications.amount_requested * rules.value / 100)
                        ELSE rules.value
                    END
                ) as total_commission')
            )
            ->leftJoin('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->leftJoin('product_type_rules as rules', 'product_types.id', '=', 'rules.product_type_id')
            ->leftJoin('regions', 'loan_applications.input_region_id', '=', 'regions.id')
            ->where('loan_applications.status', 'APPROVED')
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate])
            ->whereNotNull('loan_applications.input_region_id');

        // --- 2. TERAPKAN BATASAN WILAYAH ---
        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                $query->whereIn('loan_applications.input_region_id', $accessibleRegionIds);
            } else {
                $query->whereRaw('1 = 0'); // Jika tidak punya region, jangan tampilkan apa-apa
            }
        } elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                $query->where('loan_applications.input_region_id', $user->region_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        // Jika user adalah Tim IT atau peran global lain, tidak ada filter tambahan yang diterapkan.

        $commissionData = $query->groupBy('regions.name')
            ->orderBy('total_commission', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Komisi',
                    'data' => $commissionData->pluck('total_commission')->toArray(),
                    'backgroundColor' => '#39ae45',
                    'borderColor' => '#2a8f34',
                ],
            ],
            'labels' => $commissionData->pluck('unit_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
