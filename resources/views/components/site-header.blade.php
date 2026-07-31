@props(['owner', 'activeSection'])

<header
    data-site-header
    {{ $attributes->class(['site-header sticky top-0 z-20 border-b border-paw-line/80 bg-paw-cream/95 backdrop-blur']) }}
>
    <div class="site-header__utility" data-header-utility>
        <x-brand-link />
        <x-header-search :active="$activeSection === 'discover'" class="site-header__search" />
        <x-header-actions :owner="$owner" :active-section="$activeSection" />
    </div>

    <div class="site-header__navigation" data-header-primary>
        <div class="site-header__navigation-inner">
            <x-primary-navigation :active-section="$activeSection" />
        </div>
    </div>
</header>
