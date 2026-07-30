@props(['record'])

<section {{ $attributes->class(['medical-critical']) }} aria-labelledby="critical-health-title">
    <div class="medical-critical__heading">
        <span class="medical-critical__icon">
            <x-lucide-shield-alert class="size-5" aria-hidden="true" />
        </span>
        <div>
            <p class="text-xs font-bold uppercase text-paw-coral">Critical summary</p>
            <h2 id="critical-health-title" class="mt-1 text-xl font-bold">Before treatment or emergency care</h2>
        </div>
    </div>

    <div class="medical-critical__grid">
        <div>
            <h3>Allergies and reactions</h3>
            @forelse ($record['critical_allergies'] as $allergy)
                <p>{{ $allergy }}</p>
            @empty
                <p class="text-paw-muted">No critical allergy recorded.</p>
            @endforelse
        </div>
        <div>
            <h3>Chronic conditions</h3>
            @forelse ($record['chronic_conditions'] as $condition)
                <p>{{ $condition }}</p>
            @empty
                <p class="text-paw-muted">No chronic condition recorded.</p>
            @endforelse
        </div>
        <div>
            <h3>Emergency note</h3>
            <p>{{ $record['emergency_notes'] ?: 'No emergency handling note recorded.' }}</p>
        </div>
        <div>
            <h3>Primary clinic</h3>
            <p>{{ $record['primary_clinic_name'] ?: 'Not assigned' }}</p>
            @if ($record['primary_clinic_contact'] ?? null)
                <p class="text-paw-muted">{{ $record['primary_clinic_contact'] }}</p>
            @endif
        </div>
    </div>
</section>
