<x-app-shell :owner="$owner" title="{{ __('ui.messages_and_calls_brand_d76656782d') }}" active-section="messages">
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

            <div class="messaging-page__summary" aria-label="{{ __('ui.inbox_summary_4323d5eb2a') }}">
                <span><x-lucide-mail class="icon icon--sm" aria-hidden="true" /> {{ __('presentation.unread_count', ['count' => $summary['unread_count']]) }}</span>
                <span><x-lucide-message-square-more class="icon icon--sm" aria-hidden="true" /> {{ trans_choice('presentation.requests_count', $summary['request_count'], ['count' => $summary['request_count']]) }}</span>
                <a href="{{ route('compose', 'message') }}" class="action action--primary action--regular">
                    <x-lucide-square-pen class="icon icon--sm" aria-hidden="true" />
                    <span>{{ __('ui.new_message_78f5975a5d') }}</span>
                </a>
            </div>
        </header>

        <x-messaging-folders
            :filters="$filters"
            :active-filter="$active_filter"
            :query="$query"
        />

        <div class="messaging-shell">
            <x-messaging-inbox
                :conversations="$conversations"
                :active-filter="$active_filter"
                :query="$query"
                :selected="$selected"
                :summary="$summary"
            />

            <section class="messaging-thread" aria-label="{{ __('presentation.conversation_with', ['name' => $selected['name']]) }}">
                <x-messaging-thread-header
                    :conversation="$selected"
                    :active-filter="$active_filter"
                />

                @if ($selected['request'] && $selected['request_status'] === 'pending')
                    <x-messaging-request :conversation="$selected" />
                @elseif ($selected['request_status'] === 'declined')
                    <section class="messaging-state messaging-state--quiet">
                        <x-lucide-message-square-off class="icon" aria-hidden="true" />
                        <div>
                            <h2>{{ __('ui.request_declined_1df48b2da0') }}</h2>
                            <p>{{ __('ui.the_sender_is_not_told_when_you_viewed_b9db847ee0') }}</p>
                        </div>
                    </section>
                @else
                    @if ($professional)
                        <x-messaging-professional-banner :professional="$professional" />
                    @endif

                    @if ($channels !== [])
                        <x-messaging-channels
                            :channels="$channels"
                            :active-channel="$active_channel"
                            :conversation="$selected['key']"
                            :active-filter="$active_filter"
                        />
                    @endif

                    @if ($message_query !== '')
                        <div class="messaging-search-result" role="status">
                            <x-lucide-search class="icon icon--sm" aria-hidden="true" />
                            <span>{{ __('presentation.search_results_context', ['count' => count($messages), 'query' => $message_query]) }}</span>
                            <a href="{{ route('messages.index', ['conversation' => $selected['key'], 'filter' => $active_filter]) }}">{{ __('ui.clear_83b12c2216') }}</a>
                        </div>
                    @endif

                    <x-messaging-message-list
                        :messages="$messages"
                        :conversation="$selected"
                    />

                    <x-messaging-composer
                        :conversation="$selected"
                        :active-filter="$active_filter"
                    />
                @endif
            </section>

            <x-messaging-context
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

        <x-messaging-call-stage
            :conversation="$selected"
            :call="$call"
            :boundary="$call_boundary"
            :active-filter="$active_filter"
        />
    </div>
</x-app-shell>
