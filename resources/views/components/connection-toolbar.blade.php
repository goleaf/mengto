@props([
    'connections',
])

<section class="connection-toolbar" aria-label="{{ __('ui.connection_filters') }}">
    <form method="GET" action="{{ $connections['browse_url'] }}" class="connection-toolbar__form">
        <input type="hidden" name="tab" value="{{ $connections['tab'] }}">

        <label for="connection-type" class="connection-toolbar__field">
            <span>{{ __('ui.profile_type') }}</span>
            <select
                id="connection-type"
                name="type"
                class="field field--select"
                onchange="this.form.submit()"
            >
                @forelse ($connections['type_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($connections['type'] === $value)>{{ $label }}</option>
                @empty
                @endforelse
            </select>
        </label>

        <label for="connection-sort" class="connection-toolbar__field">
            <span>{{ __('ui.order') }}</span>
            <select
                id="connection-sort"
                name="sort"
                class="field field--select"
                onchange="this.form.submit()"
            >
                @forelse ($connections['sort_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($connections['sort'] === $value)>{{ $label }}</option>
                @empty
                @endforelse
            </select>
        </label>

        <x-action-control
            type="submit"
            label="{{ __('ui.apply') }}"
            icon="sliders-horizontal"
            variant="paper"
            size="regular"
        />
    </form>

    @if ($connections['tab'] === 'following')
        <x-action-control
            :href="$connections['feed_url']"
            label="{{ __('ui.open_following_feed') }}"
            icon="newspaper"
            variant="quiet"
            size="regular"
        />
    @endif
</section>
