<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Menjalankan ProductTypeSeeder...');

        // Daftar semua produk yang akan dibuat
        $products = [
            [
                'name' => 'Pembiayaan UMKM',
                'description' => 'Produk pembiayaan untuk Usaha Mikro, Kecil, dan Menengah.',
                'min_amount' => 5000000, 'max_amount' => 500000000,
                'required_documents' => ['KTP Pemilik', 'NPWP Usaha', 'SIUP/NIB', 'Laporan Keuangan 1 Tahun Terakhir'],
                'escalation_threshold' => 100000000,
            ],
            [
                'name' => 'Kredit Usaha Rakyat (KUR)',
                'description' => 'Program kredit/pembiayaan dari pemerintah untuk UMKM.',
                'min_amount' => 1000000, 'max_amount' => 100000000,
                'required_documents' => ['KTP Pemohon', 'Kartu Keluarga', 'Surat Keterangan Usaha'],
                'escalation_threshold' => 50000000,
            ],
            [
                'name' => 'Pembiayaan Modal Kerja',
                'description' => 'Pembiayaan untuk kebutuhan modal kerja usaha.',
                'min_amount' => 25000000, 'max_amount' => 2000000000,
                'required_documents' => ['KTP Direksi', 'NPWP Perusahaan', 'Akta Pendirian'],
                'escalation_threshold' => 500000000,
            ],
            [
                'name' => 'KPR',
                'description' => 'Kredit Pemilikan Rumah untuk nasabah perorangan.',
                'min_amount' => 50000000, 'max_amount' => 1000000000,
                'required_documents' => ['KTP', 'NPWP', 'Slip Gaji', 'Surat Keterangan Kerja'],
                'escalation_threshold' => 250000000,
            ],
            [
                'name' => 'Renovasi Rumah',
                'description' => 'Pembiayaan untuk keperluan renovasi atau perbaikan rumah.',
                'min_amount' => 10000000, 'max_amount' => 300000000,
                'required_documents' => ['KTP', 'Sertifikat Rumah', 'RAB (Rencana Anggaran Biaya)'],
                'escalation_threshold' => 100000000,
            ],
            [
                'name' => 'Pendidikan',
                'description' => 'Pembiayaan untuk kebutuhan biaya pendidikan.',
                'min_amount' => 5000000, 'max_amount' => 100000000,
                'required_documents' => ['KTP Wali', 'Kartu Keluarga', 'Surat Keterangan Sekolah/Kampus'],
                'escalation_threshold' => 50000000,
            ],
            [
                'name' => 'Pertanian/Perkebunan/Peternakan',
                'description' => 'Pembiayaan untuk sektor agribisnis.',
                'min_amount' => 10000000, 'max_amount' => 250000000,
                'required_documents' => ['KTP Petani/Peternak', 'Surat Keterangan Lahan/Kepemilikan Ternak'],
                'escalation_threshold' => 75000000,
            ],
        ];

        // Loop dan buat setiap produk
        foreach ($products as $productData) {
            ProductType::firstOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }

        $this->command->info('ProductTypeSeeder berhasil dijalankan.');
    }
}
