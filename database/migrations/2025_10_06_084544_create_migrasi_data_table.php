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
        Schema::create('migrasi_data', function (Blueprint $table) {
            $table->id();
            $table->string('nama_nasabah')->nullable();
            $table->string('nama_ibu_kandung')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jenis_kelamin', 1)->nullable(); // P atau L
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('identitas_nasabah')->default('KTP')->nullable();
            $table->string('nik', 16)->unique()->nullable();
            $table->string('agama')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota_kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('no_hp')->nullable();
            $table->date('tanggal_register')->nullable();
            $table->decimal('simpok', 15, 2)->default(0);
            $table->decimal('simwajib', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migrasi_data');
    }
};