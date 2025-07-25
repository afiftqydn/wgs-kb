<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Region;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seed database.
     */
    public function run(): void
    {
        // --- 1. Ambil Semua Peran (Roles) yang Dibutuhkan ---
        // Ini memastikan bahwa peran-peran tersebut sudah ada di database sebelum kita mencoba menugaskannya.
        $roleTimIT = Role::where('name', 'Tim IT')->first();
        $roleManagerKeuangan = Role::where('name', 'Manager Keuangan')->first();
        $roleAdminCabang = Role::where('name', 'Admin Cabang')->first();
        $roleKepalaCabang = Role::where('name', 'Kepala Cabang')->first();
        $roleAnalisCabang = Role::where('name', 'Analis Cabang')->first();
        $roleAdminUnit = Role::where('name', 'Admin Unit')->first();
        $roleAnalisUnit = Role::where('name', 'Analis Unit')->first();
        $roleKepalaUnit = Role::where('name', 'Kepala Unit')->first();
        $roleAdminSubUnit = Role::where('name', 'Admin SubUnit')->first();
        $roleKepalaSubUnit = Role::where('name', 'Kepala SubUnit')->first();

        $regionCabangKalbar = Region::where('code', 'KB00')->first();
        $unitKubuRaya = Region::where('code', 'KRY01')->first();
        $subunitSry = Region::where('code', 'KRY01-SRY')->first(); 
        $unitPontianak = Region::where('code', 'PNK01')->first();
        $subunitPnkKota = Region::where('code', 'PNK01-PK')->first(); 

        // --- 3. Buat Pengguna Global (Global Users) ---
        // Pengguna ini tidak terikat pada region tertentu (region_id = null).
        if ($roleTimIT) {
            User::firstOrCreate(
                ['email' => 'it@wgs.com'],
                [
                    'name' => 'Tim IT WGS',
                    'password' => Hash::make('password123'),
                    'region_id' => null,
                    'wgs_job_title' => 'Staf IT',
                    'wgs_level' => 'GLOBAL',
                    'email_verified_at' => now()
                ]
            )->assignRole($roleTimIT);
        }

        if ($roleManagerKeuangan) {
            User::firstOrCreate(
                ['email' => 'manager.keuangan@wgs.com'],
                [
                    'name' => 'Manager Keuangan',
                    'password' => Hash::make('password123'),
                    'region_id' => null, 
                    'wgs_job_title' => 'Manager Keuangan',
                    'wgs_level' => 'GLOBAL',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleManagerKeuangan);
        }

        // --- 4. Buat Pengguna Cabang (Branch Users) ---
        // Pengguna yang terkait dengan region cabang, dalam hal ini Cabang Kalbar.
        if ($roleKepalaCabang && $regionCabangKalbar) {
            User::firstOrCreate(
                ['email' => 'kacab.kalbar@wgs.com'],
                [
                    'name' => 'Kepala Cabang Kalbar',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionCabangKalbar->id,
                    'wgs_job_title' => 'Kepala Cabang',
                    'wgs_level' => 'CABANG',
                    'email_verified_at' => now()
                ]
            )->assignRole($roleKepalaCabang);
        }
        if ($roleAdminCabang && $regionCabangKalbar) {
            User::firstOrCreate(
                ['email' => 'admin.cabang.kalbar@wgs.com'],
                [
                    'name' => 'Admin Cabang Kalbar',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionCabangKalbar->id,
                    'wgs_job_title' => 'Admin Cabang',
                    'wgs_level' => 'CABANG',
                    'email_verified_at' => now()
                ]
            )->assignRole($roleAdminCabang);
        }
        if ($roleAnalisCabang && $regionCabangKalbar) {
            User::firstOrCreate(
                ['email' => 'analis.kalbar@wgs.com'],
                [
                    'name' => 'Analis Cabang Kalbar',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionCabangKalbar->id,
                    'wgs_job_title' => 'Analis Cabang',
                    'wgs_level' => 'CABANG',
                    'email_verified_at' => now()
                ]
            )->assignRole($roleAnalisCabang);
        }

        // --- 5. Daftar Kabupaten/Kota untuk Pengguna Unit ---
        // Daftar semua kabupaten/kota yang akan memiliki pengguna Admin Unit, Analis Unit, dan Kepala Unit.
        // Asumsi kode region: 3 huruf awal kota/kabupaten + 01.
        $kabupatenKotaUnits = [
            ['name' => 'Pontianak', 'code' => 'PNK01'],
            ['name' => 'Sambas', 'code' => 'SBS01'],
            ['name' => 'Kubu Raya', 'code' => 'KRY01'],
            ['name' => 'Ketapang', 'code' => 'KTP01'],
            ['name' => 'Singkawang', 'code' => 'SKW01'],
        ];

        // --- 6. Buat Pengguna Unit untuk Setiap Kabupaten/Kota ---
        // Loop melalui daftar di atas untuk membuat Admin, Analis, dan Kepala Unit untuk setiap region.
        foreach ($kabupatenKotaUnits as $data) {
            $regionName = $data['name'];
            $regionCode = $data['code'];
            $unitRegion = Region::where('code', $regionCode)->first();

            // Pastikan region unit ditemukan sebelum membuat pengguna
            if ($unitRegion) {
                // Admin Unit
                if ($roleAdminUnit) {
                    User::firstOrCreate(
                        ['email' => 'admin.' . strtolower(str_replace(' ', '', $regionName)) . '@wgs.com'],
                        [
                            'name' => 'Admin Unit ' . $regionName,
                            'password' => Hash::make('password123'),
                            'region_id' => $unitRegion->id,
                            'wgs_job_title' => 'Admin Unit ' . $regionName,
                            'wgs_level' => 'UNIT',
                            'email_verified_at' => now(),
                        ]
                    )->assignRole($roleAdminUnit);
                }

                // Analis Unit
                if ($roleAnalisUnit) {
                    User::firstOrCreate(
                        ['email' => 'analis.' . strtolower(str_replace(' ', '', $regionName)) . '@wgs.com'],
                        [
                            'name' => 'Analis Unit ' . $regionName,
                            'password' => Hash::make('password123'),
                            'region_id' => $unitRegion->id,
                            'wgs_job_title' => 'Analis Unit ' . $regionName,
                            'wgs_level' => 'UNIT',
                            'email_verified_at' => now(),
                        ]
                    )->assignRole($roleAnalisUnit);
                }

                // Kepala Unit
                if ($roleKepalaUnit) {
                    User::firstOrCreate(
                        ['email' => 'kaunit.' . strtolower(str_replace(' ', '', $regionName)) . '@wgs.com'],
                        [
                            'name' => 'Kepala Unit ' . $regionName,
                            'password' => Hash::make('password123'),
                            'region_id' => $unitRegion->id,
                            'wgs_job_title' => 'Kepala Unit ' . $regionName,
                            'wgs_level' => 'UNIT',
                            'email_verified_at' => now(),
                        ]
                    )->assignRole($roleKepalaUnit);
                }
            } else {
                // Pesan peringatan jika region tidak ditemukan.
                $this->command->warn("Region dengan kode '{$regionCode}' untuk '{$regionName}' tidak ditemukan. Pengguna unit tidak dibuat.");
            }
        }

        if ($roleAdminSubUnit && $subunitSry) {
            User::firstOrCreate(
                ['email' => 'admin.subunit.sry@wgs.com'],
                [
                    'name' => 'Admin SubUnit Sungai Raya',
                    'password' => Hash::make('password123'),
                    'region_id' => $subunitSry->id,
                    'wgs_job_title' => 'Admin SubUnit Sungai Raya',
                    'wgs_level' => 'SUBUNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleAdminSubUnit);
        }
        if ($roleKepalaSubUnit && $subunitSry) {
            User::firstOrCreate(
                ['email' => 'kasubunit.sry@wgs.com'],
                [
                    'name' => 'Kepala SubUnit Sungai Raya',
                    'password' => Hash::make('password123'),
                    'region_id' => $subunitSry->id,
                    'wgs_job_title' => 'Kepala SubUnit Sungai Raya',
                    'wgs_level' => 'SUBUNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleKepalaSubUnit);
        }

        if ($roleAdminSubUnit && $subunitPnkKota) {
            User::firstOrCreate(
                ['email' => 'admin.subunit.pnkkota@wgs.com'],
                [
                    'name' => 'Admin SubUnit Pontianak Kota',
                    'password' => Hash::make('password123'),
                    'region_id' => $subunitPnkKota->id,
                    'wgs_job_title' => 'Admin SubUnit Pontianak Kota',
                    'wgs_level' => 'SUBUNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleAdminSubUnit);
        }
        if ($roleKepalaSubUnit && $subunitPnkKota) {
            User::firstOrCreate(
                ['email' => 'kasubunit.pnkkota@wgs.com'],
                [
                    'name' => 'Kepala SubUnit Pontianak Kota',
                    'password' => Hash::make('password123'),
                    'region_id' => $subunitPnkKota->id,
                    'wgs_job_title' => 'Kepala SubUnit Pontianak Kota',
                    'wgs_level' => 'SUBUNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleKepalaSubUnit);
        }


        // --- 8. Pesan Konfirmasi ---
        $this->command->info('UserSeeder berhasil dijalankan. Semua pengguna yang diperlukan telah dibuat atau diperbarui.');
    }
}
