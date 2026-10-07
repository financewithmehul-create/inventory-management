<?php

namespace Webkul\Support\Filament\Clusters\Settings\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Webkul\Support\Filament\Clusters\Settings;
use Webkul\Support\Services\LicenseService;
use Webkul\Support\Settings\LicenseSettings;

class ManageLicense extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'license';

    public ?string $licenseKey = null;

    public static function canAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('Admin');
    }

    public static function getNavigationGroup(): string
    {
        return __('support::filament/clusters/manage-branding.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('support::license.title');
    }

    public function getTitle(): string
    {
        return __('support::license.title');
    }

    public function content(Schema $schema): Schema
    {
        $status = app(LicenseService::class)->status();

        return $schema->components([
            Section::make(__('support::license.status.'.$status['state']))
                ->description(match ($status['state']) {
                    'licensed' => __('support::license.status.licensed-to', ['name' => $status['licensed_to'] ?? '-', 'until' => $status['expires_at'] ?? __('support::license.status.lifetime')]),
                    'trial'    => trans_choice('support::license.banner.trial', $status['days_left'], ['days' => $status['days_left']]),
                    default    => __('support::license.banner.expired'),
                })
                ->schema([
                    Textarea::make('licenseKey')
                        ->label(__('support::license.key'))
                        ->helperText($status['reason'] ? __('support::license.reasons.'.$status['reason']) : __('support::license.key-helper'))
                        ->rows(4)
                        ->autocomplete(false),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label(__('support::license.activate'))
                ->icon('heroicon-o-check-badge')
                ->action('activate'),

            Action::make('buy')
                ->label(__('support::license.buy'))
                ->color('gray')
                ->icon('heroicon-o-shopping-cart')
                ->visible(fn (): bool => filled(config('license.buy_url')))
                ->url(fn (): string => (string) config('license.buy_url'), shouldOpenInNewTab: true),
        ];
    }

    public function activate(): void
    {
        $licenses = app(LicenseService::class);

        $check = $licenses->verify((string) $this->licenseKey);

        if (! $check['valid']) {
            Notification::make()->danger()->title(__('support::license.invalid'))->body(__('support::license.reasons.'.$check['reason']))->send();

            return;
        }

        $settings = settings(LicenseSettings::class);
        $settings->license_key = trim((string) $this->licenseKey);
        $settings->save();

        $licenses->forget();

        Notification::make()->success()->title(__('support::license.activated', ['name' => $check['payload']['to'] ?? '']))->send();

        $this->redirect(static::getUrl());
    }
}
