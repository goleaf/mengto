@props(['feed'])

<div class="feed-toolbar">
    <x-tab-list :tabs="$feed['modes']" label="{{ __('ui.choose_feed_08ed5140c2') }}" />

    <form method="GET" action="{{ route('home') }}" class="feed-filters">
        <input type="hidden" name="feed" value="{{ $feed['mode'] }}">

        <label class="compact-select">
            <span>{{ __('ui.order_6be090825c') }}</span>
            <select name="sort" onchange="this.form.submit()">
                @foreach ($feed['sort_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['sort'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="compact-select">
            <span>{{ __('ui.format_2f343666aa') }}</span>
            <select name="type" onchange="this.form.submit()">
                @foreach ($feed['type_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="compact-select">
            <span>{{ __('ui.pet_8f0d1b30eb') }}</span>
            <select name="pet" onchange="this.form.submit()">
                @foreach ($feed['pet_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['pet'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <noscript>
            <x-action-control type="submit" label="{{ __('ui.apply_31e392d1c0') }}" icon="filter" variant="paper" />
        </noscript>
    </form>
</div>
