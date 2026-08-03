@props([
    'navigation',
    'filters',
    'draftCount' => 0,
])

<section
    aria-labelledby="forum-category-navigator-heading"
    data-forum-category-navigator
    {{ $attributes->class(['forum-taxonomy']) }}
>
    <div class="forum-taxonomy__header">
        <div>
            <span class="forum-taxonomy__eyebrow">{{ __('forum.directory.browse_eyebrow') }}</span>
            <h2 id="forum-category-navigator-heading">{{ __('forum.directory.categories_title') }}</h2>
            <p>{{ __('forum.directory.categories_description') }}</p>
        </div>

        @if ($draftCount > 0)
            <span class="forum-badge forum-badge--sun">
                {{ trans_choice('presentation.draft_count', $draftCount, ['count' => $draftCount]) }}
            </span>
        @endif
    </div>

    <details class="forum-taxonomy__catalog" @if ($navigation['active_root'] === 'all') open @endif>
        <summary>
            <span class="forum-taxonomy__summary-copy">
                <x-lucide-layout-grid aria-hidden="true" />
                <span>
                    <strong>{{ __('forum.directory.choose_category') }}</strong>
                    <small>{{ __('forum.directory.category_total', ['count' => $navigation['total']]) }}</small>
                </span>
            </span>
            <x-lucide-chevron-down class="forum-taxonomy__chevron" aria-hidden="true" />
        </summary>

        <nav
            class="forum-taxonomy__roots"
            aria-label="{{ __('forum.directory.categories_title') }}"
            data-forum-category-tree
        >
            <a
                href="{{ route('forum.index', [...$filters, 'category' => 'all', 'page' => null]) }}"
                class="forum-taxonomy__root"
                data-category-all
                @if ($navigation['active_root'] === 'all') aria-current="page" @endif
            >
                <span class="forum-taxonomy__root-icon">
                    <x-lucide-messages-square aria-hidden="true" />
                </span>
                <span>
                    <strong>{{ __('ui.all_topics_29366ff597') }}</strong>
                    <small>{{ __('forum.directory.all_topics_description') }}</small>
                </span>
            </a>

            @forelse ($navigation['items'] as $key => $category)
                <a
                    href="{{ route('forum.index', [...$filters, 'category' => $key, 'page' => null]) }}"
                    class="forum-taxonomy__root"
                    data-category-root="{{ $key }}"
                    data-active-root="{{ $navigation['active_root'] === $key ? 'true' : 'false' }}"
                    @if ($navigation['active_root'] === $key && $navigation['active_subcategory'] === null) aria-current="page" @endif
                >
                    <span class="forum-taxonomy__root-icon">
                        <x-dynamic-component :component="'lucide-'.$category['icon']" aria-hidden="true" />
                    </span>
                    <strong>{{ $category['label'] }}</strong>
                </a>
            @empty
                <span>{{ __('ui.no_categories_available_a557500f61') }}</span>
            @endforelse
        </nav>
    </details>

    @if ($navigation['active_category'] !== null)
        <section class="forum-taxonomy__selection" aria-labelledby="active-forum-category-heading">
            <nav class="forum-taxonomy__breadcrumbs" aria-label="{{ __('forum.directory.category_path') }}">
                <a href="{{ route('forum.index', [...$filters, 'category' => 'all', 'page' => null]) }}">
                    {{ __('forum.directory.categories_title') }}
                </a>
                <x-lucide-chevron-right aria-hidden="true" />
                <span aria-current="page">{{ $navigation['active_category']['label'] }}</span>
            </nav>

            <div class="forum-taxonomy__selection-header">
                <span class="forum-taxonomy__selection-icon">
                    <x-dynamic-component :component="'lucide-'.$navigation['active_category']['icon']" aria-hidden="true" />
                </span>
                <div>
                    <span class="forum-taxonomy__eyebrow">{{ __('forum.directory.active_category') }}</span>
                    <h3 id="active-forum-category-heading">{{ $navigation['active_category']['label'] }}</h3>
                    @if ($navigation['active_category']['description'])
                        <p>{{ $navigation['active_category']['description'] }}</p>
                    @endif
                </div>
            </div>

            @if ($navigation['active_category']['notice'])
                <aside
                    class="forum-safety"
                    role="note"
                    data-section="one-health-professional-boundary"
                >
                    <x-lucide-shield-alert aria-hidden="true" />
                    <div>
                        <strong>{{ __('forum_categories.notice_title') }}</strong>
                        <span>{{ $navigation['active_category']['notice'] }}</span>
                    </div>
                </aside>
            @endif

            <div class="forum-taxonomy__subcategories-header">
                <h4>{{ __('forum.directory.subcategories') }}</h4>
                <span>{{ __('forum.directory.subcategory_total', ['count' => $navigation['active_subcategory_total']]) }}</span>
            </div>

            <nav
                class="forum-taxonomy__subcategories"
                aria-label="{{ __('forum.directory.subcategories') }}"
                data-subcategory-list="{{ $navigation['active_root'] }}"
            >
                <a
                    href="{{ route('forum.index', [...$filters, 'category' => $navigation['active_root'], 'page' => null]) }}"
                    data-category-scope="{{ $navigation['active_root'] }}"
                    @if ($navigation['active_subcategory'] === null) aria-current="page" @endif
                >
                    <x-lucide-layers-3 aria-hidden="true" />
                    <span>{{ __('forum.directory.all_in_category', ['category' => $navigation['active_category']['label']]) }}</span>
                </a>

                @forelse ($navigation['active_category']['subcategories'] as $subcategoryKey => $subcategoryLabel)
                    <a
                        href="{{ route('forum.index', [...$filters, 'category' => $subcategoryKey, 'page' => null]) }}"
                        data-category-child="{{ $subcategoryKey }}"
                        @if ($navigation['active_subcategory'] === $subcategoryKey) aria-current="page" @endif
                    >
                        <x-lucide-message-circle aria-hidden="true" />
                        <span>{{ $subcategoryLabel }}</span>
                    </a>
                @empty
                    <span>{{ __('ui.no_subcategories_9f1010c1a3') }}</span>
                @endforelse
            </nav>
        </section>
    @endif
</section>
