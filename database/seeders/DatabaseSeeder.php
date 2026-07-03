<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Runs the full seed set in dependency order:
 * locales first (every translation FK points at them), then RBAC,
 * reference data, templates, consent, an initial admin, and CMS demo
 * content.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocaleSeeder::class,
            RoleAndPermissionSeeder::class,
            OfficeSeeder::class,
            CaseStatusSeeder::class,
            CaseCategorySeeder::class,
            PriorityLevelSeeder::class,
            PriorityRuleSeeder::class,
            LookupSeeder::class,
            NotificationTemplateSeeder::class,
            ConsentVersionSeeder::class,
            AdminUserSeeder::class,
            SettingSeeder::class,
            CmsSeeder::class,
        ]);
    }
}
