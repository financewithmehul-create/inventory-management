<?php

namespace Webkul\PluginManager\Services;

use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Webkul\PluginManager\Models\Plugin;
use Webkul\PluginManager\Package;

class PluginInstaller
{
    protected const TIMEOUT_SECONDS = 300;

    /**
     * Install a plugin (and its dependencies) and mark it installed and active.
     *
     * Runs the plugin's `{name}:install` command in a separate PHP process when the host allows it, which
     * keeps long installs out of the web request. Shared hosts that disable exec() fall back to running the
     * command in the current process.
     *
     * @throws RuntimeException when the installation fails
     */
    public function install(Plugin $plugin): void
    {
        $command = "{$plugin->name}:install";

        if ($this->canSpawnProcess()) {
            $this->installInSeparateProcess($command);
        } else {
            $this->installInProcess($command);
        }

        $plugin->update([
            'is_installed' => true,
            'is_active'    => true,
        ]);
    }

    protected function installInSeparateProcess(string $command): void
    {
        $php = escapeshellarg(Package::phpBinaryPath());

        $artisan = escapeshellarg(base_path('artisan'));

        $cmd = Package::buildTimeoutCommand(
            self::TIMEOUT_SECONDS,
            "$php $artisan ".escapeshellarg($command).' --no-interaction 2>&1'
        );

        $output = [];

        $exitCode = 0;

        exec($cmd, $output, $exitCode);

        if ($exitCode === 124) {
            throw new RuntimeException('Installation timed out after 5 minutes.');
        }

        if ($exitCode !== 0) {
            $errorOutput = implode(PHP_EOL, array_slice($output, -10));

            throw new RuntimeException(
                "Installation failed with exit code {$exitCode}.".($errorOutput ? " Last output: {$errorOutput}" : '')
            );
        }
    }

    protected function installInProcess(string $command): void
    {
        $exitCode = Artisan::call($command, ['--no-interaction' => true]);

        if ($exitCode !== 0) {
            throw new RuntimeException("Installation failed with exit code {$exitCode}. ".trim(Artisan::output()));
        }
    }

    protected function canSpawnProcess(): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return ! in_array('exec', $disabled, true);
    }
}
