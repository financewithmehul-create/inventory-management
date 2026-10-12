<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('general.allow_registration')) {
            $this->migrator->add('general.allow_registration', true);
        }

        if (! $this->migrator->exists('general.registration_requires_approval')) {
            $this->migrator->add('general.registration_requires_approval', false);
        }
    }

    public function down(): void
    {
        $this->migrator->delete('general.allow_registration');
        $this->migrator->delete('general.registration_requires_approval');
    }
};
