<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PomigorDepot;
use App\Models\Region;
use App\Models\Customer;
use App\Models\User;

class PomigorDepotSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan kode yang benar dan konsisten sesuai RegionSeeder.php
        $unitPontianak = Region::where('code', 'PNK01')->where('type', 'UNIT')->first();
        $unitKubuRaya = Region::where('code', 'KRY01')->where('type', 'UNIT')->first();

        // Pastikan customer dan user yang dicari ada dari seeder sebelumnya
        $customerA = Customer::where('email', 'ahmad.subagja@example.com')->first();
        $customerB = Customer::where('email', 'siti.zulaikha@example.com')->first();
        $adminUnitPnk = User::where('email', 'admin.pontianak@wgs.com')->first();
        $adminUnitKry = User::where('email', 'admin.kuburaya@wgs.com')->first();

        if (!$unitPontianak || !$customerA || !$adminUnitPnk) {
            $this->command->warn('Data prasyarat untuk Depot Pontianak tidak ditemukan. Sebagian PomigorDepotSeeder mungkin tidak berjalan.');
        } else {
            PomigorDepot::firstOrCreate(
                ['name' => 'Depot POMIGOR Pontianak Kota 01', 'region_id' => $unitPontianak->id],
                [
                    // depot_code akan di-generate otomatis oleh model
                    'customer_id' => $customerA->id,
                    'address' => 'Jl. Imam Bonjol No. 10, Pontianak',
                    'latitude' => -0.0222820,
                    'longitude' => 109.3456340,
                    'status' => 'ACTIVE',
                    'created_by' => $adminUnitPnk->id,
                ]
            );
        }

        if (!$unitKubuRaya || !$customerB || !$adminUnitKry) {
            $this->command->warn('Data prasyarat untuk Depot Kubu Raya tidak ditemukan. Sebagian PomigorDepotSeeder mungkin tidak berjalan.');
        } else {
            PomigorDepot::firstOrCreate(
                ['name' => 'Depot POMIGOR Sungai Raya 01', 'region_id' => $unitKubuRaya->id],
                [
                    'customer_id' => $customerB->id,
                    'address' => 'Jl. Adisucipto Km. 10, Kubu Raya',
                    'latitude' => -0.0489000,
                    'longitude' => 109.3000000,
                    'status' => 'ACTIVE',
                    'created_by' => $adminUnitKry->id,
                ]
            );
        }
        $this->command->info('PomigorDepotSeeder (dengan auto depot_code) berhasil dijalankan.');
    }
}