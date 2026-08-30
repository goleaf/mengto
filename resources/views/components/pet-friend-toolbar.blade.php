@props([
    'center',
])

<section class="friend-toolbar" aria-label="{{ __('ui.pet_friend_filters') }}">
    <form method="GET" action="{{ $center['browse_url'] }}" class="friend-toolbar__form">
        <input type="hidden" name="pet" value="{{ $center['source']['slug'] }}">
        <input type="hidden" name="tab" value="{{ $center['tab'] }}">

        <label for="friend-search" class="friend-toolbar__search">
            <span>{{ __('ui.search_this_view') }}</span>
            <span class="friend-toolbar__search-control">
                <x-ui-icon name="search" size="sm" />
                <input
                    id="friend-search"
                    type="search"
                    name="q"
                    value="{{ $center['query'] }}"
                    placeholder="{{ __('ui.pet_breed_owner_or_area') }}"
                    class="field"
                >
            </span>
        </label>

        <label for="friend-intent" class="friend-toolbar__field">
            <span>{{ __('ui.friendship_type') }}</span>
            <select id="friend-intent" name="intent" class="field field--select">
                @forelse ($center['intent_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($center['intent'] === $value)>{{ $label }}</option>
                @empty
                @endforelse
            </select>
        </label>

        <label for="friend-sort" class="friend-toolbar__field">
            <span>{{ __('ui.order') }}</span>
            <select id="friend-sort" name="sort" class="field field--select">
                @forelse ($center['sort_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($center['sort'] === $value)>{{ $label }}</option>
                @empty
                @endforelse
            </select>
        </label>

        <x-action-control
            type="submit"
            label="{{ __('ui.apply_filters') }}"
            icon="sliders-horizontal"
            variant="paper"
            size="regular"
        />
    </form>
</section>
