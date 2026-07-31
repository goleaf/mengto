<div class="forum-page">
    <header class="forum-header">
        <div class="forum-header__copy">
            <p class="forum-header__eyebrow">{{ __('forum_admin.eyebrow') }}</p>
            <h1>{{ __('forum_admin.title') }}</h1>
            <p>{{ __('forum_admin.summary') }}</p>
        </div>
        <button type="button" wire:click="invalidateCategoryCaches" wire:loading.attr="disabled" class="forum-button">
            <x-lucide-refresh-cw aria-hidden="true" />
            <span wire:loading.remove wire:target="invalidateCategoryCaches">{{ __('forum_admin.actions.invalidate_cache') }}</span>
            <span wire:loading wire:target="invalidateCategoryCaches">{{ __('forum_admin.actions.working') }}</span>
        </button>
    </header>

    @if (session('feedback'))
        <p role="status" aria-live="polite" class="mb-4 rounded-md border border-status-success bg-paw-paper px-4 py-3">
            {{ session('feedback') }}
        </p>
    @endif

    <nav class="forum-filter-tabs" aria-label="{{ __('forum_admin.tabs.label') }}">
        <button type="button" wire:click="$set('tab', 'categories')" @if ($tab === 'categories') aria-current="page" @endif>
            {{ __('forum_admin.tabs.categories') }}
        </button>
        <button type="button" wire:click="$set('tab', 'guides')" @if ($tab === 'guides') aria-current="page" @endif>
            {{ __('forum_admin.tabs.guides') }}
        </button>
        <button type="button" wire:click="$set('tab', 'taxonomy')" @if ($tab === 'taxonomy') aria-current="page" @endif>
            {{ __('forum_admin.tabs.taxonomy') }}
        </button>
        <button type="button" wire:click="$set('tab', 'verification')" @if ($tab === 'verification') aria-current="page" @endif>
            {{ __('forum_admin.tabs.verification') }}
        </button>
        <button type="button" wire:click="$set('tab', 'moderation')" @if ($tab === 'moderation') aria-current="page" @endif>
            {{ __('forum_admin.tabs.moderation') }}
        </button>
    </nav>

    @if ($tab === 'categories')
        <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,24rem)]">
            <section aria-labelledby="admin-categories-heading">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <h2 id="admin-categories-heading" class="text-xl font-semibold">{{ __('forum_admin.categories.heading') }}</h2>
                    <label>
                        <span class="sr-only">{{ __('forum_admin.categories.search') }}</span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('forum_admin.categories.search') }}" class="min-h-11 rounded-md border border-paw-line bg-white px-3 py-2">
                    </label>
                </div>
                <div class="overflow-x-auto rounded-md border border-paw-line bg-white">
                    <table class="w-full min-w-[44rem] text-start text-sm">
                        <thead class="bg-paw-paper">
                            <tr>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.categories.name') }}</th>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.categories.key') }}</th>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.categories.visibility') }}</th>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.categories.owner') }}</th>
                                <th scope="col" class="px-3 py-2"><span class="sr-only">{{ __('forum_admin.actions.edit') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->categories as $category)
                                <tr wire:key="admin-category-{{ $category['id'] }}" class="border-t border-paw-line">
                                    <td class="px-3 py-2 font-medium">{{ $category['name'] }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $category['stable_key'] }}</td>
                                    <td class="px-3 py-2">{{ $category['visibility'] }}</td>
                                    <td class="px-3 py-2">{{ $category['is_system_managed'] ? __('forum_admin.categories.system') : __('forum_admin.categories.administrator') }}</td>
                                    <td class="px-3 py-2 text-end">
                                        <button type="button" wire:click="selectCategory({{ $category['id'] }})" class="forum-button" aria-label="{{ __('forum_admin.actions.edit_named', ['name' => $category['name']]) }}">
                                            <x-lucide-pencil aria-hidden="true" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-paw-muted">{{ __('forum_admin.categories.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section aria-labelledby="category-editor-heading" class="border-s border-paw-line ps-5">
                <h2 id="category-editor-heading" class="text-xl font-semibold">{{ __('forum_admin.categories.editor') }}</h2>
                @if ($selectedCategoryId !== null)
                    <form wire:submit="saveCategory" class="forum-form mt-4">
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.categories.name') }}</span>
                            <input wire:model="translationName" required maxlength="180">
                            @error('translationName') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.categories.visibility') }}</span>
                            <select wire:model="visibility">
                                <option value="public">{{ __('forum_admin.visibility.public') }}</option>
                                <option value="members">{{ __('forum_admin.visibility.members') }}</option>
                                <option value="restricted">{{ __('forum_admin.visibility.restricted') }}</option>
                                <option value="hidden">{{ __('forum_admin.visibility.hidden') }}</option>
                            </select>
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.categories.moderation') }}</span>
                            <select wire:model="moderationLevel">
                                <option value="standard">{{ __('forum_admin.moderation.standard') }}</option>
                                <option value="review">{{ __('forum_admin.moderation.review') }}</option>
                                <option value="high-risk">{{ __('forum_admin.moderation.high_risk') }}</option>
                                <option value="emergency">{{ __('forum_admin.moderation.emergency') }}</option>
                            </select>
                        </label>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveCategory" class="forum-button forum-button--primary">
                            <x-lucide-save aria-hidden="true" />
                            {{ __('forum_admin.actions.save') }}
                        </button>
                    </form>
                @else
                    <p class="mt-4 text-paw-muted">{{ __('forum_admin.categories.select') }}</p>
                @endif
            </section>
        </div>
    @elseif ($tab === 'guides')
        <section class="mt-5" aria-labelledby="admin-guides-heading">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="admin-guides-heading" class="text-xl font-semibold">{{ __('forum_admin.guides.heading') }}</h2>
                    <p class="text-sm text-paw-muted">{{ __('forum_admin.guides.summary') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label>
                        <span class="sr-only">{{ __('forum_admin.guides.search') }}</span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('forum_admin.guides.search') }}" class="min-h-11 rounded-md border border-paw-line bg-white px-3 py-2">
                    </label>
                    <a href="{{ route('knowledge.guides.create') }}" class="forum-button forum-button--primary">
                        <x-lucide-file-plus-2 aria-hidden="true" />
                        {{ __('forum_admin.guides.create') }}
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto rounded-md border border-paw-line bg-white">
                <table class="w-full table-fixed text-start text-sm sm:table-auto">
                    <thead class="bg-paw-paper">
                        <tr>
                            <th scope="col" class="px-3 py-2">{{ __('forum_admin.guides.title') }}</th>
                            <th scope="col" class="px-3 py-2">{{ __('forum_admin.guides.status') }}</th>
                            <th scope="col" class="hidden px-3 py-2 sm:table-cell">{{ __('forum_admin.guides.locale') }}</th>
                            <th scope="col" class="hidden px-3 py-2 sm:table-cell">{{ __('forum_admin.guides.version') }}</th>
                            <th scope="col" class="hidden px-3 py-2 lg:table-cell">{{ __('forum_admin.guides.collaborators') }}</th>
                            <th scope="col" class="hidden px-3 py-2 lg:table-cell">{{ __('forum_admin.guides.corrections') }}</th>
                            <th scope="col" class="hidden px-3 py-2 lg:table-cell">{{ __('forum_admin.guides.updated') }}</th>
                            <th scope="col" class="px-3 py-2"><span class="sr-only">{{ __('forum_admin.actions.edit') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->guides as $guide)
                            <tr wire:key="admin-guide-{{ $guide['id'] }}" class="border-t border-paw-line">
                                <td class="break-words px-3 py-2 font-medium">{{ $guide['title'] }}</td>
                                <td class="px-3 py-2">{{ $guide['status_label'] }}</td>
                                <td class="hidden px-3 py-2 sm:table-cell">{{ $guide['language'] }}</td>
                                <td class="hidden px-3 py-2 sm:table-cell">{{ $guide['version'] }}</td>
                                <td class="hidden px-3 py-2 lg:table-cell">{{ $guide['collaborators'] }}</td>
                                <td class="hidden px-3 py-2 lg:table-cell">{{ $guide['corrections'] }}</td>
                                <td class="hidden px-3 py-2 lg:table-cell">{{ $guide['updated'] }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-2">
                                        @if ($guide['public_url'] !== null)
                                            <a href="{{ $guide['public_url'] }}" class="forum-button" aria-label="{{ __('forum_admin.guides.open_named', ['name' => $guide['title']]) }}">
                                                <x-lucide-external-link aria-hidden="true" />
                                            </a>
                                        @endif
                                        <a href="{{ $guide['edit_url'] }}" class="forum-button" aria-label="{{ __('forum_admin.actions.edit_named', ['name' => $guide['title']]) }}">
                                            <x-lucide-pencil aria-hidden="true" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-paw-muted">{{ __('forum_admin.guides.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($tab === 'taxonomy')
        <section class="mt-5" aria-labelledby="taxonomy-imports-heading">
            <h2 id="taxonomy-imports-heading" class="text-xl font-semibold">{{ __('forum_admin.taxonomy.heading') }}</h2>
            <div class="mt-3 overflow-x-auto rounded-md border border-paw-line bg-white">
                <table class="w-full min-w-[42rem] text-sm">
                    <thead class="bg-paw-paper">
                        <tr>
                            <th scope="col" class="px-3 py-2">{{ __('forum_admin.taxonomy.source') }}</th>
                            <th scope="col" class="px-3 py-2">{{ __('forum_admin.taxonomy.version') }}</th>
                            <th scope="col" class="px-3 py-2">{{ __('forum_admin.taxonomy.state') }}</th>
                            <th scope="col" class="px-3 py-2">{{ __('forum_admin.taxonomy.processed') }}</th>
                            <th scope="col" class="px-3 py-2">{{ __('forum_admin.taxonomy.issues') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->imports as $import)
                            <tr wire:key="taxonomy-import-{{ $import['id'] }}" class="border-t border-paw-line">
                                <td class="px-3 py-2">{{ $import['source'] }}</td>
                                <td class="px-3 py-2">{{ $import['version'] }}</td>
                                <td class="px-3 py-2">{{ $import['state'] }}</td>
                                <td class="px-3 py-2">{{ $import['processed'] }}</td>
                                <td class="px-3 py-2">{{ $import['errors'] }} / {{ $import['warnings'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-paw-muted">{{ __('forum_admin.taxonomy.empty') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($tab === 'verification')
        <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,24rem)]">
            <section aria-labelledby="credential-queue-heading">
                <h2 id="credential-queue-heading" class="text-xl font-semibold">{{ __('forum_admin.verification.heading') }}</h2>
                <div class="mt-3 overflow-x-auto rounded-md border border-paw-line bg-white">
                    <table class="w-full min-w-[48rem] text-sm">
                        <thead class="bg-paw-paper">
                            <tr>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.verification.professional') }}</th>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.verification.credential') }}</th>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.verification.jurisdiction') }}</th>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.verification.status') }}</th>
                                <th scope="col" class="px-3 py-2">{{ __('forum_admin.verification.expires') }}</th>
                                <th scope="col" class="px-3 py-2"><span class="sr-only">{{ __('forum_admin.verification.review') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->credentials as $credential)
                                <tr wire:key="credential-{{ $credential['id'] }}" class="border-t border-paw-line">
                                    <td class="px-3 py-2">{{ $credential['professional'] }}</td>
                                    <td class="px-3 py-2">
                                        <span class="block font-medium">{{ $credential['title'] }}</span>
                                        <span class="text-xs text-paw-muted">{{ $credential['type'] }}</span>
                                    </td>
                                    <td class="px-3 py-2">{{ $credential['jurisdiction'] }}</td>
                                    <td class="px-3 py-2">{{ $credential['status'] }}</td>
                                    <td class="px-3 py-2">{{ $credential['expires'] ?? __('forum_admin.verification.no_expiry') }}</td>
                                    <td class="px-3 py-2 text-end">
                                        <button type="button" wire:click="selectCredential({{ $credential['id'] }})" class="forum-button" aria-label="{{ __('forum_admin.verification.review_named', ['name' => $credential['professional']]) }}">
                                            <x-lucide-shield-check aria-hidden="true" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-6 text-center text-paw-muted">{{ __('forum_admin.verification.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section aria-labelledby="credential-review-heading" class="border-s border-paw-line ps-5">
                <h2 id="credential-review-heading" class="text-xl font-semibold">{{ __('forum_admin.verification.review') }}</h2>
                @if ($selectedCredentialId !== null)
                    <form wire:submit="reviewCredential" class="forum-form mt-4">
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.verification.target_status') }}</span>
                            <select wire:model="credentialTargetStatus">
                                <option value="in-review">{{ __('credential_verification.status.in-review') }}</option>
                                <option value="verified">{{ __('credential_verification.status.verified') }}</option>
                                <option value="rejected">{{ __('credential_verification.status.rejected') }}</option>
                                <option value="suspended">{{ __('credential_verification.status.suspended') }}</option>
                                <option value="revoked">{{ __('credential_verification.status.revoked') }}</option>
                            </select>
                            @error('credentialTargetStatus') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <label class="forum-form__field">
                            <span>{{ __('forum_admin.verification.internal_reason') }}</span>
                            <textarea wire:model="verificationInternalReason" required minlength="20" maxlength="2000" rows="5"></textarea>
                            <small>{{ __('forum_admin.verification.internal_reason_help') }}</small>
                            @error('verificationInternalReason') <small role="alert">{{ $message }}</small> @enderror
                        </label>
                        <button type="submit" wire:loading.attr="disabled" wire:target="reviewCredential" class="forum-button forum-button--primary">
                            <x-lucide-shield-check aria-hidden="true" />
                            <span wire:loading.remove wire:target="reviewCredential">{{ __('forum_admin.verification.apply') }}</span>
                            <span wire:loading wire:target="reviewCredential">{{ __('forum_admin.actions.working') }}</span>
                        </button>
                    </form>
                @else
                    <p class="mt-4 text-paw-muted">{{ __('forum_admin.verification.select') }}</p>
                @endif
            </section>
        </div>
    @else
        <livewire:forum.moderation-operations />
    @endif

    <p wire:offline role="status" class="mt-4 rounded-md border border-status-warning bg-paw-paper px-4 py-3">
        {{ __('forum_admin.offline') }}
    </p>
</div>
