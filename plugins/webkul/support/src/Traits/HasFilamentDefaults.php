<?php

namespace Webkul\Support\Traits;

use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

trait HasFilamentDefaults
{
    protected function registerFilamentDefaults(): void
    {
        Fieldset::configureUsing(fn (Fieldset $fieldset) => $fieldset->columnSpanFull());

        Grid::configureUsing(fn (Grid $grid) => $grid->columnSpanFull());

        Section::configureUsing(fn (Section $section) => $section->columnSpanFull());

        Table::configureUsing(fn (Table $table) => $table->defaultCurrency(fn (): string => default_currency_code()));

        Schema::configureUsing(fn (Schema $schema) => $schema->defaultCurrency(fn (): string => default_currency_code()));
    }

    protected function registerHooks(): void
    {
        //
    }
}
