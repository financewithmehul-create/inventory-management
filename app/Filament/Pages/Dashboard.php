<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BusinessNumbers;
use App\Filament\Widgets\LatestPurchases;
use App\Filament\Widgets\LatestSales;
use App\Support\AppNavigation;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Webkul\Inventory\Filament\Clusters\Products\Resources\ProductResource;
use Webkul\Invoice\Filament\Clusters\Customers\Resources\InvoiceResource;
use Webkul\Sale\Filament\Clusters\Orders\Resources\QuotationResource;

/**
 * Home screen: the numbers that matter today and one-click buttons for the most common jobs.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getSubheading(): ?string
    {
        return 'Your business today.';
    }

    public function getWidgets(): array
    {
        return [
            BusinessNumbers::class,
            LatestSales::class,
            LatestPurchases::class,
        ];
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            ['new-sale', 'New quote', QuotationResource::class],
            ['new-purchase', 'New purchase', \Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\QuotationResource::class],
            ['new-invoice', 'New invoice', InvoiceResource::class],
            ['new-product', 'Add product', ProductResource::class],
        ];

        $buttons = [];

        foreach ($actions as $index => [$name, $label, $resource]) {
            $route = AppNavigation::routeFor($resource);

            if ($route === null) {
                continue;
            }

            $createRoute = str_replace('.index', '.create', $route);

            $buttons[] = Action::make($name)
                ->label($label)
                ->color($index === 0 ? 'primary' : 'gray')
                ->url(fn (): string => route($createRoute))
                ->visible(fn (): bool => $resource::canCreate());
        }

        return $buttons;
    }
}
