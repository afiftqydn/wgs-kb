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

        // === BUAT PERMISSIONS ===
        // Format: nama_aksi_nama_resource (contoh: view_users, create_loan_applications)

        // Permissions untuk Regions
        Permission::firstOrCreate(['name' => 'view_regions', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_regions', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_regions', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_regions', 'guard_name' => 'web']);

        // Permissions untuk ProductTypes
        Permission::firstOrCreate(['name' => 'view_product_types', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_product_types', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_product_types', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_product_types', 'guard_name' => 'web']);

        // Permissions untuk Users
        Permission::firstOrCreate(['name' => 'view_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_users', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'assign_roles_to_users', 'guard_name' => 'web']);

        // Permissions untuk Referrers
        Permission::firstOrCreate(['name' => 'view_referrers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'create_referrers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'edit_referrers', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'delete_referrers', 'guard_name' => 'web']);

        // Permissions umum sistem
        Permission::firstOrCreate(['name' => 'access_admin_panel', 'guard_name' => 'web']); // Untuk akses Filament

        // === BUAT ROLES (atau pastikan sudah ada) ===
        // Peran yang diidentifikasi dalam dokumen
        $roleTimIT = Role::firstOrCreate(['name' => 'Tim IT', 'guard_name' => 'web']);
        $roleAdminCabang = Role::firstOrCreate(['name' => 'Admin Cabang', 'guard_name' => 'web']);
        $roleKepalaCabang = Role::firstOrCreate(['name' => 'Kepala Cabang', 'guard_name' => 'web']);
        $roleAdminUnit = Role::firstOrCreate(['name' => 'Admin Unit', 'guard_name' => 'web']);
        $roleAnalisUnit = Role::firstOrCreate(['name' => 'Analis Unit', 'guard_name' => 'web']);
        $roleKepalaUnit = Role::firstOrCreate(['name' => 'Kepala Unit', 'guard_name' => 'web']);
        $roleAdminSubUnit = Role::firstOrCreate(['name' => 'Admin SubUnit', 'guard_name' => 'web']);
        $roleKepalaSubUnit = Role::firstOrCreate(['name' => 'Kepala SubUnit', 'guard_name' => 'web']);


        // === TUGASKAN PERMISSIONS KE ROLES ===

        // Tim IT: Akses penuh ke semua permission yang baru dibuat
        $allPermissions = Permission::all();
        $roleTimIT->syncPermissions($allPermissions);

        // Admin Cabang
        $roleAdminCabang->givePermissionTo([
            'access_admin_panel',
            'view_regions',
            'create_regions',
            'edit_regions', // Mungkin nanti dibatasi lebih detail
            'view_product_types',
            'create_product_types',
            'edit_product_types',
            'view_users',
            'create_users',
            'edit_users', // Untuk pengguna di bawahnya
            'assign_roles_to_users', // Untuk pengguna di bawahnya
            'view_referrers',
            'create_referrers',
            'edit_referrers',
        ]);

        // Kepala Cabang
        $roleKepalaCabang->givePermissionTo([
            'access_admin_panel',
            'view_regions',
            'view_product_types',
            'view_users',
            'view_referrers',
            // Nanti akan ada permission untuk approve eskalasi, dll.
        ]);

        // Admin Unit
        $roleAdminUnit->givePermissionTo([
            'access_admin_panel',
            'view_regions', // View Unitnya dan SubUnit di bawahnya
            'view_product_types',
            'view_users', // View user di Unitnya dan SubUnit di bawahnya
            'create_users', // Untuk SubUnit di bawahnya jika perlu
            'view_referrers', // Mungkin hanya view
            // Nanti ada permission untuk memproses permohonan, CRUD Nasabah
        ]);

        // Analis Unit
        $roleAnalisUnit->givePermissionTo([
            'access_admin_panel',
            'view_regions', // Terbatas pada Unitnya
            'view_product_types',
            // Nanti ada permission untuk menganalisis permohonan
        ]);

        // Kepala Unit
        $roleKepalaUnit->givePermissionTo([
            'access_admin_panel',
            'view_regions', // Unitnya dan SubUnit di bawahnya
            'view_product_types',
            'view_users', // User di Unitnya dan SubUnit di bawahnya
            // Nanti ada permission untuk menyetujui/menolak permohonan normal, eskalasi
        ]);

        // Admin SubUnit
        $roleAdminSubUnit->givePermissionTo([
            'access_admin_panel',
            'view_regions', // Hanya SubUnit sendiri
            // Nanti ada permission untuk membuat & melihat permohonan dari SubUnitnya
        ]);

        // Kepala SubUnit
        $roleKepalaSubUnit->givePermissionTo([
            'access_admin_panel',
            'view_regions', // Hanya SubUnit sendiri
            // Nanti ada permission untuk membuat & melihat permohonan dari SubUnitnya
        ]);
    }
}
