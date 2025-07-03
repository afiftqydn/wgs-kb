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
        Schema::create('pomigor_depots', function (Blueprint $table) {
            $table->id(); // Primary Key (ID unik untuk setiap depot)

            $table->string('depot_code')->unique(); // Kode unik untuk depot, misal: PGR-KBR-001
            $table->string('name'); // Nama deskriptif depot, misal: Depot POMIGOR Desa Sukamaju

            $table->foreignId('region_id') // ID dari Unit WGS yang mengelola depot ini
                  ->constrained('regions') // Merujuk ke tabel 'regions'
                  ->onUpdate('cascade')
                  ->onDelete('restrict'); // Jika Unit dihapus, depot tidak bisa ada tanpa Unit pengelola

            $table->foreignId('customer_id') // ID dari Nasabah WGS yang menjadi pengurus/pemilik depot
                  ->constrained('customers') // Merujuk ke tabel 'customers'
                  ->onUpdate('cascade')
                  ->nullable()
                  ->onDelete('restrict'); // Jika Nasabah pengurus dihapus, perlu penanganan khusus (mungkin tidak boleh dihapus jika masih ada depot aktif)

            $table->text('address'); // Alamat lengkap depot
            $table->decimal('latitude', 10, 7); // Koordinat Latitude (presisi tinggi)
            $table->decimal('longitude', 10, 7); // Koordinat Longitude (presisi tinggi)

            $table->decimal('current_stock_liters', 15, 2)->default(0); // Stok terkini dalam liter

            $table->enum('status', ['ACTIVE', 'INACTIVE', 'MAINTENANCE'])->default('ACTIVE'); // Status operasional depot

            $table->foreignId('created_by')->nullable() // ID Admin Unit yang mendaftarkan depot
                  ->constrained('users')    // Merujuk ke tabel 'users'
                  ->onUpdate('cascade')
                  ->onDelete('set null'); // Jika user dihapus, created_by menjadi NULL

            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomigor_depots');
    }
};

