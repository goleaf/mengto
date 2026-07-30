<x-layout.app-shell :owner="$owner" title="Messages and calls | PawCircle" active-section="messages">
    <div
        class="messaging-page"
        data-messaging-center
        @if ($thread_first) data-selected-conversation="{{ $selected['key'] }}" @endif
    >
        <header class="messaging-page__header">
            <div class="messaging-page__heading">
                <p>{{ $summary['eyebrow'] }}</p>
                <h1>{{ $summary['title'] }}</h1>
                <span>{{ $summary['description'] }}</span>
            </div>

            <div class="messaging-page__summary" aria-label="Inbox summary">
                <span><x-lucide-mail class="icon icon--sm" aria-hidden="true" /> {{ $summary['unread_count'] }} unread</span>
                <span><x-lucide-message-square-more class="icon icon--sm" aria-hidden="true" /> {{ $summary['request_count'] }} request</span>
                <a href="{{ route('compose', 'message') }}" class="action action--primary action--regular">
                    <x-lucide-square-pen class="icon icon--sm" aria-hidden="true" />
                    <span>New message</span>
                </a>
            </div>
        </header>

        <div class="messaging-shell">
            <x-feature.messaging-inbox
                :conversations="$conversations"
                :filters="$filters"
                :active-filter="$active_filter"
                :query="$query"
                :selected="$selected"
                :summary="$summary"
            />

            <main class="messaging-thread" aria-label="Conversation with {{ $selected['name'] }}">
                <x-feature.messaging-thread-header
                    :conversation="$selected"
                    :active-filter="$active_filter"
                />

                @if ($selected['request'] && $selected['request_status'] === 'pending')
                    <x-feature.messaging-request :conversation="$selected" />
                @elseif ($selected['request_status'] === 'declined')
                    <section class="messaging-state messaging-state--quiet">
                        <x-lucide-message-square-off class="icon" aria-hidden="true" />
                        <div>
                            <h2>Request declined</h2>
                            <p>The sender is not told when you viewed or declined the request. You can still block or report the profile.</p>
                        </div>
                    </section>
                @else
                    @if ($professional)
                        <x-feature.messaging-professional-banner :professional="$professional" />
                    @endif

                    @if ($channels !== [])
                        <x-feature.messaging-channels
                            :channels="$channels"
                            :active-channel="$active_channel"
                            :conversation="$selected['key']"
                            :active-filter="$active_filter"
                        />
                    @endif

                    @if ($message_query !== '')
                        <div class="messaging-search-result" role="status">
                            <x-lucide-search class="icon icon--sm" aria-hidden="true" />
                            <span>{{ count($messages) }} results for “{{ $message_query }}” with surrounding context preserved.</span>
                            <a href="{{ route('messages.index', ['conversation' => $selected['key'], 'filter' => $active_filter]) }}">Clear</a>
                        </div>
                    @endif

                    <x-feature.messaging-message-list
                        :messages="$messages"
                        :conversation="$selected"
                    />

                    <x-feature.messaging-composer
                        :conversation="$selected"
                        :active-filter="$active_filter"
                    />
                @endif
            </main>

            <x-feature.messaging-context
                :conversation="$selected"
                :context="$context"
                :members="$members"
                :poll="$poll"
                :tasks="$tasks"
                :professional="$professional"
                :active-filter="$active_filter"
                :message-query="$message_query"
                :coverage="$coverage"
            />
        </div>

        <x-feature.messaging-call-stage
            :conversation="$selected"
            :call="$call"
            :boundary="$call_boundary"
            :active-filter="$active_filter"
        />
    </div>
</x-layout.app-shell>
