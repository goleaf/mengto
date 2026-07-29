@props(['active' => false])

<div class="hidden min-w-0 flex-1 md:block">
    <a
        href="{{ route('pet-social.discover.index') }}"
        aria-label="Search PawCircle"
        data-header-link="discover"
        @if ($active) aria-current="page" @endif
        class="pc-search-link"
    >
        <x-lucide-search class="pc-icon pc-icon--sm" aria-hidden="true" />
        <span class="truncate">Search parks, pets, people, and routines</span>
    </a>
</div>
