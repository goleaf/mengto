<x-app-shell title="{{ __('messaging.page.browser_title') }}" active-section="messages">
    <div class="messaging-page" data-messaging-center>
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

            <section class="messaging-thread messaging-state messaging-state--quiet" aria-labelledby="empty-inbox-title">
                <x-ui-icon name="inbox" />
                <div>
                    <h2 id="empty-inbox-title">{{ __('messaging.inbox.empty_title') }}</h2>
                    <p>{{ __('messaging.inbox.empty_description') }}</p>
                </div>
            </section>
        </div>
    </div>
</x-app-shell>
