<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pomigor_depots', function (Blueprint $table) {
            // Pastikan kolom depot_code ada dan unik (mungkin sudah ada)
            if (!Schema::hasColumn('pomigor_depots', 'depot_code')) {
                $table->string('depot_code')->unique()->nullable()->after('name');
            }

            // Pastikan kolom serial_number ada, unik, dan bisa null
            if (!Schema::hasColumn('pomigor_depots', 'serial_number')) {
                $table->string('serial_number')->nullable()->unique()->after('depot_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pomigor_depots', function (Blueprint $table) {
            if (Schema::hasColumn('pomigor_depots', 'depot_code')) {
                $table->dropColumn('depot_code');
            }
            if (Schema::hasColumn('pomigor_depots', 'serial_number')) {
                $table->dropColumn('serial_number');
            }
        });
    }
};