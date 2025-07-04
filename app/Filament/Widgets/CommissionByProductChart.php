<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\Region;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CommissionByProductChart extends ChartWidget
{
    protected static ?string $heading = 'Sumber Pendapatan Komisi';
    public ?string $filter = 'this_month';

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
        $user = auth()->user();

        if ($activeFilter === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $startDate = Carbon::createFromFormat('Y-m', $activeFilter)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $activeFilter)->endOfMonth();
        }

        $query = LoanApplication::query()
            ->select(
                'product_types.name as product_name',
                DB::raw('COALESCE(SUM(
                    CASE
                        WHEN rules.type = "percentage" THEN (loan_applications.amount_requested * rules.value / 100)
                        WHEN rules.type = "flat" THEN rules.value
                        ELSE 0
                    END
                ), 0) as total_commission')
            )
            ->leftJoin('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->leftJoin('product_type_rules as rules', 'product_types.id', '=', 'rules.product_type_id')
            ->where('loan_applications.status', 'APPROVED')
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate])
            ->whereNotNull('loan_applications.product_type_id');

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

        $commissionData = $query->groupBy('product_types.name')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Komisi per Produk',
                    'data' => $commissionData->pluck('total_commission')->toArray(),
                ],
            ],
            'labels' => $commissionData->pluck('product_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
