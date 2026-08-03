<x-page-stack data-section="organization-directory">
    <x-page-header
        :eyebrow="__('organizations.pages.index.eyebrow')"
        :title="__('organizations.pages.index.title')"
        :description="__('organizations.pages.index.description')"
        heading-id="organization-directory-heading"
    />

    @if ($feedback !== '')
        <x-flash-feedback :message="$feedback" />
    @endif

    <x-content-panel
        eyebrow="{{ __('organizations.pages.index.create_eyebrow') }}"
        title="{{ __('organizations.pages.index.create_title') }}"
    >
        <form wire:submit="create" class="mt-5 grid gap-5" novalidate>
            @if ($errors->any())
                <x-forum-error-summary
                    :messages="$errors->getMessages()"
                    :heading="__('organizations.validation.summary')"
                />
            @endif

            <div class="grid min-w-0 gap-5 md:grid-cols-2">
                <label class="forum-form__field md:col-span-2" for="organization-name">
                    <span>{{ __('organizations.fields.name') }}</span>
                    <input id="organization-name" type="text" wire:model="form.name" maxlength="180" required>
                    @error('form.name') <small role="alert">{{ $message }}</small> @enderror
                </label>

                <label class="forum-form__field" for="organization-type">
                    <span>{{ __('organizations.fields.type') }}</span>
                    <select id="organization-type" wire:model="form.type" required>
                        @forelse ($this->typeOptions as $value => $label)
                            <option wire:key="organization-type-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                        @empty
                            <option value="community">{{ __('organizations.types.community') }}</option>
                        @endforelse
                    </select>
                </label>

                <label class="forum-form__field" for="organization-region">
                    <span>{{ __('organizations.fields.public_region') }}</span>
                    <input id="organization-region" type="text" wire:model="form.publicRegion" maxlength="160">
                </label>
            </div>

            <label class="forum-form__field" for="organization-summary">
                <span>{{ __('organizations.fields.summary') }}</span>
                <textarea id="organization-summary" wire:model="form.summary" rows="4" maxlength="3000"></textarea>
            </label>

            <div>
                <button
                    type="submit"
                    class="forum-button forum-button--primary min-h-11"
                    wire:loading.attr="disabled"
                    wire:target="create"
                >
                    <x-ui-icon name="plus" />
                    <span wire:loading.remove wire:target="create">{{ __('organizations.actions.create') }}</span>
                    <span wire:loading wire:target="create">{{ __('organizations.actions.creating') }}</span>
                </button>
            </div>
        </form>
    </x-content-panel>

    <section aria-labelledby="your-organizations-heading">
        <header class="mb-4">
            <h2 id="your-organizations-heading">{{ __('organizations.pages.index.yours_title') }}</h2>
            <p>{{ __('organizations.pages.index.yours_description') }}</p>
        </header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->organizations as $organization)
                <article
                    class="grid min-w-0 gap-4 rounded-lg border border-border-subtle bg-white p-5"
                    wire:key="organization-{{ $organization['id'] }}"
                >
                    <header class="grid gap-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold text-paw-muted">{{ $organization['type'] }}</p>
                            <x-status-badge :label="$organization['status']" icon="building-2" />
                        </div>
                        <h3 class="text-xl">{{ $organization['name'] }}</h3>
                    </header>

                    @if ($organization['summary'])
                        <p>{{ $organization['summary'] }}</p>
                    @endif

                    <dl class="grid gap-2 text-sm">
                        <div>
                            <dt class="font-semibold">{{ __('organizations.fields.verification') }}</dt>
                            <dd>{{ $organization['verification'] }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold">{{ __('organizations.fields.members') }}</dt>
                            <dd>{{ $organization['member_count'] }}</dd>
                        </div>
                    </dl>

                    <a class="forum-button min-h-11 justify-self-start" href="{{ $organization['url'] }}" wire:navigate>
                        <x-ui-icon name="settings" />
                        {{ __('organizations.actions.open_workspace') }}
                    </a>
                </article>
            @empty
                <x-empty-state
                    :title="__('organizations.empty.title')"
                    :description="__('organizations.empty.description')"
                    icon="building-2"
                />
            @endforelse
        </div>

        <div class="mt-6">{{ $this->organizations->links() }}</div>
    </section>
</x-page-stack>
