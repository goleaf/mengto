<section class="grid gap-5" aria-labelledby="mentor-profile-heading">
    <header>
        <h2 id="mentor-profile-heading">{{ __('forum_mentorship.profile.heading') }}</h2>
        <p>{{ __('forum_mentorship.profile.description') }}</p>
    </header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    @unless ($this->canActivate)
        <p class="border-s-4 border-status-warning py-3 ps-4" role="note">
            {{ __('forum_mentorship.profile.trust_required') }}
        </p>
    @endunless

    <form wire:submit="saveProfile" class="forum-form">
        @if ($errors->any())
            <x-forum-error-summary
                :messages="$errors->getMessages()"
                :heading="__('forum_mentorship.validation.summary')"
            />
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.profile_state') }}</span>
                <select wire:model="profileForm.state">
                    @foreach ($this->profileStateOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.capacity') }}</span>
                <input type="number" wire:model="profileForm.capacity" min="1" max="10">
            </label>
            <label class="forum-form__field sm:col-span-2">
                <span>{{ __('forum_mentorship.fields.headline') }}</span>
                <input type="text" wire:model="profileForm.headline" minlength="5" maxlength="160">
                @error('profileForm.headline') <small role="alert">{{ $message }}</small> @enderror
            </label>
            <label class="forum-form__field sm:col-span-2">
                <span>{{ __('forum_mentorship.fields.summary') }}</span>
                <textarea wire:model="profileForm.summary" rows="5" minlength="20" maxlength="3000"></textarea>
                @error('profileForm.summary') <small role="alert">{{ $message }}</small> @enderror
            </label>
            <fieldset class="forum-form__field">
                <legend>{{ __('forum_mentorship.fields.languages') }}</legend>
                <div class="grid gap-2">
                    @foreach ($this->localeOptions as $locale => $label)
                        <label class="forum-form__check">
                            <input type="checkbox" wire:model="profileForm.languages" value="{{ $locale }}">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.location_scope') }}</span>
                <input type="text" wire:model="profileForm.locationScope" maxlength="160">
            </label>
            <label class="forum-form__field">
                <span>{{ __('forum_mentorship.fields.timezone') }}</span>
                <input type="text" wire:model="profileForm.timezone" maxlength="80">
            </label>
            <label class="forum-form__field sm:col-span-2">
                <span>{{ __('forum_mentorship.fields.availability') }}</span>
                <textarea wire:model="profileForm.availability" rows="2" maxlength="500"></textarea>
            </label>
        </div>

        <label class="forum-form__check">
            <input type="checkbox" wire:model="profileForm.isPublic">
            <span>{{ __('forum_mentorship.fields.public_profile') }}</span>
        </label>
        <label class="forum-form__check">
            <input type="checkbox" wire:model="profileForm.safetyAcknowledged">
            <span>{{ __('forum_mentorship.fields.safety_acknowledgement') }}</span>
        </label>

        <button
            type="submit"
            class="forum-button forum-button--primary min-h-11"
            wire:loading.attr="disabled"
            wire:target="saveProfile"
        >
            <x-lucide-save aria-hidden="true" />
            <span wire:loading.remove wire:target="saveProfile">{{ __('forum_mentorship.profile.save') }}</span>
            <span wire:loading wire:target="saveProfile">{{ __('forum_mentorship.profile.saving') }}</span>
        </button>
    </form>

    <section class="grid gap-4" aria-labelledby="mentor-scope-heading">
        <div>
            <h3 id="mentor-scope-heading">{{ __('forum_mentorship.profile.scope_heading') }}</h3>
            <p>{{ __('forum_mentorship.profile.scope_description') }}</p>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            @forelse ($this->scopes as $scope)
                <article class="forum-form" wire:key="mentor-scope-{{ $scope['id'] }}">
                    <div class="flex flex-wrap items-center gap-2">
                        <strong>{{ $scope['type'] }}</strong>
                        <x-status-badge
                            :label="$scope['active'] ? __('forum_mentorship.profile.scope_heading') : __('forum_mentorship.profile_states.paused')"
                            :icon="$scope['active'] ? 'circle-check' : 'pause'"
                        />
                    </div>
                    <p>{{ $scope['experience'] }}</p>
                    <p class="text-sm text-paw-muted">
                        {{ $scope['category'] ?? $scope['taxon'] ?? __('forum_mentorship.profile.general_scope') }}
                    </p>
                    <p class="text-sm">
                        {{ $scope['professional']
                            ? __('forum_mentorship.profile.professional_required')
                            : __('forum_mentorship.profile.peer_scope') }}
                    </p>
                </article>
            @empty
                <p>{{ __('forum_mentorship.profile.no_scopes') }}</p>
            @endforelse
        </div>

        <form wire:submit="saveScope" class="forum-form">
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="forum-form__field">
                    <span>{{ __('forum_mentorship.fields.mentorship_type') }}</span>
                    <select wire:model="scopeForm.type">
                        @foreach ($this->typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="forum-form__field">
                    <span>{{ __('forum_mentorship.fields.category') }}</span>
                    <select wire:model="scopeForm.forumCategoryId">
                        <option value="">{{ __('forum_mentorship.profile.general_scope') }}</option>
                        @foreach ($this->categoryOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="forum-form__field sm:col-span-2">
                    <span>{{ __('forum_mentorship.fields.experience') }}</span>
                    <textarea wire:model="scopeForm.experienceSummary" rows="4" minlength="20" maxlength="2000"></textarea>
                    @error('scopeForm.experienceSummary') <small role="alert">{{ $message }}</small> @enderror
                </label>
            </div>

            <livewire:forum.animal-taxonomy-selector
                wire:model.live="scopeForm.taxonIds"
                input-name="taxon_id"
                :selection-limit="1"
            />

            <label class="forum-form__check">
                <input type="checkbox" wire:model="scopeForm.requiresVerifiedExpertise">
                <span>{{ __('forum_mentorship.fields.professional_scope') }}</span>
            </label>
            <label class="forum-form__check">
                <input type="checkbox" wire:model="scopeForm.isActive">
                <span>{{ __('forum_mentorship.fields.scope_active') }}</span>
            </label>
            @error('scope') <small role="alert">{{ $message }}</small> @enderror

            <button
                type="submit"
                class="forum-button forum-button--primary min-h-11"
                wire:loading.attr="disabled"
                wire:target="saveScope"
            >
                <x-lucide-plus aria-hidden="true" />
                <span wire:loading.remove wire:target="saveScope">{{ __('forum_mentorship.profile.save_scope') }}</span>
                <span wire:loading wire:target="saveScope">{{ __('forum_mentorship.profile.saving_scope') }}</span>
            </button>
        </form>
    </section>
</section>
