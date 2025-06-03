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
        Schema::create('referrers', function (Blueprint $table) {
            $table->id(); // Kolom id (Primary Key, Auto Increment)
            $table->string('name'); // Nama lengkap pihak marketing atau nama Ormas
            $table->enum('type', ['MARKETING', 'ORMAS']); // Tipe referrer
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null'); // Relasi ke tabel regions (wilayah operasional WGS tempat referrer terdaftar/beroperasi). Nullable jika referrer tidak terikat wilayah spesifik.
            $table->string('unique_person_organization_code')->comment('Kode unik internal untuk marketing/ormas per wilayah/tipe jika diperlukan'); // Misal: MKT001, ORM001
            $table->string('generated_referral_code')->unique(); // Kode referral lengkap yang akan digunakan nasabah, contoh: MRKT-KB00-MKT001
            $table->string('contact_person')->nullable(); // Nama narahubung jika tipe 'ORMAS' atau jika diperlukan
            $table->string('phone')->nullable(); // Nomor telepon kontak
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE'); // Status keaktifan referrer
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrers');
    }
};
