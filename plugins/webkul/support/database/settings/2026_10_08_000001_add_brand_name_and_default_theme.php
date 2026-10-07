<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('branding.brand_name', null);
        $this->migrator->add('branding.default_theme', 'system');
    }

    public function down(): void
    {
        $this->migrator->delete('branding.brand_name');
        $this->migrator->delete('branding.default_theme');
    }
};
