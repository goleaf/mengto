<div
    data-section="onboarding"
    x-data
    @if ($focusCurrentStep) x-init="$nextTick(() => { $refs.stepHeading?.focus(); $refs.stepHeading?.scrollIntoView({ block: 'start' }) })" @endif
    x-on:onboarding-validation-failed.window="$nextTick(() => $refs.errorSummary?.focus())"
    x-on:onboarding-step-changed.window="$nextTick(() => { $refs.stepHeading?.focus(); $refs.stepHeading?.scrollIntoView({ block: 'start' }) })"
>
    <p class="text-sm font-bold uppercase tracking-wide text-paw-leaf">{{ __('onboarding.page.eyebrow') }}</p>
    <h1 class="mt-2 text-2xl font-semibold leading-tight text-paw-ink">{{ __('onboarding.page.title') }}</h1>
    <p class="mt-3 max-w-2xl text-base leading-7 text-paw-muted">{{ __('onboarding.page.description') }}</p>

    <div data-onboarding-progress class="mt-6">
        <h2 id="onboarding-progress-label" class="sr-only">{{ __('onboarding.progress.label') }}</h2>
        <ol data-onboarding-progress-list role="list" aria-labelledby="onboarding-progress-label" class="grid grid-cols-4 gap-2">
            @foreach ($this->progressSteps as $progressStep)
                <li
                    wire:key="onboarding-progress-{{ $progressStep['step'] }}"
                    data-onboarding-step
                    data-step="{{ $progressStep['step'] }}"
                    data-status="{{ $progressStep['status'] }}"
                    @if ($progressStep['status'] === 'current') aria-current="step" @endif
                    @class([
                        'min-w-0 rounded-lg border px-2 py-2 text-center text-sm md:px-3 md:py-3 md:text-left',
                        'border-paw-leaf bg-paw-mint font-semibold' => $progressStep['status'] === 'current',
                        'border-paw-leaf text-paw-ink' => $progressStep['status'] === 'complete',
                        'border-paw-line text-paw-muted' => $progressStep['status'] === 'upcoming',
                    ])
                >
                    <span class="mx-auto grid size-7 place-items-center rounded-full border border-current md:mx-0" aria-hidden="true">{{ $progressStep['number'] }}</span>
                    <span class="mt-1 hidden break-words leading-5 md:block" aria-hidden="true">{{ $progressStep['label'] }}</span>
                    <span class="sr-only">{{ $progressStep['label'] }}: {{ __('onboarding.progress.status.'.$progressStep['status']) }}</span>
                    @if ($progressStep['status'] === 'current')
                        <span class="mt-1 block break-words text-xs leading-5 md:hidden" aria-hidden="true">{{ $progressStep['label'] }}</span>
                    @endif
                    <span class="mt-1 hidden text-xs font-normal md:block" aria-hidden="true">{{ __('onboarding.progress.status.'.$progressStep['status']) }}</span>
                </li>
            @endforeach
        </ol>
        <progress aria-labelledby="onboarding-progress-label" aria-valuetext="{{ __('onboarding.progress.completed', ['completed' => $this->completedProgressSteps, 'total' => 4]) }}" class="mt-3 h-2 w-full accent-paw-leaf" value="{{ $this->completedProgressSteps }}" max="4">
            {{ __('onboarding.progress.completed', ['completed' => $this->completedProgressSteps, 'total' => 4]) }}
        </progress>
    </div>

    @if ($errors->any())
        <div x-ref="errorSummary" tabindex="-1" role="alert" aria-labelledby="onboarding-error-summary-title" class="mt-6 rounded-lg border-2 border-paw-coral bg-paw-coral/10 px-4 py-3 text-paw-ink">
            <p id="onboarding-error-summary-title" class="font-semibold">{{ __('onboarding.validation.summary') }}</p>
            <ul role="list" class="mt-2 list-disc space-y-1 pl-5 text-sm">
                @foreach ($errors->all() as $message)
                    <li data-error-summary-message>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p wire:offline role="status" aria-live="polite" class="mt-5 rounded-lg border border-paw-coral bg-paw-coral/10 px-4 py-3 text-sm font-semibold">{{ __('onboarding.states.offline') }}</p>

    @if ($expectedStep === 'introduction')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-lg font-semibold">{{ __('onboarding.steps.introduction.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.introduction.body') }}</p>
            <p class="mt-3 text-sm leading-6 text-paw-muted">{{ __('onboarding.page.resume_note') }}</p>
            <form wire:submit="acknowledgeIntroduction" class="mt-6 flex flex-col items-stretch sm:items-start">
                <button type="submit" wire:loading.attr="disabled" wire:loading.attr="aria-busy" wire:target="acknowledgeIntroduction" class="action action--primary action--regular min-h-11 w-full whitespace-normal sm:w-auto">
                    <span wire:loading.remove wire:target="acknowledgeIntroduction">{{ __('onboarding.steps.introduction.continue') }}</span>
                    <span wire:loading wire:target="acknowledgeIntroduction" role="status" aria-live="polite">{{ __('onboarding.states.saving') }}</span>
                </button>
            </form>
        </section>
    @elseif ($expectedStep === 'preferences')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-lg font-semibold">{{ __('onboarding.steps.preferences.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.preferences.body') }}</p>
            <form wire:submit="savePreferences" class="mt-6 grid min-w-0 gap-5" novalidate>
                <div class="min-w-0">
                    <label for="onboarding-locale" class="font-semibold">{{ __('auth.fields.locale') }}</label>
                    <select id="onboarding-locale" wire:model="preferencesForm.locale" required aria-required="true" class="mt-2 min-h-11 w-full max-w-full rounded-md border-2 border-paw-muted bg-white px-3 py-2 aria-invalid:border-paw-coral" aria-describedby="onboarding-locale-help @error('preferencesForm.locale') onboarding-locale-error @enderror" @error('preferencesForm.locale') aria-invalid="true" @enderror>
                        @foreach ($this->localeOptions as $locale => $label)
                            <option wire:key="onboarding-locale-{{ $locale }}" value="{{ $locale }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p id="onboarding-locale-help" class="mt-2 text-sm text-paw-muted">{{ __('onboarding.steps.preferences.locale_help') }}</p>
                    @error('preferencesForm.locale') <p id="onboarding-locale-error" class="mt-2 text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                </div>
                <div class="min-w-0">
                    <label for="onboarding-timezone" class="font-semibold">{{ __('auth.fields.timezone') }}</label>
                    <select id="onboarding-timezone" wire:model="preferencesForm.timezone" required aria-required="true" class="mt-2 min-h-11 w-full max-w-full rounded-md border-2 border-paw-muted bg-white px-3 py-2 aria-invalid:border-paw-coral" aria-describedby="onboarding-timezone-help @error('preferencesForm.timezone') onboarding-timezone-error @enderror" @error('preferencesForm.timezone') aria-invalid="true" @enderror>
                        @foreach ($this->timezoneOptions as $timezone => $label)
                            <option wire:key="onboarding-timezone-{{ $timezone }}" value="{{ $timezone }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p id="onboarding-timezone-help" class="mt-2 text-sm text-paw-muted">{{ __('onboarding.steps.preferences.timezone_help') }}</p>
                    @error('preferencesForm.timezone') <p id="onboarding-timezone-error" class="mt-2 text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                </div>
                <p wire:dirty role="status" aria-live="polite" class="text-sm font-medium text-paw-muted">{{ __('onboarding.states.unsaved') }}</p>
                <div class="flex flex-col items-stretch sm:items-start">
                    <button type="submit" wire:loading.attr="disabled" wire:loading.attr="aria-busy" wire:target="savePreferences" class="action action--primary action--regular min-h-11 w-full whitespace-normal sm:w-auto">
                        <span wire:loading.remove wire:target="savePreferences">{{ __('onboarding.steps.preferences.save') }}</span>
                        <span wire:loading wire:target="savePreferences" role="status" aria-live="polite">{{ __('onboarding.states.saving') }}</span>
                    </button>
                </div>
            </form>
        </section>
    @elseif ($expectedStep === 'pet-relationship')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-lg font-semibold">{{ __('onboarding.steps.pet_relationship.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.pet_relationship.body') }}</p>
            <a href="{{ route('pets.manage.create') }}" class="action action--regular mt-5 min-h-11 w-full whitespace-normal sm:w-auto">{{ __('onboarding.steps.pet_relationship.create_or_find') }}</a>
            <form wire:submit="savePetRelationship" class="mt-6 grid gap-5" novalidate>
                <fieldset data-onboarding-choice-group data-onboarding-pet-choices aria-required="true" @error('petForm.choice') aria-invalid="true" aria-describedby="onboarding-pet-choice-error" @enderror>
                    <legend class="font-semibold">{{ __('onboarding.steps.pet_relationship.legend') }}</legend>
                    <div class="mt-3 grid gap-3">
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4 has-checked:border-paw-leaf has-checked:bg-paw-mint forced-colors:has-checked:border-[Highlight]"><input type="radio" wire:model="petForm.choice" value="managed-pet" required class="mt-1 size-5 shrink-0 accent-paw-leaf" @error('petForm.choice') aria-invalid="true" aria-describedby="onboarding-pet-choice-error" @enderror><span class="min-w-0 break-words"><span class="block font-semibold">{{ __('onboarding.steps.pet_relationship.managed_pet.label') }}</span><span class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.pet_relationship.managed_pet.description') }}</span></span></label>
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4 has-checked:border-paw-leaf has-checked:bg-paw-mint forced-colors:has-checked:border-[Highlight]"><input type="radio" wire:model="petForm.choice" value="access-requested" required class="mt-1 size-5 shrink-0 accent-paw-leaf" @error('petForm.choice') aria-invalid="true" aria-describedby="onboarding-pet-choice-error" @enderror><span class="min-w-0 break-words"><span class="block font-semibold">{{ __('onboarding.steps.pet_relationship.access_requested.label') }}</span><span class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.pet_relationship.access_requested.description') }}</span></span></label>
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4 has-checked:border-paw-leaf has-checked:bg-paw-mint forced-colors:has-checked:border-[Highlight]"><input type="radio" wire:model="petForm.choice" value="no-pet" required class="mt-1 size-5 shrink-0 accent-paw-leaf" @error('petForm.choice') aria-invalid="true" aria-describedby="onboarding-pet-choice-error" @enderror><span class="min-w-0 break-words"><span class="block font-semibold">{{ __('onboarding.steps.pet_relationship.no_pet.label') }}</span><span class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.pet_relationship.no_pet.description') }}</span></span></label>
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4 has-checked:border-paw-leaf has-checked:bg-paw-mint forced-colors:has-checked:border-[Highlight]"><input type="radio" wire:model="petForm.choice" value="add-later" required class="mt-1 size-5 shrink-0 accent-paw-leaf" @error('petForm.choice') aria-invalid="true" aria-describedby="onboarding-pet-choice-error" @enderror><span class="min-w-0 break-words"><span class="block font-semibold">{{ __('onboarding.steps.pet_relationship.add_later.label') }}</span><span class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.pet_relationship.add_later.description') }}</span></span></label>
                    </div>
                </fieldset>
                @error('petForm.choice') <p id="onboarding-pet-choice-error" class="text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                @if ($this->petRelationship['managed_pets'] !== [])
                    <section class="rounded-lg border border-paw-line bg-paw-paper p-4" aria-labelledby="onboarding-managed-pets-heading">
                        <h3 id="onboarding-managed-pets-heading" class="font-semibold">{{ __('onboarding.steps.pet_relationship.managed_summary') }}</h3>
                        <ul role="list" class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($this->petRelationship['managed_pets'] as $managedPet)
                                <li data-onboarding-managed-pet wire:key="onboarding-managed-pet-{{ $managedPet['profile_key'] }}" class="min-w-0 rounded-md border border-paw-line bg-white p-3">
                                    <span class="block break-words font-semibold">{{ $managedPet['name'] }}</span>
                                    <span class="mt-1 block text-sm text-paw-muted">{{ $managedPet['species'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($this->petRelationship['has_more_managed'])
                            <p class="mt-3 text-sm text-paw-muted">{{ __('onboarding.steps.pet_relationship.managed_summary_more') }}</p>
                        @endif
                    </section>
                @elseif ($this->petRelationship['managed'])
                    <p class="rounded-lg border border-paw-line bg-paw-paper p-4 text-sm leading-6">{{ __('onboarding.steps.pet_relationship.managed_evidence_private') }}</p>
                @else
                    <p class="rounded-lg border border-paw-line bg-paw-paper p-4 text-sm leading-6">{{ __('onboarding.steps.pet_relationship.managed_empty') }}</p>
                @endif
                @if ($this->petRelationship['access_requested'])
                    <p role="status" class="rounded-lg border border-paw-leaf bg-paw-mint p-4 text-sm leading-6">{{ __('onboarding.steps.pet_relationship.access_pending') }}</p>
                @endif
                @if ($this->petRelationship['has_current_invitation'])
                    <p class="rounded-lg border border-paw-line bg-paw-paper p-4 text-sm leading-6">{{ __('onboarding.steps.pet_relationship.invitation_pending') }}</p>
                @endif
                @if ($this->petRelationship['has_inactive_relationship'])
                    <p class="rounded-lg border border-paw-line bg-paw-paper p-4 text-sm leading-6">{{ __('onboarding.steps.pet_relationship.inactive_relationship') }}</p>
                @endif
                <p wire:dirty role="status" aria-live="polite" class="text-sm font-medium text-paw-muted">{{ __('onboarding.states.unsaved') }}</p>
                <div class="flex flex-col items-stretch sm:items-start">
                    <button type="submit" wire:loading.attr="disabled" wire:loading.attr="aria-busy" wire:target="savePetRelationship" class="action action--primary action--regular min-h-11 w-full whitespace-normal sm:w-auto">
                        <span wire:loading.remove wire:target="savePetRelationship">{{ __('onboarding.steps.pet_relationship.continue') }}</span>
                        <span wire:loading wire:target="savePetRelationship" role="status" aria-live="polite">{{ __('onboarding.states.checking') }}</span>
                    </button>
                </div>
            </form>
        </section>
    @elseif ($expectedStep === 'privacy-discovery')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-lg font-semibold">{{ __('onboarding.steps.privacy_discovery.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.body') }}</p>
            @if ($this->needsPetEvidenceRecovery)
                <div class="mt-5 rounded-lg border border-paw-coral bg-paw-coral/10 p-4" role="alert">
                    <p>{{ __('onboarding.validation.pet_evidence') }}</p>
                    <button type="button" wire:click="deferPetRelationship" wire:loading.attr="disabled" wire:loading.attr="aria-busy" wire:target="deferPetRelationship" class="action action--regular mt-3 min-h-11 w-full whitespace-normal sm:w-auto"><span wire:loading.remove wire:target="deferPetRelationship">{{ __('onboarding.steps.pet_relationship.add_later.label') }}</span><span wire:loading wire:target="deferPetRelationship" role="status" aria-live="polite">{{ __('onboarding.states.checking') }}</span></button>
                </div>
            @endif
            <form wire:submit="savePrivacy" class="mt-6 grid gap-5" novalidate>
                <fieldset data-onboarding-choice-group>
                    <legend class="font-semibold">{{ __('onboarding.steps.privacy_discovery.options_legend') }}</legend>
                    <div class="mt-3 grid gap-3">
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4 has-checked:border-paw-leaf has-checked:bg-paw-mint forced-colors:has-checked:border-[Highlight]"><input type="checkbox" wire:model="privacyForm.isDiscoverable" class="mt-1 size-5 shrink-0 accent-paw-leaf" aria-labelledby="onboarding-discoverable-label" aria-describedby="onboarding-discoverable-description @error('privacyForm.isDiscoverable') onboarding-discoverable-error @enderror" @error('privacyForm.isDiscoverable') aria-invalid="true" @enderror><span class="min-w-0"><span id="onboarding-discoverable-label" class="block font-semibold">{{ __('onboarding.steps.privacy_discovery.discoverable_label') }}</span><span id="onboarding-discoverable-description" class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.discoverable_description') }}</span></span></label>
                        @error('privacyForm.isDiscoverable') <p id="onboarding-discoverable-error" class="text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4 has-checked:border-paw-leaf has-checked:bg-paw-mint forced-colors:has-checked:border-[Highlight]"><input type="checkbox" wire:model="privacyForm.isRecommendable" class="mt-1 size-5 shrink-0 accent-paw-leaf" aria-labelledby="onboarding-recommendable-label" aria-describedby="onboarding-recommendable-description @error('privacyForm.isRecommendable') onboarding-recommendable-error @enderror" @error('privacyForm.isRecommendable') aria-invalid="true" @enderror><span class="min-w-0"><span id="onboarding-recommendable-label" class="block font-semibold">{{ __('onboarding.steps.privacy_discovery.recommendable_label') }}</span><span id="onboarding-recommendable-description" class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.recommendable_description') }}</span></span></label>
                        @error('privacyForm.isRecommendable') <p id="onboarding-recommendable-error" class="text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4 has-checked:border-paw-leaf has-checked:bg-paw-mint forced-colors:has-checked:border-[Highlight]"><input type="checkbox" wire:model="privacyForm.allowMessageRequests" class="mt-1 size-5 shrink-0 accent-paw-leaf" aria-labelledby="onboarding-messages-label" aria-describedby="onboarding-messages-description @error('privacyForm.allowMessageRequests') onboarding-messages-error @enderror" @error('privacyForm.allowMessageRequests') aria-invalid="true" @enderror><span class="min-w-0"><span id="onboarding-messages-label" class="block font-semibold">{{ __('onboarding.steps.privacy_discovery.messages_label') }}</span><span id="onboarding-messages-description" class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.messages_description') }}</span></span></label>
                        @error('privacyForm.allowMessageRequests') <p id="onboarding-messages-error" class="text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                    </div>
                </fieldset>
                <div class="rounded-lg border border-paw-line bg-paw-paper p-4 text-sm leading-6">{{ __('onboarding.steps.privacy_discovery.protected_data') }}</div>
                <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-lg border border-paw-line p-4"><input type="checkbox" wire:model="privacyAcknowledged" required aria-required="true" @error('privacyAcknowledged') aria-invalid="true" aria-describedby="onboarding-privacy-acknowledgement-error" @enderror class="mt-1 size-5 shrink-0 accent-paw-leaf"><span class="min-w-0 break-words">{{ __('onboarding.steps.privacy_discovery.acknowledgement') }}</span></label>
                @error('privacyAcknowledged') <p id="onboarding-privacy-acknowledgement-error" class="text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                <p wire:dirty role="status" aria-live="polite" class="text-sm font-medium text-paw-muted">{{ __('onboarding.states.unsaved') }}</p>
                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" wire:click="editPetRelationship" wire:loading.attr="disabled" wire:loading.attr="aria-busy" wire:target="editPetRelationship" class="action action--paper action--regular min-h-11 w-full whitespace-normal sm:w-auto"><span wire:loading.remove wire:target="editPetRelationship">{{ __('onboarding.steps.pet_relationship.edit') }}</span><span wire:loading wire:target="editPetRelationship" role="status" aria-live="polite">{{ __('onboarding.states.checking') }}</span></button>
                    <button type="submit" wire:loading.attr="disabled" wire:loading.attr="aria-busy" wire:target="savePrivacy" class="action action--primary action--regular min-h-11 w-full whitespace-normal sm:w-auto"><span wire:loading.remove wire:target="savePrivacy">{{ __('onboarding.steps.privacy_discovery.save') }}</span><span wire:loading wire:target="savePrivacy" role="status" aria-live="polite">{{ __('onboarding.states.saving') }}</span></button>
                </div>
            </form>
        </section>
    @endif
</div>
