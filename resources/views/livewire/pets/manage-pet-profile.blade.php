<x-page-stack data-section="pet-profile-management">
    <x-page-header
        eyebrow="{{ __('pet_profiles.manage.eyebrow') }}"
        title="{{ __('pet_profiles.manage.title', ['name' => $profile->name]) }}"
        description="{{ __('pet_profiles.manage.description') }}"
        heading-id="manage-pet-profile-heading"
        action-label="{{ __('pet_profiles.actions.view_profile') }}"
        action-icon="external-link"
        :action-href="route('pets.profile', ['petProfile' => $profile->profile_key])"
    />

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <section class="grid gap-4 border-b border-paw-line pb-6 sm:grid-cols-[minmax(0,1fr)_9rem] sm:items-center" aria-labelledby="pet-stable-link-heading">
        <div>
            <h2 id="pet-stable-link-heading">{{ __('pet_profiles.manage.stable_link') }}</h2>
            <p class="mt-2 break-all text-sm text-paw-muted">{{ route('pets.profile', ['petProfile' => $profile->profile_key]) }}</p>
        </div>
        <img
            src="{{ $qrCode }}"
            alt="{{ __('pet_profiles.manage.qr_alt', ['name' => $profile->name]) }}"
            class="size-36 bg-white p-2"
        >
    </section>

    <nav aria-label="{{ __('pet_profiles.manage.sections') }}" class="grid grid-cols-2 gap-2 border-b border-paw-line pb-4 sm:flex sm:flex-wrap">
        <a class="forum-button min-h-11 min-w-0 w-full whitespace-normal text-center sm:w-auto" href="#pet-basics"><x-lucide-paw-print class="shrink-0" aria-hidden="true" />{{ __('pet_profiles.manage.basics') }}</a>
        <a class="forum-button min-h-11 min-w-0 w-full whitespace-normal text-center sm:w-auto" href="#pet-privacy"><x-lucide-shield class="shrink-0" aria-hidden="true" />{{ __('pet_profiles.manage.privacy') }}</a>
        <a class="forum-button min-h-11 min-w-0 w-full whitespace-normal text-center sm:w-auto" href="#pet-managers"><x-lucide-users class="shrink-0" aria-hidden="true" />{{ __('pet_profiles.manage.managers') }}</a>
        <a class="forum-button min-h-11 min-w-0 w-full whitespace-normal text-center sm:w-auto" href="#pet-lifecycle"><x-lucide-history class="shrink-0" aria-hidden="true" />{{ __('pet_profiles.manage.lifecycle') }}</a>
    </nav>

    <section id="pet-basics" aria-labelledby="pet-basics-heading" class="scroll-mt-4">
        <h2 id="pet-basics-heading">{{ __('pet_profiles.manage.basics') }}</h2>
        <form wire:submit="saveBasics" class="forum-form mt-4">
            @if ($errors->any())
                <x-forum-error-summary
                    :messages="$errors->getMessages()"
                    :heading="__('pet_profiles.validation.summary')"
                />
            @endif
            <div class="grid min-w-0 gap-4 md:grid-cols-2">
                <label class="forum-form__field" for="managed-pet-name">
                    <span>{{ __('pet_profiles.fields.name') }}</span>
                    <input id="managed-pet-name" type="text" wire:model="form.name" maxlength="120" required>
                    @error('form.name') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field" for="managed-pet-species">
                    <span>{{ __('pet_profiles.fields.species') }}</span>
                    <select id="managed-pet-species" wire:model="form.species">
                        @forelse ($this->speciesOptions as $value => $label)
                            <option wire:key="managed-pet-species-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.species.unknown') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="managed-pet-breed">
                    <span>{{ __('pet_profiles.fields.breed') }}</span>
                    <input id="managed-pet-breed" type="text" wire:model="form.breed" maxlength="120">
                </label>
                <label class="forum-form__field" for="managed-pet-birth-date">
                    <span>{{ __('pet_profiles.fields.birth_date') }}</span>
                    <input id="managed-pet-birth-date" type="date" wire:model="form.birthDate" max="{{ now()->toDateString() }}">
                </label>
                <label class="forum-form__field" for="managed-pet-birth-precision">
                    <span>{{ __('pet_profiles.fields.birth_precision') }}</span>
                    <select id="managed-pet-birth-precision" wire:model="form.birthDatePrecision">
                        @forelse (['exact', 'estimated', 'month', 'year', 'age-estimate', 'unknown'] as $value)
                            <option wire:key="managed-birth-precision-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.birth_precision.'.$value) }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.birth_precision.unknown') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="managed-pet-sex">
                    <span>{{ __('pet_profiles.fields.sex') }}</span>
                    <select id="managed-pet-sex" wire:model="form.sex">
                        @forelse (['male', 'female', 'unknown', 'undetermined', 'other-confirmed'] as $value)
                            <option wire:key="managed-pet-sex-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.sex.'.$value) }}</option>
                        @empty
                            <option value="unknown">{{ __('pet_profiles.sex.unknown') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field md:col-span-2" for="managed-pet-bio">
                    <span>{{ __('pet_profiles.fields.bio') }}</span>
                    <textarea id="managed-pet-bio" wire:model="form.bio" rows="5" maxlength="3000"></textarea>
                </label>
            </div>

            <livewire:forum.animal-taxonomy-selector
                wire:model.live="form.taxonIds"
                input-name="taxon_id"
                :selection-limit="1"
            />

            <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="saveBasics">
                <x-lucide-save aria-hidden="true" />
                <span wire:loading.remove wire:target="saveBasics">{{ __('pet_profiles.actions.save_basics') }}</span>
                <span wire:loading wire:target="saveBasics">{{ __('pet_profiles.actions.saving') }}</span>
            </button>
        </form>
    </section>

    <section id="pet-privacy" aria-labelledby="pet-privacy-heading" class="scroll-mt-4 border-t border-paw-line pt-6">
        <h2 id="pet-privacy-heading">{{ __('pet_profiles.manage.privacy') }}</h2>
        <form wire:submit="savePrivacy" class="forum-form mt-4">
            <div class="grid min-w-0 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <label class="forum-form__field" for="pet-profile-privacy">
                    <span>{{ __('pet_profiles.fields.profile_visibility') }}</span>
                    <select id="pet-profile-privacy" wire:model="privacyForm.profileVisibility">
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="profile-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-location-privacy">
                    <span>{{ __('pet_profiles.fields.location_visibility') }}</span>
                    <select id="pet-location-privacy" wire:model="privacyForm.locationVisibility">
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="location-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-posts-privacy">
                    <span>{{ __('pet_profiles.fields.posts_visibility') }}</span>
                    <select id="pet-posts-privacy" wire:model="privacyForm.postsVisibility">
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="posts-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-friends-privacy">
                    <span>{{ __('pet_profiles.fields.friends_visibility') }}</span>
                    <select id="pet-friends-privacy" wire:model="privacyForm.friendsVisibility">
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="friends-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-care-privacy">
                    <span>{{ __('pet_profiles.fields.care_visibility') }}</span>
                    <select id="pet-care-privacy" wire:model="privacyForm.careVisibility">
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="care-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-activity-privacy">
                    <span>{{ __('pet_profiles.fields.activity_visibility') }}</span>
                    <select id="pet-activity-privacy" wire:model="privacyForm.activityVisibility">
                        @forelse ($this->visibilityOptions as $value => $label)
                            <option wire:key="activity-privacy-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="private">{{ __('pet_profiles.visibility.private') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-owner-display">
                    <span>{{ __('pet_profiles.fields.owner_display') }}</span>
                    <select id="pet-owner-display" wire:model="privacyForm.ownerDisplayMode">
                        @forelse (['full', 'public-name', 'username', 'organization', 'contact-button', 'hidden'] as $value)
                            <option wire:key="owner-display-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.owner_display.'.$value) }}</option>
                        @empty
                            <option value="hidden">{{ __('pet_profiles.owner_display.hidden') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-manager-display">
                    <span>{{ __('pet_profiles.fields.manager_display') }}</span>
                    <select id="pet-manager-display" wire:model="privacyForm.managerDisplayMode">
                        @forelse (['all', 'primary', 'organization', 'count', 'hidden'] as $value)
                            <option wire:key="manager-display-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.manager_display.'.$value) }}</option>
                        @empty
                            <option value="hidden">{{ __('pet_profiles.manager_display.hidden') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-location-precision">
                    <span>{{ __('pet_profiles.fields.location_precision') }}</span>
                    <select id="pet-location-precision" wire:model="privacyForm.publicLocationPrecision">
                        @forelse (['country', 'city', 'district', 'region', 'hidden'] as $value)
                            <option wire:key="location-precision-{{ $value }}" value="{{ $value }}">{{ __('pet_profiles.location_precision.'.$value) }}</option>
                        @empty
                            <option value="hidden">{{ __('pet_profiles.location_precision.hidden') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="privacyForm.isDiscoverable">
                    <span>{{ __('pet_profiles.fields.discoverable') }}</span>
                </label>
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="privacyForm.allowDirectLink">
                    <span>{{ __('pet_profiles.fields.direct_link') }}</span>
                </label>
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="privacyForm.allowExternalIndexing">
                    <span>{{ __('pet_profiles.fields.external_indexing') }}</span>
                </label>
            </div>

            <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="savePrivacy">
                <x-lucide-shield-check aria-hidden="true" />
                <span wire:loading.remove wire:target="savePrivacy">{{ __('pet_profiles.actions.save_privacy') }}</span>
                <span wire:loading wire:target="savePrivacy">{{ __('pet_profiles.actions.saving') }}</span>
            </button>
        </form>
    </section>

    <section id="pet-managers" aria-labelledby="pet-managers-heading" class="scroll-mt-4 border-t border-paw-line pt-6">
        <h2 id="pet-managers-heading">{{ __('pet_profiles.manage.managers') }}</h2>
        <div class="mt-4 grid gap-3">
            @forelse ($this->managers as $manager)
                <article class="forum-form" wire:key="pet-manager-{{ $manager['id'] }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="break-words">{{ $manager['name'] }}</h3>
                            <p>{{ $manager['role'] }} · {{ $manager['status'] }}</p>
                            @if ($manager['ends_at'] !== null)
                                <p class="text-sm text-paw-muted">{{ __('pet_profiles.managers.ends', ['date' => $manager['ends_at']]) }}</p>
                            @endif
                        </div>
                        @if ($manager['revocable'])
                            <button
                                type="button"
                                class="forum-button min-h-11"
                                wire:click="revokeManager({{ $manager['id'] }})"
                                wire:confirm="{{ __('pet_profiles.confirmations.revoke_manager') }}"
                            >
                                <x-lucide-user-x aria-hidden="true" />
                                <span>{{ __('pet_profiles.actions.revoke') }}</span>
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <p class="forum-form">{{ __('pet_profiles.managers.empty') }}</p>
            @endforelse
        </div>

        <form wire:submit="inviteManager" class="forum-form mt-4">
            <h3>{{ __('pet_profiles.managers.invite') }}</h3>
            <div class="grid min-w-0 gap-4 md:grid-cols-3">
                <label class="forum-form__field" for="pet-manager-email">
                    <span>{{ __('pet_profiles.fields.email') }}</span>
                    <input id="pet-manager-email" type="email" wire:model="invitationForm.email" autocomplete="email" required>
                    @error('invitationForm.email') <small role="alert">{{ $message }}</small> @enderror
                </label>
                <label class="forum-form__field" for="pet-manager-role">
                    <span>{{ __('pet_profiles.fields.role') }}</span>
                    <select id="pet-manager-role" wire:model="invitationForm.role">
                        @forelse ($this->invitationRoleOptions as $value => $label)
                            <option wire:key="invite-role-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="other">{{ __('pet_profiles.manager_roles.other') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-manager-ends-at">
                    <span>{{ __('pet_profiles.fields.ends_at') }}</span>
                    <input id="pet-manager-ends-at" type="datetime-local" wire:model="invitationForm.endsAt" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}">
                </label>
            </div>
            <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled" wire:target="inviteManager">
                <x-lucide-user-plus aria-hidden="true" />
                <span>{{ __('pet_profiles.actions.invite') }}</span>
            </button>
        </form>
    </section>

    <section id="pet-lifecycle" aria-labelledby="pet-lifecycle-heading" class="scroll-mt-4 border-t border-paw-line pt-6">
        <h2 id="pet-lifecycle-heading">{{ __('pet_profiles.manage.lifecycle') }}</h2>
        <form wire:submit="transitionStatus" class="forum-form mt-4">
            <div class="grid min-w-0 gap-4 md:grid-cols-2">
                <label class="forum-form__field" for="pet-target-status">
                    <span>{{ __('pet_profiles.fields.status') }}</span>
                    <select id="pet-target-status" wire:model="targetStatus">
                        @forelse ($this->statusOptions as $value => $label)
                            <option wire:key="pet-status-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="{{ $profile->status->value }}">{{ $profile->status->label() }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="forum-form__field" for="pet-status-reason">
                    <span>{{ __('pet_profiles.fields.reason') }}</span>
                    <input id="pet-status-reason" type="text" wire:model="statusReason" maxlength="500">
                </label>
            </div>
            <button
                type="submit"
                class="forum-button forum-button--primary min-h-11"
                wire:loading.attr="disabled"
                wire:target="transitionStatus"
                @disabled($targetStatus === $profile->status->value)
            >
                <x-lucide-refresh-cw aria-hidden="true" />
                <span>{{ __('pet_profiles.actions.change_status') }}</span>
            </button>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[36rem] border-collapse text-left">
                <caption class="sr-only">{{ __('pet_profiles.history.caption') }}</caption>
                <thead>
                    <tr class="border-b border-paw-line">
                        <th scope="col" class="px-3 py-2">{{ __('pet_profiles.history.event') }}</th>
                        <th scope="col" class="px-3 py-2">{{ __('pet_profiles.history.actor') }}</th>
                        <th scope="col" class="px-3 py-2">{{ __('pet_profiles.history.time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->history as $event)
                        <tr class="border-b border-paw-line" wire:key="pet-history-{{ $event['id'] }}">
                            <td class="px-3 py-2">{{ $event['event'] }}</td>
                            <td class="px-3 py-2">{{ $event['actor'] }}</td>
                            <td class="px-3 py-2"><time>{{ $event['occurred_at'] }}</time></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-paw-muted">{{ __('pet_profiles.history.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-page-stack>
