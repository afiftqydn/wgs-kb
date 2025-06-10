<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ProductType; // Gunakan model

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        // ProductType::truncate(); // Hati-hati

        ProductType::firstOrCreate(
            ['name' => 'Pembiayaan UMKM'],
            [
                'description' => 'Produk pembiayaan untuk Usaha Mikro, Kecil, dan Menengah.',
                'min_amount' => 5000000.00,
                'max_amount' => 500000000.00,
                'required_documents' => json_encode(['KTP Pemilik', 'NPWP Usaha', 'SIUP/NIB', 'Laporan Keuangan 1 Tahun Terakhir']),
                'escalation_threshold' => 100000000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        ProductType::firstOrCreate(
            ['name' => 'Kredit Usaha Rakyat (KUR)'],
            [
                'description' => 'Program kredit/pembiayaan dari pemerintah untuk UMKM.',
                'min_amount' => 1000000.00,
                'max_amount' => 100000000.00,
                'required_documents' => json_encode(['KTP Pemohon', 'Kartu Keluarga', 'Surat Keterangan Usaha dari Desa/Kelurahan']),
                'escalation_threshold' => 50000000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        ProductType::firstOrCreate(
            ['name' => 'Pembiayaan Modal Kerja'],
            [
                'description' => 'Pembiayaan untuk kebutuhan modal kerja usaha.',
                'min_amount' => 25000000.00,
                'max_amount' => 2000000000.00,
                'required_documents' => json_encode(['KTP Direksi', 'NPWP Perusahaan', 'Akta Pendirian & Perubahan', 'Laporan Keuangan 2 Tahun Terakhir']),
                'escalation_threshold' => 500000000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        ProductType::firstOrCreate(
            ['name' => 'Pembiayaan Haji & Umroh'],
            [
                'description' => 'Pembiayaan untuk keberangkatan Haji dan Umroh.',
                'min_amount' => 10000000.00,
                'max_amount' => 150000000.00,
                'required_documents' => json_encode(['KTP Pemohon', 'Kartu Keluarga', 'Paspor (jika ada)']),
                'escalation_threshold' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        $this->command->info('ProductTypeSeeder berhasil dijalankan.');
    }
}