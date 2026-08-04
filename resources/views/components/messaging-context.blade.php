<aside class="messaging-context" aria-label="{{ __('messaging.context.label') }}" data-messaging-context>
    <section class="messaging-context__identity">
        <x-linked-media
            :href="$conversation['media_target']['url']"
            :label="$conversation['media_target']['label']"
            variant="avatar"
        >
            <img src="{{ $conversation['avatar'] }}" alt="" width="64" height="64">
        </x-linked-media>
        <div>
            <h2>{{ $conversation['name'] }}</h2>
            <p data-messaging-context-purpose>{{ $conversation['purpose'] }}</p>
            <span data-messaging-context-privacy><x-ui-icon name="shield-check" size="sm" /> {{ $conversation['privacy'] }}</span>
        </div>
    </section>
    <p class="messaging-context__identity-note" data-messaging-context-identity-note>
        <x-ui-icon name="user-round-check" size="sm" />
        <span>{{ $context['identity_note'] }}</span>
    </p>

    <form method="GET" action="{{ route('messages.index') }}" class="messaging-context__search">
        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
        <input type="hidden" name="filter" value="{{ $activeFilter }}">
        <label for="message-history-search">{{ __('messaging.context.search_label') }}</label>
        <div>
            <x-ui-icon name="search" size="sm" />
            <input id="message-history-search" type="search" name="message_q" value="{{ $messageQuery }}" placeholder="{{ __('messaging.context.search_placeholder') }}">
        </div>
    </form>

    <div class="messaging-context__actions" aria-label="{{ __('messaging.context.controls_label') }}" data-messaging-context-controls>
        @forelse ($controls as $control)
            <form method="POST" action="{{ route('messages.actions') }}">
                @csrf
                <input type="hidden" name="action" value="{{ $control['action'] }}">
                <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                <button type="submit">
                    <x-ui-icon size="sm" :name="$control['icon']" />
                    <span>{{ $control['label'] }}</span>
                </button>
            </form>
        @empty
            <span>{{ __('messaging.context.boundary.unavailable') }}</span>
        @endforelse
    </div>

    @if ($professional)
        <section class="messaging-context__section" data-messaging-context-professional>
            <header><x-ui-icon name="briefcase-medical" size="sm" /><h3>{{ __('messaging.context.professional.title') }}</h3></header>
            <dl>
                <div><dt>{{ __('messaging.context.professional.status') }}</dt><dd>{{ $professional['status'] }}</dd></div>
                <div><dt>{{ __('messaging.context.professional.assigned') }}</dt><dd>{{ $professional['assigned'] }}</dd></div>
                <div><dt>{{ __('messaging.context.professional.hours') }}</dt><dd>{{ $professional['hours'] }}</dd></div>
            </dl>
            <p>{{ $professional['privacy'] }}</p>
        </section>
    @endif

    @if ($poll)
        <section class="messaging-context__section" data-messaging-context-poll>
            <header><x-ui-icon name="list-checks" size="sm" /><h3>{{ __('messaging.context.poll.title') }}</h3></header>
            <p>{{ $poll['question'] }}</p>
            <div class="messaging-poll">
                @forelse ($poll['options'] as $option)
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="vote-chat-poll">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="poll_option" value="{{ $option['key'] }}">
                        <button type="submit">
                            <span>{{ $option['label'] }}</span>
                            <strong>{{ $option['votes'] }}</strong>
                        </button>
                    </form>
                @empty
                    <span>{{ __('messaging.context.poll.empty') }}</span>
                @endforelse
            </div>
        </section>
    @endif

    @if ($tasks !== [])
        <section class="messaging-context__section" data-messaging-context-tasks>
            <header><x-ui-icon name="list-todo" size="sm" /><h3>{{ __('messaging.context.tasks.title') }}</h3></header>
            <div class="messaging-tasks">
                @forelse ($tasks as $task)
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="update-chat-task">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="task" value="{{ $task['key'] }}">
                        <input type="hidden" name="task_status" value="completed">
                        <button type="submit">
                            @if ($task['status'] === 'completed')
                                <x-ui-icon name="circle-check-big" size="sm" label="{{ __('messaging.context.tasks.completed') }}" />
                            @else
                                <x-ui-icon name="circle" size="sm" label="{{ $task['status_label'] }}" />
                            @endif
                            <span><strong>{{ $task['label'] }}</strong><small>{{ $task['owner'] }} · {{ $task['status_label'] }}</small></span>
                        </button>
                    </form>
                @empty
                    <span>{{ __('messaging.context.tasks.empty') }}</span>
                @endforelse
            </div>
        </section>
    @endif

    <details class="messaging-context__section" open data-messaging-context-members>
        <summary><x-ui-icon name="users-round" size="sm" /><strong>{{ __('messaging.context.members.title') }}</strong><span>{{ $conversation['members'] }}</span></summary>
        <div class="messaging-members">
            @forelse ($members as $member)
                <div>
                    <span>{{ $member['initial'] }}</span>
                    <p><strong>{{ $member['name'] }}</strong><small>{{ $member['role'] }} · {{ $member['pet'] }}</small></p>
                </div>
            @empty
                <p>{{ __('messaging.context.members.empty') }}</p>
            @endforelse
        </div>
    </details>

    <details class="messaging-context__section" data-messaging-context-shared>
        <summary><x-ui-icon name="folders" size="sm" /><strong>{{ __('messaging.context.shared.title') }}</strong></summary>
        <div class="messaging-shared-grid">
            @forelse ($context['shared_cards'] as $card)
                <button type="button">
                    <x-ui-icon size="sm" :name="$card['icon']" />
                    <span><strong>{{ $card['label'] }}</strong><small>{{ $card['value'] }}</small></span>
                </button>
            @empty
                <p>{{ __('messaging.context.shared.empty') }}</p>
            @endforelse
        </div>
    </details>

    <details class="messaging-context__section" data-messaging-context-safety>
        <summary><x-ui-icon name="shield-alert" size="sm" /><strong>{{ __('messaging.context.safety.title') }}</strong></summary>
        <div class="messaging-safety">
            @forelse ($context['safety'] as $item)
                <div>
                    <x-ui-icon size="sm" :name="$item['icon']" />
                    <p><strong>{{ $item['title'] }}</strong><span>{{ $item['description'] }}</span></p>
                </div>
            @empty
                <p>{{ __('messaging.context.safety.empty') }}</p>
            @endforelse
        </div>

        <div class="messaging-danger-actions">
            @forelse ($safetyActions as $safetyAction)
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="{{ $safetyAction['action'] }}">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <button type="submit"><x-ui-icon size="sm" :name="$safetyAction['icon']" /> {{ $safetyAction['label'] }}</button>
                </form>
            @empty
                <span>{{ __('messaging.context.boundary.unavailable') }}</span>
            @endforelse
        </div>
    </details>

    <details class="messaging-context__section messaging-context__section--boundary" data-messaging-context-boundary>
        <summary><x-ui-icon name="layers-3" size="sm" /><strong>{{ __('messaging.context.boundary.title') }}</strong></summary>
        <dl>
            @forelse ($coverage as $item)
                <div><dt>{{ $item['label'] }}</dt><dd>{{ $item['value'] }}</dd></div>
            @empty
                <div><dt>{{ __('messaging.context.boundary.status') }}</dt><dd>{{ __('messaging.context.boundary.unavailable') }}</dd></div>
            @endforelse
        </dl>
    </details>
</aside>
