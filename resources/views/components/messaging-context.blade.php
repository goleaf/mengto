@props([
    'conversation',
    'context',
    'members',
    'poll',
    'tasks',
    'professional',
    'activeFilter',
    'messageQuery',
    'coverage',
])

<aside class="messaging-context" aria-label="Conversation information">
    <section class="messaging-context__identity">
        <img src="{{ $conversation['avatar'] }}" alt="" width="64" height="64">
        <div>
            <h2>{{ $conversation['name'] }}</h2>
            <p>{{ $conversation['purpose'] }}</p>
            <span><x-lucide-shield-check class="icon icon--sm" aria-hidden="true" /> {{ $conversation['privacy'] }}</span>
        </div>
    </section>

    <form method="GET" action="{{ route('messages.index') }}" class="messaging-context__search">
        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
        <input type="hidden" name="filter" value="{{ $activeFilter }}">
        <label for="message-history-search">Search this dialog</label>
        <div>
            <x-lucide-search class="icon icon--sm" aria-hidden="true" />
            <input id="message-history-search" type="search" name="message_q" value="{{ $messageQuery }}" placeholder="Text, sender, transcript">
        </div>
    </form>

    <div class="messaging-context__actions" aria-label="Conversation controls">
        @foreach ([
            ['action' => 'pin-conversation', 'icon' => 'pin', 'label' => $conversation['pinned'] ? 'Unpin' : 'Pin'],
            ['action' => 'mute-conversation', 'icon' => $conversation['muted'] ? 'bell' : 'bell-off', 'label' => $conversation['muted'] ? 'Unmute' : 'Mute'],
            ['action' => 'archive-conversation', 'icon' => 'archive', 'label' => $conversation['archived'] ? 'Restore' : 'Archive'],
            ['action' => 'mark-conversation-unread', 'icon' => 'mail', 'label' => 'Unread'],
        ] as $control)
            <form method="POST" action="{{ route('messages.actions') }}">
                @csrf
                <input type="hidden" name="action" value="{{ $control['action'] }}">
                <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
                <button type="submit">
                    <x-dynamic-component :component="'lucide-'.$control['icon']" class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $control['label'] }}</span>
                </button>
            </form>
        @endforeach
    </div>

    @if ($professional)
        <section class="messaging-context__section">
            <header><x-lucide-briefcase-medical class="icon icon--sm" /><h3>Professional case</h3></header>
            <dl>
                <div><dt>Status</dt><dd>{{ $professional['status'] }}</dd></div>
                <div><dt>Assigned</dt><dd>{{ $professional['assigned'] }}</dd></div>
                <div><dt>Hours</dt><dd>{{ $professional['hours'] }}</dd></div>
            </dl>
            <p>{{ $professional['privacy'] }}</p>
        </section>
    @endif

    @if ($poll)
        <section class="messaging-context__section">
            <header><x-lucide-list-checks class="icon icon--sm" /><h3>Group poll</h3></header>
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
                    <span>No active poll</span>
                @endforelse
            </div>
        </section>
    @endif

    @if ($tasks !== [])
        <section class="messaging-context__section">
            <header><x-lucide-list-todo class="icon icon--sm" /><h3>Shared tasks</h3></header>
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
                                <x-lucide-circle-check-big class="icon icon--sm" aria-label="Completed" />
                            @else
                                <x-lucide-circle class="icon icon--sm" aria-label="{{ str($task['status'])->headline() }}" />
                            @endif
                            <span><strong>{{ $task['label'] }}</strong><small>{{ $task['owner'] }} · {{ str($task['status'])->headline() }}</small></span>
                        </button>
                    </form>
                @empty
                    <span>No shared tasks</span>
                @endforelse
            </div>
        </section>
    @endif

    <details class="messaging-context__section" open>
        <summary><x-lucide-users-round class="icon icon--sm" /><strong>Members</strong><span>{{ $conversation['members'] }}</span></summary>
        <div class="messaging-members">
            @forelse ($members as $member)
                <div>
                    <span>{{ str($member['name'])->substr(0, 1) }}</span>
                    <p><strong>{{ $member['name'] }}</strong><small>{{ $member['role'] }} · {{ $member['pet'] }}</small></p>
                </div>
            @empty
                <p>Member list is hidden.</p>
            @endforelse
        </div>
    </details>

    <details class="messaging-context__section">
        <summary><x-lucide-folders class="icon icon--sm" /><strong>Shared content</strong></summary>
        <div class="messaging-shared-grid">
            @forelse ($context['shared_cards'] as $card)
                <button type="button">
                    <x-dynamic-component :component="'lucide-'.$card['icon']" class="icon icon--sm" />
                    <span><strong>{{ $card['label'] }}</strong><small>{{ $card['value'] }}</small></span>
                </button>
            @empty
                <p>No shared content.</p>
            @endforelse
        </div>
    </details>

    <details class="messaging-context__section">
        <summary><x-lucide-shield-alert class="icon icon--sm" /><strong>Safety and privacy</strong></summary>
        <div class="messaging-safety">
            @forelse ($context['safety'] as $item)
                <div>
                    <x-dynamic-component :component="'lucide-'.$item['icon']" class="icon icon--sm" />
                    <p><strong>{{ $item['title'] }}</strong><span>{{ $item['description'] }}</span></p>
                </div>
            @empty
                <p>Safety guidance unavailable.</p>
            @endforelse
        </div>

        <div class="messaging-danger-actions">
            @foreach ([
                ['action' => 'restrict-conversation', 'label' => $conversation['restricted'] ? 'Remove restriction' : 'Restrict', 'icon' => 'shield-minus'],
                ['action' => 'block-conversation', 'label' => $conversation['blocked'] ? 'Unblock' : 'Block', 'icon' => 'ban'],
                ['action' => 'export-conversation', 'label' => 'Export my data', 'icon' => 'download'],
            ] as $safetyAction)
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="{{ $safetyAction['action'] }}">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <button type="submit"><x-dynamic-component :component="'lucide-'.$safetyAction['icon']" class="icon icon--sm" /> {{ $safetyAction['label'] }}</button>
                </form>
            @endforeach
        </div>
    </details>

    <details class="messaging-context__section messaging-context__section--boundary">
        <summary><x-lucide-layers-3 class="icon icon--sm" /><strong>Delivery boundary</strong></summary>
        <dl>
            @forelse ($coverage as $item)
                <div><dt>{{ $item['label'] }}</dt><dd>{{ $item['value'] }}</dd></div>
            @empty
                <div><dt>Status</dt><dd>Unavailable</dd></div>
            @endforelse
        </dl>
    </details>
</aside>
