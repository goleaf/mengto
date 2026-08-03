<x-page-stack data-section="organization-invitation-response">
    <x-page-header
        :eyebrow="__('organizations.pages.invitation.eyebrow')"
        :title="__('organizations.pages.invitation.title')"
        :description="__('organizations.pages.invitation.description')"
        heading-id="organization-invitation-heading"
    />

    @if ($feedback !== '')
        <x-flash-feedback :message="$feedback" />
    @endif

    <x-content-panel
        eyebrow="{{ __('organizations.pages.invitation.details_eyebrow') }}"
        :title="$invitation['organization']"
    >
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div><dt class="font-semibold">{{ __('organizations.fields.role') }}</dt><dd>{{ $invitation['role'] }}</dd></div>
            <div><dt class="font-semibold">{{ __('organizations.fields.expires_at') }}</dt><dd>{{ $invitation['expires_at'] }}</dd></div>
        </dl>

        <div class="mt-6 flex flex-wrap gap-3">
            <button class="forum-button forum-button--primary min-h-11" type="button" wire:click="respond(true)" wire:loading.attr="disabled" wire:target="respond">
                <x-ui-icon name="check" />
                {{ __('organizations.actions.accept_invitation') }}
            </button>
            <button class="forum-button min-h-11" type="button" wire:click="respond(false)" wire:loading.attr="disabled" wire:target="respond">
                <x-ui-icon name="x" />
                {{ __('organizations.actions.decline_invitation') }}
            </button>
        </div>
    </x-content-panel>
</x-page-stack>
