@props(['feed'])

<div class="feed-toolbar">
    <x-tab-list :tabs="$feed['modes']" label="{{ __('ui.choose_feed') }}" />

    <form method="GET" action="{{ route('preview.feed') }}" class="feed-filters">
        <input type="hidden" name="feed" value="{{ $feed['mode'] }}">

        <label class="compact-select">
            <span>{{ __('ui.order') }}</span>
            <select name="sort" onchange="this.form.submit()">
                @foreach ($feed['sort_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['sort'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="compact-select">
            <span>{{ __('ui.format') }}</span>
            <select name="type" onchange="this.form.submit()">
                @foreach ($feed['type_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="compact-select">
            <span>{{ __('ui.pet') }}</span>
            <select name="pet" onchange="this.form.submit()">
                @foreach ($feed['pet_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['pet'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <noscript>
            <x-action-control type="submit" label="{{ __('ui.apply') }}" icon="filter" variant="paper" />
        </noscript>
    </form>
</div>
