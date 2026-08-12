<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ServiceMarkPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $managerPermissions = [
            'teacher-service-marks.view',
            'teacher-service-marks.create',
            'teacher-service-marks.manage-rates',
            'teacher-service-marks.print',
        ];

        $viewerPermissions = [
            'teacher-service-marks.view',
            'teacher-service-marks.print',
        ];

        foreach ($managerPermissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $managerRole = Role::query()
            ->where('name', 'Provincial System Admin')
            ->where('guard_name', 'web')
            ->first();

        $managerRole?->givePermissionTo($managerPermissions);

        foreach (['sleas officer', 'Zonal Data Confirm'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            $role?->givePermissionTo($viewerPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
