<a
    href="{{ route('home') }}"
    {{ $attributes->class(['brand-link']) }}
    aria-label="{{ __('navigation.utility.brand_home') }}"
>
    <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-paw-ink text-sm font-bold text-white">{{ __('ui.pc') }}</span>
    <span class="brand-link__name truncate text-lg font-semibold tracking-normal">{{ __('ui.brand_name') }}</span>
</a>
