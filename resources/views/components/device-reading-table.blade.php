@props(['readings', 'device' => null, 'shared' => false])

<div class="device-table-wrap">
    <table class="device-table">
        <thead>
            <tr>
                <th scope="col">{{ __('ui.reading_463816d070') }}</th>
                <th scope="col">{{ __('ui.pet_8f0d1b30eb') }}</th>
                <th scope="col">{{ __('ui.recorded_c7175fa7a0') }}</th>
                <th scope="col">{{ __('ui.quality_1b2c08a873') }}</th>
                @unless ($shared)
                    <th scope="col"><span class="sr-only">{{ __('ui.actions_ff8059dc67') }}</span></th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse ($readings as $reading)
                <tr>
                    <td data-label="{{ __('ui.reading_463816d070') }}">
                        <strong>{{ $reading['value'] }}</strong>
                        <span>{{ $reading['metric_label'] }}</span>
                    </td>
                    <td data-label="{{ __('ui.pet_8f0d1b30eb') }}">{{ $reading['pet_name'] }}</td>
                    <td data-label="{{ __('ui.recorded_c7175fa7a0') }}">
                        {{ $reading['recorded_at'] }}
                        @if ($reading['is_stale'])
                            <span class="device-table__warning">{{ __('ui.stale_data_66f3ffc7a5') }}</span>
                        @endif
                    </td>
                    <td data-label="{{ __('ui.quality_1b2c08a873') }}">
                        <span>{{ $reading['confidence'] }}</span>
                        <small>{{ $reading['verification'] }}{{ $reading['accuracy'] ? ' · '.$reading['accuracy'] : '' }}</small>
                    </td>
                    @unless ($shared)
                        <td data-label="{{ __('ui.action_64cff1319d') }}">
                            @if ($reading['can_add_medical'])
                                <form method="POST" action="{{ route('devices.readings.medical-entry', [$device['slug'], $reading['id']]) }}">
                                    @csrf
                                    <input type="hidden" name="confirmed" value="1">
                                    <button class="device-text-button" type="submit">
                                        <x-ui-icon name="heart-pulse" size="sm" />
                                        <span>{{ __('ui.add_to_health_799912bafc') }}</span>
                                    </button>
                                </form>
                            @else
                                <span class="device-table__done">{{ __('ui.reviewed_or_unassigned_cfea7afcef') }}</span>
                            @endif
                        </td>
                    @endunless
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $shared ? 4 : 5 }}" class="device-table__empty">{{ __('ui.no_readings_are_available_for_this_access_scope_1cdd67a9db') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
