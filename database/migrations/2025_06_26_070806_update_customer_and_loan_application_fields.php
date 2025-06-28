<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Memperbaiki dan melengkapi tabel 'customers' sesuai form
        Schema::table('customers', function (Blueprint $table) {
            // Asumsi kolom-kolom ini sudah ada dari default laravel/filament: name, email, phone, address
            // Kita tambahkan kolom yang belum ada dari form

            // Tambahkan kolom dasar untuk data personal jika belum ada
            if (!Schema::hasColumn('customers', 'birth_place')) {
                $table->string('birth_place')->nullable();
            }
            if (!Schema::hasColumn('customers', 'birth_date')) {
                $table->date('birth_date')->nullable();
            }
            if (!Schema::hasColumn('customers', 'identity_number')) {
                $table->string('identity_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('customers', 'gender')) {
                $table->string('gender')->nullable();
            }

            // INI BAGIAN PENTING: Buat kolom marital_status DULU
            if (!Schema::hasColumn('customers', 'marital_status')) {
                $table->string('marital_status')->nullable()->after('gender');
            }
            
            // Setelah itu, baru tambahkan kolom lain yang bergantung padanya
            if (!Schema::hasColumn('customers', 'spouse_name')) {
                $table->string('spouse_name')->nullable()->after('marital_status');
            }
            if (!Schema::hasColumn('customers', 'dependents')) {
                $table->integer('dependents')->default(0)->after('spouse_name');
            }
            if (!Schema::hasColumn('customers', 'education_level')) {
                $table->string('education_level')->nullable()->after('dependents');
            }
             if (!Schema::hasColumn('customers', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'home_ownership_status')) {
                $table->string('home_ownership_status')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('customers', 'length_of_stay')) {
                $table->string('length_of_stay')->nullable()->after('home_ownership_status');
            }
             if (!Schema::hasColumn('customers', 'mother_maiden_name')) {
                $table->string('mother_maiden_name')->nullable()->after('length_of_stay');
            }
             if (!Schema::hasColumn('customers', 'photo')) {
                $table->string('photo')->nullable()->after('mother_maiden_name');
            }
        });

        // Menambahkan kolom ke tabel 'loan_applications' (ini seharusnya sudah aman)
        Schema::table('loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_applications', 'emergency_contact')) {
                $table->json('emergency_contact')->nullable();
            }
            if (!Schema::hasColumn('loan_applications', 'other_bank_relations')) {
                $table->json('other_bank_relations')->nullable();
            }
            if (!Schema::hasColumn('loan_applications', 'amount_in_words')) {
                $table->string('amount_in_words')->nullable();
            }
            if (!Schema::hasColumn('loan_applications', 'disbursement_date')) {
                $table->date('disbursement_date')->nullable();
            }
            if (!Schema::hasColumn('loan_applications', 'billing_address_type')) {
                $table->string('billing_address_type')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['spouse_name', 'dependents', 'education_level', 'home_ownership_status', 'length_of_stay', 'mother_maiden_name']);
        });

        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['emergency_contact', 'other_bank_relations', 'amount_in_words', 'disbursement_date', 'billing_address_type']);
        });
    }
};