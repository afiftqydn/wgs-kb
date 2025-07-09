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
        $this->command->info('Menjalankan ProductTypeRuleSeeder dengan data yang diperbaiki...');

        // Data komisi dengan nama produk yang sudah disesuaikan
        // Perhatikan bahwa nama produk sekarang cocok dengan yang ada di ProductTypeSeeder
        $commissionData = [
            // Fee untuk Unit
            ['product_name' => 'Kredit KPR', 'commission' => 2000000, 'recipient' => 'Unit'],
            ['product_name' => 'Kredit Renovasi Rumah', 'commission' => 800000, 'recipient' => 'Unit'],
            ['product_name' => 'Kredit Pendidikan', 'commission' => 500000, 'recipient' => 'Unit'],
            ['product_name' => 'Kredit Pertanian, Perkebunan Dan Kelautan', 'commission' => 500000, 'recipient' => 'Unit'],
            // Asumsi 'Pembiayaan UMKM' sama dengan 'Kredit Modal Kerja'
            ['product_name' => 'Kredit Modal Kerja', 'commission' => 750000, 'recipient' => 'Unit'],
            ['product_name' => 'Kredit Modal Kerja', 'commission' => 1500000, 'recipient' => 'Unit'], // Anda punya dua aturan untuk ini, saya sertakan keduanya
            
            // Aturan untuk semua jenis KUR
            ['product_name' => 'KUR SUPERMI (Super Mikro)', 'commission' => 500000, 'recipient' => 'Unit'],
            ['product_name' => 'KUR Mikro Small', 'commission' => 500000, 'recipient' => 'Unit'],
            ['product_name' => 'KUR Mikro Medium', 'commission' => 500000, 'recipient' => 'Unit'],
            
            // Fee untuk Referral
            ['product_name' => 'Kredit KPR', 'commission' => 500000, 'recipient' => 'Referral'],
        ];

        foreach ($commissionData as $data) {
            $productType = ProductType::where('name', $data['product_name'])->first();

            if ($productType) {
                // Buat aturan komisi untuk ProductType yang ditemukan
                ProductTypeRule::firstOrCreate(
                    [
                        'product_type_id' => $productType->id,
                        'name' => 'Komisi ' . $data['recipient'],
                        'recipient_level' => $data['recipient'],
                    ],
                    [
                        'type' => 'flat',
                        'value' => $data['commission'],
                    ]
                );
            } else {
                // Beri peringatan jika produk tidak ditemukan
                $this->command->warn('Peringatan: ProductType dengan nama "' . $data['product_name'] . '" tidak ditemukan. Aturan dilewati.');
            }
        }

        $this->command->info('ProductTypeRuleSeeder selesai dijalankan.');
    }
}