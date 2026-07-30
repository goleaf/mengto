<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DirectoryToolbar extends Component
{
    public bool $hasSearch;

    /**
     * @param  array<int, array<string, mixed>>  $filters
     * @param  array<string, string>  $sortOptions
     */
    public function __construct(
        public string $label,
        public string $filtersLabel,
        public string $sortLabel,
        public string $section,
        public array $filters = [],
        public ?string $searchId = null,
        public ?string $searchLabel = null,
        public ?string $searchPlaceholder = null,
        public string $query = '',
        public ?string $activeFilter = null,
        public string $activeSort = 'recommended',
        public array $sortOptions = [],
    ) {
        $this->hasSearch = filled($searchId)
            && filled($searchLabel)
            && filled($searchPlaceholder);
    }

    public function render(): View
    {
        return view('components.directory-toolbar');
    }
}
