@props(['record'])

<section {{ $attributes->class(['medical-critical']) }} aria-labelledby="critical-health-title">
    <div class="medical-critical__heading">
        <span class="medical-critical__icon">
            <x-ui-icon name="shield-alert" size="lg" />
        </span>
        <div>
            <p class="text-xs font-bold uppercase text-paw-coral">{{ __('ui.critical_summary') }}</p>
            <h2 id="critical-health-title" class="mt-1 text-xl font-bold">{{ __('ui.before_treatment_or_emergency_care') }}</h2>
        </div>
    </div>

    <div class="medical-critical__grid">
        <div>
            <h3>{{ __('ui.allergies_and_reactions') }}</h3>
            @forelse ($record['critical_allergies'] as $allergy)
                <p>{{ $allergy }}</p>
            @empty
                <p class="text-paw-muted">{{ $record['allergy_knowledge_label'] }}</p>
            @endforelse
        </div>
        <div>
            <h3>{{ __('ui.chronic_conditions') }}</h3>
            @forelse ($record['chronic_conditions'] as $condition)
                <p>{{ $condition }}</p>
            @empty
                <p class="text-paw-muted">{{ __('ui.no_chronic_condition_recorded') }}</p>
            @endforelse
        </div>
        <div>
            <h3>{{ __('ui.emergency_note') }}</h3>
            <p>{{ $record['emergency_notes'] ?: __('ui.no_emergency_handling_note_recorded') }}</p>
        </div>
        <div>
            <h3>{{ __('ui.primary_clinic') }}</h3>
            <p>{{ $record['primary_clinic_name'] ?: __('ui.not_assigned') }}</p>
            @if ($record['primary_clinic_contact'] ?? null)
                <p class="text-paw-muted">{{ $record['primary_clinic_contact'] }}</p>
            @endif
        </div>
    </div>
</section>
