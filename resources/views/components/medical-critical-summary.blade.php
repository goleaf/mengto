@props(['record'])

<section {{ $attributes->class(['medical-critical']) }} aria-labelledby="critical-health-title">
    <div class="medical-critical__heading">
        <span class="medical-critical__icon">
            <x-lucide-shield-alert class="size-5" aria-hidden="true" />
        </span>
        <div>
            <p class="text-xs font-bold uppercase text-paw-coral">{{ __('ui.critical_summary_de4c3e1637') }}</p>
            <h2 id="critical-health-title" class="mt-1 text-xl font-bold">{{ __('ui.before_treatment_or_emergency_care_db70d77207') }}</h2>
        </div>
    </div>

    <div class="medical-critical__grid">
        <div>
            <h3>{{ __('ui.allergies_and_reactions_12c7715e3e') }}</h3>
            @forelse ($record['critical_allergies'] as $allergy)
                <p>{{ $allergy }}</p>
            @empty
                <p class="text-paw-muted">{{ __('ui.no_critical_allergy_recorded_7f5c568371') }}</p>
            @endforelse
        </div>
        <div>
            <h3>{{ __('ui.chronic_conditions_c3a152a299') }}</h3>
            @forelse ($record['chronic_conditions'] as $condition)
                <p>{{ $condition }}</p>
            @empty
                <p class="text-paw-muted">{{ __('ui.no_chronic_condition_recorded_3b8e97471b') }}</p>
            @endforelse
        </div>
        <div>
            <h3>{{ __('ui.emergency_note_50429d9332') }}</h3>
            <p>{{ $record['emergency_notes'] ?: __('ui.no_emergency_handling_note_recorded_42e3bd2759') }}</p>
        </div>
        <div>
            <h3>{{ __('ui.primary_clinic_004de405e6') }}</h3>
            <p>{{ $record['primary_clinic_name'] ?: __('ui.not_assigned_13075c2336') }}</p>
            @if ($record['primary_clinic_contact'] ?? null)
                <p class="text-paw-muted">{{ $record['primary_clinic_contact'] }}</p>
            @endif
        </div>
    </div>
</section>
