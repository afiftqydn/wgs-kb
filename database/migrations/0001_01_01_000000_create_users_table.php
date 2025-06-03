<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Kolom tambahan untuk WGS
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null'); // Relasi ke tabel regions, bisa null jika Tim IT tidak terikat wilayah tertentu atau jika ada user global lain. onDelete('set null') agar jika region dihapus, user tidak ikut terhapus.
            $table->string('wgs_job_title')->nullable(); // Menyimpan nama jabatan internal WGS, misal "Kepala Unit Pontianak", "Staf SubUnit Teluk Pakedai". Bisa juga enum jika daftar jabatannya baku.
            $table->enum('wgs_level', ['CABANG', 'UNIT', 'SUBUNIT', 'GLOBAL'])->nullable(); // Level pengguna berdasarkan struktur organisasi WGS. 'GLOBAL' untuk Tim IT atau peran yang tidak terikat unit/cabang tertentu.

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
