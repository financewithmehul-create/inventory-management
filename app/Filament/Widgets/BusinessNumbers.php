<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Throwable;
use Webkul\Account\Enums\MoveState;
use Webkul\Account\Enums\MoveType;
use Webkul\Account\Enums\PaymentState;
use Webkul\Account\Models\Move;
use Webkul\Inventory\Enums\LocationType;
use Webkul\Inventory\Models\Product;
use Webkul\Sale\Enums\OrderState;
use Webkul\Sale\Models\Order as SaleOrder;

class BusinessNumbers extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return array_values(array_filter([
            $this->stat('Sales this month', fn () => $this->money(
                SaleOrder::query()
                    ->where('state', OrderState::SALE)
                    ->whereBetween('date_order', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount_total')
            ), 'Confirmed sales orders since the 1st.', class_exists(SaleOrder::class)),

            $this->stat('Money to receive', fn () => $this->money($this->openAmount(MoveType::OUT_INVOICE)),
                'Customer invoices not paid yet.', class_exists(Move::class)),

            $this->stat('Bills to pay', fn () => $this->money($this->openAmount(MoveType::IN_INVOICE)),
                'Supplier bills not paid yet.', class_exists(Move::class)),

            $this->stat('Out of stock', fn () => (string) Product::query()
                ->where('is_storable', true)
                ->whereDoesntHave('quantities', fn ($query) => $query
                    ->where('quantity', '>', 0)
                    ->whereHas('location', fn ($location) => $location->where('type', LocationType::INTERNAL)))
                ->count(), 'Products with no stock left.', class_exists(Product::class)),
        ]));
    }

    protected function openAmount(MoveType $type): float
    {
        return (float) Move::query()
            ->where('move_type', $type)
            ->where('state', MoveState::POSTED)
            ->whereIn('payment_state', [PaymentState::NOT_PAID, PaymentState::PARTIAL, PaymentState::IN_PAYMENT])
            ->sum('amount_residual');
    }

    protected function stat(string $label, callable $value, string $description, bool $available): ?Stat
    {
        if (! $available) {
            return null;
        }

        try {
            $result = $value();
        } catch (Throwable $exception) {
            report($exception);

            $result = 'Not available';
        }

        return Stat::make($label, $result)->description($description);
    }

    protected function money(float|int|string $amount): string
    {
        return Number::currency((float) $amount, default_currency_code(), app()->getLocale()) ?: (string) $amount;
    }
}
