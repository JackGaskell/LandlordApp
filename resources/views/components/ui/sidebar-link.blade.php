@props(['href', 'active' => false, 'icon', 'badge' => null])

<a
    href="{{ $href }}"
    @click="if (window.innerWidth < 1024) closeSidebar()"
    {{ $attributes->class([
        'ui-sidebar-link',
        'ui-sidebar-link-active' => $active,
    ]) }}
>
    <x-ui.icon :name="$icon" class="ui-sidebar-icon h-5 w-5 shrink-0 {{ $active ? 'text-accent-teal' : 'text-slate-500' }}" />
    <span class="min-w-0 flex-1">{{ $slot }}</span>
    @if ($badge)
        <span class="ml-auto rounded-full bg-brand-500/25 px-2 py-0.5 text-[10px] font-bold text-brand-200">{{ $badge }}</span>
    @endif
</a>
