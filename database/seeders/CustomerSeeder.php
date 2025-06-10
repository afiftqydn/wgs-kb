<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Region;
use App\Models\Referrer;
use App\Models\Customer; // Gunakan model

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $adminSubUnitUser = User::where('email', 'admin.subunit.ptkkota@wgs.com')->first();
        $regionSubUnitPtkKota = Region::where('code', 'PNK01-PK')->first();
        
        // Ambil referrer berdasarkan kode yang di-generate (jika ReferrerSeeder sudah jalan)
        // Atau buat contoh referrer sederhana di sini jika perlu
        $referrerBudi = Referrer::where('generated_referral_code', 'like', '%MKT001CB%')->first(); // Sesuaikan pencarian
        $referrerOrmas = Referrer::where('generated_referral_code', 'like', '%ORM001PNK%')->first(); // Sesuaikan pencarian

        if (!$adminSubUnitUser || !$regionSubUnitPtkKota ) {
             $this->command->warn('Data Admin SubUnit atau Region SubUnit contoh tidak ditemukan. CustomerSeeder mungkin tidak berjalan maksimal.');
             // Tidak perlu return, karena referrer bisa null
        }
        
        Customer::firstOrCreate(
            ['nik' => '3270011203900001'],
            [
                'name' => 'Ahmad Subagja',
                'phone' => '081200010001',
                'email' => 'ahmad.subagja@example.com',
                'address' => 'Jl. Merdeka No. 1, Pontianak Kota',
                'region_id' => $regionSubUnitPtkKota ? $regionSubUnitPtkKota->id : null,
                'created_by' => $adminSubUnitUser ? $adminSubUnitUser->id : null,
                'referrer_id' => $referrerBudi ? $referrerBudi->id : null,
                'referral_code_used' => $referrerBudi ? $referrerBudi->generated_referral_code : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        Customer::firstOrCreate(
            ['nik' => '3270011203900002'],
            [
                'name' => 'Siti Zulaikha',
                'phone' => '081200010002',
                'email' => 'siti.zulaikha@example.com',
                'address' => 'Jl. Pahlawan No. 2, Pontianak Kota',
                'region_id' => $regionSubUnitPtkKota ? $regionSubUnitPtkKota->id : null,
                'created_by' => $adminSubUnitUser ? $adminSubUnitUser->id : null,
                'referrer_id' => $referrerOrmas ? $referrerOrmas->id : null,
                'referral_code_used' => $referrerOrmas ? $referrerOrmas->generated_referral_code : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        Customer::firstOrCreate(
            ['nik' => '3270011203900003'],
            [
                'name' => 'Bambang Pamungkas',
                'phone' => '081200010003',
                'email' => 'bambang.p@example.com',
                'address' => 'Jl. Reformasi No. 3, Pontianak Kota',
                'region_id' => $regionSubUnitPtkKota ? $regionSubUnitPtkKota->id : null,
                'created_by' => $adminSubUnitUser ? $adminSubUnitUser->id : null,
                'referrer_id' => null,
                'referral_code_used' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        $this->command->info('CustomerSeeder berhasil dijalankan.');
    }
}