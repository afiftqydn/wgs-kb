<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Karyawan;
use App\Models\Gaji;

class GajiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada data karyawan sebelum menjalankan seeder ini
        $karyawans = Karyawan::query()->take(3)->get();

        if ($karyawans->isEmpty()) {
            $this->command->info('Tidak ada data karyawan. Silakan jalankan KaryawanSeeder terlebih dahulu.');
            return;
        }

        foreach ($karyawans as $karyawan) {
            Gaji::create([
                'karyawan_id'       => $karyawan->id,
                'periode_bulan'     => 'Juni',
                'periode_tahun'     => '2025',
                'tanggal_bayar'     => '2025-07-05',
                'gaji_pokok'        => 5000000,
                'transport'         => 500000,
                'tun_kehadiran'     => 300000,
                'tun_komunikasi'    => 250000,
                'lembur'            => 150000,
                'bpjs'              => 180000,
                'absen'             => 0,
                'kas_bon'           => 500000,
                'note'              => 'Data gaji dibuat oleh seeder.',
            ]);
        }
    }
}