<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Installs that already exist when this arrives are treated as set up; erp:install clears the value
        // so a brand-new install starts with the onboarding wizard.
        $this->migrator->add('onboarding.completed_at', now()->toIso8601String());
        $this->migrator->add('onboarding.completed_by', null);
    }

    public function down(): void
    {
        $this->migrator->delete('onboarding.completed_at');
        $this->migrator->delete('onboarding.completed_by');
    }
};
