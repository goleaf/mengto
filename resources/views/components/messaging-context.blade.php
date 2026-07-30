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

<aside class="messaging-context" aria-label="{{ __('ui.conversation_information_df634b408e') }}">
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
        <label for="message-history-search">{{ __('ui.search_this_dialog_ce86abd45e') }}</label>
        <div>
            <x-lucide-search class="icon icon--sm" aria-hidden="true" />
            <input id="message-history-search" type="search" name="message_q" value="{{ $messageQuery }}" placeholder="{{ __('ui.text_sender_transcript_0b449170d2') }}">
        </div>
    </form>

    <div class="messaging-context__actions" aria-label="{{ __('ui.conversation_controls_5d0a3948ed') }}">
        @foreach ([
            ['action' => 'pin-conversation', 'icon' => 'pin', 'label' => $conversation['pinned'] ? __('ui.unpin_ee3c716130') : __('ui.pin_ff1cee7441')],
            ['action' => 'mute-conversation', 'icon' => $conversation['muted'] ? 'bell' : 'bell-off', 'label' => $conversation['muted'] ? __('ui.unmute_ce4ee4efc5') : __('ui.mute_8dd6857baf')],
            ['action' => 'archive-conversation', 'icon' => 'archive', 'label' => $conversation['archived'] ? __('ui.restore_a76e13b983') : __('ui.archive_66f4804ee2')],
            ['action' => 'mark-conversation-unread', 'icon' => 'mail', 'label' => __('ui.unread_1b9f384c14')],
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
            <header><x-lucide-briefcase-medical class="icon icon--sm" /><h3>{{ __('ui.professional_case_422c23181c') }}</h3></header>
            <dl>
                <div><dt>{{ __('ui.status_920e413c7d') }}</dt><dd>{{ $professional['status'] }}</dd></div>
                <div><dt>{{ __('ui.assigned_8191888dd9') }}</dt><dd>{{ $professional['assigned'] }}</dd></div>
                <div><dt>{{ __('ui.hours_21e8492938') }}</dt><dd>{{ $professional['hours'] }}</dd></div>
            </dl>
            <p>{{ $professional['privacy'] }}</p>
        </section>
    @endif

    @if ($poll)
        <section class="messaging-context__section">
            <header><x-lucide-list-checks class="icon icon--sm" /><h3>{{ __('ui.group_poll_a5105be25a') }}</h3></header>
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
                    <span>{{ __('ui.no_active_poll_166acb5b2c') }}</span>
                @endforelse
            </div>
        </section>
    @endif

    @if ($tasks !== [])
        <section class="messaging-context__section">
            <header><x-lucide-list-todo class="icon icon--sm" /><h3>{{ __('ui.shared_tasks_d8ed38476f') }}</h3></header>
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
                                <x-lucide-circle-check-big class="icon icon--sm" aria-label="{{ __('ui.completed_22a970d2e5') }}" />
                            @else
                                <x-lucide-circle class="icon icon--sm" aria-label="{{ $task['status_label'] }}" />
                            @endif
                            <span><strong>{{ $task['label'] }}</strong><small>{{ $task['owner'] }} · {{ $task['status_label'] }}</small></span>
                        </button>
                    </form>
                @empty
                    <span>{{ __('ui.no_shared_tasks_1973f60923') }}</span>
                @endforelse
            </div>
        </section>
    @endif

    <details class="messaging-context__section" open>
        <summary><x-lucide-users-round class="icon icon--sm" /><strong>{{ __('ui.members_1044a4c056') }}</strong><span>{{ $conversation['members'] }}</span></summary>
        <div class="messaging-members">
            @forelse ($members as $member)
                <div>
                    <span>{{ $member['initial'] }}</span>
                    <p><strong>{{ $member['name'] }}</strong><small>{{ $member['role'] }} · {{ $member['pet'] }}</small></p>
                </div>
            @empty
                <p>{{ __('ui.member_list_is_hidden_1ac12fc8f4') }}</p>
            @endforelse
        </div>
    </details>

    <details class="messaging-context__section">
        <summary><x-lucide-folders class="icon icon--sm" /><strong>{{ __('ui.shared_content_c42aceea28') }}</strong></summary>
        <div class="messaging-shared-grid">
            @forelse ($context['shared_cards'] as $card)
                <button type="button">
                    <x-dynamic-component :component="'lucide-'.$card['icon']" class="icon icon--sm" />
                    <span><strong>{{ $card['label'] }}</strong><small>{{ $card['value'] }}</small></span>
                </button>
            @empty
                <p>{{ __('ui.no_shared_content_17da1ee21d') }}</p>
            @endforelse
        </div>
    </details>

    <details class="messaging-context__section">
        <summary><x-lucide-shield-alert class="icon icon--sm" /><strong>{{ __('ui.safety_and_privacy_87d672f087') }}</strong></summary>
        <div class="messaging-safety">
            @forelse ($context['safety'] as $item)
                <div>
                    <x-dynamic-component :component="'lucide-'.$item['icon']" class="icon icon--sm" />
                    <p><strong>{{ $item['title'] }}</strong><span>{{ $item['description'] }}</span></p>
                </div>
            @empty
                <p>{{ __('ui.safety_guidance_unavailable_71dcc6b999') }}</p>
            @endforelse
        </div>

        <div class="messaging-danger-actions">
            @foreach ([
                ['action' => 'restrict-conversation', 'label' => $conversation['restricted'] ? __('ui.remove_restriction_8c3017834e') : __('ui.restrict_26f2f6e68e'), 'icon' => 'shield-minus'],
                ['action' => 'block-conversation', 'label' => $conversation['blocked'] ? __('ui.unblock_712da63171') : __('ui.block_211d0bb8cf'), 'icon' => 'ban'],
                ['action' => 'export-conversation', 'label' => __('ui.export_my_data_fddb7f9c69'), 'icon' => 'download'],
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
        <summary><x-lucide-layers-3 class="icon icon--sm" /><strong>{{ __('ui.delivery_boundary_715f18a1dd') }}</strong></summary>
        <dl>
            @forelse ($coverage as $item)
                <div><dt>{{ $item['label'] }}</dt><dd>{{ $item['value'] }}</dd></div>
            @empty
                <div><dt>{{ __('ui.status_920e413c7d') }}</dt><dd>{{ __('ui.unavailable_ca18449697') }}</dd></div>
            @endforelse
        </dl>
    </details>
</aside>
