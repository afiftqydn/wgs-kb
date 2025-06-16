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
        Schema::create('karyawans', function (Blueprint $table) {
            // --- INFORMASI UTAMA ---
            $table->id();
            $table->string('nama_lengkap');
            $table->string('jabatan');
            $table->string('email')->unique();
            $table->string('no_hp', 20);

            // --- DATA PRIBADI ---
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Pria', 'Wanita']);
            $table->text('alamat_ktp');
            $table->text('alamat_domisili');
            $table->enum('status_pernikahan', ['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati']);
            $table->enum('agama', ['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha', 'Khonghucu']);

            // --- DATA KEPEGAWAIAN ---
            $table->foreignId('region_id')->constrained('regions')->onDelete('restrict'); // Mengubah kantor menjadi foreign key ke tabel regions
            $table->enum('status_karyawan', ['Tetap/PKWTT', 'Kontrak/PKWT', 'Magang', 'Harian']);
            $table->date('tanggal_bergabung');
            $table->date('tanggal_berakhir_kontrak')->nullable(); // Hanya untuk PKWT

            // --- DATA LEGAL & FINANSIAL ---
            $table->string('npwp', 25)->nullable()->unique();
            $table->string('bpjs_ketenagakerjaan', 25)->nullable()->unique();
            $table->string('bpjs_kesehatan', 25)->nullable()->unique();
            $table->string('nama_bank')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_pemilik_rekening')->nullable();

            // --- KONTAK DARURAT ---
            $table->string('nama_kontak_darurat');
            $table->string('hubungan_kontak_darurat');
            $table->string('no_hp_kontak_darurat', 20);

            // --- DOKUMEN DIGITAL (PATH FILE) ---
            $table->string('pas_foto')->nullable();
            $table->string('file_ktp')->nullable();
            $table->string('file_npwp')->nullable();
            $table->string('file_perjanjian_kerja')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};