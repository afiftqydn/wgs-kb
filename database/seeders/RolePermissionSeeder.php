<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

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
        // LoanApplications (General CRUD)
        Permission::firstOrCreate(['name' => 'view_loan_applications', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_loan_applications', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_loan_applications', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_loan_applications', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'upload_application_documents', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_application_documents', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_application_documents', 'guard_name' => 'web']);
        // LoanApplications (Workflow Specific)
        Permission::firstOrCreate(['name' => 'process_submitted_application_admin_unit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'decide_application_analis_unit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'escalate_application_analis_unit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'review_decided_application_kepala_unit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'decide_escalated_application_kepala_cabang', 'guard_name' => 'web']);
        // Reporting
        Permission::firstOrCreate(['name' => 'view_reports_unit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_reports_cabang', 'guard_name' => 'web']);
        // POMIGOR
        Permission::firstOrCreate(['name' => 'manage_pomigor_depots', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage_pomigor_stock', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_pomigor_depots', 'guard_name' => 'web']);


        // ROLES
        $roleTimIT = Role::firstOrCreate(['name' => 'Tim IT', 'guard_name' => 'web']);
        $roleAdminCabang = Role::firstOrCreate(['name' => 'Admin Cabang', 'guard_name' => 'web']);
        $roleAnalisCabang = Role::firstOrCreate(['name' => 'Analis Cabang', 'guard_name' => 'web']);
        $roleKepalaCabang = Role::firstOrCreate(['name' => 'Kepala Cabang', 'guard_name' => 'web']);
        $roleAdminUnit = Role::firstOrCreate(['name' => 'Admin Unit', 'guard_name' => 'web']);
        $roleAnalisUnit = Role::firstOrCreate(['name' => 'Analis Unit', 'guard_name' => 'web']);
        $roleKepalaUnit = Role::firstOrCreate(['name' => 'Kepala Unit', 'guard_name' => 'web']);
        $roleAdminSubUnit = Role::firstOrCreate(['name' => 'Admin SubUnit', 'guard_name' => 'web']);
        $roleKepalaSubUnit = Role::firstOrCreate(['name' => 'Kepala SubUnit', 'guard_name' => 'web']);

        // ASSIGN PERMISSIONS
        $roleTimIT->syncPermissions(Permission::all());

        $roleAdminSubUnit->syncPermissions(['access_admin_panel', 'create_loan_applications', 'view_loan_applications', 'edit_loan_applications', 'upload_application_documents', 'view_application_documents', 'create_customers', 'view_customers', 'edit_customers']);
        $roleKepalaSubUnit->syncPermissions(['access_admin_panel', 'view_loan_applications', 'view_application_documents', 'view_customers']);
        
        $roleAdminUnit->syncPermissions(['access_admin_panel', 'view_loan_applications', 'edit_loan_applications', 'process_submitted_application_admin_unit', 'upload_application_documents', 'view_application_documents', 'create_customers', 'view_customers', 'edit_customers', 'delete_customers', 'view_users', 'view_referrers', 'manage_pomigor_depots', 'manage_pomigor_stock', 'view_pomigor_depots']);
        $roleAnalisUnit->syncPermissions(['access_admin_panel', 'view_loan_applications', 'view_application_documents', 'decide_application_analis_unit', 'escalate_application_analis_unit', 'view_customers', 'view_pomigor_depots']);
        $roleKepalaUnit->syncPermissions(['access_admin_panel', 'view_loan_applications', 'view_application_documents', 'review_decided_application_kepala_unit', 'view_reports_unit', 'view_users', 'view_customers', 'view_referrers', 'view_pomigor_depots']);
        
        $roleAdminCabang->syncPermissions(['access_admin_panel', 'view_users', 'create_users', 'edit_users', 'delete_users', 'assign_roles_to_users', 'manage_roles_permissions', 'view_regions', 'create_regions', 'edit_regions', 'delete_regions', 'view_product_types', 'create_product_types', 'edit_product_types', 'delete_product_types', 'view_referrers', 'create_referrers', 'edit_referrers', 'delete_referrers', 'view_loan_applications', 'view_application_documents', 'view_customers', 'view_pomigor_depots']);
        $roleAnalisCabang->syncPermissions(['access_admin_panel', 'view_loan_applications', 'view_application_documents', 'view_customers', 'view_reports_cabang', 'view_pomigor_depots']);
        $roleKepalaCabang->syncPermissions(['access_admin_panel', 'view_loan_applications', 'view_application_documents', 'decide_escalated_application_kepala_cabang', 'view_reports_cabang', 'view_users', 'view_customers', 'view_pomigor_depots']);
        
        $this->command->info('RolePermissionSeeder berhasil dijalankan.');
    }
}