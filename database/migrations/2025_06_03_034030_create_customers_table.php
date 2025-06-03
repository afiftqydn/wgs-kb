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
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // Kolom id (Primary Key, Auto Increment)
            $table->string('nik')->unique()->nullable(); // Nomor Induk Kependudukan, unik, bisa null jika belum ada saat input awal [cite: 35]
            $table->string('name'); // Nama lengkap nasabah [cite: 35]
            $table->string('phone')->nullable(); // Nomor telepon nasabah [cite: 35]
            $table->string('email')->nullable()->unique(); // Alamat email nasabah, bisa null, sebaiknya unik jika diisi [cite: 35]
            $table->text('address')->nullable(); // Alamat lengkap nasabah [cite: 35]

            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null'); // Wilayah domisili nasabah (opsional) [cite: 35]
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User WGS yang menginput data nasabah ini [cite: 35]

            // Kolom untuk fitur referral
            $table->foreignId('referrer_id')->nullable()->constrained('referrers')->onDelete('set null'); // Pihak referrer yang membawa nasabah ini
            $table->string('referral_code_used')->nullable(); // Kode referral yang digunakan saat nasabah ini didaftarkan

            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
