@props(['plan'])

<dl {{ $attributes->class('walk-meta') }}>
    <x-walk-plan-meta-item
        icon="calendar-days"
        label="{{ __('ui.date_99c40ab405') }}"
        :value="$plan['date_label']"
        :datetime="$plan['datetime']"
    />
    <x-walk-plan-meta-item
        icon="clock-3"
        label="{{ __('ui.start_e4bb9f1ece') }}"
        :value="$plan['time_label']"
        :datetime="$plan['datetime']"
    />
    <x-walk-plan-meta-item
        icon="map-pin"
        label="{{ __('ui.meeting_point_f08183059f') }}"
        :value="$plan['location']"
    />
</dl>
