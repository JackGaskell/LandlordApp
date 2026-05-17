@props(['href', 'active' => false, 'icon'])

<a
    href="{{ $href }}"
    @click="if (window.innerWidth < 1024) closeSidebar()"
    {{ $attributes->class([
        'ui-sidebar-link',
        'ui-sidebar-link-active' => $active,
    ]) }}
>
    <x-ui.icon :name="$icon" class="ui-sidebar-icon h-5 w-5 shrink-0 {{ $active ? 'text-accent-teal' : 'text-slate-500' }}" />
    <span>{{ $slot }}</span>
</a>
