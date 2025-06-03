<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User; // Untuk mengambil user created_by
use App\Models\Region; // Untuk mengambil region_id
use App\Models\Referrer; // Untuk mengambil referrer_id

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil contoh data untuk relasi
        $adminSubUnitUser = User::where('email', 'admin.subunit.ptkkota@wgs.com')->first(); // User contoh dari UserSeeder
        $regionSubUnitPtkKota = Region::where('code', 'PNK01-PK')->first(); // Region contoh dari RegionSeeder

        $referrerBudi = Referrer::where('generated_referral_code', 'like', 'MRKT-KB00%')->first(); // Referrer contoh
        $referrerOrmas = Referrer::where('generated_referral_code', 'like', 'ORMS-PNK01%')->first(); // Referrer contoh lain

        $customers = [];

        // Nasabah 1 (dengan referrer)
        if ($adminSubUnitUser && $regionSubUnitPtkKota && $referrerBudi) {
            $customers[] = [
                'nik' => '3270011203900001', // Contoh NIK
                'name' => 'Ahmad Subagja',
                'phone' => '081200010001',
                'email' => 'ahmad.subagja@example.com',
                'address' => 'Jl. Merdeka No. 1, Pontianak Kota',
                'region_id' => $regionSubUnitPtkKota->id,
                'created_by' => $adminSubUnitUser->id,
                'referrer_id' => $referrerBudi->id,
                'referral_code_used' => $referrerBudi->generated_referral_code,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // Nasabah 2 (dengan referrer lain)
        if ($adminSubUnitUser && $regionSubUnitPtkKota && $referrerOrmas) {
            $customers[] = [
                'nik' => '3270011203900002', // Contoh NIK
                'name' => 'Siti Zulaikha',
                'phone' => '081200010002',
                'email' => 'siti.zulaikha@example.com',
                'address' => 'Jl. Pahlawan No. 2, Pontianak Kota',
                'region_id' => $regionSubUnitPtkKota->id,
                'created_by' => $adminSubUnitUser->id,
                'referrer_id' => $referrerOrmas->id,
                'referral_code_used' => $referrerOrmas->generated_referral_code,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        // Nasabah 3 (tanpa referrer)
        if ($adminSubUnitUser && $regionSubUnitPtkKota) {
            $customers[] = [
                'nik' => '3270011203900003', // Contoh NIK
                'name' => 'Bambang Pamungkas',
                'phone' => '081200010003',
                'email' => 'bambang.p@example.com',
                'address' => 'Jl. Reformasi No. 3, Pontianak Kota',
                'region_id' => $regionSubUnitPtkKota->id,
                'created_by' => $adminSubUnitUser->id,
                'referrer_id' => null,
                'referral_code_used' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        if (!empty($customers)) {
            DB::table('customers')->insert($customers);
        }
    }
}
