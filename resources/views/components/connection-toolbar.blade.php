@props([
    'connections',
])

<section class="connection-toolbar" aria-label="{{ __('ui.connection_filters_3eb27ff736') }}">
    <form method="GET" action="{{ $connections['browse_url'] }}" class="connection-toolbar__form">
        <input type="hidden" name="tab" value="{{ $connections['tab'] }}">

        <label for="connection-type" class="connection-toolbar__field">
            <span>{{ __('ui.profile_type_3a2cfc3fe4') }}</span>
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
            <span>{{ __('ui.order_6be090825c') }}</span>
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
            label="{{ __('ui.apply_31e392d1c0') }}"
            icon="sliders-horizontal"
            variant="paper"
            size="regular"
        />
    </form>

    @if ($connections['tab'] === 'following')
        <x-action-control
            :href="$connections['feed_url']"
            label="{{ __('ui.open_following_feed_c3a30a536e') }}"
            icon="newspaper"
            variant="quiet"
            size="regular"
        />
    @endif
</section>
