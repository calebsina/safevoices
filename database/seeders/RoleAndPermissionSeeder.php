<?php

namespace Database\Seeders;

use App\Models\Role\Permission;
use App\Models\Role\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the three system roles and the permission matrix from
 * dossier section 3.5.1.
 */
class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'case'      => ['case.view', 'case.update_status', 'case.assign', 'case.escalate', 'case.message', 'case.action', 'case.refer'],
            'evidence'  => ['evidence.view', 'evidence.upload', 'evidence.download'],
            'admin'     => ['users.manage', 'roles.manage', 'offices.manage', 'localization.manage', 'reference.manage', 'cms.manage', 'settings.manage', 'audit.view'],
        ];

        $ids = [];

        foreach ($permissions as $group => $keys) {
            foreach ($keys as $key) {
                $ids[$key] = Permission::updateOrCreate(['key' => $key], ['group' => $group])->id;
            }
        }

        $matrix = [
            // Caseworker: assigned cases only (row scope in ReportPolicy).
            Role::CASEWORKER => [
                'case.view', 'case.update_status', 'case.escalate', 'case.message', 'case.action', 'case.refer',
                'evidence.view', 'evidence.upload', 'evidence.download',
            ],
            // Supervisor: everything a caseworker has + triage/assignment.
            Role::SUPERVISOR => [
                'case.view', 'case.update_status', 'case.assign', 'case.escalate', 'case.message', 'case.action', 'case.refer',
                'evidence.view', 'evidence.upload', 'evidence.download',
            ],
            // Administrator: full platform.
            Role::ADMINISTRATOR => array_merge(...array_values($permissions)),
        ];

        $labels = [
            Role::CASEWORKER    => ['en' => 'Caseworker',    'fr' => 'Agent de suivi'],
            Role::SUPERVISOR    => ['en' => 'Supervisor',    'fr' => 'Superviseur'],
            Role::ADMINISTRATOR => ['en' => 'Administrator', 'fr' => 'Administrateur'],
        ];

        foreach ($matrix as $key => $keys) {
            $role = Role::updateOrCreate(['key' => $key], ['is_system' => true]);
            $role->permissions()->sync(collect($keys)->map(fn ($k) => $ids[$k])->all());
            $role->syncTranslations([
                'en' => ['label' => $labels[$key]['en']],
                'fr' => ['label' => $labels[$key]['fr']],
            ]);
        }
    }
}
