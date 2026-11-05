<?php

namespace Webkul\Support\Filament\Clusters\Settings\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Webkul\Support\Filament\Clusters\Settings;
use Webkul\Support\Filament\Pages\Onboarding;
use Webkul\Support\Settings\BrandSettings;

class ManageBranding extends SettingsPage
{
    use HasPageShield;

    protected static ?string $cluster = Settings::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 10;

    protected static string $settings = BrandSettings::class;

    protected static function getPagePermission(): ?string
    {
        return 'page_support_manage_branding';
    }

    public static function getNavigationGroup(): string
    {
        return __('support::filament/clusters/manage-branding.group');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('support::filament/clusters/manage-branding.breadcrumb'),
        ];
    }

    public function getTitle(): string
    {
        return __('support::filament/clusters/manage-branding.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('support::filament/clusters/manage-branding.title');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rerunSetup')
                ->label(__('support::filament/clusters/manage-branding.actions.rerun-setup.label'))
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible(fn (): bool => Onboarding::canAccess())
                ->url(fn (): string => Onboarding::getUrl()),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('support::filament/clusters/manage-branding.form.sections.identity.title'))
                    ->description(__('support::filament/clusters/manage-branding.form.sections.identity.description'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('brand_name')
                            ->label(__('support::filament/clusters/manage-branding.form.fields.brand-name'))
                            ->helperText(__('support::filament/clusters/manage-branding.form.fields.brand-name-helper'))
                            ->maxLength(60),
                    ]),
                Section::make(__('support::filament/clusters/manage-branding.form.sections.logo.title'))
                    ->description(__('support::filament/clusters/manage-branding.form.sections.logo.description'))
                    ->columns(2)
                    ->schema([
                        FileUpload::make('light_logo')
                            ->label(__('support::filament/clusters/manage-branding.form.fields.light-logo'))
                            ->helperText(__('support::filament/clusters/manage-branding.form.fields.light-logo-helper'))
                            ->image()
                            ->maxSize(2048)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/bmp',
                                'image/webp',
                            ])
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public'),
                        FileUpload::make('favicon')
                            ->label(__('support::filament/clusters/manage-branding.form.fields.favicon'))
                            ->helperText(__('support::filament/clusters/manage-branding.form.fields.favicon-helper'))
                            ->image()
                            ->maxSize(2048)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/bmp',
                                'image/webp',
                            ])
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public'),
                    ]),
                Section::make(__('support::filament/clusters/manage-branding.form.sections.colors.title'))
                    ->description(__('support::filament/clusters/manage-branding.form.sections.colors.description'))
                    ->columns(1)
                    ->headerActions([
                        Action::make('reset')
                            ->label(__('support::filament/clusters/manage-branding.actions.reset.label'))
                            ->icon('heroicon-o-arrow-path')
                            ->color('gray')
                            ->link()
                            ->requiresConfirmation()
                            ->action(function (Set $set): void {
                                foreach ($this->getDefaultColors() as $field => $hex) {
                                    $set($field, $hex);
                                }
                            }),
                    ])
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label(__('support::filament/clusters/manage-branding.form.fields.primary-color'))
                            ->hexColor(),
                    ]),
            ]);
    }

    private function getDefaultColors(): array
    {
        return [
            'primary_color' => '#1f3b57',
        ];
    }
}
