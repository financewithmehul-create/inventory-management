<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Calm default colours and light mode. Colours a customer already chose are kept.
 */
return new class extends SettingsMigration
{
    /** @var array<string, array{0: array<int, string>, 1: string}> stock values => new default */
    protected const COLORS = [
        'primary_color' => [['#155dfc', '#2563eb', '#7c3aed'], '#1f3b57'],
        'gray_color'    => [['#52525c', '#52525b'], '#57534e'],
        'danger_color'  => [['#e7000b', '#dc2626'], '#a33a2c'],
        'success_color' => [['#00a63e', '#16a34a'], '#3f6b3f'],
        'warning_color' => [['#e17100', '#d97706'], '#9a6a1a'],
        'info_color'    => [['#155dfc', '#2563eb'], '#2f5a7a'],
    ];

    public function up(): void
    {
        foreach (self::COLORS as $property => [$stock, $default]) {
            if ($this->migrator->exists("branding.{$property}")) {
                $this->migrator->update("branding.{$property}", fn ($value) => in_array(strtolower((string) $value), $stock, true) || blank($value) ? $default : $value);
            }
        }

        if ($this->migrator->exists('branding.default_theme')) {
            $this->migrator->update('branding.default_theme', fn () => 'light');
        }
    }

    public function down(): void
    {
        //
    }
};
