<x-page-stack data-section="pet-profile-access-requests">
    <x-page-header
        :eyebrow="__('pet_profiles.access_requests.review_eyebrow')"
        :title="__('pet_profiles.access_requests.review_title', ['name' => $pet['name']])"
        :description="__('pet_profiles.access_requests.review_description')"
        heading-id="pet-access-requests-heading"
        :action-label="__('pet_profiles.actions.back_to_profile')"
        action-icon="arrow-left"
        :action-href="route('pets.manage.show', ['petProfile' => $pet['profile_key'], 'step' => 'owners'])"
    />

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <section class="grid gap-4" aria-labelledby="pending-pet-access-requests-heading">
        <h2 id="pending-pet-access-requests-heading">{{ __('pet_profiles.access_requests.pending_title') }}</h2>

        @forelse ($this->requests as $request)
            <article class="forum-form grid gap-4" wire:key="pet-access-review-{{ $request['request_key'] }}">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-sm font-semibold text-paw-muted">{{ __('pet_profiles.access_requests.requester') }}</p>
                        <h3 class="break-words">{{ $request['requester'] }}</h3>
                    </div>
                    <dl class="grid gap-2 text-sm">
                        <div><dt class="font-semibold">{{ __('pet_profiles.access_requests.request_type') }}</dt><dd>{{ $request['type'] }}</dd></div>
                        <div><dt class="font-semibold">{{ __('pet_profiles.access_requests.requested_role') }}</dt><dd>{{ $request['role'] }}</dd></div>
                        @if ($request['temporary_ends_at'] !== null)
                            <div><dt class="font-semibold">{{ __('pet_profiles.access_requests.temporary_ends_at') }}</dt><dd>{{ $request['temporary_ends_at'] }}</dd></div>
                        @endif
                        @if ($request['submitted_at'] !== null)
                            <div><dt class="font-semibold">{{ __('pet_profiles.access_requests.submitted_at') }}</dt><dd>{{ $request['submitted_at'] }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div>
                    <p class="text-sm font-semibold text-paw-muted">{{ __('pet_profiles.access_requests.evidence') }}</p>
                    <p class="mt-1 whitespace-pre-line break-words">{{ $request['evidence'] }}</p>
                </div>

                @if ($request['protected'])
                    <x-notice icon="shield-alert" :title="__('pet_profiles.access_requests.protected_title')" :description="__('pet_profiles.access_requests.review_protected_description')" />
                @endif

                <label class="forum-form__field" for="pet-access-resolution-{{ $request['request_key'] }}">
                    <span>{{ __('pet_profiles.access_requests.resolution_note') }}</span>
                    <textarea id="pet-access-resolution-{{ $request['request_key'] }}" wire:model="resolutionNotes.{{ $request['request_key'] }}" rows="3" maxlength="1000"></textarea>
                    @error('resolution_note') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <div class="flex flex-wrap justify-end gap-2">
                    <button type="button" class="forum-button min-h-11" wire:click="reject('{{ $request['request_key'] }}')" wire:loading.attr="disabled">
                        <x-ui-icon name="x" />
                        <span>{{ __('pet_profiles.access_requests.reject') }}</span>
                    </button>
                    @unless ($request['protected'])
                        <button type="button" class="forum-button forum-button--primary min-h-11" wire:click="approve('{{ $request['request_key'] }}')" wire:loading.attr="disabled">
                            <x-ui-icon name="check" />
                            <span>{{ __('pet_profiles.access_requests.approve') }}</span>
                        </button>
                    @endunless
                </div>
            </article>
        @empty
            <p class="forum-form">{{ __('pet_profiles.access_requests.empty') }}</p>
        @endforelse
    </section>
</x-page-stack>
