<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanApplication;
use App\Models\Customer;
use App\Models\ProductType;
use App\Models\User;
use App\Models\Region;
use App\Models\Referrer;
use Carbon\Carbon;

class LoanApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('== Memulai Loan Application Seeder...');

        // Ambil data master
        $adminUnitPnk = User::where('email', 'admin.pontianak@wgs.com')->first();
        $customer1 = Customer::where('email', 'ahmad.subagja@example.com')->first();
        $productKUR = ProductType::where('name', 'KUR SUPERMI (Super Mikro)')->first();
        $productKPR = ProductType::where('name', 'Kredit KPR')->first();
        $unitPontianak = Region::where('code', 'PNK01')->first();
        $referralBudi = Referrer::where('name', 'like', 'Budi Marketing Cabang%')->first();
        $creatorKubuRaya = User::where('email', 'admin.subunit.sry@wgs.com')->first();
        $customerKubuRaya = Customer::where('nik', '3210987654321004')->first();

        // Validasi data penting
        if (!$adminUnitPnk || !$customer1 || !$productKUR || !$productKPR || !$unitPontianak) {
            $this->command->error('Data master tidak lengkap. Seeder dibatalkan.');
            return;
        }

        // =========================================================================
        // BAGIAN 1: Data Spesifik untuk UAT & Komisi (Unit dan Referral)
        // =========================================================================
        $this->command->info('-- Membuat data spesifik untuk UAT & Komisi...');

        // UAT - Pengajuan dari Sub Unit Kubu Raya
        if ($customerKubuRaya && $creatorKubuRaya) {
            LoanApplication::firstOrCreate(
                ['application_number' => 'UAT-SUBMITTED-KR-001'],
                [
                    'customer_id' => $customerKubuRaya->id,
                    'product_type_id' => $productKUR->id,
                    'amount_requested' => 25000000.00,
                    'purpose' => 'Pengembangan usaha laundry di Kubu Raya',
                    'input_region_id' => $creatorKubuRaya->region_id,
                    'status' => 'SUBMITTED',
                    'created_by' => $creatorKubuRaya->id,
                ]
            );
        }

        // Komisi Unit (tanpa referral)
        LoanApplication::firstOrCreate(
            ['application_number' => 'TEST/COMM/UNIT/001'],
            [
                'customer_id' => $customer1->id,
                'product_type_id' => $productKPR->id,
                'referrer_id' => null,
                'amount_requested' => 200000000.00,
                'purpose' => 'Pengajuan KPR untuk komisi unit.',
                'input_region_id' => $unitPontianak->id,
                'status' => 'APPROVED',
                'created_by' => $adminUnitPnk->id,
                'created_at' => Carbon::now()->startOfMonth()->addDays(5),
            ]
        );

        // Komisi Referral
        if ($referralBudi) {
            LoanApplication::firstOrCreate(
                ['application_number' => 'TEST/COMM/REF/001'],
                [
                    'customer_id' => $customer1->id,
                    'product_type_id' => $productKPR->id,
                    'referrer_id' => $referralBudi->id,
                    'amount_requested' => 300000000.00,
                    'purpose' => 'Pengajuan KPR dengan fee untuk referral.',
                    'input_region_id' => $unitPontianak->id,
                    'status' => 'APPROVED',
                    'created_by' => $adminUnitPnk->id,
                    'created_at' => Carbon::now()->startOfMonth()->addDays(10),
                ]
            );
        }

        // APPROVED bulan lalu
        LoanApplication::firstOrCreate(
            ['application_number' => 'TEST/COMM/LASTMONTH/001'],
            [
                'customer_id' => $customer1->id,
                'product_type_id' => $productKPR->id,
                'referrer_id' => null,
                'amount_requested' => 150000000.00,
                'purpose' => 'Pengajuan KPR bulan lalu.',
                'input_region_id' => $unitPontianak->id,
                'status' => 'APPROVED',
                'created_by' => $adminUnitPnk->id,
                'created_at' => Carbon::now()->subMonth()->startOfMonth()->addDays(3),
            ]
        );

        // =========================================================================
        // BAGIAN 2: Data Acak untuk Testing Volume
        // =========================================================================
        $this->command->info('-- Membuat 50 data acak untuk volume...');

        $customers = Customer::all();
        $productTypes = ProductType::all();
        $users = User::all();
        $regions = Region::whereNotNull('parent_id')->get();
        $statuses = ['DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'APPROVED', 'REJECTED'];

        if ($customers->isEmpty() || $productTypes->isEmpty() || $users->isEmpty() || $regions->isEmpty()) {
            $this->command->warn('Data master tidak lengkap. Data acak tidak dibuat.');
        } else {
            for ($i = 0; $i < 50; $i++) {
                $productType = $productTypes->random();
                $creator = $users->random();
                $region = $regions->random();
                $status = (rand(1, 10) > 4) ? 'APPROVED' : $statuses[array_rand($statuses)];

                LoanApplication::create([
                    'customer_id' => $customers->random()->id,
                    'product_type_id' => $productType->id,
                    'created_by' => $creator->id,
                    'assigned_to' => $status !== 'DRAFT' ? $users->random()->id : null,
                    'input_region_id' => $region->id,
                    'amount_requested' => rand(max(1000000, $productType->min_amount), min(500000000, $productType->max_amount)),
                    'purpose' => 'Kebutuhan konsumtif (data acak)',
                    'status' => $status,
                    'created_at' => Carbon::now()->subDays(rand(0, 365)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 365)),
                ]);
            }
        }

        $this->command->info('== Loan Application Seeder selesai dijalankan.');
    }
}
