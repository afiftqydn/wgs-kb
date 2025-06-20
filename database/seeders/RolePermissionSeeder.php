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

        $permissions = [
            'access_admin_panel',
            'view_any_user', 'create_user', 'update_user', 'delete_user',
            'view_any_shield::role', 'update_shield::role', 'view_any_karyawan', 'create_karyawan', 'update_karyawan','delete_karyawan',
            'view_any_region', 'create_region', 'update_region', 'delete_region',
            'view_any_product::type', 'create_product::type', 'update_product::type', 'delete_product::type',
            'view_any_customer', 'create_customer', 'update_customer', 'delete_customer',
            'view_any_referrer', 'create_referrer', 'update_referrer', 'delete_referrer',
            'view_any_loan::application', 'create_loan::application', 'update_loan::application', 'delete_loan::application','view_loan::application',
            'view_any_pomigor::depot', 'create_pomigor::depot', 'update_pomigor::depot', 'delete_pomigor::depot',
            'view_any_activity::log',
            'view_report::generator::page',
            'view_any_arsip', 'create_arsip', 'update_arsip', 'delete_arsip',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 3. Buat Semua Peran
        $roleTimIT = Role::firstOrCreate(['name' => 'Tim IT']);
        $roleManagerKeuangan = Role::firstOrCreate(['name' => 'Manager Keuangan']);
        $roleKepalaCabang = Role::firstOrCreate(['name' => 'Kepala Cabang']);
        $roleAnalisCabang = Role::firstOrCreate(['name' => 'Analis Cabang']);
        $roleAdminCabang = Role::firstOrCreate(['name' => 'Admin Cabang']);
        $roleKepalaUnit = Role::firstOrCreate(['name' => 'Kepala Unit']);
        $roleAnalisUnit = Role::firstOrCreate(['name' => 'Analis Unit']);
        $roleAdminUnit = Role::firstOrCreate(['name' => 'Admin Unit']);
        $roleKepalaSubUnit = Role::firstOrCreate(['name' => 'Kepala SubUnit']);
        $roleAdminSubUnit = Role::firstOrCreate(['name' => 'Admin SubUnit']);
        // 4. Tugaskan Permissions ke Roles
        
        // Super Admin
        $roleTimIT->syncPermissions(Permission::all());

        // Level SubUnit
        $roleAdminSubUnit->syncPermissions(['access_admin_panel', 'create_customer', 'view_any_customer', 'create_loan::application', 'view_any_loan::application']);
        $roleKepalaSubUnit->syncPermissions(['access_admin_panel', 'view_any_customer', 'view_any_loan::application']);

        // Level Unit
        $roleAdminUnit->syncPermissions(['access_admin_panel', 'view_any_loan::application', 'update_loan::application','view_loan::application', 'create_loan::application', 'view_any_customer', 'create_customer', 'update_customer', 'delete_customer', 'view_any_pomigor::depot', 'create_pomigor::depot', 'update_pomigor::depot']);
        $roleAnalisUnit->syncPermissions(['access_admin_panel','view_loan::application', 'view_any_loan::application', 'update_loan::application', 'view_any_customer', 'view_any_pomigor::depot']);
        $roleKepalaUnit->syncPermissions(['access_admin_panel', 'view_any_loan::application','view_loan::application', 'update_loan::application', 'view_any_customer', 'view_any_pomigor::depot', 'view_report::generator::page']);

        // Level Cabang
        $roleAdminCabang->syncPermissions(['access_admin_panel', 'view_any_user', 'create_user', 'update_user', 'view_any_shield::role', 'update_shield::role', 'view_any_region', 'create_region', 'update_region', 'view_any_product::type', 'create_product::type', 'update_product::type', 'view_any_referrer', 'create_referrer', 'update_referrer','view_loan::application', 'create_loan::application', 'update_loan::application']);
        $roleAnalisCabang->syncPermissions(['access_admin_panel', 'view_any_loan::application', 'view_any_customer', 'view_any_pomigor::depot', 'view_report::generator::page','view_loan::application']);
        $roleKepalaCabang->syncPermissions(['access_admin_panel', 'view_any_loan::application', 'update_loan::application', 'view_any_customer', 'view_any_pomigor::depot', 'view_report::generator::page', 'view_any_user', 'view_loan::application']);
        $roleManagerKeuangan->syncPermissions([
            'access_admin_panel',
            'view_any_karyawan',
            'create_karyawan',
            'update_karyawan',
            'delete_karyawan',
            'view_any_shield::role', 
        ]);
        
        $this->command->info('RolePermissionSeeder dengan skema lengkap berhasil dijalankan.');
    }
}