<?php

namespace Webkul\Security\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Security\Services\DefaultRoleSynchronizer;

class SyncDefaultRoles extends Command
{
    protected $signature = 'erp:roles:sync';

    protected $description = 'Create the built-in roles (Inventory Manager, Accountant, ...) and grant them their permissions';

    public function handle(DefaultRoleSynchronizer $synchronizer): int
    {
        foreach ($synchronizer->sync() as $role => $count) {
            $this->line(sprintf('  %-26s %d permissions', $role, $count));
        }

        $this->info('Built-in roles are up to date.');

        return self::SUCCESS;
    }
}
