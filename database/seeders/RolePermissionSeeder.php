<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'access_admin_panel', 'view_regions', 'create_regions', 'edit_regions', 'delete_regions',
            'view_product_types', 'create_product_types', 'edit_product_types', 'delete_product_types',
            'view_users', 'create_users', 'edit_users', 'delete_users', 'assign_roles_to_users', 'manage_roles_permissions',
            'view_referrers', 'create_referrers', 'edit_referrers', 'delete_referrers',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_loan_applications', 'create_loan_applications', 'edit_loan_applications', 'delete_loan_applications',
            'upload_application_documents', 'view_application_documents', 'delete_application_documents',
            'process_submitted_application_admin_unit', 'decide_application_analis_unit', 'escalate_application_analis_unit',
            'review_decided_application_kepala_unit', 'decide_escalated_application_kepala_cabang',
            'view_reports_unit', 'view_reports_cabang',
            'manage_pomigor_depots', 'manage_pomigor_stock', 'view_pomigor_depots'
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles
        $roleTimIT = Role::firstOrCreate(['name' => 'Tim IT', 'guard_name' => 'web']);
        $roleAdminCabang = Role::firstOrCreate(['name' => 'Admin Cabang', 'guard_name' => 'web']);
        $roleAnalisCabang = Role::firstOrCreate(['name' => 'Analis Cabang', 'guard_name' => 'web']);
        $roleKepalaCabang = Role::firstOrCreate(['name' => 'Kepala Cabang', 'guard_name' => 'web']);
        $roleAdminUnit = Role::firstOrCreate(['name' => 'Admin Unit', 'guard_name' => 'web']);
        $roleAnalisUnit = Role::firstOrCreate(['name' => 'Analis Unit', 'guard_name' => 'web']);
        $roleKepalaUnit = Role::firstOrCreate(['name' => 'Kepala Unit', 'guard_name' => 'web']);
        $roleAdminSubUnit = Role::firstOrCreate(['name' => 'Admin SubUnit', 'guard_name' => 'web']);
        $roleKepalaSubUnit = Role::firstOrCreate(['name' => 'Kepala SubUnit', 'guard_name' => 'web']);

        // Assign Permissions
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