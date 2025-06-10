<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LoanApplication;
use App\Models\Customer;
use App\Models\ProductType;
use App\Models\User;
use App\Models\Region;
use Illuminate\Support\Carbon;

class LoanApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $customer1 = Customer::where('email', 'ahmad.subagja@example.com')->first();
        $productUMKM = ProductType::where('name', 'Pembiayaan UMKM')->first();
        // Ambil admin unit dari Pontianak, karena customer domisili di sana & processing region awal dari sana
        $adminUnitPnk = User::where('email', 'admin.pontianak@wgs.com')->first(); 
        $unitPontianak = Region::where('code', 'PNK01')->where('type', 'UNIT')->first();

        if ($customer1 && $productUMKM && $adminUnitPnk && $unitPontianak) {
            // Permohonan yang langsung SUBMITTED dan akan ditugaskan otomatis ke Admin Unit Pontianak
            LoanApplication::firstOrCreate(
                ['application_number' => 'APP/' . date('Y/m') . '/S0001'], // Buat nomor unik untuk seeder
                [
                    'customer_id' => $customer1->id,
                    'product_type_id' => $productUMKM->id,
                    'amount_requested' => 75000000.00, // 75 Juta (di bawah threshold UMKM)
                    'purpose' => 'Pengembangan usaha toko kelontong Subagja',
                    'input_region_id' => $customer1->region_id, // Asumsi region nasabah adalah tempat input
                    'processing_region_id' => $unitPontianak->id, // Harus ID UNIT
                    'status' => 'SUBMITTED', // Langsung submitted
                    'created_by' => $adminUnitPnk->id, // Diinput oleh Admin Unit (contoh)
                    // 'assigned_to' akan diisi oleh model event
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(5),
                ]
            );

            // Permohonan DRAFT
             LoanApplication::firstOrCreate(
                ['application_number' => 'APP/' . date('Y/m') . '/D0001'],
                [
                    'customer_id' => $customer1->id,
                    'product_type_id' => $productUMKM->id,
                    'amount_requested' => 20000000.00,
                    'purpose' => 'Renovasi tempat usaha Subagja',
                    'input_region_id' => $customer1->region_id,
                    'processing_region_id' => $unitPontianak->id,
                    'status' => 'DRAFT',
                    'created_by' => $adminUnitPnk->id,
                    'assigned_to' => $adminUnitPnk->id, // Draft bisa ditugaskan ke diri sendiri dulu
                    'created_at' => Carbon::now()->subDays(3),
                    'updated_at' => Carbon::now()->subDays(3),
                ]
            );
        }

        $customer2 = Customer::where('email', 'siti.zulaikha@example.com')->first();
        $productKUR = ProductType::where('name', 'Kredit Usaha Rakyat (KUR)')->first();
        if($customer2 && $productKUR && $adminUnitPnk && $unitPontianak) {
            // Permohonan lain yang nominalnya besar dan akan butuh eskalasi
            LoanApplication::firstOrCreate(
                ['application_number' => 'APP/' . date('Y/m') . '/S0002'],
                [
                    'customer_id' => $customer2->id,
                    'product_type_id' => $productKUR->id,
                    'amount_requested' => 60000000.00, // 60 Juta (di atas threshold KUR 50 Juta)
                    'purpose' => 'Pembelian alat untuk usaha katering Zulaikha',
                    'input_region_id' => $customer2->region_id,
                    'processing_region_id' => $unitPontianak->id,
                    'status' => 'SUBMITTED',
                    'created_by' => $adminUnitPnk->id,
                    'created_at' => Carbon::now()->subDays(2),
                    'updated_at' => Carbon::now()->subDays(2),
                ]
            );
        }

        $this->command->info('LoanApplicationSeeder berhasil dijalankan.');
    }
}