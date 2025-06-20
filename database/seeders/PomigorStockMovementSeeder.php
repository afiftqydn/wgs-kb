<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Region;
use App\Models\PomigorDepot;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Models\PomigorStockMovement;

class PomigorStockMovementSeeder extends Seeder
{
    public function run(): void
    {
        // Cari depot berdasarkan nama dan region_id
        $unitPontianak = Region::where('code', 'PNK01')->where('type', 'UNIT')->first();
        $unitKubuRaya = Region::where('code', 'KRY01')->where('type', 'UNIT')->first(); // <-- Perbaikan kode wilayah

        $depotPnk01 = null;
        if ($unitPontianak) {
            $depotPnk01 = PomigorDepot::where('name', 'Depot POMIGOR Pontianak Kota 01')
                                    ->where('region_id', $unitPontianak->id)
                                    ->first();
        }
        
        $depotKry01 = null;
        if ($unitKubuRaya) {
            $depotKry01 = PomigorDepot::where('name', 'Depot POMIGOR Sungai Raya 01')
                                    ->where('region_id', $unitKubuRaya->id)
                                    ->first();
        }

        $adminUnitPnk = User::where('email', 'admin.pontianak@wgs.com')->first();
        $adminUnitKry = User::where('email', 'admin.kuburaya@wgs.com')->first();

        // Pergerakan untuk Depot Pontianak 01
        if ($depotPnk01 && $adminUnitPnk) {
            PomigorStockMovement::create([
                'pomigor_depot_id' => $depotPnk01->id, 'transaction_type' => 'REFILL',
                'quantity_liters' => 500.00, 'transaction_date' => Carbon::now()->subDays(2),
                'notes' => 'Pengisian awal dari distributor PQR', 'recorded_by' => $adminUnitPnk->id,
            ]);
            PomigorStockMovement::create([
                'pomigor_depot_id' => $depotPnk01->id, 'transaction_type' => 'SALE_REPORTED',
                'quantity_liters' => 50.00, 'transaction_date' => Carbon::now()->subDays(1),
                'notes' => 'Laporan penjualan harian', 'recorded_by' => $adminUnitPnk->id,
            ]);
        } else {
             $this->command->warn('Depot Pontianak atau Admin Pontianak tidak ditemukan untuk PomigorStockMovementSeeder.');
        }

        // Pergerakan untuk Depot Kubu Raya 01
        if ($depotKry01 && $adminUnitKry) {
             PomigorStockMovement::create([
                'pomigor_depot_id' => $depotKry01->id, 'transaction_type' => 'REFILL',
                'quantity_liters' => 300.00, 'transaction_date' => Carbon::now()->subDays(1),
                'notes' => 'Pengisian awal KRY', 'recorded_by' => $adminUnitKry->id,
            ]);
        } else {
            $this->command->warn('Depot Kubu Raya atau Admin Kubu Raya tidak ditemukan untuk PomigorStockMovementSeeder.');
        }
            
        $this->command->info('PomigorStockMovementSeeder berhasil dijalankan.');
    }
}