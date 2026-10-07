@php
    $show = config('license.enforce') && ($status['state'] === 'expired' || ($status['state'] === 'trial'));
    $expired = $status['state'] === 'expired';
@endphp

@if ($show && auth()->check() && ! request()->routeIs('filament.*.pages.onboarding'))
    @php($tone = $expired ? 'danger' : 'warning')

    <div
        class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-lg px-4 py-3 text-sm"
        style="background: color-mix(in oklab, var(--{{ $tone }}-500) 12%, transparent); color: var(--{{ $tone }}-600); border: 1px solid color-mix(in oklab, var(--{{ $tone }}-500) 30%, transparent)"
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
