@props(['feed'])

<div class="feed-toolbar">
    <x-ui.tab-list :tabs="$feed['modes']" label="Choose feed" />

    <form method="GET" action="{{ route('pet-social.preview') }}" class="feed-filters">
        <input type="hidden" name="feed" value="{{ $feed['mode'] }}">

        <label class="compact-select">
            <span>Order</span>
            <select name="sort" onchange="this.form.submit()">
                @foreach ($feed['sort_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['sort'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="compact-select">
            <span>Format</span>
            <select name="type" onchange="this.form.submit()">
                @foreach ($feed['type_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="compact-select">
            <span>Pet</span>
            <select name="pet" onchange="this.form.submit()">
                @foreach ($feed['pet_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($feed['pet'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <noscript>
            <x-ui.action-control type="submit" label="Apply" icon="filter" variant="paper" />
        </noscript>
    </form>
</div>
