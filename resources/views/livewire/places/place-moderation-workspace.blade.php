<x-page-stack data-section="place-moderation">
    <x-page-header
        :eyebrow="__('places.submissions.moderation.eyebrow')"
        :title="__('places.submissions.moderation.title')"
        :description="__('places.submissions.moderation.description')"
        heading-id="place-moderation-heading"
        :action-label="__('places.submissions.actions.back')"
        action-icon="arrow-left"
        :action-href="route('places.index')"
    />

    @if (session('place-moderation-feedback'))
        <p class="notice" role="status">{{ session('place-moderation-feedback') }}</p>
    @endif

    <section class="panel stack" aria-labelledby="place-moderation-reason-heading">
        <h2 id="place-moderation-reason-heading">{{ __('places.submissions.moderation.decision') }}</h2>
        <label class="stack" for="place-moderation-reason-code">
            <span>{{ __('places.submissions.moderation.reason_code') }}</span>
            <input id="place-moderation-reason-code" class="field" wire:model="reasonCode" type="text" maxlength="80">
        </label>
        <label class="stack" for="place-moderation-reason-detail">
            <span>{{ __('places.submissions.moderation.reason_detail') }}</span>
            <textarea id="place-moderation-reason-detail" class="field" wire:model="reasonDetail" rows="3" maxlength="2000"></textarea>
        </label>
    </section>

    <div class="stack">
        @forelse ($this->submissions as $submission)
            <article wire:key="moderation-{{ $submission['key'] }}" class="panel stack">
                <header>
                    <h2>{{ $submission['name'] }}</h2>
                    <p>{{ $submission['region'] }} · {{ $submission['submitter'] }}</p>
                    <p class="badge">{{ __('places.submissions.statuses.'.$submission['status']) }}</p>
                </header>

                <div class="cluster">
                    @if (in_array($submission['status'], ['submitted', 'duplicate-review', 'needs-information'], true))
                        <button class="action action--primary action--regular" type="button" wire:click="approve('{{ $submission['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.approve') }}
                        </button>
                    @endif
                    @if ($submission['status'] === 'approved')
                        <button class="action action--primary action--regular" type="button" wire:click="publish('{{ $submission['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.publish') }}
                        </button>
                    @endif
                    @if (in_array($submission['status'], ['submitted', 'duplicate-review', 'approved'], true))
                        <button class="action action--paper action--regular" type="button" wire:click="requestInformation('{{ $submission['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.request_information') }}
                        </button>
                    @endif
                    @if ($submission['status'] !== 'rejected')
                        <button class="action action--danger action--regular" type="button" wire:click="reject('{{ $submission['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.reject') }}
                        </button>
                    @else
                        <button class="action action--paper action--regular" type="button" wire:click="reopen('{{ $submission['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.reopen') }}
                        </button>
                    @endif
                </div>

                @forelse ($submission['candidates'] as $candidate)
                    <div wire:key="moderation-candidate-{{ $candidate['key'] }}" class="cluster">
                        <span>{{ $candidate['name'] }} · {{ $candidate['region'] }}</span>
                        <button class="action action--paper action--regular" type="button" wire:click="link('{{ $submission['key'] }}', '{{ $candidate['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.link') }}
                        </button>
                    </div>
                @empty
                    <p>{{ __('places.submissions.duplicates.protected') }}</p>
                @endforelse
            </article>
        @empty
            <p class="panel">{{ __('places.submissions.empty.queue') }}</p>
        @endforelse
    </div>

    <p wire:loading class="notice" role="status">{{ __('places.submissions.states.loading') }}</p>
    <p wire:offline class="notice" role="status">{{ __('places.submissions.states.offline') }}</p>
</x-page-stack>
