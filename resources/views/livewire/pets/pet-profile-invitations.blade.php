<x-page-stack data-section="pet-profile-invitations">
    <x-page-header
        eyebrow="{{ __('pet_profiles.invitations.eyebrow') }}"
        title="{{ __('pet_profiles.invitations.title') }}"
        description="{{ __('pet_profiles.invitations.description') }}"
        heading-id="pet-profile-invitations-heading"
        action-label="{{ __('pet_profiles.actions.back_to_pets') }}"
        action-icon="arrow-left"
        :action-href="route('pets.index')"
    />

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <section class="grid gap-4" aria-labelledby="pet-invitations-heading">
        <h2 id="pet-invitations-heading" class="sr-only">{{ __('pet_profiles.invitations.title') }}</h2>
        @forelse ($this->invitations as $invitation)
            <article class="forum-form" wire:key="pet-invitation-{{ $invitation['id'] }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="break-words">{{ $invitation['pet_name'] }}</h3>
                        <p>{{ $invitation['species'] }} · {{ $invitation['role'] }}</p>
                        <p class="text-sm text-paw-muted">
                            {{ __('pet_profiles.invitations.invited_by', ['name' => $invitation['inviter']]) }}
                        </p>
                        @if ($invitation['expires_at'] !== null)
                            <p class="text-sm text-paw-muted">
                                {{ __('pet_profiles.invitations.expires', ['date' => $invitation['expires_at']]) }}
                            </p>
                        @endif
                    </div>
                    <button
                        type="button"
                        class="forum-button forum-button--primary min-h-11"
                        wire:click="accept({{ $invitation['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="accept({{ $invitation['id'] }})"
                    >
                        <x-lucide-check aria-hidden="true" />
                        <span>{{ __('pet_profiles.actions.accept') }}</span>
                    </button>
                </div>
            </article>
        @empty
            <p class="forum-form">{{ __('pet_profiles.invitations.empty') }}</p>
        @endforelse
    </section>
</x-page-stack>
