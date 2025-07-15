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
        Schema::create('gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->string('periode_bulan'); // e.g., 'Mei'
            $table->year('periode_tahun'); // e.g., '2025'
            $table->date('tanggal_bayar');

            // Pendapatan
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('transport', 15, 2)->default(0);
            $table->decimal('tun_kehadiran', 15, 2)->default(0);
            $table->decimal('tun_komunikasi', 15, 2)->default(0);
            $table->decimal('lembur', 15, 2)->default(0);

            // Potongan
            $table->decimal('bpjs', 15, 2)->default(0);
            $table->decimal('absen', 15, 2)->default(0);
            $table->decimal('kas_bon', 15, 2)->default(0);

            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gajis');
    }
};