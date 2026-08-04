@props([
    'modeLinks',
    'viewLinks',
    'sortParameters',
    'sortOptions',
    'currentSort',
    'browseUrl',
])

<section class="place-directory__controls" aria-label="{{ __('place_directory.controls.catalog_mode') }}" data-place-controls>
    <nav class="place-directory__modes" aria-label="{{ __('place_directory.controls.catalog_mode') }}">
        @forelse ($modeLinks as $link)
            <a
                href="{{ $link['url'] }}"
                class="{{ $link['current'] ? 'is-active' : '' }}"
                @if ($link['current']) aria-current="page" @endif
            >
                <x-ui-icon size="sm" :name="$link['icon']" />
                <span>{{ $link['label'] }}</span>
            </a>
        @empty
            <span>{{ __('place_directory.controls.no_catalog_modes') }}</span>
        @endforelse
    </nav>

    <div class="place-directory__toolbar">
        <nav class="place-directory__views" aria-label="{{ __('place_directory.controls.view_mode') }}">
            @forelse ($viewLinks as $link)
                <a
                    href="{{ $link['url'] }}"
                    class="{{ $link['current'] ? 'is-active' : '' }}"
                    @if ($link['current']) aria-current="page" @endif
                >
                    <x-ui-icon size="sm" :name="$link['icon']" />
                    <span>{{ $link['label'] }}</span>
                </a>
            @empty
                <span>{{ __('place_directory.controls.no_views') }}</span>
            @endforelse
        </nav>

        <form method="GET" action="{{ $browseUrl }}" class="place-directory__sort">
            @forelse ($sortParameters as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @empty
                <input type="hidden" name="view" value="split">
            @endforelse
            <label for="place-sort">{{ __('place_directory.controls.sort') }}</label>
            <select id="place-sort" name="sort" class="field field--select" data-auto-submit>
                @forelse ($sortOptions as $value => $label)
                    <option value="{{ $value }}" @selected($currentSort === $value)>{{ $label }}</option>
                @empty
                    <option value="recommended">{{ __('place_directory.controls.recommended') }}</option>
                @endforelse
            </select>
            <button type="submit" class="icon-button" aria-label="{{ __('place_directory.controls.apply_sorting') }}">
                <x-ui-icon name="arrow-up-down" size="sm" />
            </button>
        </form>
    </div>
</section>
