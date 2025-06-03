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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('application_number')->unique(); // Nomor unik permohonan, bisa digenerate otomatis nantinya

            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // Relasi ke nasabah. Jika nasabah dihapus, permohonannya ikut terhapus.
            $table->foreignId('product_type_id')->constrained('product_types')->onDelete('restrict'); // Relasi ke jenis produk. Restrict delete jika produk masih digunakan.

            $table->decimal('amount_requested', 15, 2); // Jumlah yang diajukan
            $table->text('purpose')->nullable(); // Tujuan pembiayaan

            $table->foreignId('input_region_id')->nullable()->constrained('regions')->onDelete('set null'); // Wilayah (SubUnit/Unit) tempat permohonan diinput
            $table->foreignId('processing_region_id')->nullable()->constrained('regions')->onDelete('set null'); // Wilayah (Unit/Cabang) yang sedang memproses permohonan

            // Status permohonan sesuai dokumen [cite: 37, 49]
            $table->enum('status', [
                'DRAFT',
                'SUBMITTED',
                'UNDER_REVIEW',
                'APPROVED',
                'REJECTED',
                'ESCALATED',
                // Pertimbangkan tambahan status seperti 'CANCELLED' atau 'DISBURSED' untuk masa depan
            ])->default('DRAFT');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User WGS yang menginput permohonan
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // User WGS yang saat ini ditugaskan untuk memproses

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
