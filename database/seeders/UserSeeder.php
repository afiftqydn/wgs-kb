<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Region;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
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
        $unitPontianak = Region::where('code', 'PNK01')->first();
        $subunitPtkKota = Region::where('code', 'PNK01-PK')->first();
        $unitKubuRaya = Region::where('code', 'KRY01')->first();

        // Global User
        if ($roleTimIT) User::firstOrCreate(['email' => 'it@wgs.com'], ['name' => 'Tim IT WGS', 'password' => Hash::make('password123'), 'region_id' => null, 'wgs_job_title' => 'Staf IT', 'wgs_level' => 'GLOBAL', 'email_verified_at' => now()])->assignRole($roleTimIT);
        if ($roleManagerKeuangan) {
            User::firstOrCreate(
                ['email' => 'manager.keuangan@wgs.com'],
                [
                    'name' => 'Manager Keuangan',
                    'password' => Hash::make('password123'),
                    'region_id' => null, // Peran ini bersifat global, tidak terikat wilayah
                    'wgs_job_title' => 'Manager Keuangan',
                    'wgs_level' => 'GLOBAL',
                    'email_verified_at' => now(),
                ]
            )->assignRole($roleManagerKeuangan);
        }

        // Cabang Users
        if ($roleKepalaCabang && $regionCabangKalbar) User::firstOrCreate(['email' => 'kacab.kalbar@wgs.com'], ['name' => 'Kepala Cabang Kalbar', 'password' => Hash::make('password123'), 'region_id' => $regionCabangKalbar->id, 'wgs_job_title' => 'Kepala Cabang', 'wgs_level' => 'CABANG', 'email_verified_at' => now()])->assignRole($roleKepalaCabang);
        if ($roleAdminCabang && $regionCabangKalbar) User::firstOrCreate(['email' => 'admin.cabang.kalbar@wgs.com'],['name' => 'Admin Cabang Kalbar', 'password' => Hash::make('password123'), 'region_id' => $regionCabangKalbar->id, 'wgs_job_title' => 'Admin Cabang', 'wgs_level' => 'CABANG', 'email_verified_at' => now()])->assignRole($roleAdminCabang);
        if ($roleAnalisCabang && $regionCabangKalbar) User::firstOrCreate(['email' => 'analis.kalbar@wgs.com'],['name' => 'Analis Cabang Kalbar', 'password' => Hash::make('password123'), 'region_id' => $regionCabangKalbar->id, 'wgs_job_title' => 'Analis Cabang', 'wgs_level' => 'CABANG', 'email_verified_at' => now()])->assignRole($roleAnalisCabang);

        // Unit Pontianak Users
        if ($roleAdminUnit && $unitPontianak) User::firstOrCreate(['email' => 'admin.pontianak@wgs.com'], ['name' => 'Admin Unit Pontianak', 'password' => Hash::make('password123'), 'region_id' => $unitPontianak->id, 'wgs_job_title' => 'Admin Unit Pontianak', 'wgs_level' => 'UNIT', 'email_verified_at' => now()])->assignRole($roleAdminUnit);
        if ($roleAnalisUnit && $unitPontianak) User::firstOrCreate(['email' => 'analis.pontianak@wgs.com'], ['name' => 'Analis Unit Pontianak', 'password' => Hash::make('password123'), 'region_id' => $unitPontianak->id, 'wgs_job_title' => 'Analis Unit Pontianak', 'wgs_level' => 'UNIT', 'email_verified_at' => now()])->assignRole($roleAnalisUnit);
        if ($roleKepalaUnit && $unitPontianak) User::firstOrCreate(['email' => 'kaunit.pontianak@wgs.com'],['name' => 'Kepala Unit Pontianak', 'password' => Hash::make('password123'), 'region_id' => $unitPontianak->id, 'wgs_job_title' => 'Kepala Unit Pontianak', 'wgs_level' => 'UNIT', 'email_verified_at' => now()])->assignRole($roleKepalaUnit);
        
        // Unit Kubu Raya User
        if ($roleAdminUnit && $unitKubuRaya) User::firstOrCreate(['email' => 'admin.kuburaya@wgs.com'], ['name' => 'Admin Unit Kubu Raya', 'password' => Hash::make('password123'), 'region_id' => $unitKubuRaya->id, 'wgs_job_title' => 'Admin Unit Kubu Raya', 'wgs_level' => 'UNIT', 'email_verified_at' => now()])->assignRole($roleAdminUnit);

        // SubUnit Pontianak Kota Users
        if ($roleAdminSubUnit && $subunitPtkKota) User::firstOrCreate(['email' => 'admin.subunit.ptkkota@wgs.com'],['name' => 'Admin SubUnit Ptk Kota', 'password' => Hash::make('password123'), 'region_id' => $subunitPtkKota->id, 'wgs_job_title' => 'Admin SubUnit Pontianak Kota', 'wgs_level' => 'SUBUNIT', 'email_verified_at' => now()])->assignRole($roleAdminSubUnit);
        if ($roleKepalaSubUnit && $subunitPtkKota) User::firstOrCreate(['email' => 'kasubunit.ptkkota@wgs.com'],['name' => 'Kepala SubUnit Ptk Kota', 'password' => Hash::make('password123'), 'region_id' => $subunitPtkKota->id, 'wgs_job_title' => 'Kepala SubUnit Pontianak Kota', 'wgs_level' => 'SUBUNIT', 'email_verified_at' => now()])->assignRole($roleKepalaSubUnit);
        
        $this->command->info('UserSeeder berhasil dijalankan.');
    }
}