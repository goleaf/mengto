<x-page-stack data-section="place-submission-status">
    <x-page-header
        :eyebrow="__('places.submissions.status.eyebrow')"
        :title="$this->submission->name"
        :description="__('places.submissions.status.description')"
        heading-id="place-submission-status-heading"
        :action-label="__('places.submissions.actions.new')"
        action-icon="plus"
        :action-href="route('places.submissions.create')"
    />

    @if (session('place-submission-feedback'))
        <p class="notice" role="status">{{ session('place-submission-feedback') }}</p>
    @endif

    <section class="panel stack" aria-labelledby="place-submission-current-status">
        <h2 id="place-submission-current-status">{{ __('places.submissions.status.current') }}</h2>
        <p class="badge">{{ $this->submission->status->label() }}</p>
        <p>{{ __('places.submissions.states.'.$this->submission->status->value) }}</p>

        @if ($this->submission->status === \App\Enums\PlaceSubmissionStatus::DuplicateReview)
            <h3>{{ __('places.submissions.duplicates.title') }}</h3>
            <p>{{ __('places.submissions.duplicates.description') }}</p>
            <div class="stack">
                @forelse ($this->visibleCandidates as $candidate)
                    <article wire:key="place-candidate-{{ $candidate['key'] }}" class="panel">
                        <h4>{{ $candidate['name'] }}</h4>
                        <p>{{ $candidate['region'] }}</p>
                        <a class="action action--paper action--regular" href="{{ $candidate['url'] }}">
                            {{ __('places.submissions.actions.view_candidate') }}
                        </a>
                        <div class="flex flex-wrap gap-2">
                            <button class="action action--primary action--regular" type="button" wire:click="confirmCandidate('{{ $candidate['key'] }}')" wire:loading.attr="disabled" wire:target="confirmCandidate">
                                {{ __('places.submissions.actions.confirm_existing') }}
                            </button>
                            <a class="action action--paper action--regular" href="{{ $candidate['correction_url'] }}">
                                {{ __('places.submissions.actions.propose_correction') }}
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="notice">{{ __('places.submissions.duplicates.protected') }}</p>
                @endforelse
            </div>

            @if ($this->visibleCandidates !== [])
                <button class="action action--paper action--regular" type="button" wire:click="continueDistinct" wire:loading.attr="disabled" wire:target="continueDistinct">
                    {{ __('places.submissions.actions.continue_distinct') }}
                </button>
            @endif
        @endif

        @if ($this->submission->status === \App\Enums\PlaceSubmissionStatus::NeedsInformation)
            <form class="stack" wire:submit="respond">
                <label class="field" for="place-submission-response">
                    <span>{{ __('places.submissions.fields.information_response') }}</span>
                    <textarea id="place-submission-response" wire:model="responseDetail" rows="5" minlength="10" maxlength="2000" required></textarea>
                </label>
                @error('response_detail') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                <button class="action action--primary action--regular" type="submit" wire:loading.attr="disabled" wire:target="respond">
                    {{ __('places.submissions.actions.send_information') }}
                </button>
            </form>
        @endif

        @if (in_array($this->submission->status, [
            \App\Enums\PlaceSubmissionStatus::Submitted,
            \App\Enums\PlaceSubmissionStatus::NeedsInformation,
            \App\Enums\PlaceSubmissionStatus::DuplicateReview,
            \App\Enums\PlaceSubmissionStatus::Approved,
        ], true))
            <button class="action action--paper action--regular" type="button" wire:click="withdraw" wire:loading.attr="disabled" wire:target="withdraw">
                {{ __('places.submissions.actions.withdraw') }}
            </button>
        @endif

        @if ($this->submission->publishedPlace !== null)
            <a data-place-published-link class="action action--primary action--regular" href="{{ route('places.show', ['place' => $this->submission->publishedPlace->slug]) }}">
                {{ __('places.submissions.actions.view_published') }}
            </a>
        @elseif ($this->submission->linkedPlace !== null)
            <a data-place-published-link class="action action--primary action--regular" href="{{ route('places.show', ['place' => $this->submission->linkedPlace->slug]) }}">
                {{ __('places.submissions.actions.view_published') }}
            </a>
        @endif
    </section>

    <p wire:loading class="notice" role="status">{{ __('places.submissions.states.loading') }}</p>
    <p wire:offline class="notice" role="status">{{ __('places.submissions.states.offline') }}</p>
</x-page-stack>
