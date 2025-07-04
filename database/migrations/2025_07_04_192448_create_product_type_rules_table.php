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
        Schema::create('product_type_rules', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel product_types
            $table->foreignId('product_type_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., 'Biaya Administrasi', 'Komisi Unit'
            $table->enum('type', ['percentage', 'flat']); // Jenis aturan: persentase atau tetap
            $table->decimal('value', 15, 2); // Nilai aturan (bisa % atau nominal)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_type_rules');
    }
};
