@props(['owner', 'activeSection'])

<header {{ $attributes->class(['sticky top-0 z-20 border-b border-paw-line/80 bg-paw-cream/95 backdrop-blur']) }}>
    <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <x-ui.brand-link />
        <x-layout.primary-navigation :active-section="$activeSection" />
        <x-layout.header-search :active="$activeSection === 'discover'" />
        <x-layout.header-actions :owner="$owner" :active-section="$activeSection" />
    </div>
</header>
