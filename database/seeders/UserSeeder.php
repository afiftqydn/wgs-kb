<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Pastikan model User sudah ada atau dibuat
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Jika ingin membuat permission dasar juga
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Roles Dasar dari Spatie
        $roleTimIT = Role::where('name', 'Tim IT')->first();
        $roleAdminCabang = Role::where('name', 'Admin Cabang')->first();

        $roleKepalaCabang = Role::firstOrCreate(['name' => 'Kepala Cabang', 'guard_name' => 'web']);
        $roleAdminUnit = Role::firstOrCreate(['name' => 'Admin Unit', 'guard_name' => 'web']);
        $roleAnalisUnit = Role::firstOrCreate(['name' => 'Analis Unit', 'guard_name' => 'web']);
        $roleKepalaUnit = Role::firstOrCreate(['name' => 'Kepala Unit', 'guard_name' => 'web']);
        $roleAdminSubUnit = Role::firstOrCreate(['name' => 'Admin SubUnit', 'guard_name' => 'web']); // Sesuai diskusi: Admin & Kepala SubUnit
        $roleKepalaSubUnit = Role::firstOrCreate(['name' => 'Kepala SubUnit', 'guard_name' => 'web']);

        // (Opsional) Buat Permissions dasar dan assign ke Roles jika diperlukan di tahap ini
        // Contoh: Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        // $roleTimIT->givePermissionTo('manage users');

        // Ambil region IDs untuk contoh
        $regionCabangKalbar = DB::table('regions')->where('code', 'KB00')->first();
        $regionUnitPontianak = DB::table('regions')->where('code', 'PNK01')->first();
        $regionSubUnitPtkKota = DB::table('regions')->where('code', 'PNK01-PK')->first();

        // 2. Buat User Contoh
        $timITUser = User::create([
            'name' => 'Tim IT WGS',
            'email' => 'it@wgs.com',
            'password' => Hash::make('password'), // Ganti dengan password yang aman
            'region_id' => null, // Tim IT mungkin tidak terikat region spesifik
            'wgs_job_title' => 'Staf IT',
            'wgs_level' => 'GLOBAL',
            'email_verified_at' => now(),
        ]);
        $timITUser->assignRole($roleTimIT);

        if ($regionCabangKalbar) {
            $kepalaCabangUser = User::create([
                'name' => 'Bapak Kepala Cabang',
                'email' => 'kacab.kalbar@wgs.com',
                'password' => Hash::make('password'),
                'region_id' => $regionCabangKalbar->id,
                'wgs_job_title' => 'Kepala Cabang Kalimantan Barat',
                'wgs_level' => 'CABANG',
                'email_verified_at' => now(),
            ]);
            $kepalaCabangUser->assignRole($roleKepalaCabang);
        }

        if ($regionUnitPontianak) {
            $adminUnitUser = User::create([
                'name' => 'Admin Unit Pontianak',
                'email' => 'admin.pontianak@wgs.com',
                'password' => Hash::make('password'),
                'region_id' => $regionUnitPontianak->id,
                'wgs_job_title' => 'Admin Unit Pontianak',
                'wgs_level' => 'UNIT',
                'email_verified_at' => now(),
            ]);
            $adminUnitUser->assignRole($roleAdminUnit);
        }

        if ($regionSubUnitPtkKota) {
            $adminSubUnitUser = User::create([
                'name' => 'Admin SubUnit Pontianak Kota',
                'email' => 'admin.subunit.ptkkota@wgs.com',
                'password' => Hash::make('password'),
                'region_id' => $regionSubUnitPtkKota->id,
                'wgs_job_title' => 'Admin SubUnit Pontianak Kota',
                'wgs_level' => 'SUBUNIT',
                'email_verified_at' => now(),
            ]);
            $adminSubUnitUser->assignRole($roleAdminSubUnit);
        }
    }
}
