<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Tambahkan ini
use Illuminate\Support\Carbon;     // <-- Tambahkan ini

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('regions')->insert([
            // Kantor Cabang Utama
            [
                'name' => 'Kantor Cabang Kalimantan Barat',
                'type' => 'CABANG',
                'parent_id' => null,
                'code' => 'KB00', // Contoh kode Cabang
                'status' => 'ACTIVE',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Unit di bawah Kantor Cabang Kalimantan Barat (parent_id = 1, asumsi ID cabang adalah 1)
            // Kita akan ambil ID setelah insert cabang untuk lebih dinamis
        ]);

        // Ambil ID Kantor Cabang yang baru saja di-insert
        $kantorCabangKalbarId = DB::table('regions')->where('code', 'KB00')->value('id');

        if ($kantorCabangKalbarId) {
            DB::table('regions')->insert([
                [
                    'name' => 'Unit Kota Pontianak',
                    'type' => 'UNIT',
                    'parent_id' => $kantorCabangKalbarId,
                    'code' => 'PNK01', // Contoh kode Unit
                    'status' => 'ACTIVE',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Unit Kubu Raya',
                    'type' => 'UNIT',
                    'parent_id' => $kantorCabangKalbarId,
                    'code' => 'KRY01', // Contoh kode Unit
                    'status' => 'ACTIVE',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);

            // Ambil ID Unit Kota Pontianak
            $unitPontianakId = DB::table('regions')->where('code', 'PNK01')->value('id');
            if ($unitPontianakId) {
                DB::table('regions')->insert([
                    [
                        'name' => 'SubUnit Pontianak Kota',
                        'type' => 'SUBUNIT',
                        'parent_id' => $unitPontianakId,
                        'code' => 'PNK01-PK', // Contoh kode SubUnit
                        'status' => 'ACTIVE',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                    [
                        'name' => 'SubUnit Pontianak Barat',
                        'type' => 'SUBUNIT',
                        'parent_id' => $unitPontianakId,
                        'code' => 'PNK01-PB', // Contoh kode SubUnit
                        'status' => 'ACTIVE',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ],
                ]);
            }
        }
    }
}
