<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Region Induk (Cabang)
        // Region ini adalah level tertinggi yang menjadi parent dari semua unit.
        $cabangKalbar = Region::firstOrCreate(
            ['code' => 'KB00'],
            [
                'name' => 'Kantor Cabang Kalimantan Barat',
                'type' => 'CABANG',
                'parent_id' => null,
                'status' => 'ACTIVE',
            ]
        );

        // 2. Definisikan Data Semua Unit
        // Daftar ini berisi semua kabupaten/kota yang akan menjadi region bertipe UNIT.
        // Data ini sengaja disamakan dengan array `kabupatenKotaUnits` di UserSeeder Anda.
        $unitsData = [
            ['code' => 'PNK01', 'name' => 'Unit Kota Pontianak'],
            ['code' => 'MPW01', 'name' => 'Unit Kabupaten Mempawah'],
            ['code' => 'SBS01', 'name' => 'Unit Kabupaten Sambas'],
            ['code' => 'KRY01', 'name' => 'Unit Kabupaten Kubu Raya'], // Unit ini akan punya subunit
            ['code' => 'STG01', 'name' => 'Unit Kabupaten Sintang'],
            ['code' => 'KTP01', 'name' => 'Unit Kabupaten Ketapang'],
            ['code' => 'MLW01', 'name' => 'Unit Kabupaten Melawi'],
            ['code' => 'BKY01', 'name' => 'Unit Kabupaten Bengkayang'],
            ['code' => 'SKD01', 'name' => 'Unit Kabupaten Sekadau'],
            ['code' => 'SGU01', 'name' => 'Unit Kabupaten Sanggau'],
            ['code' => 'KYU01', 'name' => 'Unit Kabupaten Kayong Utara'],
            ['code' => 'KPH01', 'name' => 'Unit Kabupaten Kapuas Hulu'],
            ['code' => 'LDK01', 'name' => 'Unit Kabupaten Landak'],
            ['code' => 'SKW01', 'name' => 'Unit Kota Singkawang'],
        ];

        // 3. Buat Semua Region Unit
        // Melakukan iterasi untuk membuat atau memperbarui setiap UNIT di bawah CABANG.
        foreach ($unitsData as $unitData) {
            Region::firstOrCreate(
                ['code' => $unitData['code']],
                [
                    'name' => $unitData['name'],
                    'type' => 'UNIT',
                    'parent_id' => $cabangKalbar->id,
                    'status' => 'ACTIVE',
                ]
            );
        }

        // 4. Buat Region SubUnit Spesifik (Hanya untuk Kubu Raya)
        // Setelah semua unit dibuat, cari unit Kubu Raya untuk menambahkan subunit di bawahnya.
        $unitKubuRaya = Region::where('code', 'KRY01')->first();

        // Pastikan Unit Kubu Raya ada sebelum membuat SubUnit di bawahnya.
        if ($unitKubuRaya) {
            Region::firstOrCreate(
                ['code' => 'KRY01-SRY'], // Kode subunit spesifik
                [
                    'name' => 'SubUnit Sungai Raya', // Nama subunit spesifik
                    'type' => 'SUBUNIT',
                    'parent_id' => $unitKubuRaya->id, // Parent-nya adalah Unit Kubu Raya
                    'status' => 'ACTIVE',
                ]
            );
        }

        $this->command->info('RegionSeeder berhasil dijalankan sesuai struktur yang benar.');
    }
}