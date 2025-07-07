<?php
// database/seeders/RegionSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $cabangKalbar = Region::firstOrCreate(
            ['code' => 'KB00'],
            ['name' => 'Kantor Cabang Kalimantan Barat', 'type' => 'CABANG', 'status' => 'ACTIVE']
        );

        $unitsData = [
            ['code_unit' => 'PNK01', 'name_unit' => 'Unit Kota Pontianak', 'code_subunit' => 'PNK01-PK', 'name_subunit' => 'SubUnit Pontianak Kota'],
            ['code_unit' => 'SKW01', 'name_unit' => 'Unit Kota Singkawang', 'code_subunit' => 'SKW01-TGH', 'name_subunit' => 'SubUnit Singkawang Tengah'],
            ['code_unit' => 'SBS01', 'name_unit' => 'Unit Kabupaten Sambas', 'code_subunit' => 'SBS01-SAM', 'name_subunit' => 'SubUnit Sambas'],
            ['code_unit' => 'KTP01', 'name_unit' => 'Unit Kabupaten Ketapang', 'code_subunit' => 'KTP01-DPN', 'name_subunit' => 'SubUnit Delta Pawan'],
            ['code_unit' => 'KRY01', 'name_unit' => 'Unit Kabupaten Kubu Raya', 'code_subunit' => 'KRY01-SRY', 'name_subunit' => 'SubUnit Sungai Raya'],
        ];

        foreach ($unitsData as $unitEntry) {
            $unit = Region::firstOrCreate(
                ['code' => $unitEntry['code_unit']],
                ['name' => $unitEntry['name_unit'], 'type' => 'UNIT', 'parent_id' => $cabangKalbar->id, 'status' => 'ACTIVE']
            );

            if ($unit) {
                Region::firstOrCreate(
                    ['code' => $unitEntry['code_subunit']],
                    ['name' => $unitEntry['name_subunit'], 'type' => 'SUBUNIT', 'parent_id' => $unit->id, 'status' => 'ACTIVE']
                );
            }
        }
        $this->command->info('RegionSeeder (versi lengkap) disinkronkan.');
    }
}