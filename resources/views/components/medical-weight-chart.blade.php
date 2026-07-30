@props(['chart', 'weights'])

<section {{ $attributes->class(['medical-section']) }} aria-labelledby="weight-chart-title">
    <div class="medical-section__heading">
        <div>
            <p class="text-xs font-bold uppercase text-paw-leaf">Physical measurements</p>
            <h2 id="weight-chart-title" class="mt-1 text-xl font-bold">Weight trend</h2>
        </div>
        <x-status-badge :label="$chart['trend']" icon="chart-no-axes-combined" tone="surface" />
    </div>

    @if ($chart['has_data'])
        <div class="medical-weight-chart" role="img" aria-label="Weight measurements over time">
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
                        <th>Date</th>
                        <th>Weight</th>
                        <th>Source</th>
                        <th>Context</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($weights as $weight)
                        <tr>
                            <td>{{ $weight['measured_at'] }}</td>
                            <td class="font-bold">{{ $weight['weight'] }}</td>
                            <td>{{ $weight['source_name'] }}<span>{{ $weight['verification'] }}</span></td>
                            <td>{{ $weight['context'] ?: 'Not specified' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No weight entries.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="medical-empty">
            <x-lucide-scale class="size-7" aria-hidden="true" />
            <p>No weight measurements yet.</p>
        </div>
    @endif
</section>
