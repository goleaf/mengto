<section class="border-y border-paw-line py-6" aria-labelledby="adoption-workflow-heading">
    @if ($caseId === null)
        <h2 id="adoption-workflow-heading" class="text-2xl font-bold">{{ __('adoption.title') }}</h2>
        <p class="mt-2 text-paw-muted">{{ __('adoption.unavailable') }}</p>
    @else
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase text-paw-muted">{{ $this->caseData['number'] }}</p>
                <h2 id="adoption-workflow-heading" class="text-2xl font-bold">
                    {{ __('adoption.heading', ['animal' => $this->caseData['animal_name']]) }}
                </h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-paw-muted">{{ __('adoption.privacy_notice') }}</p>
            </div>
            <x-status-badge :label="$this->caseData['status_label']" icon="heart-handshake" tone="success" />
        </div>

        @if ($feedback !== '')
            <p class="mt-4 rounded-md border border-status-success px-4 py-3" role="status" aria-live="polite">
                {{ $feedback }}
            </p>
        @endif

        <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <dl class="grid content-start gap-2 text-sm">
                <div><dt class="text-paw-muted">{{ __('adoption.fields.provider_type') }}</dt><dd class="font-semibold">{{ $this->caseData['provider_type'] }}</dd></div>
                <div>
                    <dt class="text-paw-muted">{{ __('adoption.fields.identity_status') }}</dt>
                    <dd class="font-semibold">{{ $this->caseData['provider_identity_label'] }}</dd>
                    <dd class="mt-1 text-xs text-paw-muted">{{ __('adoption.identity_help.'.$this->caseData['provider_identity_status']) }}</dd>
                </div>
                <div><dt class="text-paw-muted">{{ __('adoption.fields.location') }}</dt><dd class="font-semibold">{{ $this->caseData['location'] }}</dd></div>
                <div><dt class="text-paw-muted">{{ __('adoption.fields.fee') }}</dt><dd class="font-semibold">{{ $this->caseData['fee'] }}</dd></div>
            </dl>
            <dl class="grid content-start gap-2 text-sm">
                <div><dt class="text-paw-muted">{{ __('adoption.fields.age') }}</dt><dd class="font-semibold">{{ $this->caseData['age'] ?? __('adoption.not_provided') }}</dd></div>
                <div><dt class="text-paw-muted">{{ __('adoption.fields.sex') }}</dt><dd class="font-semibold">{{ $this->caseData['sex'] ?? __('adoption.not_provided') }}</dd></div>
                <div><dt class="text-paw-muted">{{ __('adoption.fields.sterilization') }}</dt><dd class="font-semibold">{{ $this->caseData['sterilization'] }}</dd></div>
                <div><dt class="text-paw-muted">{{ __('adoption.fields.vaccination') }}</dt><dd class="font-semibold">{{ $this->caseData['vaccination'] }}</dd></div>
            </dl>
            <div class="text-sm">
                <h3 class="font-bold">{{ __('adoption.fields.behavior') }}</h3>
                <p class="mt-1 leading-6 text-paw-muted">{{ $this->caseData['behavior'] ?? __('adoption.not_provided') }}</p>
                <h3 class="mt-3 font-bold">{{ __('adoption.fields.compatibility') }}</h3>
                <p class="mt-1 leading-6 text-paw-muted">{{ $this->caseData['compatibility'] ?? __('adoption.not_provided') }}</p>
            </div>
            <div class="text-sm">
                <h3 class="font-bold">{{ __('adoption.fields.special_requirements') }}</h3>
                <p class="mt-1 leading-6 text-paw-muted">{{ $this->caseData['requirements'] ?? __('adoption.not_provided') }}</p>
                <h3 class="mt-3 font-bold">{{ __('adoption.fields.fee_explanation') }}</h3>
                <p class="mt-1 leading-6 text-paw-muted">{{ $this->caseData['fee_explanation'] ?? __('adoption.not_provided') }}</p>
            </div>
        </div>

        <div wire:offline class="mt-4 border-s-4 border-status-warning ps-3 text-sm" role="status">
            {{ __('adoption.offline') }}
        </div>

        @if ($this->canManage)
            <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,1fr)]">
                <section aria-labelledby="adoption-applications-heading">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 id="adoption-applications-heading" class="text-xl font-bold">{{ __('adoption.applications.heading') }}</h3>
                        @if ($this->caseData['status'] !== 'closed')
                            <button
                                type="button"
                                wire:click="closeCase"
                                wire:confirm="{{ __('adoption.actions.close_case_confirm') }}"
                                wire:loading.attr="disabled"
                                wire:target="closeCase"
                                class="forum-button"
                            >
                                <x-lucide-lock-keyhole aria-hidden="true" />
                                {{ __('adoption.actions.close_case') }}
                            </button>
                        @endif
                    </div>
                    <div class="mt-3 grid gap-3">
                        @forelse ($this->applications as $application)
                            <button
                                type="button"
                                wire:key="adoption-application-{{ $application['id'] }}"
                                wire:click="selectApplication({{ $application['id'] }})"
                                class="min-h-11 border-b border-paw-line py-3 text-start"
                            >
                                <span class="flex flex-wrap items-center justify-between gap-2">
                                    <strong>{{ $application['applicant'] }}</strong>
                                    <x-status-badge :label="$application['status_label']" icon="clipboard-check" />
                                </span>
                                <span class="mt-1 block text-sm text-paw-muted">{{ $application['placement'] }} · {{ $application['submitted'] }}</span>
                            </button>
                        @empty
                            <p class="py-4 text-paw-muted">{{ __('adoption.applications.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="adoption-review-heading" class="border-s border-paw-line ps-5">
                    <h3 id="adoption-review-heading" class="text-xl font-bold">{{ __('adoption.applications.review') }}</h3>
                    @if ($this->selectedApplication !== null)
                        <dl class="mt-4 grid gap-3 text-sm">
                            @forelse ($this->selectedApplication as $field => $value)
                                <div>
                                    <dt class="font-semibold">{{ __("adoption.fields.{$field}") }}</dt>
                                    <dd class="mt-1 whitespace-pre-line text-paw-muted">{{ $value !== '' ? $value : __('adoption.not_provided') }}</dd>
                                </div>
                            @empty
                                <div><dt>{{ __('adoption.applications.private_profile') }}</dt><dd>{{ __('adoption.not_provided') }}</dd></div>
                            @endforelse
                        </dl>

                        @if ($this->transitionOptions !== [])
                            <form wire:submit="updateApplicationStatus" class="mt-5 grid gap-3">
                                <label class="grid gap-1 text-sm font-semibold">
                                    {{ __('adoption.fields.next_status') }}
                                    <select wire:model="targetStatus" class="min-h-11 rounded-md border border-paw-line bg-white px-3 py-2">
                                        @forelse ($this->transitionOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @empty
                                            <option disabled>{{ __('adoption.applications.no_transitions') }}</option>
                                        @endforelse
                                    </select>
                                </label>
                                @error('targetStatus') <p role="alert" class="text-sm text-status-danger">{{ $message }}</p> @enderror
                                @error('application') <p role="alert" class="text-sm text-status-danger">{{ $message }}</p> @enderror
                                <button type="submit" wire:loading.attr="disabled" wire:target="updateApplicationStatus" class="forum-button forum-button--primary">
                                    <x-lucide-git-branch aria-hidden="true" />
                                    <span wire:loading.remove wire:target="updateApplicationStatus">{{ __('adoption.actions.update_status') }}</span>
                                    <span wire:loading wire:target="updateApplicationStatus">{{ __('adoption.actions.working') }}</span>
                                </button>
                            </form>
                        @else
                            <p class="mt-4 text-sm text-paw-muted">{{ __('adoption.applications.no_transitions') }}</p>
                        @endif
                    @else
                        <p class="mt-4 text-paw-muted">{{ __('adoption.applications.select') }}</p>
                    @endif
                </section>
            </div>
        @elseif ($this->applications !== [])
            <section class="mt-7" aria-labelledby="my-adoption-application-heading">
                <h3 id="my-adoption-application-heading" class="text-xl font-bold">{{ __('adoption.applications.mine') }}</h3>
                @forelse ($this->applications as $application)
                    <div wire:key="my-adoption-application-{{ $application['id'] }}" class="mt-3 flex flex-wrap items-center justify-between gap-3 border-b border-paw-line pb-3">
                        <div>
                            <x-status-badge :label="$application['status_label']" icon="clipboard-check" />
                            <p class="mt-2 text-sm text-paw-muted">{{ $application['submitted'] }}</p>
                        </div>
                        @if (! in_array($application['status'], ['withdrawn', 'declined', 'closed', 'adopted'], true))
                            <button type="button" wire:click="selectApplication({{ $application['id'] }})" class="forum-button">
                                <x-lucide-file-search aria-hidden="true" />
                                {{ __('adoption.actions.review_application') }}
                            </button>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-paw-muted">{{ __('adoption.applications.empty') }}</p>
                @endforelse

                @if ($this->selectedApplication !== null && isset($this->transitionOptions['withdrawn']))
                    <form wire:submit="updateApplicationStatus" class="mt-4">
                        <input type="hidden" wire:model="targetStatus" value="withdrawn">
                        <button type="submit" wire:loading.attr="disabled" wire:target="updateApplicationStatus" class="forum-button">
                            <x-lucide-x-circle aria-hidden="true" />
                            {{ __('adoption.actions.withdraw') }}
                        </button>
                    </form>
                @endif
            </section>
        @elseif ($this->canApply)
            <form wire:submit="submit" class="mt-7 grid gap-4" aria-labelledby="adoption-application-heading">
                <div>
                    <h3 id="adoption-application-heading" class="text-xl font-bold">{{ __('adoption.application_form.heading') }}</h3>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('adoption.application_form.help') }}</p>
                </div>

                @if ($errors->any())
                    <p role="alert" class="form-errors">{{ __('adoption.validation.summary') }}</p>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.placement_type') }}
                        <select wire:model="form.placementType" class="min-h-11 rounded-md border border-paw-line bg-white px-3 py-2">
                            @forelse ($this->placementOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('adoption.not_provided') }}</option>
                            @endforelse
                        </select>
                        @error('form.placementType') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.message') }}
                        <textarea wire:model="form.message" rows="3" maxlength="1500" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.message') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.experience') }}
                        <textarea wire:model="form.experience" rows="3" maxlength="1500" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.experience') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.home_context') }}
                        <textarea wire:model="form.homeContext" rows="3" maxlength="1500" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.homeContext') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.household') }}
                        <textarea wire:model="form.household" rows="3" maxlength="1000" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.household') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.other_animals') }}
                        <textarea wire:model="form.otherAnimals" rows="3" maxlength="1000" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.otherAnimals') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.care_plan') }}
                        <textarea wire:model="form.carePlan" rows="3" maxlength="1500" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.carePlan') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('adoption.fields.placement_reason') }}
                        <textarea wire:model="form.placementReason" rows="3" maxlength="1500" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.placementReason') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold md:col-span-2">
                        {{ __('adoption.fields.transport_plan') }}
                        <textarea wire:model="form.transportPlan" rows="3" maxlength="1000" class="rounded-md border border-paw-line bg-white px-3 py-2"></textarea>
                        @error('form.transportPlan') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror
                    </label>
                </div>

                <label class="flex gap-3 text-sm leading-6">
                    <input type="checkbox" wire:model="form.termsAccepted" class="mt-1 size-4 rounded border-paw-line text-paw-leaf">
                    <span>{{ __('adoption.consent.terms') }}</span>
                </label>
                @error('form.termsAccepted') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror

                <label class="flex gap-3 text-sm leading-6">
                    <input type="checkbox" wire:model="form.privacyAccepted" class="mt-1 size-4 rounded border-paw-line text-paw-leaf">
                    <span>{{ __('adoption.consent.privacy') }}</span>
                </label>
                @error('form.privacyAccepted') <small role="alert" class="text-status-danger">{{ $message }}</small> @enderror

                <label class="flex gap-3 text-sm leading-6">
                    <input type="checkbox" wire:model="form.referenceContactConsent" class="mt-1 size-4 rounded border-paw-line text-paw-leaf">
                    <span>{{ __('adoption.consent.references') }}</span>
                </label>

                <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="forum-button forum-button--primary w-fit">
                    <x-lucide-send aria-hidden="true" />
                    <span wire:loading.remove wire:target="submit">{{ __('adoption.actions.submit') }}</span>
                    <span wire:loading wire:target="submit">{{ __('adoption.actions.submitting') }}</span>
                </button>
            </form>
        @elseif (! $isAuthenticated)
            <div class="mt-6">
                <a href="{{ route('login') }}" class="forum-button forum-button--primary">
                    <x-lucide-log-in aria-hidden="true" />
                    {{ __('adoption.actions.sign_in') }}
                </a>
            </div>
        @else
            <p class="mt-6 text-paw-muted">{{ __('adoption.applications.unavailable') }}</p>
        @endif
    @endif
</section>
