<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['license.installed_at', 'license.license_key'] as $property) {
            if ($this->migrator->exists($property)) {
                $this->migrator->delete($property);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
