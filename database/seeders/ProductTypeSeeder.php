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
        $this->command->info('Menjalankan ProductTypeSeeder dengan data baru...');

        // Daftar semua produk baru yang akan dibuat
        $products = [
            [
                'name' => 'Haji dan Umroh',
                'description' => 'Pembiayaan untuk perjalanan Haji dan Umroh.',
                'min_amount' => 10000000, 'max_amount' => 100000000,
                'required_documents' => ['KTP', 'Kartu Keluarga', 'Paspor'],
                'escalation_threshold' => 50000000,
            ],
            [
                'name' => 'Kredit KPR',
                'description' => 'Kredit Pemilikan Rumah untuk nasabah perorangan.',
                'min_amount' => 50000000, 'max_amount' => 2000000000,
                'required_documents' => ['KTP', 'NPWP', 'Slip Gaji', 'Surat Keterangan Kerja', 'Sertifikat Rumah'],
                'escalation_threshold' => 250000000,
            ],
            [
                'name' => 'Kredit Kendaraan Roda 2',
                'description' => 'Pembiayaan untuk pembelian kendaraan bermotor roda dua.',
                'min_amount' => 5000000, 'max_amount' => 50000000,
                'required_documents' => ['KTP', 'Kartu Keluarga', 'Slip Gaji'],
                'escalation_threshold' => 25000000,
            ],
            [
                'name' => 'Kredit Kendaraan Roda 4',
                'description' => 'Pembiayaan untuk pembelian kendaraan bermotor roda empat.',
                'min_amount' => 25000000, 'max_amount' => 500000000,
                'required_documents' => ['KTP', 'Kartu Keluarga', 'NPWP', 'Slip Gaji/Surat Keterangan Penghasilan'],
                'escalation_threshold' => 100000000,
            ],
            [
                'name' => 'Kredit Renovasi Rumah',
                'description' => 'Pembiayaan untuk keperluan renovasi atau perbaikan rumah.',
                'min_amount' => 10000000, 'max_amount' => 300000000,
                'required_documents' => ['KTP', 'Sertifikat Rumah', 'RAB (Rencana Anggaran Biaya)'],
                'escalation_threshold' => 100000000,
            ],
            [
                'name' => 'Kredit Take Over Dan Top Up',
                'description' => 'Pembiayaan untuk pengambilalihan kredit dari bank lain dan/atau penambahan plafon kredit.',
                'min_amount' => 25000000, 'max_amount' => 1000000000,
                'required_documents' => ['KTP', 'Slip Gaji', 'Surat Keterangan Sisa Pinjaman dari Bank Lama'],
                'escalation_threshold' => 200000000,
            ],
            [
                'name' => 'Kredit Pendidikan',
                'description' => 'Pembiayaan untuk kebutuhan biaya pendidikan formal dan non-formal.',
                'min_amount' => 5000000, 'max_amount' => 150000000,
                'required_documents' => ['KTP Wali', 'Kartu Keluarga', 'Surat Keterangan Sekolah/Kampus'],
                'escalation_threshold' => 50000000,
            ],
            [
                'name' => 'Kredit Modal Kerja',
                'description' => 'Pembiayaan untuk kebutuhan modal kerja usaha.',
                'min_amount' => 25000000, 'max_amount' => 2000000000,
                'required_documents' => ['KTP Direksi', 'NPWP Perusahaan', 'Akta Pendirian', 'Laporan Keuangan'],
                'escalation_threshold' => 500000000,
            ],
            [
                'name' => 'Kredit Pertanian, Perkebunan Dan Kelautan',
                'description' => 'Pembiayaan khusus untuk sektor agribisnis, termasuk pertanian, perkebunan, dan kelautan.',
                'min_amount' => 10000000, 'max_amount' => 500000000,
                'required_documents' => ['KTP Petani/Nelayan', 'Surat Keterangan Lahan/Kepemilikan Kapal'],
                'escalation_threshold' => 75000000,
            ],
            [
                'name' => 'Kredit Emas',
                'description' => 'Pembiayaan dengan agunan emas.',
                'min_amount' => 1000000, 'max_amount' => 250000000,
                'required_documents' => ['KTP', 'Surat Emas'],
                'escalation_threshold' => 50000000,
            ],
            [
                'name' => 'KUR SUPERMI (Super Mikro)',
                'description' => 'Kredit Usaha Rakyat (KUR) dengan plafon super mikro.',
                'min_amount' => 1000000, 'max_amount' => 10000000,
                'required_documents' => ['KTP', 'Kartu Keluarga', 'Surat Keterangan Usaha'],
                'escalation_threshold' => 10000000,
            ],
            [
                'name' => 'KUR Mikro Small',
                'description' => 'Kredit Usaha Rakyat (KUR) untuk usaha mikro skala kecil.',
                'min_amount' => 10000001, 'max_amount' => 50000000,
                'required_documents' => ['KTP', 'Kartu Keluarga', 'Surat Keterangan Usaha', 'NPWP (jika > 50jt)'],
                'escalation_threshold' => 50000000,
            ],
            [
                'name' => 'KUR Mikro Medium',
                'description' => 'Kredit Usaha Rakyat (KUR) untuk usaha mikro skala menengah.',
                'min_amount' => 50000001, 'max_amount' => 100000000,
                'required_documents' => ['KTP', 'Kartu Keluarga', 'Surat Keterangan Usaha', 'NPWP'],
                'escalation_threshold' => 100000000,
            ],
            [
                'name' => 'POMIGOR (POM Minyak Goreng)',
                'description' => 'Pembiayaan khusus untuk usaha waralaba Pertashop Minyak Goreng.',
                'min_amount' => 50000000, 'max_amount' => 250000000,
                'required_documents' => ['KTP', 'NPWP', 'Surat Rekomendasi'],
                'escalation_threshold' => 100000000,
            ],
            [
                'name' => 'IJARAH',
                'description' => 'Pembiayaan berbasis sewa sesuai prinsip syariah.',
                'min_amount' => 10000000, 'max_amount' => 500000000,
                'required_documents' => ['KTP', 'NPWP', 'Akad Ijarah'],
                'escalation_threshold' => 100000000,
            ],
        ];

        // Loop dan buat setiap produk
        foreach ($products as $productData) {
            ProductType::firstOrCreate(
                ['name' => $productData['name']], // Kunci untuk mengecek duplikasi
                $productData // Data lengkap untuk dibuat jika belum ada
            );
        }

        $this->command->info('ProductTypeSeeder berhasil dijalankan.');
    }
}