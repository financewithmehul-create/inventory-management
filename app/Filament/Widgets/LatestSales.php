<?php

namespace App\Filament\Widgets;

use App\Support\AppNavigation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestSales extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Latest sales';

    public static function canView(): bool
    {
        return class_exists(\Webkul\Sale\Models\Order::class) && AppNavigation::routeFor(\Webkul\Sale\Filament\Clusters\Orders\Resources\OrderResource::class) !== null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => \Webkul\Sale\Models\Order::query()->with('partner')->latest('id')->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label('Number'),
                TextColumn::make('partner.name')->label('Customer'),
                TextColumn::make('amount_total')->label('Total')->money(fn ($record) => $record->currency?->name),
                TextColumn::make('state')->label('Status')->badge(),
            ])
            ->recordUrl(fn ($record): ?string => \Webkul\Sale\Filament\Clusters\Orders\Resources\OrderResource::canView($record) ? \Webkul\Sale\Filament\Clusters\Orders\Resources\OrderResource::getUrl('view', ['record' => $record]) : null)
            ->emptyStateHeading('Nothing here yet')
            ->emptyStateDescription('No sales yet. Use "New quote" above to start one.')
            ->emptyStateIcon(null);
    }
}
