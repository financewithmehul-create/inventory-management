<?php

namespace App\Support;

use App\Filament\Pages\Dashboard;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Route;
use Throwable;
use Webkul\Accounting\Filament\Clusters\Accounting\Resources\JournalEntryResource;
use Webkul\Accounting\Filament\Clusters\Configuration\Resources\AccountResource;
use Webkul\Accounting\Filament\Clusters\Reporting\Pages\AgedPayable;
use Webkul\Accounting\Filament\Clusters\Reporting\Pages\AgedReceivable;
use Webkul\Accounting\Filament\Clusters\Reporting\Pages\BalanceSheet;
use Webkul\Accounting\Filament\Clusters\Reporting\Pages\ProfitLoss;
use Webkul\Inventory\Filament\Clusters\Configurations\Resources\WarehouseResource;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\DeliveryResource;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\QuantityResource;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\ReceiptResource;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\ReplenishmentResource;
use Webkul\Inventory\Filament\Clusters\Products\Resources\ProductResource;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\BankAccountResource;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\PaymentTermResource;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource;
use Webkul\Invoice\Filament\Clusters\Customers\Resources\InvoiceResource;
use Webkul\Invoice\Filament\Clusters\Customers\Resources\PaymentResource;
use Webkul\Invoice\Filament\Clusters\Vendors\Resources\BillResource;
use Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\PurchaseOrderResource;
use Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource;
use Webkul\Sale\Filament\Clusters\Orders\Resources\CustomerResource;
use Webkul\Sale\Filament\Clusters\Orders\Resources\OrderResource;
use Webkul\Sale\Filament\Clusters\Orders\Resources\QuotationResource;
use Webkul\Support\Filament\Clusters\Settings\Pages\ManageBranding;
use Webkul\Support\Filament\Pages\Profile;

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
            ['Quotes', QuotationResource::class, 'heroicon-o-document-text'],
            ['Sales orders', OrderResource::class, 'heroicon-o-shopping-cart'],
            ['Customers', CustomerResource::class, 'heroicon-o-user-group'],
        ],
        'Inventory' => [
            ['Products', ProductResource::class, 'heroicon-o-cube'],
            ['Stock on hand', QuantityResource::class, 'heroicon-o-archive-box'],
            ['Goods in', ReceiptResource::class, 'heroicon-o-arrow-down-tray'],
            ['Goods out', DeliveryResource::class, 'heroicon-o-arrow-up-tray'],
            ['Reorder', ReplenishmentResource::class, 'heroicon-o-arrow-path'],
        ],
        'Purchase' => [
            ['Price requests', \Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\QuotationResource::class, 'heroicon-o-document-text'],
            ['Purchase orders', PurchaseOrderResource::class, 'heroicon-o-truck'],
            ['Suppliers', VendorResource::class, 'heroicon-o-building-storefront'],
        ],
        'Invoices' => [
            ['Customer invoices', InvoiceResource::class, 'heroicon-o-document-currency-rupee'],
            ['Money received', PaymentResource::class, 'heroicon-o-banknotes'],
            ['Bills to pay', BillResource::class, 'heroicon-o-receipt-percent'],
            ['Money paid', \Webkul\Invoice\Filament\Clusters\Vendors\Resources\PaymentResource::class, 'heroicon-o-credit-card'],
        ],
        'Accounting' => [
            ['Entries', JournalEntryResource::class, 'heroicon-o-book-open'],
            ['Profit and loss', ProfitLoss::class, 'heroicon-o-chart-bar'],
            ['Balance sheet', BalanceSheet::class, 'heroicon-o-scale'],
            ['Who owes us', AgedReceivable::class, 'heroicon-o-clock'],
            ['What we owe', AgedPayable::class, 'heroicon-o-clock'],
            ['Accounts list', AccountResource::class, 'heroicon-o-list-bullet'],
        ],
        'Settings' => [
            ['Business and look', ManageBranding::class, 'heroicon-o-building-office'],
            ['Taxes', TaxResource::class, 'heroicon-o-percent-badge'],
            ['Payment terms', PaymentTermResource::class, 'heroicon-o-calendar-days'],
            ['Bank accounts', BankAccountResource::class, 'heroicon-o-building-library'],
            ['Warehouse', WarehouseResource::class, 'heroicon-o-home-modern'],
            ['My profile', Profile::class, 'heroicon-o-user-circle'],
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
