<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Region;
use App\Models\Referrer; // Gunakan model

class ReferrerSeeder extends Seeder
{
    public function run(): void
    {
        $regionCabangKalbar = Region::where('code', 'KB00')->first();
        $regionUnitPontianak = Region::where('code', 'PNK01')->first();

        $referrersData = [];

        if ($regionCabangKalbar) {
            $referrersData[] = [
                'name' => 'Budi Marketing Cabang', 'type' => 'MARKETING',
                'region_id' => $regionCabangKalbar->id,
                'unique_person_organization_code' => 'MKT001CB',
                'generated_referral_code' => 'MRKT-' . $regionCabangKalbar->code . '-MKT001CB',
                'contact_person' => 'Budi Santoso', 'phone' => '081234567890',
                'status' => 'ACTIVE', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
            ];
        }
        if ($regionUnitPontianak) {
            $referrersData[] = [
                'name' => 'Ormas Maju Bersama Pontianak', 'type' => 'ORMAS',
                'region_id' => $regionUnitPontianak->id,
                'unique_person_organization_code' => 'ORM001PNK',
                'generated_referral_code' => 'ORMS-' . $regionUnitPontianak->code . '-ORM001PNK',
                'contact_person' => 'Siti Aminah', 'phone' => '085678901234',
                'status' => 'ACTIVE', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
            ];
            $referrersData[] = [
                'name' => 'Citra Marketing Pontianak', 'type' => 'MARKETING',
                'region_id' => $regionUnitPontianak->id,
                'unique_person_organization_code' => 'MKT002PNK',
                'generated_referral_code' => 'MRKT-' . $regionUnitPontianak->code . '-MKT002PNK',
                'contact_person' => 'Citra Lestari', 'phone' => '081122334455',
                'status' => 'ACTIVE', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
            ];
        }
        
        if (!empty($referrersData)) {
            foreach($referrersData as $referrer) {
                Referrer::firstOrCreate(['generated_referral_code' => $referrer['generated_referral_code']], $referrer);
            }
        }
        $this->command->info('ReferrerSeeder berhasil dijalankan.');
    }
}