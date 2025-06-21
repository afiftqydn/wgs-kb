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
        // 1. Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Definisikan dan Buat Semua Permission yang Dibutuhkan Aplikasi
        // Dengan daftar ini, Anda tidak perlu lagi menjalankan `shield:generate`
        $permissions = [
            'access_admin_panel',
            // User Resource Permissions
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user',
            // Shield Role Resource Permissions
            'view_any_shield::role', 'view_shield::role', 'create_shield::role', 'update_shield::role', 'delete_shield::role',
            // Region Resource Permissions
            'view_any_region', 'view_region', 'create_region', 'update_region', 'delete_region',
            // ProductType Resource Permissions
            'view_any_product::type', 'view_product::type', 'create_product::type', 'update_product::type', 'delete_product::type',
            // Customer Resource Permissions
            'view_any_customer', 'view_customer', 'create_customer', 'update_customer', 'delete_customer',
            // Referrer Resource Permissions
            'view_any_referrer', 'view_referrer', 'create_referrer', 'update_referrer', 'delete_referrer',
            // LoanApplication Resource Permissions
            'view_any_loan::application', 'view_loan::application', 'create_loan::application', 'update_loan::application', 'delete_loan::application',
            // Arsip
            'view_any_arsip', 'view_arsip', 'create_arsip', 'update_arsip', 'delete_arsip',
            // PomigorDepot Resource Permissions
            'view_any_pomigor::depot', 'view_pomigor::depot', 'create_pomigor::depot', 'update_pomigor::depot', 'delete_pomigor::depot',
            // ActivityLog Resource Permission
            'view_any_activity::log',
            // Custom Page Permissions
            'view_report::generator::page',
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
        
        // 4. Tugaskan Permissions ke Roles (Sesuai Matriks Peran) 
        
        // Super Admin -> bisa melakukan segalanya
        $roleTimIT->syncPermissions(Permission::all());

        // Level SubUnit
        $roleAdminSubUnit->syncPermissions(['access_admin_panel', 'create_customer', 'view_any_customer', 'view_customer', 'create_loan::application', 'view_any_loan::application', 'view_loan::application']);
        $roleKepalaSubUnit->syncPermissions(['access_admin_panel', 'view_any_customer', 'view_customer', 'view_any_loan::application', 'view_loan::application']);

        // Level Unit
        $roleAdminUnit->syncPermissions([
            'access_admin_panel',
            'view_any_loan::application',
            'view_loan::application',
            'create_loan::application', // <-- TAMBAHKAN PERMISSION INI
            'update_loan::application',
            'view_any_customer', 'create_customer', 'update_customer', 'delete_customer',
            'view_any_pomigor::depot', 'create_pomigor::depot', 'update_pomigor::depot',
        ]);
        $roleAnalisUnit->syncPermissions(['access_admin_panel', 'view_any_loan::application', 'view_loan::application', 'update_loan::application', 'view_any_customer', 'view_any_pomigor::depot']);
        $roleKepalaUnit->syncPermissions(['access_admin_panel', 'view_any_loan::application', 'view_loan::application', 'update_loan::application', 'view_report::generator::page', 'view_any_customer', 'view_any_pomigor::depot', 'view_any_user']);

        // Level Cabang
        $roleAdminCabang->syncPermissions(['access_admin_panel', 'view_any_user', 'create_user', 'update_user', 'view_any_shield::role', 'update_shield::role', 'view_any_region', 'create_region', 'update_region', 'view_any_product::type', 'create_product::type', 'update_product::type', 'view_any_referrer', 'create_referrer', 'update_referrer']);
        $roleAnalisCabang->syncPermissions(['access_admin_panel', 'view_any_loan::application', 'view_any_customer', 'view_any_pomigor::depot', 'view_report::generator::page']);
        $roleKepalaCabang->syncPermissions(['access_admin_panel', 'view_any_loan::application', 'update_loan::application', 'view_any_customer', 'view_any_pomigor::depot', 'view_report::generator::page', 'view_any_user']);
        
        // Peran Global Lainnya
        $roleManagerKeuangan->syncPermissions(['access_admin_panel', 'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user', 'view_any_shield::role']);

        $this->command->info('RolePermissionSeeder dengan skema lengkap dan mandiri berhasil dijalankan.');
    }
}
            
