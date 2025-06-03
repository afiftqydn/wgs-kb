<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_types')->insert([
            [
                'name' => 'Pembiayaan UMKM',
                'description' => 'Produk pembiayaan untuk Usaha Mikro, Kecil, dan Menengah.',
                'min_amount' => 5000000.00, // 5 Juta
                'max_amount' => 500000000.00, // 500 Juta
                'required_documents' => json_encode(['KTP Pemilik', 'NPWP Usaha', 'SIUP/NIB', 'Laporan Keuangan 1 Tahun Terakhir']),
                'escalation_threshold' => 100000000.00, // 100 Juta
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Kredit Usaha Rakyat (KUR)',
                'description' => 'Program kredit/pembiayaan dari pemerintah untuk UMKM.',
                'min_amount' => 1000000.00, // 1 Juta
                'max_amount' => 100000000.00, // 100 Juta
                'required_documents' => json_encode(['KTP Pemohon', 'Kartu Keluarga', 'Surat Keterangan Usaha dari Desa/Kelurahan']),
                'escalation_threshold' => 50000000.00, // 50 Juta
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pembiayaan Modal Kerja',
                'description' => 'Pembiayaan untuk kebutuhan modal kerja usaha.',
                'min_amount' => 25000000.00, // 25 Juta
                'max_amount' => 2000000000.00, // 2 Miliar
                'required_documents' => json_encode(['KTP Direksi', 'NPWP Perusahaan', 'Akta Pendirian & Perubahan', 'Company Profile', 'Laporan Keuangan 2 Tahun Terakhir']),
                'escalation_threshold' => 500000000.00, // 500 Juta
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Pembiayaan Haji & Umroh', // Contoh produk lain [cite: 33]
                'description' => 'Pembiayaan untuk keberangkatan Haji dan Umroh.',
                'min_amount' => 10000000.00, // 10 Juta
                'max_amount' => 150000000.00, // 150 Juta
                'required_documents' => json_encode(['KTP Pemohon', 'Kartu Keluarga', 'Paspor (jika ada)']),
                'escalation_threshold' => null, // Mungkin tidak ada eskalasi khusus atau diatur berbeda
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
