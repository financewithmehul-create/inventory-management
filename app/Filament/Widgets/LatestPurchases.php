<?php

namespace App\Filament\Widgets;

use App\Support\AppNavigation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestPurchases extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Latest purchases';

    public static function canView(): bool
    {
        return class_exists(\Webkul\Purchase\Models\Order::class) && AppNavigation::routeFor(\Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\PurchaseOrderResource::class) !== null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => \Webkul\Purchase\Models\Order::query()->with('partner')->latest('id')->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->label('Number'),
                TextColumn::make('partner.name')->label('Supplier'),
                TextColumn::make('amount_total')->label('Total')->money(fn ($record) => $record->currency?->name),
                TextColumn::make('state')->label('Status')->badge(),
            ])
            ->recordUrl(fn ($record): ?string => \Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\PurchaseOrderResource::canView($record) ? \Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\PurchaseOrderResource::getUrl('view', ['record' => $record]) : null)
            ->emptyStateHeading('Nothing here yet')
            ->emptyStateDescription('No purchases yet. Use "New purchase" above to start one.')
            ->emptyStateIcon(null);
    }
}
