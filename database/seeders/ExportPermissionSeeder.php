<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ExportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();

        $guard = 'web';

        $permissions = [
            'page_ExportData',

            'export_assets',
            'export_daily_reports',
            'export_monthly_reports',

            'view_export_history',
            'download_export_archive',
            'finalize_export_document',

            'manage_report_signatories',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate(
                $permission,
                $guard
            );
        }

        $superAdmin = Role::findOrCreate(
            'super_admin',
            $guard
        );

        $superAdmin->givePermissionTo(
            $permissions
        );

        $headIt = Role::findOrCreate(
            'kepala_it',
            $guard
        );

        $headIt->givePermissionTo([
            'page_ExportData',

            'export_assets',
            'export_daily_reports',
            'export_monthly_reports',

            'view_export_history',
            'download_export_archive',
            'finalize_export_document',

            'manage_report_signatories',
        ]);

        $staffIt = Role::findOrCreate(
            'staff_it',
            $guard
        );

        $staffIt->givePermissionTo([
            'page_ExportData',

            'export_assets',
            'export_daily_reports',

            'view_export_history',
            'download_export_archive',
        ]);

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}
