@props(['chart', 'weights'])

<section {{ $attributes->class(['medical-section']) }} aria-labelledby="weight-chart-title">
    <div class="medical-section__heading">
        <div>
            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.physical_measurements') }}</p>
            <h2 id="weight-chart-title" class="mt-1 text-xl font-bold">{{ __('ui.weight_trend') }}</h2>
        </div>
        <x-status-badge :label="$chart['trend']" icon="chart-no-axes-combined" tone="surface" />
    </div>

    @if ($chart['has_data'])
        <div class="medical-weight-chart" role="img" aria-label="{{ __('ui.weight_measurements_over_time') }}">
            <svg viewBox="0 0 600 160" preserveAspectRatio="none" aria-hidden="true">
                <line x1="24" y1="28" x2="576" y2="28" />
                <line x1="24" y1="80" x2="576" y2="80" />
                <line x1="24" y1="132" x2="576" y2="132" />
                <polyline points="{{ $chart['path'] }}" />
                @forelse ($chart['points'] as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4">
                        <title>{{ $point['date'] }} · {{ $point['label'] }}</title>
                    </circle>
                @empty
                @endforelse
            </svg>
            <div class="medical-weight-chart__range">
                <span>{{ $chart['minimum'] }}</span>
                <span>{{ $chart['maximum'] }}</span>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="medical-table min-w-[38rem]">
                <thead>
                    <tr>
                        <th>{{ __('ui.date') }}</th>
                        <th>{{ __('ui.weight') }}</th>
                        <th>{{ __('ui.source') }}</th>
                        <th>{{ __('ui.context') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($weights as $weight)
                        <tr>
                            <td>{{ $weight['measured_at'] }}</td>
                            <td class="font-bold">{{ $weight['weight'] }}</td>
                            <td>{{ $weight['source_name'] }}<span>{{ $weight['verification'] }}</span></td>
                            <td>{{ $weight['context'] ?: __('ui.not_specified') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('ui.no_weight_entries') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="medical-empty">
            <x-ui-icon name="scale" size="xl" />
            <p>{{ __('ui.no_weight_measurements_yet') }}</p>
        </div>
    @endif
</section>
