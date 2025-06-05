<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\LoanApplication; // Import model LoanApplication
use App\Models\Customer;      // Import model Customer

class LoanApplicationStatsOverview extends BaseWidget
{
    // Properti untuk mengatur polling (refresh otomatis)
    protected static ?string $pollingInterval = '15s'; // Contoh: refresh setiap 15 detik
    protected static bool $isLazy = true; // Hanya load jika terlihat di layar

    protected function getStats(): array
    {
        // Ambil data statistik dari database
        $totalApplications = LoanApplication::count();
        $newApplications = LoanApplication::whereIn('status', ['DRAFT', 'SUBMITTED'])->count();
        $approvedApplications = LoanApplication::where('status', 'APPROVED')->count();
        $totalCustomers = Customer::count();

        return [
            Stat::make('Total Permohonan', $totalApplications)
                ->description('Semua permohonan yang tercatat')
                ->descriptionIcon('heroicon-m-arrow-trending-up') // Opsional: ikon deskripsi
                ->color('primary'), // Opsional: warna stat

            Stat::make('Permohonan Baru', $newApplications)
                ->description('Status DRAFT atau SUBMITTED')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('info'),

            Stat::make('Permohonan Disetujui', $approvedApplications)
                ->description('Permohonan dengan status APPROVED')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Nasabah', $totalCustomers)
                ->description('Jumlah nasabah terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }

    /**
     * Kontrol kapan widget ini harus ditampilkan.
     * Berguna jika Anda ingin widget hanya muncul untuk peran tertentu.
     */
    // public static function canView(): bool
    // {
    //     return auth()->user()->hasRole(['Admin Cabang', 'Kepala Cabang', 'Tim IT']); // Contoh
    // }
}