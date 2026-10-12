<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)); gap: 8px;"
    >
        @foreach ($getAvatarOptions() as $slug => $label)
            <button
                type="button"
                x-on:click="state = (state === '{{ $slug }}') ? null : '{{ $slug }}'"
                title="{{ $label }}"
                style="display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px; border-radius: 10px; border: 2px solid transparent; background: transparent; cursor: pointer;"
                x-bind:style="state === '{{ $slug }}' ? 'border-color: var(--primary-600); background: color-mix(in oklab, var(--primary-500) 12%, transparent)' : ''"
            >
                <img src="{{ asset('images/roles/'.$slug.'.png') }}" alt="{{ $label }}" width="56" height="56" loading="lazy" style="width: 56px; height: 56px; border-radius: 9999px; object-fit: cover;">
                <span style="font-size: 11px; line-height: 1.2; text-align: center;" class="text-gray-600 dark:text-gray-300">{{ $label }}</span>
            </button>
        @endforeach
    </div>
</x-dynamic-component>
