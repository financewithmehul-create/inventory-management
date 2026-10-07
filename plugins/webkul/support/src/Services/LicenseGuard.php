<?php

namespace Webkul\Support\Services;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Webkul\Partner\Models\Partner;
use Webkul\Security\Models\User;
use Webkul\Support\Exceptions\LicenseExpiredException;

/**
 * Makes an install read-only once the trial has ended and no valid license key is present.
 * Reading, signing in and entering a key keep working, and nothing is ever deleted.
 */
class LicenseGuard
{
    public function __construct(protected LicenseService $licenses) {}

    public function register(): void
    {
        foreach (['creating', 'updating', 'deleting', 'restoring'] as $event) {
            Event::listen("eloquent.{$event}: *", function (string $name, array $payload): void {
                $this->check($payload[0] ?? null);
            });
        }
    }

    protected function check(mixed $model): void
    {
        if (app()->runningInConsole() || $this->isExempt($model) || ! $this->licenses->isReadOnly()) {
            return;
        }

        throw new LicenseExpiredException;
    }

    protected function isExempt(mixed $model): bool
    {
        return $model instanceof User
            || $model instanceof DatabaseNotification
            || ($model instanceof Partner && $model->user_id !== null);
    }
}
