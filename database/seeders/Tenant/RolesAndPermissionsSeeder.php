<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Support\Enums\Tenant\PermissionName;
use App\Support\Enums\Tenant\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const string GUARD = 'tenant_api';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, self::GUARD);
        }

        // O DatabaseSeeder roda sem model events, então o spatie não invalida o
        // cache sozinho e o syncPermissions abaixo não enxergaria as novas permissions.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleName::cases() as $roleName) {
            Role::findOrCreate($roleName->value, self::GUARD)->syncPermissions(
                array_map(fn (PermissionName $permission) => $permission->value, $roleName->permissions()),
            );
        }

        $this->pruneOrphans(Permission::class, array_column(PermissionName::cases(), 'value'));
        $this->pruneOrphans(Role::class, array_column(RoleName::cases(), 'value'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Remove do guard o que não existe mais nos enums. As tabelas pivot têm
     * cascadeOnDelete, então os vínculos com roles e usuários saem junto.
     *
     * @param  class-string<Permission|Role>  $model
     * @param  list<string>  $names
     */
    private function pruneOrphans(string $model, array $names): void
    {
        $orphans = $model::query()
            ->where('guard_name', self::GUARD)
            ->whereNotIn('name', $names)
            ->pluck('name');

        if ($orphans->isEmpty()) {
            return;
        }

        $model::query()
            ->where('guard_name', self::GUARD)
            ->whereIn('name', $orphans)
            ->delete();

        $this->command?->warn('Removido(s) no tenant '.class_basename($model).': '.$orphans->implode(', '));
    }
}
