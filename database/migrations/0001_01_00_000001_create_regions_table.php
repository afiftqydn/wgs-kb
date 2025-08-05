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
        Schema::create('regions', function (Blueprint $table) {
            $table->id(); // Kolom id (Primary Key, Auto Increment)
            $table->string('name'); // Kolom name (nama wilayah)
            $table->enum('type', ['CABANG', 'UNIT', 'SUBUNIT']); // Kolom type (jenis wilayah: Cabang, Unit, SubUnit)
            $table->foreignId('parent_id')->nullable()->constrained('regions')->onDelete('cascade'); // Kolom parent_id (untuk hierarki, self-reference ke tabel regions sendiri). Nullable karena Cabang tidak punya parent. onDelete('cascade') berarti jika parent dihapus, children juga ikut terhapus (hati-hati dengan ini, atau bisa gunakan onDelete('set null'))
            $table->string('code')->unique()->nullable(); // Kolom code (kode unik wilayah, misal untuk kode di referral). Unique dan bisa null jika ada kasus belum ada kode.
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE'); // Kolom status (ACTIVE atau INACTIVE)
            $table->timestamps(); // Kolom created_at dan updated_at
            $table->string('maps_url')->nullable(); // Kolom untuk menyimpan link Google Maps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
