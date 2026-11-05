<?php

namespace App\Support;

use App\Filament\Pages\Dashboard;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * The whole menu, in plain words: Dashboard, Sales, Inventory, Purchase, Invoices, Accounting and Settings.
 * Each entry points at an existing screen; entries whose module is not installed are hidden.
 */
class AppNavigation
{
    /**
     * @var array<string, array<int, array{0: string, 1: class-string, 2: string}>> group => [label, page or resource, icon]
     */
    public const MENU = [
        'Sales' => [
            ['Quotes', \Webkul\Sale\Filament\Clusters\Orders\Resources\QuotationResource::class, 'heroicon-o-document-text'],
            ['Sales orders', \Webkul\Sale\Filament\Clusters\Orders\Resources\OrderResource::class, 'heroicon-o-shopping-cart'],
            ['Customers', \Webkul\Sale\Filament\Clusters\Orders\Resources\CustomerResource::class, 'heroicon-o-user-group'],
        ],
        'Inventory' => [
            ['Products', \Webkul\Inventory\Filament\Clusters\Products\Resources\ProductResource::class, 'heroicon-o-cube'],
            ['Stock on hand', \Webkul\Inventory\Filament\Clusters\Operations\Resources\QuantityResource::class, 'heroicon-o-archive-box'],
            ['Goods in', \Webkul\Inventory\Filament\Clusters\Operations\Resources\ReceiptResource::class, 'heroicon-o-arrow-down-tray'],
            ['Goods out', \Webkul\Inventory\Filament\Clusters\Operations\Resources\DeliveryResource::class, 'heroicon-o-arrow-up-tray'],
            ['Reorder', \Webkul\Inventory\Filament\Clusters\Operations\Resources\ReplenishmentResource::class, 'heroicon-o-arrow-path'],
        ],
        'Purchase' => [
            ['Price requests', \Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\QuotationResource::class, 'heroicon-o-document-text'],
            ['Purchase orders', \Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\PurchaseOrderResource::class, 'heroicon-o-truck'],
            ['Suppliers', \Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource::class, 'heroicon-o-building-storefront'],
        ],
        'Invoices' => [
            ['Customer invoices', \Webkul\Invoice\Filament\Clusters\Customers\Resources\InvoiceResource::class, 'heroicon-o-document-currency-rupee'],
            ['Money received', \Webkul\Invoice\Filament\Clusters\Customers\Resources\PaymentResource::class, 'heroicon-o-banknotes'],
            ['Bills to pay', \Webkul\Invoice\Filament\Clusters\Vendors\Resources\BillResource::class, 'heroicon-o-receipt-percent'],
            ['Money paid', \Webkul\Invoice\Filament\Clusters\Vendors\Resources\PaymentResource::class, 'heroicon-o-credit-card'],
        ],
        'Accounting' => [
            ['Entries', \Webkul\Accounting\Filament\Clusters\Accounting\Resources\JournalEntryResource::class, 'heroicon-o-book-open'],
            ['Profit and loss', \Webkul\Accounting\Filament\Clusters\Reporting\Pages\ProfitLoss::class, 'heroicon-o-chart-bar'],
            ['Balance sheet', \Webkul\Accounting\Filament\Clusters\Reporting\Pages\BalanceSheet::class, 'heroicon-o-scale'],
            ['Who owes us', \Webkul\Accounting\Filament\Clusters\Reporting\Pages\AgedReceivable::class, 'heroicon-o-clock'],
            ['What we owe', \Webkul\Accounting\Filament\Clusters\Reporting\Pages\AgedPayable::class, 'heroicon-o-clock'],
            ['Accounts list', \Webkul\Accounting\Filament\Clusters\Configuration\Resources\AccountResource::class, 'heroicon-o-list-bullet'],
        ],
        'Settings' => [
            ['Business and look', \Webkul\Support\Filament\Clusters\Settings\Pages\ManageBranding::class, 'heroicon-o-building-office'],
            ['Taxes', \Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource::class, 'heroicon-o-percent-badge'],
            ['Payment terms', \Webkul\Invoice\Filament\Clusters\Configuration\Resources\PaymentTermResource::class, 'heroicon-o-calendar-days'],
            ['Bank accounts', \Webkul\Invoice\Filament\Clusters\Configuration\Resources\BankAccountResource::class, 'heroicon-o-building-library'],
            ['Warehouse', \Webkul\Inventory\Filament\Clusters\Configurations\Resources\WarehouseResource::class, 'heroicon-o-home-modern'],
            ['My profile', \Webkul\Support\Filament\Pages\Profile::class, 'heroicon-o-user-circle'],
        ],
    ];

    public static function build(NavigationBuilder $builder): NavigationBuilder
    {
        $builder->items([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->url(fn (): string => Dashboard::getUrl())
                ->isActiveWhen(fn (): bool => request()->routeIs(Dashboard::getRouteName())),
        ]);

        $groups = [];

        foreach (self::MENU as $group => $entries) {
            $items = [];

            foreach ($entries as [$label, $target, $icon]) {
                $route = self::routeFor($target);

                if ($route === null) {
                    continue;
                }

                $items[] = NavigationItem::make($label)
                    ->icon($icon)
                    ->url(fn (): string => route($route))
                    ->isActiveWhen(fn (): bool => request()->routeIs(self::activePattern($route)));
            }

            if ($items !== []) {
                $groups[] = NavigationGroup::make($group)->items($items);
            }
        }

        return $builder->groups($groups);
    }

    /**
     * The route name of the index page of a resource, or of a page; null when the module is not installed.
     */
    public static function routeFor(string $target): ?string
    {
        if (! class_exists($target)) {
            return null;
        }

        try {
            $name = method_exists($target, 'getRouteBaseName')
                ? $target::getRouteBaseName().'.index'
                : $target::getRouteName();
        } catch (Throwable) {
            return null;
        }

        return Route::has($name) ? $name : null;
    }

    protected static function activePattern(string $route): string
    {
        return str_ends_with($route, '.index') ? substr($route, 0, -strlen('.index')).'.*' : $route;
    }
}
