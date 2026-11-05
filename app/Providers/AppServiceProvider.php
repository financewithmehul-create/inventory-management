<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Livewire;
use Throwable;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;

use function Livewire\on;
use function Livewire\store;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Authenticatable::class, User::class);
    }

    public function boot(): void
    {
        View::composer('legal.*', function ($view): void {
            try {
                $company = Company::query()->withoutGlobalScopes()->orderBy('id')->first();
            } catch (Throwable) {
                $company = null;
            }

            $view->with([
                'businessName' => $company?->name ?: config('app.name'),
                'contactEmail' => $company?->email,
            ]);
        });

        if (app()->environment('production') && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn (): Password => app()->environment('production')
            ? Password::min(10)->mixedCase()->numbers()->uncompromised()
            : Password::min(8));

        on('dehydrate', function (Component $component): void {
            if (! Livewire::isLivewireRequest()) {
                return;
            }

            if (! store($component)->has('redirect')) {
                return;
            }

            $notifications = session()->pull('filament.notifications');

            if (empty($notifications)) {
                return;
            }

            session()->put('filament.claimed_notifications', $notifications);
        });
    }
}
