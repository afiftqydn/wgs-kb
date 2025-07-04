<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanApplication;
use App\Models\Customer;
use App\Models\ProductType;
use App\Models\User;
use App\Models\Region;
use Carbon\Carbon;

class LoanApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai Loan Application Seeder...');

        //======================================================================
        // BAGIAN 1: MEMBUAT DATA SPESIFIK UNTUK TESTING
        //======================================================================
        $this->command->info('Membuat data spesifik untuk UAT...');

        $customer1 = Customer::where('email', 'ahmad.subagja@example.com')->first();
        $productUMKM = ProductType::where('name', 'Pembiayaan UMKM')->first();
        $adminUnitPnk = User::where('email', 'admin.pontianak@wgs.com')->first();
        $unitPontianak = Region::where('code', 'PNK01')->where('type', 'UNIT')->first();

        if ($customer1 && $productUMKM && $adminUnitPnk && $unitPontianak) {
            LoanApplication::firstOrCreate(
                ['application_number' => 'APP/' . date('Y/m') . '/S0001'],
                [
                    'customer_id' => $customer1->id,
                    'product_type_id' => $productUMKM->id,
                    'amount_requested' => 75000000.00, // Menggunakan 'amount_requested'
                    'purpose' => 'Pengembangan usaha toko kelontong Subagja',
                    'input_region_id' => $customer1->region_id,
                    'processing_region_id' => $unitPontianak->id,
                    'status' => 'SUBMITTED',
                    'created_by' => $adminUnitPnk->id, // Menggunakan 'created_by'
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(5),
                ]
            );

            LoanApplication::firstOrCreate(
                ['application_number' => 'APP/' . date('Y/m') . '/D0001'],
                [
                    'customer_id' => $customer1->id,
                    'product_type_id' => $productUMKM->id,
                    'amount_requested' => 20000000.00, // Menggunakan 'amount_requested'
                    'purpose' => 'Renovasi tempat usaha Subagja',
                    'input_region_id' => $customer1->region_id,
                    'processing_region_id' => $unitPontianak->id,
                    'status' => 'DRAFT',
                    'created_by' => $adminUnitPnk->id, // Menggunakan 'created_by'
                    'assigned_to' => $adminUnitPnk->id,
                    'created_at' => Carbon::now()->subDays(3),
                    'updated_at' => Carbon::now()->subDays(3),
                ]
            );
        }

        $customerKubuRaya = Customer::where('nik', '3210987654321004')->first();
        $creatorKubuRaya = User::where('email', 'admin.subunit.sry@wgs.com')->first();
        $productKUR = ProductType::where('name', 'Kredit Usaha Rakyat (KUR)')->first();

        if ($customerKubuRaya && $creatorKubuRaya && $productKUR) {
            LoanApplication::firstOrCreate(
                ['application_number' => 'UAT-SUBMITTED-KR-001'],
                [
                    'customer_id' => $customerKubuRaya->id,
                    'product_type_id' => $productKUR->id,
                    'amount_requested' => 25000000.00, // Menggunakan 'amount_requested'
                    'purpose' => 'Pengembangan usaha laundry di Kubu Raya',
                    'input_region_id' => $creatorKubuRaya->region_id,
                    'status' => 'SUBMITTED',
                    'created_by' => $creatorKubuRaya->id, // Menggunakan 'created_by'
                ]
            );
        }

        //======================================================================
        // BAGIAN 2: MEMBUAT DATA ACAK UNTUK VOLUME
        //======================================================================
        $this->command->info('Membuat 50 data acak untuk volume...');

        $customers = Customer::all();
        $productTypes = ProductType::all();
        $users = User::all();
        $regions = Region::whereNotNull('parent_id')->get();

        if ($customers->isEmpty() || $productTypes->isEmpty() || $users->isEmpty() || $regions->isEmpty()) {
            $this->command->warn('Data master tidak lengkap, data acak tidak dibuat.');
        } else {
            $statuses = ['DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'APPROVED', 'REJECTED'];
            for ($i = 0; $i < 50; $i++) {
                $productType = $productTypes->random();
                $creator = $users->random();
                $region = $regions->random();
                $status = (rand(1, 10) > 4) ? 'APPROVED' : $statuses[array_rand($statuses)];

                LoanApplication::create([
                    'customer_id' => $customers->random()->id,
                    'product_type_id' => $productType->id,
                    'created_by' => $creator->id, // Menggunakan 'created_by'
                    'assigned_to' => $status !== 'DRAFT' ? $users->random()->id : null,
                    'input_region_id' => $region->id,
                    'amount_requested' => rand(max(1000000, $productType->min_amount), min(500000000, $productType->max_amount)), // Menggunakan 'amount_requested'
                    'purpose' => 'Kebutuhan konsumtif (data acak)',
                    'status' => $status,
                    'created_at' => Carbon::now()->subDays(rand(0, 365)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 365)),
                ]);
            }
        }

        $this->command->info('Loan Application Seeder selesai dijalankan.');
    }
}
