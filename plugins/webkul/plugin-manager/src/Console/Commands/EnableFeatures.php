<?php

namespace Webkul\PluginManager\Console\Commands;

use Filament\Facades\Filament;
use Filament\Pages\SettingsPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Throwable;
use Webkul\Security\Models\Role;
use Webkul\Security\Models\User;

class EnableFeatures extends Command
{
    protected $signature = 'erp:features:enable';

    protected $description = 'Switch on every optional feature in the settings pages (locations, lots, packages, routes, work orders, ...) so every screen is available';

    public function handle(): int
    {
        $panel = Filament::getPanel('admin');

        Filament::setCurrentPanel($panel);

        $adminRole = Role::query()->orderBy('id')->first();

        $admin = $adminRole ? User::query()->role($adminRole->name)->orderBy('id')->first() : null;

        if (! $admin) {
            $this->error('No administrator found. Create one first (php artisan erp:admin:reset you@example.com).');

            return self::FAILURE;
        }

        Auth::login($admin);

        $switched = 0;

        foreach ($panel->getPages() as $page) {
            if (! is_subclass_of($page, SettingsPage::class)) {
                continue;
            }

            try {
                $total = 0;

                for ($pass = 0; $pass < 3; $pass++) {
                    $component = Livewire::test($page);

                    $changed = 0;

                    foreach ((array) $component->get('data') as $key => $value) {
                        if ($value === false && str_starts_with((string) $key, 'enable_')) {
                            $component->set("data.{$key}", true);

                            $changed++;
                        }
                    }

                    if ($changed === 0) {
                        break;
                    }

                    $component->call('save');

                    $total += $changed;
                }

                if ($total > 0) {
                    $switched += $total;

                    $this->line("  ✓ {$page}: {$total} features switched on");
                }
            } catch (Throwable $exception) {
                $this->warn("  ! {$page}: ".$exception->getMessage());
            }
        }

        $this->info("Done. {$switched} features switched on.");

        return self::SUCCESS;
    }
}
