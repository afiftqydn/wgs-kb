<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            // Penanda kapan Admin Unit melakukan verifikasi
            $table->timestamp('admin_unit_verified_at')->nullable()->after('assigned_to');

            // Penanda kapan Analis Unit melakukan verifikasi
            $table->timestamp('analis_verified_at')->nullable()->after('admin_unit_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['admin_unit_verified_at', 'analis_verified_at']);
        });
    }
};