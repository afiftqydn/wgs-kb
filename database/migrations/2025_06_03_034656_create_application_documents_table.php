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
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('loan_application_id')->constrained('loan_applications')->onDelete('cascade'); // Relasi ke permohonan. Jika permohonan dihapus, dokumennya ikut terhapus.

            $table->string('document_type'); // Jenis dokumen, contoh: "KTP", "NPWP", "Surat Keterangan Usaha", dll. Sesuai yang didefinisikan di ProductTypes.required_documents.
            $table->string('file_name'); // Nama asli file yang diunggah
            $table->string('file_path'); // Path penyimpanan file di server (misal: 'documents/tahun/bulan/namafileunik.pdf')
            $table->unsignedBigInteger('file_size')->nullable(); // Ukuran file dalam bytes (opsional, tapi berguna)
            $table->string('mime_type')->nullable(); // Tipe MIME file (misal: 'application/pdf', 'image/jpeg') (opsional, tapi berguna)

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null'); // User WGS yang mengunggah dokumen

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
