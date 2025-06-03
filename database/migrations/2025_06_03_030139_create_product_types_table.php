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
        Schema::create('product_types', function (Blueprint $table) {
            $table->id(); // Kolom id (Primary Key, Auto Increment)
            $table->string('name')->unique(); // Nama produk pembiayaan, contoh: "Pembiayaan UMKM" [cite: 5]
            $table->text('description')->nullable(); // Deskripsi produk [cite: 5]
            $table->decimal('min_amount', 15, 2)->default(0); // Jumlah minimal pembiayaan [cite: 5]
            $table->decimal('max_amount', 15, 2)->default(0); // Jumlah maksimal pembiayaan [cite: 5]
            $table->json('required_documents')->nullable(); // Daftar dokumen yang dibutuhkan, disimpan sebagai JSON [cite: 5]
            $table->decimal('escalation_threshold', 15, 2)->nullable(); // Ambang batas nominal untuk eskalasi [cite: 5]
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};
