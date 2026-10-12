<?php

namespace Webkul\PluginManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;
use Spatie\Permission\Models\Permission;
use Throwable;
use Webkul\PluginManager\Models\Plugin;

class Doctor extends Command
{
    protected $signature = 'erp:doctor {--fix : Run the missing migrations and refresh permissions}';

    protected $description = 'Check that every installed module has its database tables and permissions, and optionally repair them';

    public function handle(): int
    {
        $problems = [];

        try {
            DB::connection()->getPdo();
            $this->ok('Database connection');
        } catch (Throwable $exception) {
            $this->error('Cannot connect to the database: '.$exception->getMessage());

            return self::FAILURE;
        }

        $pending = $this->pendingMigrations();

        if ($pending === []) {
            $this->ok('All migrations of the installed modules have run');
        } else {
            $problems[] = count($pending).' migrations have not run (modules would crash on missing tables)';
            $this->bad(end($problems));
        }

        $installed = Plugin::query()->where('is_installed', true)->count();

        $this->ok("{$installed} modules installed");

        $permissionCount = Permission::query()->count();

        if ($permissionCount < 100 || ($installed > 0 && $permissionCount < 10 * $installed)) {
            $problems[] = "Only {$permissionCount} permissions exist; modules installed from the web may lack theirs";
            $this->bad(end($problems));
        } else {
            $this->ok("{$permissionCount} permissions generated");
        }

        if (! file_exists(public_path('storage'))) {
            $problems[] = 'public/storage link is missing (uploaded logos and photos will not show). Run: ln -s ../storage/app/public public/storage';
            $this->bad(end($problems));
        } else {
            $this->ok('Storage link');
        }

        if (app()->isProduction() && config('app.debug')) {
            $problems[] = 'APP_DEBUG is true in production; set APP_DEBUG=false in .env';
            $this->bad(end($problems));
        }

        if ($problems === []) {
            $this->info('Everything looks healthy.');

            return self::SUCCESS;
        }

        if (! $this->option('fix')) {
            $this->warn('Run again with --fix to repair the migrations and permissions automatically.');

            return self::FAILURE;
        }

        $this->info('Repairing...');

        Artisan::call('migrate', ['--force' => true], $this->getOutput());
        Artisan::call('erp:permissions:refresh', [], $this->getOutput());
        foreach (app(SettingsCacheFactory::class)->all() as $settingsCache) {
            $settingsCache->clear();
        }

        $this->info('Done. Run erp:doctor again to confirm.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function pendingMigrations(): array
    {
        $migrator = app('migrator');

        if (! $migrator->repositoryExists()) {
            return ['migrations table'];
        }

        $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));

        return array_values(array_diff(array_keys($files), $migrator->getRepository()->getRan()));
    }

    protected function ok(string $message): void
    {
        $this->line("  <info>✓</info> {$message}");
    }

    protected function bad(string $message): void
    {
        $this->line("  <error> ✗ </error> {$message}");
    }
}
