<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\InitialsAvatarProvider;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\ApplyBrandSettings;
use App\Http\Middleware\SetLocale;
use App\Support\AppNavigation;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Webkul\Security\Filament\Pages\Register;
use Webkul\Support\Filament\Pages\Profile;
use Webkul\Support\GlobalSearchProvider;
use Webkul\Support\Http\Middleware\EnsureOnboardingCompleted;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        if (! app()->runningInConsole()) {
            set_time_limit(300);
        }

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('images/favicon.ico'))
            ->brandName(fn (): string => config('app.name'))
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->darkMode(false)
            ->defaultAvatarProvider(InitialsAvatarProvider::class)
            ->font('IBM Plex Sans', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::hex('#1f3b57'),
                'gray'    => Color::Stone,
                'danger'  => Color::hex('#a33a2c'),
                'success' => Color::hex('#3f6b3f'),
                'warning' => Color::hex('#9a6a1a'),
                'info'    => Color::hex('#2f5a7a'),
            ])
            ->unsavedChangesAlerts()
            ->breadcrumbs(false)
            ->maxContentWidth(Width::SevenExtraLarge)
            ->pages([Dashboard::class])
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => AppNavigation::build($builder))
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn () => Auth::user()?->name)
                    ->url(fn (): string => Profile::getUrl()),
            ])
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): string => Blade::render('<div class="px-6 pb-6 text-xs text-gray-600"><a href="{{ route(\'legal.terms\') }}" class="underline">Terms of service</a> &nbsp; <a href="{{ route(\'legal.privacy\') }}" class="underline">Privacy policy</a></div>'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => Blade::render('<p class="mt-6 text-center text-xs text-gray-600"><a href="{{ route(\'legal.terms\') }}" class="underline">Terms of service</a> &nbsp; <a href="{{ route(\'legal.privacy\') }}" class="underline">Privacy policy</a></p>'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.offline-banner')->render(),
            )
            ->plugins([
                FilamentShieldPlugin::make(),
                SpatieTranslatablePlugin::make()
                    ->defaultLocales(array_keys(config('app.supported_locales'))),
            ])
            ->globalSearch(provider: GlobalSearchProvider::class)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
                ApplyBrandSettings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureOnboardingCompleted::class,
            ])
            ->multiFactorAuthentication([
                AppAuthentication::make()->recoverable(),
                EmailAuthentication::make(),
            ]);

        if (! config('erp.single_user')) {
            $panel->registration(Register::class);
        }

        return $panel;
    }
}
