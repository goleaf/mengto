<x-app-shell :owner="$owner" title="{{ __('messaging.page.browser_title') }}" active-section="messages">
    <div
        class="messaging-page"
        data-messaging-center
        @if ($thread_first) data-selected-conversation="{{ $selected['key'] }}" @endif
    >
        <x-page-header
            :eyebrow="$summary['eyebrow']"
            :title="$summary['title']"
            :description="$summary['description']"
            heading-id="messages-heading"
            :meta-label="__('messaging.page.meta_label')"
            data-section="messages-header"
            class="page-header--messaging"
        >
            <x-slot:meta>
                <span class="page-header__metric">
                    <x-ui-icon name="mail" size="sm" />
                    {{ __('presentation.unread_count', ['count' => $summary['unread_count']]) }}
                </span>
                <span class="page-header__metric">
                    <x-ui-icon name="message-square-more" size="sm" />
                    {{ trans_choice('presentation.requests_count', $summary['request_count'], ['count' => $summary['request_count']]) }}
                </span>
            </x-slot:meta>

            <x-slot:actions>
                <x-action-control
                    :href="route('compose', 'message')"
                    label="{{ __('messaging.page.new_message') }}"
                    icon="square-pen"
                    variant="primary"
                    size="regular"
                />
            </x-slot:actions>
        </x-page-header>

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
                        <x-ui-icon name="message-square-off" />
                        <div>
                            <h2>{{ __('messaging.page.declined_title') }}</h2>
                            <p>{{ __('messaging.page.declined_description') }}</p>
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
                            <x-ui-icon name="search" size="sm" />
                            <span>{{ __('presentation.search_results_context', ['count' => count($messages), 'query' => $message_query]) }}</span>
                            <a href="{{ route('messages.index', ['conversation' => $selected['key'], 'filter' => $active_filter]) }}" class="inline-flex items-center gap-1">
                                <x-ui-icon name="rotate-ccw" size="xs" />
                                <span>{{ __('messaging.page.clear_search') }}</span>
                            </a>
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
