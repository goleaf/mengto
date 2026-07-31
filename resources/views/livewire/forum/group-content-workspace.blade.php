<section class="grid gap-8 border-t border-border-subtle pt-6" aria-labelledby="group-content-heading">
    <header>
        <h2 id="group-content-heading">{{ __('forum_polls.title') }}</h2>
        <p>{{ __('forum_polls.notices.file_private') }}</p>
    </header>

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    @if ($errors->any())
        <x-forum-error-summary
            :messages="$errors->getMessages()"
            :heading="__('forum_groups.validation.summary')"
        />
    @endif

    <section class="grid gap-3" aria-labelledby="group-announcements-heading">
        <h3 id="group-announcements-heading">{{ __('forum_polls.sections.announcements') }}</h3>
        @forelse ($this->content['announcements'] as $announcement)
            <article class="border-s-4 border-status-information py-2 ps-4" wire:key="announcement-{{ $announcement['id'] }}">
                <h4 class="text-base font-semibold">{{ $announcement['title'] }}</h4>
                <p class="whitespace-pre-line">{{ $announcement['body'] }}</p>
                <p class="text-sm text-text-muted">
                    {{ __('forum_polls.labels.created_by', ['name' => $announcement['author']]) }}
                    · {{ $announcement['published_at'] }}
                </p>
            </article>
        @empty
            <p>{{ __('forum_polls.empty.announcements') }}</p>
        @endforelse
    </section>

    <div class="grid gap-8 lg:grid-cols-2">
        <section class="grid content-start gap-3" aria-labelledby="group-topics-heading">
            <h3 id="group-topics-heading">{{ __('forum_polls.sections.topics') }}</h3>
            @forelse ($this->content['topics'] as $topic)
                <article class="border-b border-border-subtle pb-3" wire:key="group-topic-{{ $topic['id'] }}">
                    <a class="inline-flex min-h-11 items-center font-semibold underline-offset-4 hover:underline" href="{{ $topic['url'] }}" wire:navigate>
                        {{ $topic['title'] }}
                    </a>
                    <p class="text-sm text-text-muted">
                        {{ $topic['type'] }} · {{ $topic['status'] }} ·
                        {{ __('forum_polls.labels.created_by', ['name' => $topic['author']]) }}
                    </p>
                </article>
            @empty
                <p>{{ __('forum_polls.empty.topics') }}</p>
            @endforelse
        </section>

        <section class="grid content-start gap-3" aria-labelledby="group-guides-heading">
            <h3 id="group-guides-heading">{{ __('forum_polls.sections.guides') }}</h3>
            @forelse ($this->content['guides'] as $guide)
                <article class="border-b border-border-subtle pb-3" wire:key="group-guide-{{ $guide['id'] }}">
                    <a class="inline-flex min-h-11 items-center font-semibold underline-offset-4 hover:underline" href="{{ $guide['url'] }}" wire:navigate>
                        {{ $guide['title'] }}
                    </a>
                    <p>{{ $guide['summary'] }}</p>
                    @if ($guide['author'])
                        <p class="text-sm text-text-muted">
                            {{ __('forum_polls.labels.created_by', ['name' => $guide['author']]) }}
                        </p>
                    @endif
                </article>
            @empty
                <p>{{ __('forum_polls.empty.guides') }}</p>
            @endforelse
        </section>
    </div>

    <section class="grid gap-3" aria-labelledby="group-activities-heading">
        <h3 id="group-activities-heading">{{ __('forum_polls.sections.activities') }}</h3>
        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($this->content['activities'] as $activityItem)
                <article class="border-b border-border-subtle pb-4" wire:key="group-activity-{{ $activityItem['id'] }}">
                    <h4 class="text-base font-semibold">{{ $activityItem['title'] }}</h4>
                    <p>{{ $activityItem['summary'] }}</p>
                    <p class="text-sm text-text-muted">
                        {{ $activityItem['format'] }} · {{ $activityItem['status'] }} ·
                        {{ __('forum_polls.labels.starts', [
                            'start' => $activityItem['starts_at'],
                            'end' => $activityItem['ends_at'],
                        ]) }}
                    </p>
                    @if ($activityItem['location_scope'])
                        <p class="text-sm">{{ $activityItem['location_scope'] }}</p>
                    @endif
                    @if ($activityItem['capacity'])
                        <p class="text-sm">{{ __('forum_polls.labels.capacity', ['count' => $activityItem['capacity']]) }}</p>
                    @endif
                </article>
            @empty
                <p>{{ __('forum_polls.empty.activities') }}</p>
            @endforelse
        </div>
    </section>

    <section class="grid gap-3" aria-labelledby="group-files-heading">
        <h3 id="group-files-heading">{{ __('forum_polls.sections.files') }}</h3>
        <p class="text-sm">{{ __('forum_polls.notices.file_private') }}</p>
        <ul class="grid gap-3 sm:grid-cols-2">
            @forelse ($this->content['files'] as $file)
                <li class="border-b border-border-subtle pb-3" wire:key="group-file-{{ $file['id'] }}">
                    <a class="forum-button min-h-11" href="{{ $file['url'] }}">
                        <x-lucide-download aria-hidden="true" />
                        {{ __('forum_polls.actions.download_file', ['name' => $file['name']]) }}
                    </a>
                    @if ($file['description'])
                        <p class="mt-2">{{ $file['description'] }}</p>
                    @endif
                    <p class="text-sm text-text-muted">
                        {{ $file['mime_type'] }} ·
                        {{ __('forum_polls.labels.file_size', ['size' => $file['size_kb']]) }} ·
                        {{ $file['uploader'] }}
                    </p>
                </li>
            @empty
                <li>{{ __('forum_polls.empty.files') }}</li>
            @endforelse
        </ul>
    </section>

    <section class="grid gap-5" aria-labelledby="group-polls-heading">
        <header>
            <h3 id="group-polls-heading">{{ __('forum_polls.sections.polls') }}</h3>
            <p class="border-s-4 border-status-warning py-2 ps-4">
                {{ __('forum_polls.notices.poll_authority') }}
            </p>
        </header>

        @forelse ($this->content['polls'] as $pollItem)
            <article class="grid gap-4 border-b border-border-subtle pb-6" wire:key="group-poll-{{ $pollItem['id'] }}">
                <header>
                    <h4 class="text-base font-semibold">{{ $pollItem['question'] }}</h4>
                    @if ($pollItem['description'])
                        <p>{{ $pollItem['description'] }}</p>
                    @endif
                    <p class="text-sm text-text-muted">
                        {{ $pollItem['type_label'] }} · {{ $pollItem['eligibility'] }} ·
                        {{ $pollItem['result_visibility'] }}
                    </p>
                    @if ($pollItem['closes_at'])
                        <p class="text-sm">
                            {{ $pollItem['is_closed']
                                ? __('forum_polls.labels.closed', ['time' => $pollItem['closes_at']])
                                : __('forum_polls.labels.closes', ['time' => $pollItem['closes_at']]) }}
                        </p>
                    @endif
                </header>

                <p class="text-sm">
                    {{ $pollItem['is_anonymous']
                        ? __('forum_polls.notices.anonymous')
                        : __('forum_polls.notices.visible_voters') }}
                    {{ $pollItem['is_vote_editable']
                        ? __('forum_polls.notices.vote_editable')
                        : __('forum_polls.notices.vote_final') }}
                </p>

                @if ($pollItem['is_location_limited'])
                    <p class="text-sm">{{ __('forum_polls.notices.location_scope') }}</p>
                @endif

                @if ($pollItem['can_vote'])
                    <form wire:submit="castVote({{ $pollItem['id'] }})" class="grid gap-3">
                        <fieldset class="grid gap-2">
                            <legend class="font-semibold">{{ $pollItem['question'] }}</legend>
                            @if ($pollItem['type'] === 'single-choice')
                                @foreach ($pollItem['options'] as $option)
                                    <label class="forum-form__check flex min-h-11 cursor-pointer items-center gap-2 py-2" wire:key="poll-{{ $pollItem['id'] }}-option-{{ $option['id'] }}">
                                        <input
                                            type="radio"
                                            value="{{ $option['id'] }}"
                                            wire:model="pollChoices.{{ $pollItem['id'] }}"
                                        >
                                        <span>{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            @elseif ($pollItem['type'] === 'multiple-choice')
                                @foreach ($pollItem['options'] as $option)
                                    <label class="forum-form__check flex min-h-11 cursor-pointer items-center gap-2 py-2" wire:key="poll-{{ $pollItem['id'] }}-option-{{ $option['id'] }}">
                                        <input
                                            type="checkbox"
                                            value="{{ $option['id'] }}"
                                            wire:model="pollChoices.{{ $pollItem['id'] }}"
                                        >
                                        <span>{{ $option['label'] }}</span>
                                    </label>
                                @endforeach
                            @else
                                <p id="ranked-poll-help-{{ $pollItem['id'] }}" class="text-sm">
                                    {{ __('forum_polls.labels.ranked_help') }}
                                </p>
                                @foreach ($pollItem['options'] as $rank => $rankOption)
                                    <label class="forum-form__field" wire:key="poll-{{ $pollItem['id'] }}-rank-{{ $rank }}">
                                        <span>{{ __('forum_polls.fields.rank', ['position' => $rank + 1]) }}</span>
                                        <select
                                            class="min-h-11 w-full rounded-md border border-border-subtle bg-white px-3 py-2"
                                            wire:model="pollChoices.{{ $pollItem['id'] }}.{{ $rank }}"
                                            aria-describedby="ranked-poll-help-{{ $pollItem['id'] }}"
                                        >
                                            <option value="">{{ __('forum_polls.fields.rank', ['position' => $rank + 1]) }}</option>
                                            @foreach ($pollItem['options'] as $option)
                                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                @endforeach
                            @endif
                        </fieldset>
                        @error('pollChoices') <small role="alert">{{ $message }}</small> @enderror
                        <button
                            class="forum-button forum-button--primary min-h-11"
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="castVote({{ $pollItem['id'] }})"
                        >
                            <x-lucide-vote aria-hidden="true" />
                            {{ $pollItem['current_choices'] === []
                                ? __('forum_polls.actions.vote')
                                : __('forum_polls.actions.update_vote') }}
                        </button>
                    </form>
                @endif

                @if ($pollItem['results_visible'])
                    <section aria-label="{{ $pollItem['question'] }}">
                        <p class="font-semibold">
                            {{ trans_choice('forum_polls.labels.votes', $pollItem['total_votes'], [
                                'count' => $pollItem['total_votes'],
                            ]) }}
                        </p>
                        <ul class="grid gap-2">
                            @foreach ($pollItem['options'] as $option)
                                <li class="flex flex-wrap items-center justify-between gap-2 border-b border-border-subtle py-2">
                                    <span>{{ $option['label'] }}</span>
                                    <span>
                                        {{ trans_choice('forum_polls.labels.selections', $option['selection_count'], [
                                            'count' => $option['selection_count'],
                                        ]) }}
                                        @if ($pollItem['type'] === 'ranked-choice')
                                            · {{ trans_choice('forum_polls.labels.first_choices', $option['first_choice_count'], [
                                                'count' => $option['first_choice_count'],
                                            ]) }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        @if ($pollItem['voters'] !== [])
                            <p class="mt-2 text-sm">{{ implode(', ', $pollItem['voters']) }}</p>
                        @endif
                    </section>
                @else
                    <p>{{ __('forum_polls.notices.results_hidden') }}</p>
                @endif
            </article>
        @empty
            <p>{{ __('forum_polls.empty.polls') }}</p>
        @endforelse
    </section>

    @if ($this->content['can_create'])
        <section class="grid gap-4 border-t border-border-subtle pt-6" aria-labelledby="group-content-create-heading">
            <h3 id="group-content-create-heading">{{ __('forum_polls.sections.create') }}</h3>

            <details class="forum-form">
                <summary class="forum-button min-h-11">
                    <x-lucide-link aria-hidden="true" />
                    {{ __('forum_polls.actions.link_topic') }}
                </summary>
                <form wire:submit="linkTopic" class="mt-4 grid gap-3">
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.topic_slug') }}</span>
                        <input type="text" wire:model="association.topicSlug" maxlength="180">
                        @error('association.topicSlug') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <button class="forum-button forum-button--primary min-h-11" type="submit" wire:loading.attr="disabled" wire:target="linkTopic">
                        <x-lucide-link aria-hidden="true" />
                        {{ __('forum_polls.actions.link_topic') }}
                    </button>
                </form>
            </details>

            <details class="forum-form">
                <summary class="forum-button min-h-11">
                    <x-lucide-book-open aria-hidden="true" />
                    {{ __('forum_polls.actions.link_guide') }}
                </summary>
                <form wire:submit="linkGuide" class="mt-4 grid gap-3">
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.guide_slug') }}</span>
                        <input type="text" wire:model="association.guideSlug" maxlength="180">
                        @error('association.guideSlug') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <button class="forum-button forum-button--primary min-h-11" type="submit" wire:loading.attr="disabled" wire:target="linkGuide">
                        <x-lucide-book-open aria-hidden="true" />
                        {{ __('forum_polls.actions.link_guide') }}
                    </button>
                </form>
            </details>

            <details class="forum-form">
                <summary class="forum-button min-h-11">
                    <x-lucide-calendar-plus aria-hidden="true" />
                    {{ __('forum_polls.actions.create_activity') }}
                </summary>
                <form wire:submit="createActivity" class="mt-4 grid gap-3 md:grid-cols-2">
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_polls.fields.activity_title') }}</span>
                        <input type="text" wire:model="activity.title" maxlength="180">
                        @error('activity.title') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_polls.fields.activity_summary') }}</span>
                        <textarea wire:model="activity.summary" rows="4" maxlength="3000"></textarea>
                        @error('activity.summary') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.activity_format') }}</span>
                        <select wire:model="activity.format">
                            @foreach ($this->activityFormatOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.activity_timezone') }}</span>
                        <input type="text" wire:model="activity.timezone" maxlength="64">
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.activity_starts_at') }}</span>
                        <input type="datetime-local" wire:model="activity.startsAt">
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.activity_ends_at') }}</span>
                        <input type="datetime-local" wire:model="activity.endsAt">
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.activity_location') }}</span>
                        <input type="text" wire:model="activity.locationScope" maxlength="160">
                        @error('activity.locationScope') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.activity_online_url') }}</span>
                        <input type="url" wire:model="activity.onlineUrl" maxlength="2000">
                        @error('activity.onlineUrl') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.activity_capacity') }}</span>
                        <input type="number" wire:model="activity.capacity" min="1" max="100000">
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_polls.fields.activity_notes') }}</span>
                        <textarea wire:model="activity.participationNotes" rows="3" maxlength="3000"></textarea>
                    </label>
                    <button class="forum-button forum-button--primary min-h-11 md:col-span-2" type="submit" wire:loading.attr="disabled" wire:target="createActivity">
                        <x-lucide-calendar-plus aria-hidden="true" />
                        {{ __('forum_polls.actions.create_activity') }}
                    </button>
                </form>
            </details>

            @if ($this->content['can_publish_announcement'])
                <details class="forum-form">
                    <summary class="forum-button min-h-11">
                        <x-lucide-megaphone aria-hidden="true" />
                        {{ __('forum_polls.actions.publish_announcement') }}
                    </summary>
                    <form wire:submit="publishAnnouncement" class="mt-4 grid gap-3">
                        <label class="forum-form__field">
                            <span>{{ __('forum_polls.fields.announcement_title') }}</span>
                            <input type="text" wire:model="announcement.title" maxlength="180">
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_polls.fields.announcement_body') }}</span>
                            <textarea wire:model="announcement.body" rows="5" maxlength="10000"></textarea>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_polls.fields.announcement_expires_at') }}</span>
                            <input type="datetime-local" wire:model="announcement.expiresAt">
                        </label>
                        <button class="forum-button forum-button--primary min-h-11" type="submit" wire:loading.attr="disabled" wire:target="publishAnnouncement">
                            <x-lucide-megaphone aria-hidden="true" />
                            {{ __('forum_polls.actions.publish_announcement') }}
                        </button>
                    </form>
                </details>
            @endif

            <details class="forum-form">
                <summary class="forum-button min-h-11">
                    <x-lucide-vote aria-hidden="true" />
                    {{ __('forum_polls.actions.create_poll') }}
                </summary>
                <form wire:submit="createPoll" class="mt-4 grid gap-3 md:grid-cols-2">
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_polls.fields.poll_question') }}</span>
                        <input type="text" wire:model="poll.question" maxlength="240">
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_polls.fields.poll_description') }}</span>
                        <textarea wire:model="poll.description" rows="3" maxlength="3000"></textarea>
                    </label>
                    <label class="forum-form__field md:col-span-2">
                        <span>{{ __('forum_polls.fields.poll_options') }}</span>
                        <textarea wire:model="poll.optionsText" rows="6" maxlength="3800"></textarea>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.poll_type') }}</span>
                        <select wire:model="poll.type">
                            @foreach ($this->pollTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.voter_visibility') }}</span>
                        <select wire:model="poll.voterVisibility">
                            @foreach ($this->voterVisibilityOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.result_visibility') }}</span>
                        <select wire:model="poll.resultVisibility">
                            @foreach ($this->resultVisibilityOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.poll_eligibility') }}</span>
                        <select wire:model="poll.eligibility">
                            @foreach ($this->pollEligibilityOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.poll_closes_at') }}</span>
                        <input type="datetime-local" wire:model="poll.closesAt">
                    </label>
                    <label class="forum-form__check">
                        <input type="checkbox" wire:model="poll.isVoteEditable">
                        <span>{{ __('forum_polls.fields.vote_editable') }}</span>
                    </label>
                    <p class="border-s-4 border-status-warning py-2 ps-4 md:col-span-2">
                        {{ __('forum_polls.notices.poll_authority') }}
                    </p>
                    <button class="forum-button forum-button--primary min-h-11 md:col-span-2" type="submit" wire:loading.attr="disabled" wire:target="createPoll">
                        <x-lucide-vote aria-hidden="true" />
                        {{ __('forum_polls.actions.create_poll') }}
                    </button>
                </form>
            </details>

            <details class="forum-form">
                <summary class="forum-button min-h-11">
                    <x-lucide-upload aria-hidden="true" />
                    {{ __('forum_polls.actions.upload_file') }}
                </summary>
                <form wire:submit="uploadFile" class="mt-4 grid gap-3">
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.file') }}</span>
                        <input
                            type="file"
                            wire:model="groupFile"
                            accept=".pdf,.txt,.jpg,.jpeg,.png,.webp,application/pdf,text/plain,image/jpeg,image/png,image/webp"
                        >
                        <small>{{ __('forum_polls.notices.accepted_files') }}</small>
                        @error('groupFile') <small role="alert">{{ $message }}</small> @enderror
                    </label>
                    <label class="forum-form__field">
                        <span>{{ __('forum_polls.fields.file_description') }}</span>
                        <textarea wire:model="groupFileDescription" rows="3" maxlength="1000"></textarea>
                    </label>
                    <button class="forum-button forum-button--primary min-h-11" type="submit" wire:loading.attr="disabled" wire:target="groupFile,uploadFile">
                        <x-lucide-upload aria-hidden="true" />
                        {{ __('forum_polls.actions.upload_file') }}
                    </button>
                </form>
            </details>
        </section>
    @endif
</section>
