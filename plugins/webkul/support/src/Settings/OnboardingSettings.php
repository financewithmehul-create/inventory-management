<?php

namespace Webkul\Support\Settings;

use Spatie\LaravelSettings\Settings;

class OnboardingSettings extends Settings
{
    public ?string $completed_at;

    public ?int $completed_by;

    public static function group(): string
    {
        return 'onboarding';
    }
}
