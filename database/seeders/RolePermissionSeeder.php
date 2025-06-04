<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === DEFINISIKAN PERMISSIONS ===
        // General
        Permission::firstOrCreate(['name' => 'access_admin_panel', 'guard_name' => 'web']);

        // Regions
        Permission::firstOrCreate(['name' => 'view_regions', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_regions', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_regions', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_regions', 'guard_name' => 'web']);

        // ProductTypes
        Permission::firstOrCreate(['name' => 'view_product_types', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_product_types', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_product_types', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_product_types', 'guard_name' => 'web']);

        // Users Management
        Permission::firstOrCreate(['name' => 'view_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'assign_roles_to_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage_roles_permissions', 'guard_name' => 'web']);

        // Referrers
        Permission::firstOrCreate(['name' => 'view_referrers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_referrers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_referrers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_referrers', 'guard_name' => 'web']);

        // Customers
        Permission::firstOrCreate(['name' => 'view_customers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_customers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_customers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_customers', 'guard_name' => 'web']);

        // LoanApplications (General CRUD - data scoping akan dihandle di Resource/Policy)
        Permission::firstOrCreate(['name' => 'view_loan_applications', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_loan_applications', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_loan_applications', 'guard_name' => 'web']); // Biasanya untuk status DRAFT
        Permission::firstOrCreate(['name' => 'delete_loan_applications', 'guard_name' => 'web']); // Biasanya untuk status DRAFT
        Permission::firstOrCreate(['name' => 'upload_application_documents', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_application_documents', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_application_documents', 'guard_name' => 'web']);

        // LoanApplications (Workflow Specific)
        Permission::firstOrCreate(['name' => 'process_submitted_application_admin_unit', 'guard_name' => 'web']); // Admin Unit -> Analis Unit
        Permission::firstOrCreate(['name' => 'decide_application_analis_unit', 'guard_name' => 'web']); // Analis Unit -> Approve/Reject (Normal)
        Permission::firstOrCreate(['name' => 'escalate_application_analis_unit', 'guard_name' => 'web']); // Analis Unit -> Eskalasi ke Cabang
        Permission::firstOrCreate(['name' => 'review_decided_application_kepala_unit', 'guard_name' => 'web']); // Kepala Unit -> Review keputusan Analis
        Permission::firstOrCreate(['name' => 'decide_escalated_application_kepala_cabang', 'guard_name' => 'web']); // Kepala Cabang -> Approve/Reject Eskalasi

        // Reporting (Contoh)
        Permission::firstOrCreate(['name' => 'view_reports_unit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_reports_cabang', 'guard_name' => 'web']);


        // === BUAT ROLES ===
        $roleTimIT = Role::firstOrCreate(['name' => 'Tim IT', 'guard_name' => 'web']);
        $roleAdminCabang = Role::firstOrCreate(['name' => 'Admin Cabang', 'guard_name' => 'web']);
        $roleAnalisCabang = Role::firstOrCreate(['name' => 'Analis Cabang', 'guard_name' => 'web']);
        $roleKepalaCabang = Role::firstOrCreate(['name' => 'Kepala Cabang', 'guard_name' => 'web']);
        $roleAdminUnit = Role::firstOrCreate(['name' => 'Admin Unit', 'guard_name' => 'web']);
        $roleAnalisUnit = Role::firstOrCreate(['name' => 'Analis Unit', 'guard_name' => 'web']);
        $roleKepalaUnit = Role::firstOrCreate(['name' => 'Kepala Unit', 'guard_name' => 'web']);
        $roleAdminSubUnit = Role::firstOrCreate(['name' => 'Admin SubUnit', 'guard_name' => 'web']);
        $roleKepalaSubUnit = Role::firstOrCreate(['name' => 'Kepala SubUnit', 'guard_name' => 'web']);

        // === TUGASKAN PERMISSIONS KE ROLES ===

        // Tim IT (Super Admin)
        $roleTimIT->syncPermissions(Permission::all());

        // Admin SubUnit / Staf SubUnit
        $roleAdminSubUnit->syncPermissions([
            'access_admin_panel',
            'create_loan_applications',
            'view_loan_applications',
            'edit_loan_applications', // Hanya untuk DRAFT miliknya
            'upload_application_documents',
            'view_application_documents',
            'create_customers',
            'view_customers',
            'edit_customers',
        ]);

        // Kepala SubUnit
        $roleKepalaSubUnit->syncPermissions([
            'access_admin_panel',
            'view_loan_applications',
            'view_application_documents',
            'view_customers',
        ]);
        
        // Admin Unit
        $roleAdminUnit->syncPermissions([
            'access_admin_panel',
            'view_loan_applications',
            'edit_loan_applications',
            'process_submitted_application_admin_unit',
            'upload_application_documents',
            'view_application_documents',
            'create_customers', 'view_customers', 'edit_customers', 'delete_customers',
            'view_users', // Pengguna di Unitnya
            'view_referrers',
        ]);

        // Analis Unit
        $roleAnalisUnit->syncPermissions([
            'access_admin_panel',
            'view_loan_applications', // Terutama yang ditugaskan padanya
            'view_application_documents',
            'decide_application_analis_unit', // Approve/Reject nominal normal
            'escalate_application_analis_unit', // Eskalasi jika nominal besar
            'view_customers',
        ]);

        // Kepala Unit
        $roleKepalaUnit->syncPermissions([
            'access_admin_panel',
            'view_loan_applications', // Semua di Unitnya
            'view_application_documents',
            'review_decided_application_kepala_unit', // Review keputusan Analis
            // 'escalate_application', // Eskalasi mungkin tetap dipegang Kepala Unit sebagai opsi kedua
            'view_reports_unit',
            'view_users', // Pengguna di Unitnya
            'view_customers',
            'view_referrers',
        ]);

        // Admin Cabang
        $roleAdminCabang->syncPermissions([
            'access_admin_panel',
            'view_users', 'create_users', 'edit_users', 'delete_users', 'assign_roles_to_users',
            'view_regions', 'create_regions', 'edit_regions', 'delete_regions',
            'view_product_types', 'create_product_types', 'edit_product_types', 'delete_product_types',
            'view_referrers', 'create_referrers', 'edit_referrers', 'delete_referrers',
            'view_loan_applications', // Monitoring global
            'view_application_documents',
            'view_customers', // Monitoring global
            'manage_roles_permissions', // Bisa mengelola role dan permission di bawah Tim IT
        ]);

        // Analis Cabang
        $roleAnalisCabang->syncPermissions([
            'access_admin_panel',
            'view_loan_applications', // Global
            'view_application_documents',
            'view_customers', // Global
            'view_reports_cabang',
        ]);

        // Kepala Cabang
        $roleKepalaCabang->syncPermissions([
            'access_admin_panel',
            'view_loan_applications', // Global
            'view_application_documents',
            'decide_escalated_application_kepala_cabang', // Memutuskan eskalasi
            'view_reports_cabang',
            'view_users', // Global
            'view_customers', // Global
        ]);
    }
}