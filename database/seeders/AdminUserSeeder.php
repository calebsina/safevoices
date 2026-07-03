<?php

namespace Database\Seeders;

use App\Models\Office\Office;
use App\Models\Role\Role;
use App\Models\User\User;
use Illuminate\Database\Seeder;

/**
 * Initial administrator. CHANGE THE PASSWORD IMMEDIATELY after the
 * first login (and enable MFA) - this is a bootstrap credential only.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@safevoice.cm'],
            [
                'name'      => 'Platform Administrator',
                'password'  => 'ChangeMe-Please-1!', // hashed cast
                'role_id'   => Role::where('key', Role::ADMINISTRATOR)->firstOrFail()->id,
                'office_id' => Office::where('key', 'yaounde-central')->first()?->id,
                'is_active' => true,
            ]
        );
    }
}
