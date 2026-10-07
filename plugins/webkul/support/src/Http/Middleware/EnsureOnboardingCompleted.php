<?php

namespace Webkul\Support\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Webkul\Support\Filament\Pages\Onboarding;
use Webkul\Support\Settings\OnboardingSettings;

/**
 * Sends everyone through the first-run setup wizard until an administrator has finished it.
 */
class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.onboarding_enabled') || $this->isCompleted() || $this->isExempt($request)) {
            return $next($request);
        }

        if (Onboarding::canAccess()) {
            return redirect()->to(Onboarding::getUrl());
        }

        abort(503, __('support::filament/pages/onboarding.waiting'));
    }

    protected function isCompleted(): bool
    {
        try {
            return filled(settings(OnboardingSettings::class)->completed_at);
        } catch (Throwable) {
            // The settings table does not exist yet (mid-install): never lock anyone out because of that.
            return true;
        }
    }

    protected function isExempt(Request $request): bool
    {
        return $request->is('livewire/*', 'livewire-*/*')
            || $request->routeIs('filament.*.pages.onboarding', 'filament.*.auth.*');
    }
}
