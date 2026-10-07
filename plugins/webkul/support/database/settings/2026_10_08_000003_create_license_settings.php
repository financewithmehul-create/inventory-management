<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('license.installed_at', now()->toIso8601String());
        $this->migrator->add('license.license_key', null);
    }

    public function down(): void
    {
        $this->migrator->delete('license.installed_at');
        $this->migrator->delete('license.license_key');
    }
};
