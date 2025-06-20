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
        Schema::create('arsips', function (Blueprint $table) {
            $table->id();
            $table->string('kode_arsip')->unique();
            $table->string('nama_arsip');
            $table->string('kategori');
            $table->date('tanggal_dokumen');
            $table->text('keterangan')->nullable();

            // --- BAGIAN YANG DIPERBAIKI ---
            // Nama kolom diubah menjadi 'customer_id'
            // dan referensi tabel diubah menjadi 'customers'
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');

            // Sesuaikan juga untuk pengajuan jika nama tabelnya berbeda
            $table->foreignId('loan_application_id')->nullable()->constrained('loan_applications')->onDelete('set null');
            
            $table->string('file_path');
            $table->string('lokasi_fisik')->nullable();
            $table->string('status')->default('Aktif');
            $table->date('tanggal_retensi')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('arsips');
        }
    };