<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arsip;
use App\Models\Customer;
use App\Models\LoanApplication; // <-- DIUBAH: model menjadi LoanApplication
use App\Models\User;

class ArsipSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $customer = Customer::first() ?? Customer::factory()->create();

        // DIUBAH: Menggunakan model LoanApplication
        $loanApplication = LoanApplication::first() ?? LoanApplication::factory()->create([
            'customer_id' => $customer->id,
        ]);

        Arsip::create([
            'nama_arsip' => 'Perjanjian Kredit Awal - ' . $customer->name, // <-- DIUBAH: $customer->name
            'kategori' => 'Dokumen Perjanjian',
            'tanggal_dokumen' => now()->subMonths(2),
            'keterangan' => 'Dokumen perjanjian pembiayaan modal usaha awal.',
            'customer_id' => $customer->id,             // <-- DIUBAH
            'loan_application_id' => $loanApplication->id, // <-- DIUBAH
            'file_path' => 'arsip-dokumen/dummy-file-1.pdf',
            'lokasi_fisik' => 'Lemari A, Rak 1, Map Merah',
            'status' => 'Aktif',
            'tanggal_retensi' => now()->addYears(5),
            'created_by' => $user->id,
        ]);

        // ... (lakukan perubahan serupa untuk Arsip::create() yang lain jika perlu) ...
        
        $this->command->info('Seeder Arsip berhasil dijalankan.');
    }
}