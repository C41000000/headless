<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Models\User;
use App\Support\Enums\RoleName;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array{name: string, email: string, password: string|null} $config */
        $config = config('auth.super_admin');

        if (blank($config['password'])) {
            throw new RuntimeException('Defina SUPER_ADMIN_PASSWORD para criar o super admin.');
        }

        $user = User::query()->firstOrNew(['email' => $config['email']]);

        if (! $user->exists) {
            // forceFill: email_verified_at não é fillable de propósito
            $user->forceFill([
                'name' => $config['name'],
                'password' => $config['password'],
                'email_verified_at' => now(),
            ])->save();
        }

        $user->assignRole(RoleName::SuperAdmin->value);
    }
}
