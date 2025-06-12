<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;
use Illuminate\Support\Carbon;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Level 1: Cabang
        $cabangKalbar = Region::firstOrCreate(
            ['code' => 'KB00'],
            [
                'name' => 'Kantor Cabang Kalimantan Barat',
                'type' => 'CABANG',
                'parent_id' => null,
                'status' => 'ACTIVE',
            ]
        );

        // Data Unit dan SubUnit dengan kode yang sudah disinkronkan
        $unitsData = [
            ['code_unit' => 'PNK01', 'name_unit' => 'Unit Kota Pontianak', 'code_subunit' => 'PNK01-PK', 'name_subunit' => 'SubUnit Pontianak Kota'],
            ['code_unit' => 'SKW01', 'name_unit' => 'Unit Kota Singkawang', 'code_subunit' => 'SKW01-TGH', 'name_subunit' => 'SubUnit Singkawang Tengah'],
            ['code_unit' => 'SBS01', 'name_unit' => 'Unit Kabupaten Sambas', 'code_subunit' => 'SBS01-SAM', 'name_subunit' => 'SubUnit Sambas'],
            ['code_unit' => 'BKY01', 'name_unit' => 'Unit Kabupaten Bengkayang', 'code_subunit' => 'BKY01-BKY', 'name_subunit' => 'SubUnit Bengkayang'],
            ['code_unit' => 'LDK01', 'name_unit' => 'Unit Kabupaten Landak', 'code_subunit' => 'LDK01-NGB', 'name_subunit' => 'SubUnit Ngabang'],
            ['code_unit' => 'MPW01', 'name_unit' => 'Unit Kabupaten Mempawah', 'code_subunit' => 'MPW01-HIL', 'name_subunit' => 'SubUnit Mempawah Hilir'],
            ['code_unit' => 'SGU01', 'name_unit' => 'Unit Kabupaten Sanggau', 'code_subunit' => 'SGU01-KPS', 'name_subunit' => 'SubUnit Kapuas'],
            ['code_unit' => 'KTP01', 'name_unit' => 'Unit Kabupaten Ketapang', 'code_subunit' => 'KTP01-DPN', 'name_subunit' => 'SubUnit Delta Pawan'],
            ['code_unit' => 'STG01', 'name_unit' => 'Unit Kabupaten Sintang', 'code_subunit' => 'STG01-STG', 'name_subunit' => 'SubUnit Sintang'],
            ['code_unit' => 'KPH01', 'name_unit' => 'Unit Kabupaten Kapuas Hulu', 'code_subunit' => 'KPH01-PTU', 'name_subunit' => 'SubUnit Putussibau Utara'],
            ['code_unit' => 'SKD01', 'name_unit' => 'Unit Kabupaten Sekadau', 'code_subunit' => 'SKD01-HIL', 'name_subunit' => 'SubUnit Sekadau Hilir'],
            ['code_unit' => 'MLW01', 'name_unit' => 'Unit Kabupaten Melawi', 'code_subunit' => 'MLW01-NPH', 'name_subunit' => 'SubUnit Nanga Pinoh'],
            ['code_unit' => 'KKU01', 'name_unit' => 'Unit Kabupaten Kayong Utara', 'code_subunit' => 'KKU01-SKD', 'name_subunit' => 'SubUnit Sukadana'],
            ['code_unit' => 'KRY01', 'name_unit' => 'Unit Kabupaten Kubu Raya', 'code_subunit' => 'KRY01-SRY', 'name_subunit' => 'SubUnit Sungai Raya'],
        ];

        foreach ($unitsData as $unitEntry) {
            $unit = Region::firstOrCreate(
                ['code' => $unitEntry['code_unit']],
                [
                    'name' => $unitEntry['name_unit'],
                    'type' => 'UNIT',
                    'parent_id' => $cabangKalbar->id,
                    'status' => 'ACTIVE',
                ]
            );

            if ($unit) {
                Region::firstOrCreate(
                    ['code' => $unitEntry['code_subunit']],
                    [
                        'name' => $unitEntry['name_subunit'],
                        'type' => 'SUBUNIT',
                        'parent_id' => $unit->id,
                        'status' => 'ACTIVE',
                    ]
                );
            }
        }
        $this->command->info('RegionSeeder (versi lengkap) disinkronkan.');
    }
}