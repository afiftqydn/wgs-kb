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
        Schema::create('pomigor_stock_movements', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('pomigor_depot_id') // ID depot POMIGOR terkait
                  ->constrained('pomigor_depots') // Merujuk ke tabel 'pomigor_depots'
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Jika depot dihapus, histori pergerakannya juga ikut terhapus

            $table->enum('transaction_type', [ // Jenis transaksi/pergerakan stok
                'REFILL',           // Pengisian ulang stok ke depot
                'SALE_REPORTED',    // Laporan penjualan dari depot (mengurangi stok)
                'ADJUSTMENT_INCREASE', // Penyesuaian stok (penambahan karena alasan lain)
                'ADJUSTMENT_DECREASE'  // Penyesuaian stok (pengurangan karena alasan lain)
            ]);

            $table->decimal('quantity_liters', 15, 2); // Jumlah minyak goreng (selalu positif)
            $table->timestamp('transaction_date')->useCurrent(); // Tanggal & waktu aktual transaksi (bisa diatur default ke waktu input)

            $table->text('notes')->nullable(); // Catatan tambahan (misal: No. DO, alasan penyesuaian)

            $table->foreignId('recorded_by')->nullable() // ID Admin Unit yang mencatat pergerakan ini
                  ->constrained('users')   // Merujuk ke tabel 'users'
                  ->onUpdate('cascade')
                  ->onDelete('set null'); // Jika user dihapus, recorded_by menjadi NULL

            $table->timestamps(); // Kapan record ini dibuat/diupdate di sistem
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomigor_stock_movements');
    }
};
