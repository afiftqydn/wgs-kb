<?php

namespace Database\Seeders;

use App\Models\MigrasiData;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MigrasiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_nasabah' => 'Ahmad Fauzi',
                'nama_ibu_kandung' => 'Siti Aminah',
                'alamat' => 'Jl. Merdeka No. 123',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Pontianak',
                'tanggal_lahir' => '1990-05-15',
                'identitas_nasabah' => 'KTP',
                'nik' => '6171011505900001',
                'agama' => 'ISLAM',
                'desa' => 'Sungai Jawi',
                'kecamatan' => 'Pontianak Kota',
                'kota_kabupaten' => 'Pontianak',
                'provinsi' => 'Kalimantan Barat',
                'no_hp' => '081234567890',
                'tanggal_register' => '2024-01-15',
                'simpok' => 100000,
                'simwajib' => 25000,
            ],
            [
                'nama_nasabah' => 'Sari Dewi',
                'nama_ibu_kandung' => 'Maria',
                'alamat' => 'Jl. Diponegoro No. 45',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Pontianak',
                'tanggal_lahir' => '1985-08-22',
                'identitas_nasabah' => 'KTP',
                'nik' => '6171022208850002',
                'agama' => 'KRISTEN',
                'desa' => 'Sungai Bangkong',
                'kecamatan' => 'Pontianak Kota',
                'kota_kabupaten' => 'Pontianak',
                'provinsi' => 'Kalimantan Barat',
                'no_hp' => '082345678901',
                'tanggal_register' => '2024-01-20',
                'simpok' => 100000,
                'simwajib' => 25000,
            ],
            [
                'nama_nasabah' => 'Budi Santoso',
                'nama_ibu_kandung' => 'Suryati',
                'alamat' => 'Jl. Ahmad Yani No. 67',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Mempawah',
                'tanggal_lahir' => '1988-12-10',
                'identitas_nasabah' => 'KTP',
                'nik' => '6171031012880003',
                'agama' => 'ISLAM',
                'desa' => 'Pasir',
                'kecamatan' => 'Mempawah Hilir',
                'kota_kabupaten' => 'Mempawah',
                'provinsi' => 'Kalimantan Barat',
                'no_hp' => '083456789012',
                'tanggal_register' => '2024-02-01',
                'simpok' => 100000,
                'simwajib' => 25000,
            ],
            [
                'nama_nasabah' => 'Maya Sari',
                'nama_ibu_kandung' => 'Ratna',
                'alamat' => 'Jl. Sutoyo No. 89',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Pontianak',
                'tanggal_lahir' => '1992-03-25',
                'identitas_nasabah' => 'KTP',
                'nik' => '6171042503920004',
                'agama' => 'ISLAM',
                'desa' => 'Tanjung Hulu',
                'kecamatan' => 'Pontianak Timur',
                'kota_kabupaten' => 'Pontianak',
                'provinsi' => 'Kalimantan Barat',
                'no_hp' => '084567890123',
                'tanggal_register' => '2024-02-05',
                'simpok' => 100000,
                'simwajib' => 25000,
            ],
            [
                'nama_nasabah' => 'Rizki Pratama',
                'nama_ibu_kandung' => 'Dewi',
                'alamat' => 'Jl. Pahlawan No. 12',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Singkawang',
                'tanggal_lahir' => '1995-07-18',
                'identitas_nasabah' => 'KTP',
                'nik' => '6171051807950005',
                'agama' => 'ISLAM',
                'desa' => 'Sungai Garam',
                'kecamatan' => 'Singkawang Barat',
                'kota_kabupaten' => 'Singkawang',
                'provinsi' => 'Kalimantan Barat',
                'no_hp' => '085678901234',
                'tanggal_register' => '2024-02-10',
                'simpok' => 100000,
                'simwajib' => 25000,
            ],
        ];

        foreach ($data as $nasabah) {
            // Cek apakah NIK sudah ada untuk menghindari duplikasi
            $existing = MigrasiData::where('nik', $nasabah['nik'])->first();
            
            if (!$existing) {
                MigrasiData::create($nasabah);
                $this->command->info("Data nasabah {$nasabah['nama_nasabah']} berhasil ditambahkan.");
            } else {
                $this->command->warn("Data nasabah dengan NIK {$nasabah['nik']} sudah ada, dilewati.");
            }
        }

        $this->command->info('Seeder MigrasiData berhasil dijalankan!');
        $this->command->info('Total data: ' . MigrasiData::count() . ' nasabah');
    }
}