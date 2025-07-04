<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductType;
use App\Models\ProductTypeRule;

class ProductTypeRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Menjalankan ProductTypeRuleSeeder...');

        // Data komisi berdasarkan PDF dan asumsi untuk produk lain
        $commissionData = [
            ['product_name' => 'KPR', 'commission' => 2000000],
            ['product_name' => 'Renovasi Rumah', 'commission' => 800000],
            ['product_name' => 'Pendidikan', 'commission' => 500000],
            ['product_name' => 'Pertanian/Perkebunan/Peternakan', 'commission' => 500000],
            // Menambahkan data komisi default untuk produk yang sudah ada di seeder Anda
            ['product_name' => 'Pembiayaan UMKM', 'commission' => 750000],
            ['product_name' => 'Kredit Usaha Rakyat (KUR)', 'commission' => 500000],
            ['product_name' => 'Pembiayaan Modal Kerja', 'commission' => 1500000],
        ];

        foreach ($commissionData as $data) {
            // Cari ProductType berdasarkan nama. Jika tidak ada, lewati.
            $productType = ProductType::where('name', $data['product_name'])->first();

            if ($productType) {
                // Buat aturan komisi untuk ProductType tersebut
                ProductTypeRule::firstOrCreate(
                    [
                        'product_type_id' => $productType->id,
                        'name' => 'Komisi Unit', // Nama aturan standar
                    ],
                    [
                        'type' => 'flat', // Semua komisi di PDF adalah flat
                        'value' => $data['commission'],
                    ]
                );
            }
        }

        $this->command->info('ProductTypeRuleSeeder selesai.');
    }
}
