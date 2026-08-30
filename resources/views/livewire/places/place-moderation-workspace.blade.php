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

    @if ($errors->any())
        <x-forum-error-summary
            :messages="$errors->getMessages()"
            :heading="__('places.submissions.validation.summary')"
        />
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

                <dl class="stack">
                    @foreach ($submission['review_rows'] as $row)
                        <div wire:key="moderation-evidence-{{ $submission['key'] }}-{{ $loop->index }}">
                            <dt><strong>{{ $row['label'] }}</strong></dt>
                            <dd>{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($submission['facts'] !== [])
                    <section class="stack" aria-label="{{ __('places.submissions.review.facts') }}">
                        <h3>{{ __('places.submissions.review.facts') }}</h3>
                        <dl class="stack">
                            @foreach ($submission['facts'] as $fact)
                                <div wire:key="moderation-fact-{{ $submission['key'] }}-{{ $loop->index }}">
                                    <dt><strong>{{ $fact['label'] }}</strong></dt>
                                    <dd>{{ $fact['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif

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
                    @if (in_array($submission['status'], ['submitted', 'duplicate-review', 'needs-information', 'approved'], true))
                        <button class="action action--danger action--regular" type="button" wire:click="reject('{{ $submission['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.reject') }}
                        </button>
                    @elseif (in_array($submission['status'], ['rejected', 'withdrawn'], true))
                        <button class="action action--paper action--regular" type="button" wire:click="reopen('{{ $submission['key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.moderation.reopen') }}
                        </button>
                    @endif
                    @if ($submission['can_restore'] && $submission['restore_key'] !== null)
                        <button class="action action--paper action--regular" type="button" wire:click="restore('{{ $submission['key'] }}', '{{ $submission['restore_key'] }}')" wire:loading.attr="disabled">
                            {{ __('places.submissions.review.restore') }}
                        </button>
                    @endif
                </div>

                @forelse ($submission['candidates'] as $candidate)
                    <div wire:key="moderation-candidate-{{ $candidate['key'] }}" class="cluster">
                        <span>{{ $candidate['name'] }} · {{ $candidate['region'] }}</span>
                        @if (in_array($submission['status'], ['duplicate-review', 'approved'], true))
                            <button class="action action--paper action--regular" type="button" wire:click="link('{{ $submission['key'] }}', '{{ $candidate['key'] }}')" wire:loading.attr="disabled">
                                {{ __('places.submissions.moderation.link') }}
                            </button>
                        @endif
                        @if ($submission['can_merge'])
                            <button class="action action--danger action--regular" type="button" wire:click="merge('{{ $submission['key'] }}', '{{ $candidate['key'] }}')" wire:loading.attr="disabled">
                                {{ __('places.submissions.review.merge') }}
                            </button>
                        @endif
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
