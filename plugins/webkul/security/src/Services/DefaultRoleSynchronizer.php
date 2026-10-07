<?php

namespace Webkul\Security\Services;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Webkul\Security\Models\Role;

/**
 * Creates the built-in roles from config/roles.php and grants them the permissions that exist right now.
 *
 * It only ever adds: permissions or roles a customer changed by hand are never taken away, so it is safe to
 * run again after installing more modules.
 */
class DefaultRoleSynchronizer
{
    protected const ACTIONS = [
        'force_delete_any', 'force_delete', 'delete_any', 'restore_any', 'view_any', 'reorder', 'replicate',
        'restore', 'create', 'update', 'delete', 'view', 'page', 'widget',
    ];

    protected const LEVELS = [
        'full'  => ['view', 'view_any', 'create', 'update', 'delete', 'delete_any', 'restore', 'restore_any', 'reorder', 'replicate', 'page', 'widget'],
        'staff' => ['view', 'view_any', 'create', 'update', 'reorder', 'replicate', 'page', 'widget'],
        'view'  => ['view', 'view_any'],
    ];

    /**
     * @return array<string, int> role name => number of permissions granted now
     */
    public function sync(): array
    {
        $permissions = Permission::query()->where('guard_name', 'web')->pluck('id', 'name');

        $granted = [];

        foreach (config('roles.definitions', []) as $roleName => $rules) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            $ids = $this->permissionIdsFor($rules, $permissions);

            $role->permissions()->syncWithoutDetaching($ids->all());

            $granted[$roleName] = $ids->count();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $granted;
    }

    /**
     * @param  array<int, array{0: string, 1: array<int, string>}>  $rules
     * @param  Collection<string, int>  $permissions
     * @return Collection<int, int>
     */
    protected function permissionIdsFor(array $rules, Collection $permissions): Collection
    {
        $ids = collect();

        foreach ($rules as [$level, $prefixes]) {
            $allowedActions = self::LEVELS[$level] ?? [];

            foreach ($permissions as $name => $id) {
                [$action, $prefix, $rest] = $this->parse($name);

                if ($action === null || ! in_array($prefix, $prefixes, true) || ! in_array($action, $allowedActions, true)) {
                    continue;
                }

                if ($level === 'staff' && in_array($action, ['page', 'widget'], true) && str_contains($rest, 'manage_')) {
                    continue;
                }

                $ids->push($id);
            }
        }

        return $ids->unique()->values();
    }

    /**
     * @return array{0: string|null, 1: string|null, 2: string}
     */
    protected function parse(string $permission): array
    {
        foreach (self::ACTIONS as $action) {
            if (str_starts_with($permission, $action.'_')) {
                $rest = substr($permission, strlen($action) + 1);

                return [$action, explode('_', $rest)[0], $rest];
            }
        }

        return [null, null, ''];
    }
}
