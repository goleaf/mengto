<x-page-stack data-section="social-relationships">
    <x-page-header
        eyebrow="{{ __('social_relationships.eyebrow') }}"
        title="{{ __('social_relationships.title') }}"
        description="{{ __('social_relationships.description') }}"
        heading-id="relationship-center-heading"
        action-label="{{ __('social_relationships.actions.back_to_circle') }}"
        action-icon="arrow-left"
        :action-href="route('circle.index')"
    />

    @if ($feedback !== '')
        <p class="border-s-4 border-status-success py-3 ps-4" role="status" aria-live="polite">
            {{ $feedback }}
        </p>
    @endif

    <div class="forum-form grid gap-3 sm:max-w-xl">
        <label for="social-actor" class="font-semibold">{{ __('social_relationships.actor') }}</label>
        <select
            id="social-actor"
            class="field"
            wire:change="selectActor($event.target.value)"
        >
            @forelse ($this->availableActors as $actor)
                <option value="{{ $actor['key'] }}" @selected($selectedActorKey === $actor['key'])>
                    {{ $actor['name'] }} · {{ $actor['type_label'] }}
                </option>
            @empty
                <option value="">{{ __('social_relationships.deleted_actor') }}</option>
            @endforelse
        </select>
    </div>

    <ul class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-paw-muted" aria-label="{{ __('social_relationships.title') }}">
        <li>{{ __('social_relationships.counts.relationships', ['count' => $this->counts['relationships']]) }}</li>
        <li>{{ __('social_relationships.counts.incoming', ['count' => $this->counts['incoming_requests']]) }}</li>
        <li>{{ __('social_relationships.counts.outgoing', ['count' => $this->counts['outgoing_requests']]) }}</li>
    </ul>

    <section class="grid gap-4" aria-labelledby="social-directory-heading">
        <h2 id="social-directory-heading">{{ __('social_relationships.sections.directory') }}</h2>

        <label for="social-actor-search" class="font-semibold">
            {{ __('social_relationships.fields.actor_search') }}
        </label>
        <div class="relative sm:max-w-xl">
            <x-lucide-search class="pointer-events-none absolute start-3 top-3 size-5 text-paw-muted" aria-hidden="true" />
            <input
                id="social-actor-search"
                type="search"
                class="field field--with-icon"
                maxlength="80"
                wire:model.live.debounce.400ms="actorSearch"
                placeholder="{{ __('social_relationships.fields.actor_search_placeholder') }}"
            >
        </div>

        <div class="grid gap-2 sm:max-w-xl">
            <label for="social-request-message" class="font-semibold">
                {{ __('social_relationships.fields.request_message') }}
            </label>
            <textarea
                id="social-request-message"
                class="field field--textarea"
                maxlength="240"
                rows="3"
                wire:model="requestMessage"
            ></textarea>
            @error('message') <p class="text-status-danger" role="alert">{{ $message }}</p> @enderror
        </div>

        @if ($this->directorySearchReady)
            <div class="grid gap-3 lg:grid-cols-2">
                @forelse ($this->directoryResults as $actor)
                    <article class="forum-form" wire:key="social-directory-{{ $actor['key'] }}">
                        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start">
                            <div class="min-w-0">
                                <h3 class="break-words">{{ $actor['name'] }}</h3>
                                <p class="text-sm text-paw-muted">{{ $actor['type_label'] }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if ($actor['can_follow'])
                                    <button
                                        type="button"
                                        class="forum-button min-h-11"
                                        wire:click="followActor('{{ $actor['key'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="followActor('{{ $actor['key'] }}')"
                                    >
                                        <x-lucide-user-plus aria-hidden="true" />
                                        <span>{{ __('social_relationships.actions.follow') }}</span>
                                    </button>
                                @endif
                                @if ($actor['friendship_type'] !== null)
                                    <button
                                        type="button"
                                        class="forum-button forum-button--primary min-h-11"
                                        wire:click="requestFriendship('{{ $actor['key'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="requestFriendship('{{ $actor['key'] }}')"
                                    >
                                        <x-lucide-users aria-hidden="true" />
                                        <span>{{ __('social_relationships.actions.request_friendship') }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="forum-form lg:col-span-2">{{ __('social_relationships.empty.directory') }}</p>
                @endforelse
            </div>
        @endif
    </section>

    <section class="grid gap-4" aria-labelledby="social-requests-heading">
        <h2 id="social-requests-heading">{{ __('social_relationships.sections.requests') }}</h2>

        @forelse ($this->inbox as $request)
            <article class="forum-form" wire:key="social-inbox-{{ $request['key'] }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="break-words">{{ $request['actor']['name'] }}</h3>
                        <p>{{ $request['type'] }} · {{ $request['status'] }}</p>
                        @if ($request['message'] !== null)
                            <p class="mt-2 max-w-prose whitespace-pre-line break-words">{{ $request['message'] }}</p>
                        @endif
                        <time class="text-sm text-paw-muted" datetime="{{ $request['sent_at'] }}">{{ $request['sent_at'] }}</time>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="forum-button forum-button--primary min-h-11"
                            wire:click="accept('{{ $request['key'] }}')"
                            wire:confirm="{{ __('social_relationships.confirm.accept') }}"
                            wire:loading.attr="disabled"
                            wire:target="accept('{{ $request['key'] }}')"
                            aria-label="{{ __('social_relationships.actions.accept_request', ['name' => $request['actor']['name']]) }}"
                        >
                            <x-lucide-check aria-hidden="true" />
                            <span>{{ __('social_relationships.actions.accept') }}</span>
                        </button>
                        <button
                            type="button"
                            class="forum-button min-h-11"
                            wire:click="decline('{{ $request['key'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="decline('{{ $request['key'] }}')"
                            aria-label="{{ __('social_relationships.actions.decline_request', ['name' => $request['actor']['name']]) }}"
                        >
                            <x-lucide-x aria-hidden="true" />
                            <span>{{ __('social_relationships.actions.decline') }}</span>
                        </button>
                        <button
                            type="button"
                            class="forum-button min-h-11"
                            wire:click="declineAndPrevent('{{ $request['key'] }}')"
                            wire:confirm="{{ __('social_relationships.confirm.prevent_repeats') }}"
                            wire:loading.attr="disabled"
                            wire:target="declineAndPrevent('{{ $request['key'] }}')"
                            aria-label="{{ __('social_relationships.actions.prevent_request', ['name' => $request['actor']['name']]) }}"
                        >
                            <x-lucide-user-x aria-hidden="true" />
                            <span>{{ __('social_relationships.actions.decline_and_prevent') }}</span>
                        </button>
                        <button
                            type="button"
                            class="forum-button min-h-11"
                            wire:click="blockIncomingAccount('{{ $request['key'] }}')"
                            wire:confirm="{{ __('social_relationships.confirm.block_account') }}"
                            wire:loading.attr="disabled"
                            wire:target="blockIncomingAccount('{{ $request['key'] }}')"
                            aria-label="{{ __('social_relationships.actions.block_account_named', ['name' => $request['actor']['name']]) }}"
                        >
                            <x-lucide-ban aria-hidden="true" />
                            <span>{{ __('social_relationships.actions.block_account') }}</span>
                        </button>
                        <button
                            type="button"
                            class="forum-button min-h-11"
                            wire:click="startReport('{{ $request['key'] }}')"
                            aria-label="{{ __('social_relationships.actions.report_request', ['name' => $request['actor']['name']]) }}"
                        >
                            <x-lucide-flag aria-hidden="true" />
                            <span>{{ __('social_relationships.actions.report') }}</span>
                        </button>
                    </div>
                </div>

                @if ($reportForm->requestKey === $request['key'])
                    <form class="mt-5 grid gap-4 border-t border-paw-border pt-5" wire:submit="submitReport">
                        <div class="grid gap-2">
                            <label for="social-report-reason-{{ $request['key'] }}">
                                {{ __('social_relationships.fields.report_reason') }}
                            </label>
                            <select
                                id="social-report-reason-{{ $request['key'] }}"
                                class="field"
                                wire:model="reportForm.reason"
                            >
                                @forelse ($this->reportReasons as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @empty
                                    <option value="unwanted-contact">{{ __('social_relationships.report_reasons.unwanted-contact') }}</option>
                                @endforelse
                            </select>
                            @error('reportForm.reason') <p class="text-status-danger" role="alert">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-2">
                            <label for="social-report-details-{{ $request['key'] }}">
                                {{ __('social_relationships.fields.report_details') }}
                            </label>
                            <textarea
                                id="social-report-details-{{ $request['key'] }}"
                                class="field field--textarea"
                                maxlength="2000"
                                rows="4"
                                wire:model="reportForm.details"
                            ></textarea>
                            @error('reportForm.details') <p class="text-status-danger" role="alert">{{ $message }}</p> @enderror
                        </div>

                        <label class="forum-form__check">
                            <input type="checkbox" wire:model="reportForm.blockAccount">
                            <span>{{ __('social_relationships.fields.report_and_block_account') }}</span>
                        </label>
                        <label class="forum-form__check">
                            <input type="checkbox" wire:model="reportForm.truthfulnessConfirmed">
                            <span>{{ __('social_relationships.fields.report_truthfulness') }}</span>
                        </label>
                        @error('reportForm.truthfulnessConfirmed') <p class="text-status-danger" role="alert">{{ $message }}</p> @enderror

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="forum-button forum-button--primary min-h-11" wire:loading.attr="disabled">
                                <x-lucide-flag aria-hidden="true" />
                                <span>{{ __('social_relationships.actions.submit_report') }}</span>
                            </button>
                            <button type="button" class="forum-button min-h-11" wire:click="cancelReport">
                                <x-lucide-x aria-hidden="true" />
                                <span>{{ __('social_relationships.actions.cancel_report') }}</span>
                            </button>
                        </div>
                    </form>
                @endif
            </article>
        @empty
            <p class="forum-form">{{ __('social_relationships.empty.requests') }}</p>
        @endforelse
    </section>

    <section class="grid gap-4" aria-labelledby="social-outgoing-heading">
        <h2 id="social-outgoing-heading">{{ __('social_relationships.sections.outgoing') }}</h2>

        @forelse ($this->outbox as $request)
            <article class="forum-form" wire:key="social-outbox-{{ $request['key'] }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="break-words">{{ $request['actor']['name'] }}</h3>
                        <p>{{ $request['type'] }} · {{ $request['status'] }}</p>
                    </div>
                    <button
                        type="button"
                        class="forum-button min-h-11"
                        wire:click="cancelRequest('{{ $request['key'] }}')"
                        wire:loading.attr="disabled"
                        wire:target="cancelRequest('{{ $request['key'] }}')"
                    >
                        <x-lucide-x aria-hidden="true" />
                        <span>{{ __('social_relationships.actions.cancel') }}</span>
                    </button>
                </div>
            </article>
        @empty
            <p class="forum-form">{{ __('social_relationships.empty.outgoing') }}</p>
        @endforelse
    </section>

    <section class="grid gap-4" aria-labelledby="social-relationships-heading">
        <h2 id="social-relationships-heading">{{ __('social_relationships.sections.relationships') }}</h2>

        @forelse ($this->relationships as $relationship)
            <article class="forum-form" wire:key="social-relationship-{{ $relationship['key'] }}">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-start">
                    <div class="min-w-0">
                        <h3 class="break-words">{{ $relationship['actor']['name'] }}</h3>
                        <p>{{ $relationship['type'] }} · {{ $relationship['status'] }}</p>
                        <time class="text-sm text-paw-muted" datetime="{{ $relationship['started_at'] }}">{{ $relationship['started_at'] }}</time>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($relationship['can_control'])
                            <button
                                type="button"
                                class="forum-button min-h-11"
                                wire:click="applyControl('{{ $relationship['key'] }}', 'mute')"
                                title="{{ __('social_relationships.actions.mute') }}"
                            >
                                <x-lucide-eye-off aria-hidden="true" />
                                <span>{{ __('social_relationships.actions.mute') }}</span>
                            </button>
                            <button
                                type="button"
                                class="forum-button min-h-11"
                                wire:click="applyControl('{{ $relationship['key'] }}', 'restrict')"
                                title="{{ __('social_relationships.actions.restrict') }}"
                            >
                                <x-lucide-shield-alert aria-hidden="true" />
                                <span>{{ __('social_relationships.actions.restrict') }}</span>
                            </button>
                            <button
                                type="button"
                                class="forum-button min-h-11"
                                wire:click="applyControl('{{ $relationship['key'] }}', 'block')"
                                wire:confirm="{{ __('social_relationships.actions.block') }}?"
                            >
                                <x-lucide-ban aria-hidden="true" />
                                <span>{{ __('social_relationships.actions.block') }}</span>
                            </button>
                        @endif
                        <button
                            type="button"
                            class="forum-button min-h-11"
                            wire:click="endRelationship('{{ $relationship['key'] }}')"
                            wire:confirm="{{ __('social_relationships.actions.end') }}?"
                        >
                            <x-lucide-user-minus aria-hidden="true" />
                            <span>{{ __('social_relationships.actions.end') }}</span>
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <p class="forum-form">{{ __('social_relationships.empty.relationships') }}</p>
        @endforelse
    </section>

    <section class="grid gap-4" aria-labelledby="social-account-blocks-heading">
        <h2 id="social-account-blocks-heading">{{ __('social_relationships.sections.account_blocks') }}</h2>

        @forelse ($this->accountBlocks as $block)
            <article class="forum-form" wire:key="social-account-block-{{ $block['key'] }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="break-words">{{ $block['name'] }}</h3>
                        <time class="text-sm text-paw-muted" datetime="{{ $block['blocked_at'] }}">{{ $block['blocked_at'] }}</time>
                    </div>
                    <button
                        type="button"
                        class="forum-button min-h-11"
                        wire:click="revokeBlock('{{ $block['key'] }}')"
                        wire:confirm="{{ __('social_relationships.confirm.revoke_account_block') }}"
                    >
                        <x-lucide-rotate-ccw aria-hidden="true" />
                        <span>{{ __('social_relationships.actions.revoke_account_block') }}</span>
                    </button>
                </div>
            </article>
        @empty
            <p class="forum-form">{{ __('social_relationships.empty.account_blocks') }}</p>
        @endforelse
    </section>

    <section aria-labelledby="social-settings-heading">
        <h2 id="social-settings-heading">{{ __('social_relationships.sections.settings') }}</h2>

        <form class="forum-form mt-4 grid gap-5" wire:submit="saveSettings">
            <div class="grid gap-5 md:grid-cols-2">
                <div class="grid gap-2">
                    <label for="friend-request-policy">{{ __('social_relationships.fields.friend_request_policy') }}</label>
                    <select id="friend-request-policy" class="field" wire:model="settingsForm.friendRequestPolicy">
                        @forelse ($this->friendRequestPolicies as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="everyone">{{ __('social_relationships.friend_request_policies.everyone') }}</option>
                        @endforelse
                    </select>
                    @error('settingsForm.friendRequestPolicy') <p class="text-status-danger" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-2">
                    <label for="follow-policy">{{ __('social_relationships.fields.follow_policy') }}</label>
                    <select id="follow-policy" class="field" wire:model="settingsForm.followPolicy">
                        @forelse ($this->followPolicies as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="public">{{ __('social_relationships.follow_policies.public') }}</option>
                        @endforelse
                    </select>
                    @error('settingsForm.followPolicy') <p class="text-status-danger" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-2">
                    <label for="friend-list-visibility">{{ __('social_relationships.fields.friend_list_visibility') }}</label>
                    <select id="friend-list-visibility" class="field" wire:model="settingsForm.friendListVisibility">
                        @forelse ($this->listVisibilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="friends">{{ __('social_relationships.list_visibility.friends') }}</option>
                        @endforelse
                    </select>
                </div>

                <div class="grid gap-2">
                    <label for="follower-list-visibility">{{ __('social_relationships.fields.follower_list_visibility') }}</label>
                    <select id="follower-list-visibility" class="field" wire:model="settingsForm.followerListVisibility">
                        @forelse ($this->listVisibilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="count-only">{{ __('social_relationships.list_visibility.count-only') }}</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="settingsForm.isRecommendable">
                    <span>{{ __('social_relationships.fields.is_recommendable') }}</span>
                </label>
                <label class="forum-form__check">
                    <input type="checkbox" wire:model="settingsForm.allowMessageRequests">
                    <span>{{ __('social_relationships.fields.allow_message_requests') }}</span>
                </label>
            </div>

            <button type="submit" class="forum-button forum-button--primary min-h-11 justify-self-start" wire:loading.attr="disabled">
                <x-lucide-save aria-hidden="true" />
                <span>{{ __('social_relationships.actions.save_settings') }}</span>
            </button>
        </form>
    </section>
</x-page-stack>
