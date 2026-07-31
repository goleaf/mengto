<x-page-stack data-section="social-relationships">
    <x-page-header
        eyebrow="{{ __('social_relationships.eyebrow') }}"
        title="{{ __('social_relationships.title') }}"
        description="{{ __('social_relationships.description') }}"
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
            class="forum-input min-h-11"
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
                class="forum-input min-h-11 w-full ps-11"
                maxlength="80"
                wire:model.live.debounce.400ms="actorSearch"
                placeholder="{{ __('social_relationships.fields.actor_search_placeholder') }}"
            >
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
                        <time class="text-sm text-paw-muted" datetime="{{ $request['sent_at'] }}">{{ $request['sent_at'] }}</time>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="forum-button forum-button--primary min-h-11"
                            wire:click="accept('{{ $request['key'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="accept('{{ $request['key'] }}')"
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
                        >
                            <x-lucide-x aria-hidden="true" />
                            <span>{{ __('social_relationships.actions.decline') }}</span>
                        </button>
                    </div>
                </div>
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

    <section aria-labelledby="social-settings-heading">
        <h2 id="social-settings-heading">{{ __('social_relationships.sections.settings') }}</h2>

        <form class="forum-form mt-4 grid gap-5" wire:submit="saveSettings">
            <div class="grid gap-5 md:grid-cols-2">
                <div class="grid gap-2">
                    <label for="friend-request-policy">{{ __('social_relationships.fields.friend_request_policy') }}</label>
                    <select id="friend-request-policy" class="forum-input min-h-11" wire:model="settingsForm.friendRequestPolicy">
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
                    <select id="follow-policy" class="forum-input min-h-11" wire:model="settingsForm.followPolicy">
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
                    <select id="friend-list-visibility" class="forum-input min-h-11" wire:model="settingsForm.friendListVisibility">
                        @forelse ($this->listVisibilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="friends">{{ __('social_relationships.list_visibility.friends') }}</option>
                        @endforelse
                    </select>
                </div>

                <div class="grid gap-2">
                    <label for="follower-list-visibility">{{ __('social_relationships.fields.follower_list_visibility') }}</label>
                    <select id="follower-list-visibility" class="forum-input min-h-11" wire:model="settingsForm.followerListVisibility">
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
