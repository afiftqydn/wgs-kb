<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region; // Pastikan Anda sudah membuat model Region
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
        ['code' => 'KB00'], // Sesuai yang dicari di UserSeeder
        [
            'name' => 'Kantor Cabang Kalimantan Barat',
            'type' => 'CABANG',
            'parent_id' => null,
            'status' => 'ACTIVE',
        ]
    );

    $unitsData = [
        // Sesuaikan code_unit dan code_subunit dengan yang Anda cari di seeder lain
        ['code_unit' => 'PNK01', 'name_unit' => 'Kota Pontianak', 'code_subunit' => 'PNK01-PK', 'name_subunit' => 'Kecamatan Pontianak Kota'],
        ['code_unit' => 'SKW01', 'name_unit' => 'Kota Singkawang', 'code_subunit' => 'SKW01-TGH', 'name_subunit' => 'Kecamatan Singkawang Tengah'],
        ['code_unit' => 'SBS01', 'name_unit' => 'Kabupaten Sambas', 'code_subunit' => 'SBS01-SAM', 'name_subunit' => 'Kecamatan Sambas'],
        ['code_unit' => 'BKY01', 'name_unit' => 'Kabupaten Bengkayang', 'code_subunit' => 'BKY01-BKY', 'name_subunit' => 'Kecamatan Bengkayang'],
        ['code_unit' => 'LDK01', 'name_unit' => 'Kabupaten Landak', 'code_subunit' => 'LDK01-NGB', 'name_subunit' => 'Kecamatan Ngabang'],
        ['code_unit' => 'MPW01', 'name_unit' => 'Kabupaten Mempawah', 'code_subunit' => 'MPW01-HIL', 'name_subunit' => 'Kecamatan Mempawah Hilir'],
        ['code_unit' => 'SGU01', 'name_unit' => 'Kabupaten Sanggau', 'code_subunit' => 'SGU01-KPS', 'name_subunit' => 'Kecamatan Kapuas'],
        ['code_unit' => 'KTP01', 'name_unit' => 'Kabupaten Ketapang', 'code_subunit' => 'KTP01-DPN', 'name_subunit' => 'Kecamatan Delta Pawan'],
        ['code_unit' => 'STG01', 'name_unit' => 'Kabupaten Sintang', 'code_subunit' => 'STG01-STG', 'name_subunit' => 'Kecamatan Sintang'],
        ['code_unit' => 'KPH01', 'name_unit' => 'Kabupaten Kapuas Hulu', 'code_subunit' => 'KPH01-PTU', 'name_subunit' => 'Kecamatan Putussibau Utara'],
        ['code_unit' => 'SKD01', 'name_unit' => 'Kabupaten Sekadau', 'code_subunit' => 'SKD01-HIL', 'name_subunit' => 'Kecamatan Sekadau Hilir'],
        ['code_unit' => 'MLW01', 'name_unit' => 'Kabupaten Melawi', 'code_subunit' => 'MLW01-NPH', 'name_subunit' => 'Kecamatan Nanga Pinoh'],
        ['code_unit' => 'KKU01', 'name_unit' => 'Kabupaten Kayong Utara', 'code_subunit' => 'KKU01-SKD', 'name_subunit' => 'Kecamatan Sukadana'],
        ['code_unit' => 'KRY01', 'name_unit' => 'Kabupaten Kubu Raya', 'code_subunit' => 'KRY01-SRY', 'name_subunit' => 'Kecamatan Sungai Raya'],
    ];

    foreach ($unitsData as $unitEntry) {
        $unit = Region::firstOrCreate(
            ['code' => $unitEntry['code_unit']], // Hapus parent_id dari kriteria pencarian agar bisa dijalankan ulang
            [
                'name' => $unitEntry['name_unit'],
                'type' => 'UNIT',
                'parent_id' => $cabangKalbar->id, // Set parent_id saat membuat
                'status' => 'ACTIVE',
            ]
        );

        if ($unit) {
            Region::firstOrCreate(
                ['code' => $unitEntry['code_subunit']], // Hapus parent_id dari kriteria pencarian
                [
                    'name' => $unitEntry['name_subunit'],
                    'type' => 'SUBUNIT',
                    'parent_id' => $unit->id, // Set parent_id saat membuat
                    'status' => 'ACTIVE',
                ]
            );
        }
    }
    // Hapus created_at & updated_at manual, biarkan Eloquent yang mengisi
    $this->command->info('RegionSeeder (versi lengkap) disinkronkan.');
    }
}