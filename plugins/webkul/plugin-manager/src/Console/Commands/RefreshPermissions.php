<?php

namespace Webkul\PluginManager\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RefreshPermissions extends Command
{
    protected $signature = 'erp:permissions:refresh';

    protected $description = 'Generate permissions for every installed module, give the first role (Admin) all of them and update the built-in roles';

    public function handle(): int
    {
        $this->call('shield:generate', [
            '--all'    => true,
            '--option' => 'permissions',
            '--panel'  => 'admin',
        ]);

        foreach (glob(base_path('plugins/webkul/*/src/Policies/*.php')) ?: [] as $policyFile) {
            preg_match_all("/->can\\('([^']+)'\\)/", (string) file_get_contents($policyFile), $matches);

            foreach (array_unique($matches[1]) as $permissionName) {
                Permission::findOrCreate($permissionName, 'web');
            }
        }

        Role::query()->orderBy('id')->first()?->permissions()->sync(Permission::query()->pluck('id')->all());

        $this->call('erp:roles:sync');

        $this->info('Permissions are up to date.');

        return self::SUCCESS;
    }
}
