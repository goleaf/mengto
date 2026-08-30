@props(['plan'])

<dl {{ $attributes->class('walk-meta') }}>
    <x-walk-plan-meta-item
        icon="calendar-days"
        label="{{ __('ui.date') }}"
        :value="$plan['date_label']"
        :datetime="$plan['datetime']"
    />
    <x-walk-plan-meta-item
        icon="clock-3"
        label="{{ __('ui.start') }}"
        :value="$plan['time_label']"
        :datetime="$plan['datetime']"
    />
    <x-walk-plan-meta-item
        icon="map-pin"
        label="{{ __('ui.meeting_point') }}"
        :value="$plan['location']"
    />
</dl>
