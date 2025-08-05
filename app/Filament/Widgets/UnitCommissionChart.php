<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\Region;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UnitCommissionChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Komisi per Unit';
    protected static ?int $sort = 2; // <-- DIUBAH: Urutan kedua, posisi kiri
    protected int | string | array $columnSpan = '1';
    public ?string $filter = 'this_month';
    // protected static ?string $maxHeight = '500px';


    protected function getFilters(): ?array
    {
        $months = ['this_month' => 'Bulan Ini'];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }
        return $months;
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $user = Auth::user();

        if ($activeFilter === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $startDate = Carbon::createFromFormat('Y-m', $activeFilter)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $activeFilter)->endOfMonth();
        }

        // Query dasar untuk komisi
        $query = LoanApplication::query()
            ->select(
                'regions.name as unit_name',
                DB::raw('SUM(
                    CASE
                        WHEN rules.type = "percentage" THEN (loan_applications.amount_requested * rules.value / 100)
                        WHEN rules.type = "flat" THEN rules.value
                        ELSE 0
                    END
                ) as total_commission')
            )
            ->join('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->join('product_type_rules as rules', 'product_types.id', '=', 'rules.product_type_id')
            ->join('regions', 'loan_applications.input_region_id', '=', 'regions.id')
            ->where('loan_applications.status', 'APPROVED')
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate])
            ->whereNotNull('loan_applications.input_region_id');

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

        $commissionData = $query->groupBy('regions.name')
            ->orderBy('total_commission', 'desc')
            ->limit(10) // Batasi 10 unit teratas agar chart tidak terlalu ramai
            ->get();
            
        return [
            'datasets' => [
                [
                    'label' => 'Total Komisi',
                    'data' => $commissionData->pluck('total_commission')->toArray(),
                    'backgroundColor' => '#38bdf8',
                    'borderColor' => '#0ea5e9',
                ],
            ],
            'labels' => $commissionData->pluck('unit_name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        // Menggunakan 'bar' dan mengaturnya menjadi horizontal di options
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Ini kunci untuk membuat bar chart menjadi horizontal
            'plugins' => [
                'legend' => [
                    'display' => false, // Sembunyikan legenda karena sudah jelas dari judul
                ],
                'tooltip' => [
                    'callbacks' => [
                        // Format tooltip menjadi format mata uang
                        'label' => 'function(context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.x !== null) {
                                label += new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR" }).format(context.parsed.x);
                            }
                            return label;
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'callback' => 'function(value) {
                            return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(value);
                        }',
                    ],
                ],
            ],
        ];
    }
}
