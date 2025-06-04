<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Region; // Import Region model
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Digunakan jika ingin mengambil region via DB facade, tapi lebih baik via Model

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil Peran (Roles) yang sudah dibuat oleh RolePermissionSeeder
        // Pastikan semua nama peran ini sudah didefinisikan di RolePermissionSeeder Anda
        $roleTimIT = Role::where('name', 'Tim IT')->first();
        $roleAdminCabang = Role::where('name', 'Admin Cabang')->first();
        $roleKepalaCabang = Role::where('name', 'Kepala Cabang')->first();
        $roleAdminUnit = Role::where('name', 'Admin Unit')->first();
        $roleAnalisUnit = Role::where('name', 'Analis Unit')->first();
        $roleKepalaUnit = Role::where('name', 'Kepala Unit')->first();
        $roleAdminSubUnit = Role::where('name', 'Admin SubUnit')->first();
        $roleKepalaSubUnit = Role::where('name', 'Kepala SubUnit')->first();
        $roleAnalisCabang = Role::where('name', 'Analis Cabang')->first();

        // 2. Ambil Wilayah (Regions) contoh dari RegionSeeder
        // Pastikan kode wilayah ini konsisten dengan yang ada di RegionSeeder Anda
        $regionCabangKalbar = Region::where('code', 'KB00')->first(); // Contoh: Kantor Cabang Kalimantan Barat
        $regionUnitPontianak = Region::where('code', 'PNK01')->first(); // Contoh: Unit Kota Pontianak
        $regionSubUnitPtkKota = Region::where('code', 'PNK01-PK')->first(); // Contoh: SubUnit Pontianak Kota

        // === Pembuatan atau Pembaruan Pengguna Contoh ===

        // Pengguna Tim IT
        if ($roleTimIT) {
            User::firstOrCreate(
                ['email' => 'it@wgs.com'],
                [
                    'name' => 'Tim IT WGS',
                    'password' => Hash::make('password123'), // Ganti dengan password yang aman
                    'region_id' => null, // Tim IT mungkin tidak terikat region spesifik
                    'wgs_job_title' => 'Staf IT',
                    'wgs_level' => 'GLOBAL',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleTimIT);
        }

        // Pengguna Kepala Cabang
        if ($roleKepalaCabang && $regionCabangKalbar) {
            User::firstOrCreate(
                ['email' => 'kacab.kalbar@wgs.com'],
                [
                    'name' => 'Bapak Kepala Cabang',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionCabangKalbar->id,
                    'wgs_job_title' => 'Kepala Cabang Kalimantan Barat',
                    'wgs_level' => 'CABANG',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleKepalaCabang);
        }

        // Pengguna Admin Cabang
        if ($roleAdminCabang && $regionCabangKalbar) {
            User::firstOrCreate(
                ['email' => 'admin.cabang.kalbar@wgs.com'],
                [
                    'name' => 'Admin Cabang Kalimantan Barat',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionCabangKalbar->id,
                    'wgs_job_title' => 'Admin Cabang Kalimantan Barat',
                    'wgs_level' => 'CABANG',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleAdminCabang);
        }


        // Pengguna Admin Unit
        if ($roleAdminUnit && $regionUnitPontianak) {
            User::firstOrCreate(
                ['email' => 'admin.pontianak@wgs.com'],
                [
                    'name' => 'Admin Unit Pontianak',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionUnitPontianak->id,
                    'wgs_job_title' => 'Admin Unit Pontianak',
                    'wgs_level' => 'UNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleAdminUnit);
        }

        // Pengguna Analis Unit
        if ($roleAnalisUnit && $regionUnitPontianak) {
            User::firstOrCreate(
                ['email' => 'analis.pontianak@wgs.com'],
                [
                    'name' => 'Analis Unit Pontianak',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionUnitPontianak->id,
                    'wgs_job_title' => 'Analis Unit Pontianak',
                    'wgs_level' => 'UNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleAnalisUnit);
        }

        // Pengguna Kepala Unit
        if ($roleKepalaUnit && $regionUnitPontianak) {
            User::firstOrCreate(
                ['email' => 'kaunit.pontianak@wgs.com'],
                [
                    'name' => 'Kepala Unit Pontianak',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionUnitPontianak->id,
                    'wgs_job_title' => 'Kepala Unit Pontianak',
                    'wgs_level' => 'UNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleKepalaUnit);
        }

        // Pengguna Admin SubUnit
        if ($roleAdminSubUnit && $regionSubUnitPtkKota) {
            User::firstOrCreate(
                ['email' => 'admin.subunit.ptkkota@wgs.com'],
                [
                    'name' => 'Admin SubUnit Pontianak Kota',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionSubUnitPtkKota->id,
                    'wgs_job_title' => 'Admin SubUnit Pontianak Kota',
                    'wgs_level' => 'SUBUNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleAdminSubUnit);
        }

        // Pengguna Kepala SubUnit
        if ($roleKepalaSubUnit && $regionSubUnitPtkKota) {
            User::firstOrCreate(
                ['email' => 'kasubunit.ptkkota@wgs.com'],
                [
                    'name' => 'Kepala SubUnit Pontianak Kota',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionSubUnitPtkKota->id,
                    'wgs_job_title' => 'Kepala SubUnit Pontianak Kota',
                    'wgs_level' => 'SUBUNIT',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleKepalaSubUnit);
        }

        // Pengguna Analis Cabang
        if ($roleAnalisCabang && $regionCabangKalbar) {
            User::firstOrCreate(
                ['email' => 'analis.kalbar@wgs.com'],
                [
                    'name' => 'Analis Cabang Kalimantan Barat',
                    'password' => Hash::make('password123'),
                    'region_id' => $regionCabangKalbar->id, // Atau null jika dianggap GLOBAL murni
                    'wgs_job_title' => 'Analis Cabang Kalimantan Barat',
                    'wgs_level' => 'CABANG', // Atau GLOBAL
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleAnalisCabang);
        }

        // Anda bisa menambahkan lebih banyak pengguna contoh di sini sesuai kebutuhan
        // Pastikan user admin Filament utama Anda juga dibuat atau salah satu user di atas
        // dapat mengakses panel Filament sesuai konfigurasi di App\Models\User -> canAccessPanel()
    }
}
