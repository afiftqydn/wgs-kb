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
            [
                'name' => 'Kantor Cabang Kalimantan Barat',
                'type' => 'CABANG',
                'status' => 'ACTIVE',
                'maps_url' => 'https://www.google.com/maps?q=-0.0263,109.3425', // Contoh: Pontianak
            ]
        );

        $unitsData = [
            [
                'code_unit' => 'PNK01',
                'name_unit' => 'Unit Kota Pontianak',
                'maps_unit' => 'https://www.google.com/maps?q=-0.0206,109.3428',
                'code_subunit' => 'PNK01-PK',
                'name_subunit' => 'SubUnit Pontianak Kota',
                'maps_subunit' => 'https://www.google.com/maps?q=-0.0263,109.3425',
            ],
            [
                'code_unit' => 'SKW01',
                'name_unit' => 'Unit Kota Singkawang',
                'maps_unit' => 'https://www.google.com/maps?q=0.9002,108.9845',
                'code_subunit' => 'SKW01-TGH',
                'name_subunit' => 'SubUnit Singkawang Tengah',
                'maps_subunit' => 'https://www.google.com/maps?q=0.9041,108.9820',
            ],
            [
                'code_unit' => 'SBS01',
                'name_unit' => 'Unit Kabupaten Sambas',
                'maps_unit' => 'https://www.google.com/maps?q=1.3333,109.2833',
                'code_subunit' => 'SBS01-SAM',
                'name_subunit' => 'SubUnit Sambas',
                'maps_subunit' => 'https://www.google.com/maps?q=1.3475,109.2989',
            ],
            [
                'code_unit' => 'KTP01',
                'name_unit' => 'Unit Kabupaten Ketapang',
                'maps_unit' => 'https://www.google.com/maps?q=-1.8500,109.9833',
                'code_subunit' => 'KTP01-DPN',
                'name_subunit' => 'SubUnit Delta Pawan',
                'maps_subunit' => 'https://www.google.com/maps?q=-1.8231,109.9726',
            ],
            [
                'code_unit' => 'KRY01',
                'name_unit' => 'Unit Kabupaten Kubu Raya',
                'maps_unit' => 'https://www.google.com/maps?q=-0.2072,109.4045',
                'code_subunit' => 'KRY01-SRY',
                'name_subunit' => 'SubUnit Sungai Raya',
                'maps_subunit' => 'https://www.google.com/maps?q=-0.1796,109.4043',
            ],
        ];

        foreach ($unitsData as $unitEntry) {
            $unit = Region::firstOrCreate(
                ['code' => $unitEntry['code_unit']],
                [
                    'name' => $unitEntry['name_unit'],
                    'type' => 'UNIT',
                    'parent_id' => $cabangKalbar->id,
                    'status' => 'ACTIVE',
                    'maps_url' => $unitEntry['maps_unit'],
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
                        'maps_url' => $unitEntry['maps_subunit'],
                    ]
                );
            }
        }

        $this->command->info('RegionSeeder (versi lengkap + maps_url) disinkronkan.');
    }
}
