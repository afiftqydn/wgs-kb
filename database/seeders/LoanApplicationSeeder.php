<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Customer;
use App\Models\ProductType;
use App\Models\User;
use App\Models\Region;

class LoanApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data contoh dari seeder lain
        $customer1 = Customer::where('email', 'ahmad.subagja@example.com')->first();
        $productUMKM = ProductType::where('name', 'Pembiayaan UMKM')->first();
        $adminSubUnitUser = User::where('email', 'admin.subunit.ptkkota@wgs.com')->first();
        $regionSubUnitPtkKota = Region::where('code', 'PNK01-PK')->first();

        if ($customer1 && $productUMKM && $adminSubUnitUser && $regionSubUnitPtkKota) {
            DB::table('loan_applications')->insert([
                [
                    'application_number' => 'APP/WGS/2025/06/00001', // Contoh nomor aplikasi
                    'customer_id' => $customer1->id,
                    'product_type_id' => $productUMKM->id,
                    'amount_requested' => 50000000.00, // 50 Juta
                    'purpose' => 'Pengembangan usaha warung kelontong',
                    'input_region_id' => $regionSubUnitPtkKota->id,
                    'processing_region_id' => $regionSubUnitPtkKota->id, // Awalnya diproses di tempat input
                    'status' => 'DRAFT',
                    'created_by' => $adminSubUnitUser->id,
                    'assigned_to' => $adminSubUnitUser->id, // Awalnya ditugaskan ke pembuat
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }
    }
}
