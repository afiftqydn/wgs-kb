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
        Schema::create('application_workflows', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('loan_application_id')->constrained('loan_applications')->onDelete('cascade'); // Relasi ke permohonan. Jika permohonan dihapus, log alur kerjanya ikut terhapus.

            $table->string('from_status')->nullable(); // Status sebelumnya (bisa null jika ini adalah log pertama)
            $table->string('to_status'); // Status baru setelah diproses

            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null'); // User WGS yang melakukan aksi/perubahan status
            $table->text('notes')->nullable(); // Catatan atau komentar dari user yang memproses

            $table->timestamp('created_at')->useCurrent(); // Hanya created_at, karena log tidak diupdate
            // $table->timestamps(); // Jika Anda juga butuh updated_at, tapi biasanya log hanya created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_workflows');
    }
};
