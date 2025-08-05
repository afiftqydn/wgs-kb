<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\Region;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommissionByProductChart extends ChartWidget
{
    protected static ?string $heading = 'Sumber Pendapatan Komisi';
    protected static ?int $sort = 3; // <-- DIUBAH: Urutan ketiga, posisi kanan
    protected int | string | array $columnSpan = '1';
    public ?string $filter = 'this_month';
    protected static ?string $maxHeight = '300px';

    protected function getFilters(): ?array
    {
        // Membuat filter bulan lebih dinamis dan rapi
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

        // Logika penentuan tanggal filter
        if ($activeFilter === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $startDate = Carbon::createFromFormat('Y-m', $activeFilter)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $activeFilter)->endOfMonth();
        }

        // Query untuk mengambil data komisi berdasarkan produk
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
            ->join('product_types', 'loan_applications.product_type_id', '=', 'product_types.id')
            ->join('product_type_rules as rules', 'product_types.id', '=', 'rules.product_type_id')
            ->where('loan_applications.status', 'APPROVED')
            ->whereBetween('loan_applications.created_at', [$startDate, $endDate])
            ->whereNotNull('loan_applications.product_type_id');

        // Terapkan batasan wilayah berdasarkan peran pengguna
        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                $query->whereIn('loan_applications.input_region_id', $accessibleRegionIds);
            } else {
                $query->whereRaw('1 = 0'); // Jika tidak ada region, jangan tampilkan data
            }
        } elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                $query->where('loan_applications.input_region_id', $user->region_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $commissionData = $query->groupBy('product_types.name')->get();

        // Siapkan data untuk chart
        $labels = $commissionData->pluck('product_name')->toArray();
        $data = $commissionData->pluck('total_commission')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Komisi per Produk',
                    'data' => $data,
                    // Palet warna modern dan menarik
                    'backgroundColor' => [
                        '#4ade80', '#38bdf8', '#fbbf24', '#f87171', '#818cf8', '#a78bfa', '#e879f9'
                    ],
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        // Mengembalikan tipe chart ke 'pie'
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom', // Pindahkan legenda ke bawah agar tidak mengganggu chart
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
