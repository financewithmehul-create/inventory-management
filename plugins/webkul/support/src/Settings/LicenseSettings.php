<?php

namespace Webkul\Support\Settings;

use Spatie\LaravelSettings\Settings;

class LicenseSettings extends Settings
{
    public ?string $installed_at;

    public ?string $license_key;

    public static function group(): string
    {
        return 'license';
    }
}
