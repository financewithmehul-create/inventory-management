@php
    $show = config('license.enforce') && ($status['state'] === 'expired' || ($status['state'] === 'trial'));
    $expired = $status['state'] === 'expired';
@endphp

@if ($show && auth()->check() && ! request()->routeIs('filament.*.pages.onboarding'))
    <div
        @class([
            'mb-4 flex flex-wrap items-center justify-between gap-2 rounded-lg px-4 py-3 text-sm ring-1',
            'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/30' => $expired,
            'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30' => ! $expired,
        ])
    >
        <span>
            @if ($expired)
                {{ __('support::license.banner.expired') }}
            @else
                {{ trans_choice('support::license.banner.trial', $status['days_left'], ['days' => $status['days_left']]) }}
            @endif
        </span>

        @if (\Webkul\Support\Filament\Clusters\Settings\Pages\ManageLicense::canAccess())
            <a href="{{ \Webkul\Support\Filament\Clusters\Settings\Pages\ManageLicense::getUrl() }}" class="font-semibold underline">
                {{ __('support::license.banner.enter-key') }}
            </a>
        @endif
    </div>
@endif
