@props([
    'modeLinks',
    'viewLinks',
    'sortParameters',
    'sortOptions',
    'currentSort',
    'browseUrl',
])

<section class="place-directory__controls" aria-label="{{ __('ui.catalog_mode_0697521ad1') }}">
    <nav class="place-directory__modes" aria-label="{{ __('ui.catalog_mode_0697521ad1') }}">
        @forelse ($modeLinks as $link)
            <a
                href="{{ $link['url'] }}"
                class="{{ $link['current'] ? 'is-active' : '' }}"
                @if ($link['current']) aria-current="page" @endif
            >
                {{ $link['label'] }}
            </a>
        @empty
            <span>{{ __('ui.no_catalog_modes_available_515199bbbc') }}</span>
        @endforelse
    </nav>

    <div class="place-directory__toolbar">
        <nav class="place-directory__views" aria-label="{{ __('ui.view_mode_18997f2413') }}">
            @forelse ($viewLinks as $link)
                <a
                    href="{{ $link['url'] }}"
                    class="{{ $link['current'] ? 'is-active' : '' }}"
                    @if ($link['current']) aria-current="page" @endif
                >
                    <x-dynamic-component :component="'lucide-'.$link['icon']" class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $link['label'] }}</span>
                </a>
            @empty
                <span>{{ __('ui.no_views_available_1e4b6ebfe8') }}</span>
            @endforelse
        </nav>

        <form method="GET" action="{{ $browseUrl }}" class="place-directory__sort">
            @forelse ($sortParameters as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @empty
                <input type="hidden" name="view" value="split">
            @endforelse
            <label for="place-sort">{{ __('ui.sort_bec69036aa') }}</label>
            <select id="place-sort" name="sort" class="field field--select" data-auto-submit>
                @forelse ($sortOptions as $value => $label)
                    <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
                @empty
                    <option value="recommended">{{ __('ui.recommended_d70604e843') }}</option>
                @endforelse
            </select>
            <button type="submit" class="icon-button" aria-label="{{ __('ui.apply_sorting_323ef154f9') }}">
                <x-lucide-arrow-up-down class="icon icon--sm" aria-hidden="true" />
            </button>
        </form>
    </div>
</section>
