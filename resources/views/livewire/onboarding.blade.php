<div
    data-section="onboarding"
    x-data
    x-on:onboarding-validation-failed.window="$nextTick(() => $refs.errorSummary?.focus())"
    x-on:onboarding-step-changed.window="$nextTick(() => $refs.stepHeading?.focus())"
>
    <p class="text-sm font-bold uppercase tracking-wide text-paw-leaf">{{ __('onboarding.page.eyebrow') }}</p>
    <h1 class="mt-2 text-3xl font-bold leading-tight text-paw-ink sm:text-4xl">{{ __('onboarding.page.title') }}</h1>
    <p class="mt-3 max-w-2xl text-base leading-7 text-paw-muted">{{ __('onboarding.page.description') }}</p>

    <nav class="mt-6" aria-label="{{ __('onboarding.progress.label') }}">
        <ol class="grid gap-2 sm:grid-cols-4">
            <li @if ($expectedStep === 'introduction') aria-current="step" @endif class="rounded-lg border px-3 py-3 text-sm @if ($expectedStep === 'introduction') border-paw-leaf bg-paw-leaf/10 font-semibold @else border-paw-line @endif">
                <span class="block text-xs text-paw-muted">{{ __('onboarding.progress.step', ['current' => 1, 'total' => 4]) }}</span>
                {{ __('onboarding.steps.introduction.label') }}
            </li>
            <li @if ($expectedStep === 'preferences') aria-current="step" @endif class="rounded-lg border px-3 py-3 text-sm @if ($expectedStep === 'preferences') border-paw-leaf bg-paw-leaf/10 font-semibold @else border-paw-line @endif">
                <span class="block text-xs text-paw-muted">{{ __('onboarding.progress.step', ['current' => 2, 'total' => 4]) }}</span>
                {{ __('onboarding.steps.preferences.label') }}
            </li>
            <li @if ($expectedStep === 'pet-relationship') aria-current="step" @endif class="rounded-lg border px-3 py-3 text-sm @if ($expectedStep === 'pet-relationship') border-paw-leaf bg-paw-leaf/10 font-semibold @else border-paw-line @endif">
                <span class="block text-xs text-paw-muted">{{ __('onboarding.progress.step', ['current' => 3, 'total' => 4]) }}</span>
                {{ __('onboarding.steps.pet_relationship.label') }}
            </li>
            <li @if ($expectedStep === 'privacy-discovery') aria-current="step" @endif class="rounded-lg border px-3 py-3 text-sm @if ($expectedStep === 'privacy-discovery') border-paw-leaf bg-paw-leaf/10 font-semibold @else border-paw-line @endif">
                <span class="block text-xs text-paw-muted">{{ __('onboarding.progress.step', ['current' => 4, 'total' => 4]) }}</span>
                {{ __('onboarding.steps.privacy_discovery.label') }}
            </li>
        </ol>
        <progress class="mt-3 h-2 w-full accent-paw-leaf" value="{{ $this->progressPosition }}" max="4">
            {{ __('onboarding.progress.step', ['current' => $this->progressPosition, 'total' => 4]) }}
        </progress>
    </nav>

    @if ($errors->any())
        <div x-ref="errorSummary" tabindex="-1" role="alert" class="mt-6 rounded-lg border-2 border-paw-coral bg-paw-coral/10 px-4 py-3 font-semibold text-paw-ink">
            {{ __('onboarding.validation.summary') }}
        </div>
    @endif

    <p wire:offline role="status" class="mt-5 rounded-lg border border-paw-coral bg-paw-coral/10 px-4 py-3 text-sm font-semibold">
        {{ __('onboarding.states.offline') }}
    </p>

    @if ($expectedStep === 'introduction')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-2xl font-bold">{{ __('onboarding.steps.introduction.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.introduction.body') }}</p>

            <form wire:submit="acknowledgeIntroduction" class="mt-6 grid gap-5">
                <label class="flex min-h-11 items-start gap-3 rounded-lg border border-paw-line p-4">
                    <input type="checkbox" wire:model="introductionAcknowledged" class="mt-1 size-5 accent-paw-leaf" @error('introductionAcknowledged') aria-invalid="true" aria-describedby="onboarding-introduction-error" @enderror>
                    <span>{{ __('onboarding.steps.introduction.acknowledgement') }}</span>
                </label>
                @error('introductionAcknowledged') <p id="onboarding-introduction-error" class="text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                <button type="submit" wire:loading.attr="disabled" wire:target="acknowledgeIntroduction" class="inline-flex min-h-11 items-center justify-center rounded-md bg-paw-leaf px-5 py-3 font-semibold text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="acknowledgeIntroduction">{{ __('onboarding.steps.introduction.continue') }}</span>
                    <span wire:loading wire:target="acknowledgeIntroduction">{{ __('onboarding.states.saving') }}</span>
                </button>
            </form>
        </section>
    @elseif ($expectedStep === 'preferences')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-2xl font-bold">{{ __('onboarding.steps.preferences.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.preferences.body') }}</p>

            <form wire:submit="savePreferences" class="mt-6 grid gap-5">
                <div>
                    <label for="onboarding-locale" class="font-semibold">{{ __('auth.fields.locale') }}</label>
                    <select id="onboarding-locale" wire:model="preferencesForm.locale" class="mt-2 min-h-11 w-full rounded-md border-2 border-paw-muted bg-white px-3 py-2" aria-describedby="onboarding-locale-help @error('preferencesForm.locale') onboarding-locale-error @enderror" @error('preferencesForm.locale') aria-invalid="true" @enderror>
                        @forelse ($this->localeOptions as $value => $label)
                            <option wire:key="onboarding-locale-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="" disabled>{{ __('auth.settings.no_locales') }}</option>
                        @endforelse
                    </select>
                    <p id="onboarding-locale-help" class="mt-2 text-sm text-paw-muted">{{ __('auth.settings.locale_help') }}</p>
                    @error('preferencesForm.locale') <p id="onboarding-locale-error" class="mt-2 text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="onboarding-timezone" class="font-semibold">{{ __('auth.fields.timezone') }}</label>
                    <select id="onboarding-timezone" wire:model="preferencesForm.timezone" class="mt-2 min-h-11 w-full rounded-md border-2 border-paw-muted bg-white px-3 py-2" aria-describedby="onboarding-timezone-help @error('preferencesForm.timezone') onboarding-timezone-error @enderror" @error('preferencesForm.timezone') aria-invalid="true" @enderror>
                        @forelse ($this->timezoneOptions as $value => $label)
                            <option wire:key="onboarding-timezone-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="" disabled>{{ __('auth.settings.no_timezones') }}</option>
                        @endforelse
                    </select>
                    <p id="onboarding-timezone-help" class="mt-2 text-sm text-paw-muted">{{ __('auth.settings.timezone_help') }}</p>
                    @error('preferencesForm.timezone') <p id="onboarding-timezone-error" class="mt-2 text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror
                </div>
                <p wire:dirty role="status" class="text-sm font-medium text-paw-muted">{{ __('onboarding.states.unsaved') }}</p>
                <button type="submit" wire:loading.attr="disabled" wire:target="savePreferences" class="inline-flex min-h-11 items-center justify-center rounded-md bg-paw-leaf px-5 py-3 font-semibold text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="savePreferences">{{ __('onboarding.steps.preferences.save') }}</span>
                    <span wire:loading wire:target="savePreferences">{{ __('onboarding.states.saving') }}</span>
                </button>
            </form>
        </section>
    @elseif ($expectedStep === 'pet-relationship')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-2xl font-bold">{{ __('onboarding.steps.pet_relationship.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.pet_relationship.body') }}</p>
            @error('petChoice') <p class="mt-4 text-sm font-medium text-paw-coral">{{ $message }}</p> @enderror

            <div class="mt-6 grid gap-3">
                <a href="{{ route('pets.manage.create', ['onboarding' => 1]) }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-paw-leaf px-5 py-3 font-semibold text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2">
                    {{ __('onboarding.steps.pet_relationship.create_or_find') }}
                </a>
                @if ($this->hasManagedPet)
                    <button type="button" wire:click="confirmPetRelationship('managed-pet')" wire:loading.attr="disabled" wire:target="confirmPetRelationship" class="min-h-11 rounded-md border-2 border-paw-leaf px-5 py-3 font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2">
                        {{ __('onboarding.steps.pet_relationship.managed_pet') }}
                    </button>
                @endif
                @if ($this->hasPendingAccessRequest)
                    <button type="button" wire:click="confirmPetRelationship('access-requested')" wire:loading.attr="disabled" wire:target="confirmPetRelationship" class="min-h-11 rounded-md border-2 border-paw-leaf px-5 py-3 font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2">
                        {{ __('onboarding.steps.pet_relationship.access_requested') }}
                    </button>
                @endif
                <button type="button" wire:click="confirmPetRelationship('not-now')" wire:loading.attr="disabled" wire:target="confirmPetRelationship" class="min-h-11 rounded-md border-2 border-paw-muted px-5 py-3 font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2">
                    {{ __('onboarding.steps.pet_relationship.not_now') }}
                </button>
            </div>
        </section>
    @elseif ($expectedStep === 'privacy-discovery')
        <section class="mt-7" aria-labelledby="onboarding-step-heading">
            <h2 id="onboarding-step-heading" x-ref="stepHeading" tabindex="-1" class="text-2xl font-bold">{{ __('onboarding.steps.privacy_discovery.title') }}</h2>
            <p class="mt-3 leading-7 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.body') }}</p>

            <form wire:submit="savePrivacy" class="mt-6 grid gap-4">
                <label class="flex min-h-11 items-start gap-3 rounded-lg border border-paw-line p-4">
                    <input type="checkbox" wire:model="privacyForm.isDiscoverable" class="mt-1 size-5 accent-paw-leaf">
                    <span><strong class="block">{{ __('onboarding.steps.privacy_discovery.discoverable_label') }}</strong><small class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.discoverable_description') }}</small></span>
                </label>
                <label class="flex min-h-11 items-start gap-3 rounded-lg border border-paw-line p-4">
                    <input type="checkbox" wire:model="privacyForm.isRecommendable" class="mt-1 size-5 accent-paw-leaf">
                    <span><strong class="block">{{ __('onboarding.steps.privacy_discovery.recommendable_label') }}</strong><small class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.recommendable_description') }}</small></span>
                </label>
                <label class="flex min-h-11 items-start gap-3 rounded-lg border border-paw-line p-4">
                    <input type="checkbox" wire:model="privacyForm.allowMessageRequests" class="mt-1 size-5 accent-paw-leaf">
                    <span><strong class="block">{{ __('onboarding.steps.privacy_discovery.messages_label') }}</strong><small class="mt-1 block text-sm leading-6 text-paw-muted">{{ __('onboarding.steps.privacy_discovery.messages_description') }}</small></span>
                </label>
                <p wire:dirty role="status" class="text-sm font-medium text-paw-muted">{{ __('onboarding.states.unsaved') }}</p>
                <button type="submit" wire:loading.attr="disabled" wire:target="savePrivacy" class="inline-flex min-h-11 items-center justify-center rounded-md bg-paw-leaf px-5 py-3 font-semibold text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-paw-leaf focus-visible:ring-offset-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="savePrivacy">{{ __('onboarding.steps.privacy_discovery.save') }}</span>
                    <span wire:loading wire:target="savePrivacy">{{ __('onboarding.states.saving') }}</span>
                </button>
            </form>
        </section>
    @endif
</div>
