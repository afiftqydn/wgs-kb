<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Region;
use App\Models\Referrer;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $adminSubUnitUser = User::where('email', 'admin.subunit.ptkkota@wgs.com')->first();
        $regionSubUnitPtkKota = Region::where('code', 'PNK01-PK')->first();
        $referrerBudi = Referrer::where('generated_referral_code', 'like', '%MKT001CB%')->first();
        $referrerOrmas = Referrer::where('generated_referral_code', 'like', '%ORM001PNK%')->first();

        if (!$adminSubUnitUser || !$regionSubUnitPtkKota) {
            $this->command->warn('Data prasyarat (Admin SubUnit/Region SubUnit) tidak ditemukan untuk CustomerSeeder.');
            return;
        }
        
        Customer::firstOrCreate(['nik' => '3270011203900001'], [
            'name' => 'Ahmad Subagja', 'phone' => '081200010001', 'email' => 'ahmad.subagja@example.com',
            'address' => 'Jl. Merdeka No. 1, Pontianak Kota',
            'region_id' => $regionSubUnitPtkKota->id, 'created_by' => $adminSubUnitUser->id,
            'referrer_id' => $referrerBudi ? $referrerBudi->id : null,
            'referral_code_used' => $referrerBudi ? $referrerBudi->generated_referral_code : null,
        ]);
        Customer::firstOrCreate(['nik' => '3270011203900002'], [
            'name' => 'Siti Zulaikha', 'phone' => '081200010002', 'email' => 'siti.zulaikha@example.com',
            'address' => 'Jl. Pahlawan No. 2, Pontianak Kota',
            'region_id' => $regionSubUnitPtkKota->id, 'created_by' => $adminSubUnitUser->id,
            'referrer_id' => $referrerOrmas ? $referrerOrmas->id : null,
            'referral_code_used' => $referrerOrmas ? $referrerOrmas->generated_referral_code : null,
        ]);
        Customer::firstOrCreate(['nik' => '3270011203900003'], [
            'name' => 'Bambang Pamungkas', 'phone' => '081200010003', 'email' => 'bambang.p@example.com',
            'address' => 'Jl. Reformasi No. 3, Pontianak Kota',
            'region_id' => $regionSubUnitPtkKota->id, 'created_by' => $adminSubUnitUser->id,
        ]);


        // --- DATA CUSTOMER BARU UNTUK UJI WILAYAH KUBU RAYA ---
        $creatorKubuRaya = User::where('email', 'kasubunit.sry@wgs.com')->first();
        $regionKubuRaya = Region::where('code', 'KRY01-SRY')->first();

        if ($creatorKubuRaya && $regionKubuRaya) {
            Customer::firstOrCreate(['nik' => '3210987654321004'], [
                'name' => 'Dewi Lestari (Test UAT Kubu Raya)',
                'phone' => '089876543210',
                'email' => 'dewi.lestari.uat@example.com',
                'address' => 'Jl. Arteri Supadio No. 50, Sungai Raya',
                'region_id' => $regionKubuRaya->id,
                'created_by' => $creatorKubuRaya->id,
            ]);
        }
        // ---------------------------------------------------------


        $this->command->info('CustomerSeeder berhasil dijalankan.');
    }
}