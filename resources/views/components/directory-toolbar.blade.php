<section
    data-section="{{ $section }}"
    aria-label="{{ $label }}"
    {{ $attributes->class(['panel', 'panel--padded-sm']) }}
>
    <form method="GET" action="{{ url()->current() }}" @class([
        'directory-toolbar',
        'directory-toolbar--with-search' => $hasSearch,
    ])>
        @if ($hasSearch)
            <x-search-field
                :id="$searchId"
                :label="$searchLabel"
                :placeholder="$searchPlaceholder"
                :value="$query"
            />
        @endif

        <x-filter-group
            :filters="$filters"
            :label="$filtersLabel"
            size="toolbar"
            :active="$activeFilter"
            submit
            class="directory-toolbar__filters"
        />

        <div class="directory-toolbar__commands">
            <label for="{{ $section }}-sort" class="sr-only">{{ $sortLabel }}</label>
            <span class="select-wrap">
                <x-lucide-arrow-up-down class="icon icon--sm" aria-hidden="true" />
                <select id="{{ $section }}-sort" name="sort" class="field field--select" onchange="this.form.submit()">
                    @foreach ($sortOptions as $value => $option)
                        <option value="{{ $value }}" @selected($activeSort === $value)>{{ $option }}</option>
                    @endforeach
                </select>
            </span>

            <x-action-control
                type="submit"
                label="{{ __('ui.search_49c266baaa') }}"
                icon="search"
                variant="primary"
                size="toolbar"
            />
        </div>
    </form>
</section>
