<?php

namespace Webkul\Support\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Throwable;
use Webkul\PluginManager\Models\Plugin;
use Webkul\PluginManager\Services\PluginInstaller;
use Webkul\Support\Models\Company;
use Webkul\Support\Models\Country;
use Webkul\Support\Models\State;
use Webkul\Support\Settings\BrandSettings;
use Webkul\Support\Settings\OnboardingSettings;

/**
 * First-run setup wizard: company details, branding and the modules to install.
 */
class Onboarding extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'onboarding';

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    /**
     * Quick colour choices shown under the colour picker.
     *
     * @var array<string, string>
     */
    protected const PRESET_COLORS = [
        'Blue'    => '#2563eb',
        'Indigo'  => '#4f46e5',
        'Violet'  => '#7c3aed',
        'Pink'    => '#db2777',
        'Red'     => '#dc2626',
        'Orange'  => '#ea580c',
        'Emerald' => '#059669',
        'Teal'    => '#0d9488',
    ];

    /**
     * Modules selected by default (when still installable).
     *
     * @var array<int, string>
     */
    protected const DEFAULT_MODULES = ['products', 'inventories', 'contacts'];

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** @var array<int, string> Modules waiting to be installed, one per request. */
    public array $installQueue = [];

    /** @var array<int, string> */
    public array $installedNow = [];

    /** @var array<string, string> */
    public array $installFailed = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('Admin') ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        return __('support::filament/pages/onboarding.title');
    }

    public function mount(): void
    {
        $company = $this->company();

        $brand = settings(BrandSettings::class);

        $this->content->fill([
            'company_name'  => $company?->name,
            'email'         => $company?->email,
            'phone'         => $company?->phone,
            'website'       => $company?->website,
            'tax_id'        => $company?->tax_id,
            'street1'       => $company?->street1,
            'city'          => $company?->city,
            'zip'           => $company?->zip,
            'country_id'    => $company?->country_id,
            'state_id'      => $company?->state_id,
            'brand_name'    => $brand->brand_name ?: $company?->name,
            'primary_color' => $brand->primary_color ?: self::PRESET_COLORS['Blue'],
            'default_theme' => $brand->default_theme ?: 'system',
            'modules'       => array_values(array_intersect(self::DEFAULT_MODULES, $this->installableModules()->pluck('name')->all())),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Form::make([
                    Wizard::make([
                        $this->companyStep(),
                        $this->brandingStep(),
                        $this->modulesStep(),
                        $this->finishStep(),
                    ])
                        ->submitAction(new HtmlString(Blade::render(
                            '<x-filament::button type="submit" size="lg">'.e(__('support::filament/pages/onboarding.finish.submit')).'</x-filament::button>'
                        ))),
                ])->livewireSubmitHandler('finish'),
            ]);
    }

    protected function companyStep(): Step
    {
        return Step::make(__('support::filament/pages/onboarding.steps.company.label'))
            ->description(__('support::filament/pages/onboarding.steps.company.description'))
            ->icon('heroicon-o-building-office')
            ->schema([
                Grid::make(['default' => 1, 'md' => 2])->schema([
                    TextInput::make('company_name')
                        ->label(__('support::filament/pages/onboarding.fields.company-name'))
                        ->required()
                        ->maxLength(120),
                    TextInput::make('tax_id')
                        ->label(__('support::filament/pages/onboarding.fields.tax-id'))
                        ->maxLength(40),
                    TextInput::make('email')
                        ->label(__('support::filament/pages/onboarding.fields.email'))
                        ->email()
                        ->maxLength(120),
                    TextInput::make('phone')
                        ->label(__('support::filament/pages/onboarding.fields.phone'))
                        ->tel()
                        ->maxLength(40),
                    TextInput::make('website')
                        ->label(__('support::filament/pages/onboarding.fields.website'))
                        ->url()
                        ->maxLength(150),
                    TextInput::make('street1')
                        ->label(__('support::filament/pages/onboarding.fields.street'))
                        ->maxLength(150),
                    TextInput::make('city')
                        ->label(__('support::filament/pages/onboarding.fields.city'))
                        ->maxLength(80),
                    TextInput::make('zip')
                        ->label(__('support::filament/pages/onboarding.fields.zip'))
                        ->maxLength(20),
                    Select::make('country_id')
                        ->label(__('support::filament/pages/onboarding.fields.country'))
                        ->options(fn () => Country::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('state_id', null)),
                    Select::make('state_id')
                        ->label(__('support::filament/pages/onboarding.fields.state'))
                        ->options(fn (Get $get) => State::query()
                            ->where('country_id', $get('country_id'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable(),
                ]),
            ]);
    }

    protected function brandingStep(): Step
    {
        return Step::make(__('support::filament/pages/onboarding.steps.branding.label'))
            ->description(__('support::filament/pages/onboarding.steps.branding.description'))
            ->icon('heroicon-o-swatch')
            ->schema([
                Grid::make(['default' => 1, 'md' => 2])->schema([
                    TextInput::make('brand_name')
                        ->label(__('support::filament/pages/onboarding.fields.brand-name'))
                        ->helperText(__('support::filament/pages/onboarding.fields.brand-name-help'))
                        ->required()
                        ->maxLength(60)
                        ->live(onBlur: true),
                    ColorPicker::make('primary_color')
                        ->label(__('support::filament/pages/onboarding.fields.primary-color'))
                        ->hexColor()
                        ->required()
                        ->live(),
                    FileUpload::make('light_logo')
                        ->label(__('support::filament/pages/onboarding.fields.light-logo'))
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'])
                        ->maxSize(2048)
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public'),
                    FileUpload::make('dark_logo')
                        ->label(__('support::filament/pages/onboarding.fields.dark-logo'))
                        ->helperText(__('support::filament/pages/onboarding.fields.dark-logo-help'))
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'])
                        ->maxSize(2048)
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public'),
                    FileUpload::make('favicon')
                        ->label(__('support::filament/pages/onboarding.fields.favicon'))
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'])
                        ->maxSize(512)
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public'),
                    ToggleButtons::make('default_theme')
                        ->label(__('support::filament/pages/onboarding.fields.default-theme'))
                        ->options([
                            'light'  => __('support::filament/pages/onboarding.fields.theme-light'),
                            'dark'   => __('support::filament/pages/onboarding.fields.theme-dark'),
                            'system' => __('support::filament/pages/onboarding.fields.theme-system'),
                        ])
                        ->icons([
                            'light'  => 'heroicon-o-sun',
                            'dark'   => 'heroicon-o-moon',
                            'system' => 'heroicon-o-computer-desktop',
                        ])
                        ->inline()
                        ->required(),
                ]),
                Actions::make(array_map(
                    fn (string $name, string $hex) => Action::make('preset_'.Str::slug($name))
                        ->label($name)
                        ->size('xs')
                        ->outlined()
                        ->icon('heroicon-s-swatch')
                        ->extraAttributes(['style' => 'color: '.$hex.'; border-color: '.$hex])
                        ->action(fn (Set $set) => $set('primary_color', $hex)),
                    array_keys(self::PRESET_COLORS),
                    array_values(self::PRESET_COLORS),
                ))->label(__('support::filament/pages/onboarding.presets')),
                Html::make(fn (Get $get): HtmlString => $this->previewHtml($get('brand_name'), $get('primary_color'))),
            ]);
    }

    protected function modulesStep(): Step
    {
        return Step::make(__('support::filament/pages/onboarding.steps.modules.label'))
            ->description(__('support::filament/pages/onboarding.steps.modules.description'))
            ->icon('heroicon-o-squares-plus')
            ->afterValidation(function (): void {
                $pending = Plugin::query()
                    ->whereIn('name', $this->data['modules'] ?? [])
                    ->where('is_installed', false)
                    ->exists();

                if ($pending || $this->installQueue !== []) {
                    Notification::make()
                        ->title(__('support::filament/pages/onboarding.modules.install-first'))
                        ->body(__('support::filament/pages/onboarding.modules.install-first-body'))
                        ->warning()
                        ->send();

                    throw new Halt;
                }
            })
            ->schema([
                CheckboxList::make('modules')
                    ->label(__('support::filament/pages/onboarding.fields.modules'))
                    ->helperText(__('support::filament/pages/onboarding.fields.modules-help'))
                    ->options(fn () => $this->installableModules()
                        ->mapWithKeys(fn (Plugin $plugin) => [
                            $plugin->name => Str::headline($plugin->name).($plugin->summary ? ' — '.$plugin->summary : ''),
                        ])
                        ->all())
                    ->columns(['default' => 1, 'md' => 2])
                    ->bulkToggleable()
                    ->searchable(),
                Actions::make([
                    Action::make('installModules')
                        ->label(__('support::filament/pages/onboarding.modules.install'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn () => $this->startInstall()),
                ]),
                Html::make(fn (): HtmlString => $this->installProgressHtml()),
            ]);
    }

    protected function finishStep(): Step
    {
        return Step::make(__('support::filament/pages/onboarding.steps.finish.label'))
            ->description(__('support::filament/pages/onboarding.steps.finish.description'))
            ->icon('heroicon-o-check-badge')
            ->schema([
                Html::make(fn (Get $get): HtmlString => new HtmlString(
                    '<div class="space-y-1"><p class="text-lg font-semibold">'.e(__('support::filament/pages/onboarding.finish.heading')).'</p>'.
                    '<p class="text-sm text-gray-500 dark:text-gray-400">'.e(__('support::filament/pages/onboarding.finish.description')).'</p>'.
                    '<p class="pt-2 text-sm"><strong>'.e((string) $get('company_name')).'</strong></p></div>'
                )),
            ]);
    }

    /**
     * Install the selected modules one at a time, one request each, so a slow host never hits its time limit.
     */
    public function startInstall(): void
    {
        $this->installFailed = [];

        $this->installQueue = Plugin::query()
            ->whereIn('name', $this->data['modules'] ?? [])
            ->where('is_installed', false)
            ->orderBy('sort')
            ->pluck('name')
            ->all();
    }

    public function installNext(): void
    {
        $name = array_shift($this->installQueue);

        if ($name === null) {
            return;
        }

        $plugin = Plugin::query()->where('name', $name)->first();

        if ($plugin === null || $plugin->is_installed) {
            return;
        }

        try {
            app(PluginInstaller::class)->install($plugin);

            $this->installedNow[] = $name;
        } catch (Throwable $e) {
            logger()->error('Onboarding module installation failed', ['plugin' => $name, 'error' => $e->getMessage()]);

            $this->installFailed[$name] = $e->getMessage();
        }
    }

    public function finish(): void
    {
        $state = $this->content->getState();

        DB::transaction(function () use ($state): void {
            $this->saveCompany($state);

            $this->saveBranding($state);

            $onboarding = settings(OnboardingSettings::class);
            $onboarding->completed_at = now()->toIso8601String();
            $onboarding->completed_by = Auth::id();
            $onboarding->save();
        });

        Notification::make()
            ->title(__('support::filament/pages/onboarding.notifications.completed'))
            ->success()
            ->send();

        $this->redirect(filament()->getUrl());
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function saveCompany(array $state): void
    {
        $company = $this->company();

        if ($company === null) {
            return;
        }

        $company->fill([
            'name'       => $state['company_name'],
            'email'      => $state['email'] ?? null,
            'phone'      => $state['phone'] ?? null,
            'website'    => $state['website'] ?? null,
            'tax_id'     => $state['tax_id'] ?? null,
            'street1'    => $state['street1'] ?? null,
            'city'       => $state['city'] ?? null,
            'zip'        => $state['zip'] ?? null,
            'country_id' => $state['country_id'] ?? null,
            'state_id'   => $state['state_id'] ?? null,
        ])->save();

        $company->partner?->update(['name' => $state['company_name']]);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected function saveBranding(array $state): void
    {
        $brand = settings(BrandSettings::class);

        $brand->brand_name = $state['brand_name'];
        $brand->primary_color = $state['primary_color'];
        $brand->default_theme = $state['default_theme'];

        foreach (['light_logo', 'dark_logo', 'favicon'] as $field) {
            $value = $state[$field] ?? null;

            $brand->{$field} = is_array($value) ? (array_values($value)[0] ?? null) : ($value ?: $brand->{$field});
        }

        $brand->save();
    }

    protected function company(): ?Company
    {
        return Company::query()->withoutGlobalScopes()->orderBy('id')->first();
    }

    protected function installableModules()
    {
        return Plugin::query()->where('is_installed', false)->orderBy('sort')->get();
    }

    protected function previewHtml(?string $name, ?string $color): HtmlString
    {
        $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $color) ? $color : self::PRESET_COLORS['Blue'];

        return new HtmlString(
            '<div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">'.
            '<p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-500">'.e(__('support::filament/pages/onboarding.preview.title')).'</p>'.
            '<div class="flex items-center justify-between gap-4">'.
            '<span class="text-lg font-semibold" style="color:'.e($color).'">'.e((string) ($name ?: '—')).'</span>'.
            '<span class="rounded-lg px-4 py-2 text-sm font-medium text-white" style="background:'.e($color).'">'.e(__('support::filament/pages/onboarding.preview.button')).'</span>'.
            '</div></div>'
        );
    }

    protected function installProgressHtml(): HtmlString
    {
        $rows = '';

        foreach ($this->installedNow as $name) {
            $rows .= '<li class="text-sm text-success-600">✓ '.e(Str::headline($name)).' — '.e(__('support::filament/pages/onboarding.modules.done')).'</li>';
        }

        foreach ($this->installFailed as $name => $message) {
            $rows .= '<li class="text-sm text-danger-600">✗ '.e(Str::headline($name)).' — '.e(__('support::filament/pages/onboarding.modules.failed')).': '.e(Str::limit($message, 160)).'</li>';
        }

        $poll = '';

        if ($this->installQueue !== []) {
            $rows .= '<li class="text-sm text-gray-500">⏳ '.e(__('support::filament/pages/onboarding.modules.installing', ['name' => Str::headline($this->installQueue[0])])).'</li>';

            $poll = ' wire:poll.1s="installNext"';
        }

        return new HtmlString('<ul class="mt-3 space-y-1"'.$poll.'>'.$rows.'</ul>');
    }
}
