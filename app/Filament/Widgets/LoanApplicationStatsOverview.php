<?php

namespace App\Filament\Widgets;

use App\Models\Region;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Customer;      // Import model Customer
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\LoanApplication; // Import model LoanApplication

class LoanApplicationStatsOverview extends BaseWidget
{
    // Properti untuk mengatur polling (refresh otomatis)
    protected static ?string $pollingInterval = '15s'; // Contoh: refresh setiap 15 detik
    protected static bool $isLazy = true; // Hanya load jika terlihat di layar

    protected function getStats(): array
    {
    $user = Auth::user();

        // Buat query dasar yang sudah terfilter berdasarkan wilayah
        $baseLoanQuery = LoanApplication::query();
        $baseCustomerQuery = Customer::query();

        // --- Terapkan Logika Batasan Wilayah pada Query Dasar ---

        if ($user->hasAnyRole(['Kepala Unit', 'Analis Unit', 'Admin Unit'])) {
            if ($user->region_id) {
                $childSubUnitIds = Region::where('parent_id', $user->region_id)->pluck('id');
                $accessibleRegionIds = $childSubUnitIds->push($user->region_id);
                
                $baseLoanQuery->whereIn('input_region_id', $accessibleRegionIds);
                $baseCustomerQuery->whereIn('region_id', $accessibleRegionIds);
            } else {
                $baseLoanQuery->whereRaw('1 = 0');
                $baseCustomerQuery->whereRaw('1 = 0');
            }
        } elseif ($user->hasAnyRole(['Kepala SubUnit', 'Admin SubUnit'])) {
            if ($user->region_id) {
                $baseLoanQuery->where('input_region_id', $user->region_id);
                $baseCustomerQuery->where('region_id', $user->region_id);
            } else {
                $baseLoanQuery->whereRaw('1 = 0');
                $baseCustomerQuery->whereRaw('1 = 0');
            }
        }
        // Untuk peran global, query dasar tidak difilter dan akan mengambil semua data

        // --- Hitung Statistik Menggunakan Query yang Sudah Terfilter ---

        // Gunakan clone() agar filter tambahan pada satu stat tidak mempengaruhi stat lainnya
        $totalApplications = (clone $baseLoanQuery)->count();
        $newApplications = (clone $baseLoanQuery)->whereIn('status', ['DRAFT', 'SUBMITTED'])->count();
        $approvedApplications = (clone $baseLoanQuery)->where('status', 'APPROVED')->count();
        $totalCustomers = $baseCustomerQuery->count();

        return [
            Stat::make('Total Permohonan di Wilayah Anda', $totalApplications)
                ->description('Semua permohonan yang bisa Anda akses')
                ->color('primary'),

            Stat::make('Permohonan Baru di Wilayah Anda', $newApplications)
                ->description('Status DRAFT atau SUBMITTED')
                ->color('info'),

            Stat::make('Permohonan Disetujui di Wilayah Anda', $approvedApplications)
                ->description('Permohonan dengan status APPROVED')
                ->color('success'),
            
            Stat::make('Total Nasabah di Wilayah Anda', $totalCustomers)
                ->description('Jumlah nasabah yang bisa Anda akses')
                ->color('warning'),
        ];
    }
}