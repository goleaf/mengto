@props(['plan'])

<dl {{ $attributes->class('walk-meta') }}>
    <x-object.walk-plan-meta-item
        icon="calendar-days"
        label="Date"
        :value="$plan['date_label']"
        :datetime="$plan['datetime']"
    />
    <x-object.walk-plan-meta-item
        icon="clock-3"
        label="Start"
        :value="$plan['time_label']"
        :datetime="$plan['datetime']"
    />
    <x-object.walk-plan-meta-item
        icon="map-pin"
        label="Meeting point"
        :value="$plan['location']"
    />
</dl>
