<?php

namespace Webkul\PluginManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;
use Webkul\PluginManager\Models\Plugin;
use Webkul\PluginManager\Package;

class PruneModules extends Command
{
    protected $signature = 'erp:modules:prune {--force : Do not ask for confirmation} {--no-backup : Skip the database backup}';

    protected $description = 'Remove every module that is not part of the product (deletes their tables and data)';

    public function handle(): int
    {
        $installed = collect(config('erp.removed_modules', []))
            ->filter(fn (string $name): bool => Package::isPluginInstalled($name))
            ->values();

        if ($installed->isEmpty()) {
            $this->info('Nothing to remove. Only the product modules are installed.');

            return self::SUCCESS;
        }

        $this->warn('These modules and ALL their data will be deleted: '.$installed->implode(', '));

        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            return self::FAILURE;
        }

        if (! $this->option('no-backup')) {
            $this->info('Making a database backup first...');

            if (Artisan::call('erp:backup', [], $this->getOutput()) !== self::SUCCESS) {
                $this->error('The backup failed, so nothing was removed. Fix the backup or run again with --no-backup.');

                return self::FAILURE;
            }
        }

        $failed = [];

        foreach ($installed as $name) {
            $this->line("Removing {$name}...");

            try {
                Artisan::call("{$name}:uninstall", ['--force' => true]);
            } catch (Throwable $exception) {
                $this->error("  Could not remove {$name}: ".$exception->getMessage());
            }

            Package::$plugins = [];

            if (Plugin::query()->where('name', $name)->where('is_installed', true)->exists()) {
                $failed[] = $name;
            }
        }

        Artisan::call('optimize:clear');
        Artisan::call('erp:doctor', ['--fix' => true], $this->getOutput());

        if ($failed !== []) {
            $this->error('Not removed: '.implode(', ', $failed));

            return self::FAILURE;
        }

        $this->info('Done. Only the product modules are left.');

        return self::SUCCESS;
    }
}
