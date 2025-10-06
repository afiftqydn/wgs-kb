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
        // Perintah untuk MENGUBAH tabel yang sudah ada
        Schema::table('loan_applications', function (Blueprint $table) {
            // Tambahkan kolom Jenis Usaha Nasabah setelah kolom 'purpose'
            $table->string('customer_business_type')->nullable()->after('purpose');

            // Tambahkan kolom Status Pembayaran setelah kolom 'status'
            $table->enum('payment_status', ['Sudah Transfer', 'Belum Transfer'])
                  ->default('Belum Transfer')
                  ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Perintah untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('customer_business_type');
            $table->dropColumn('payment_status');
        });
    }
};