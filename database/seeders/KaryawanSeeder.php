<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Karyawan; // Pastikan model Karyawan sudah ada
use App\Models\Region;   // Menggunakan model Region untuk mengambil data kantor
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker; // Import kelas Faker

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Inisialisasi Faker dengan lokal Indonesia untuk data yang lebih relevan
        $faker = Faker::create('id_ID');

        // Ambil semua ID dari tabel regions untuk diassign ke karyawan secara acak
        // Ini memastikan tidak ada error foreign key constraint
        $regionIds = Region::pluck('id')->all();

        // Jika tidak ada region, hentikan seeder untuk mencegah error
        if (empty($regionIds)) {
            $this->command->error('Tabel "regions" kosong. Harap jalankan RegionSeeder terlebih dahulu.');
            return;
        }

        // Tentukan pilihan untuk kolom enum agar datanya konsisten
        $jenisKelaminOptions = ['Pria', 'Wanita'];
        $statusPernikahanOptions = ['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati'];
        $agamaOptions = ['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
        $statusKaryawanOptions = ['Tetap/PKWTT', 'Kontrak/PKWT', 'Magang', 'Harian'];

        // Loop untuk membuat 10 data karyawan dummy
        for ($i = 0; $i < 10; $i++) {
            
            $statusKaryawan = $faker->randomElement($statusKaryawanOptions);
            $tanggalBergabung = $faker->dateTimeBetween('-5 years', 'now');
            $tanggalBerakhirKontrak = null;

            // Jika statusnya kontrak, maka generate tanggal berakhir kontrak
            if ($statusKaryawan === 'Kontrak/PKWT') {
                $tanggalBerakhirKontrak = $faker->dateTimeBetween($tanggalBergabung, '+2 years');
            }

            Karyawan::create([
                // --- INFORMASI UTAMA ---
                'nama_lengkap' => $faker->name,
                'jabatan' => $faker->jobTitle,
                'email' => $faker->unique()->safeEmail,
                'no_hp' => $faker->phoneNumber,

                // --- DATA PRIBADI ---
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '-20 years'),
                'jenis_kelamin' => $faker->randomElement($jenisKelaminOptions),
                'alamat_ktp' => $faker->address,
                'alamat_domisili' => $faker->address,
                'status_pernikahan' => $faker->randomElement($statusPernikahanOptions),
                'agama' => $faker->randomElement($agamaOptions),

                // --- DATA KEPEGAWAIAN ---
                'region_id' => $faker->randomElement($regionIds),
                'status_karyawan' => $statusKaryawan,
                'tanggal_bergabung' => $tanggalBergabung,
                'tanggal_berakhir_kontrak' => $tanggalBerakhirKontrak,

                // --- DATA LEGAL & FINANSIAL ---
                'npwp' => $faker->unique()->numerify('##.###.###.#-###.###'),
                'bpjs_ketenagakerjaan' => $faker->unique()->numerify('###############'),
                'bpjs_kesehatan' => $faker->unique()->numerify('###############'),
                'nama_bank' => $faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga']),
                'nomor_rekening' => $faker->creditCardNumber,
                'nama_pemilik_rekening' => $faker->name,

                // --- KONTAK DARURAT ---
                'nama_kontak_darurat' => $faker->name,
                'hubungan_kontak_darurat' => $faker->randomElement(['Orang Tua', 'Pasangan', 'Saudara Kandung', 'Kerabat']),
                'no_hp_kontak_darurat' => $faker->phoneNumber,

                // --- DOKUMEN DIGITAL (PATH FILE) ---
                'pas_foto' => 'documents/pas_foto/' . $faker->uuid . '.jpg',
                'file_ktp' => 'documents/ktp/' . $faker->uuid . '.pdf',
                'file_npwp' => 'documents/npwp/' . $faker->uuid . '.pdf',
                'file_perjanjian_kerja' => 'documents/perjanjian/' . $faker->uuid . '.pdf',
                
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('KaryawanSeeder berhasil dijalankan dan 10 data karyawan telah ditambahkan.');
    }
}
