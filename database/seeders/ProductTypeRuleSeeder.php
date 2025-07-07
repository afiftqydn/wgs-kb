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
            // recipient_level ditambahkan di sini
            ['product_name' => 'KPR', 'commission' => 2000000, 'recipient' => 'Unit'],
            ['product_name' => 'Renovasi Rumah', 'commission' => 800000, 'recipient' => 'Unit'],
            ['product_name' => 'Pendidikan', 'commission' => 500000, 'recipient' => 'Unit'],
            ['product_name' => 'Pertanian/Perkebunan/Peternakan', 'commission' => 500000, 'recipient' => 'Unit'],
            ['product_name' => 'Pembiayaan UMKM', 'commission' => 750000, 'recipient' => 'Unit'],
            ['product_name' => 'Kredit Usaha Rakyat (KUR)', 'commission' => 500000, 'recipient' => 'Unit'],
            ['product_name' => 'Pembiayaan Modal Kerja', 'commission' => 1500000, 'recipient' => 'Unit'],
            // Contoh untuk Fee Referral
            ['product_name' => 'KPR', 'commission' => 500000, 'recipient' => 'Referral'],
        ];

        foreach ($commissionData as $data) {
            $productType = ProductType::where('name', $data['product_name'])->first();

            if ($productType) {
                // Buat aturan komisi untuk ProductType tersebut
                ProductTypeRule::firstOrCreate(
                    [
                        'product_type_id' => $productType->id,
                        'name' => 'Komisi ' . $data['recipient'], // e.g., "Komisi Unit", "Komisi Referral"
                        'recipient_level' => $data['recipient'], // Mengisi kolom yang hilang
                    ],
                    [
                        'type' => 'flat',
                        'value' => $data['commission'],
                    ]
                );
            }
        }

        $this->command->info('ProductTypeRuleSeeder selesai.');
    }
}
