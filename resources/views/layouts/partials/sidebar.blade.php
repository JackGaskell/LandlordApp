@php
    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
            'icon' => 'chart',
        ],
        [
            'label' => 'Tenants',
            'route' => 'tenants.index',
            'active' => request()->routeIs('tenants.*'),
            'icon' => 'users',
        ],
        [
            'label' => config('landlord.payment_confirmations.nav'),
            'route' => 'payment-proofs.index',
            'active' => request()->routeIs('payment-proofs.*'),
            'icon' => 'card',
            'badge' => $pendingProofCount ?? 0,
        ],
        [
            'label' => 'Get paid',
            'route' => 'settings.payments',
            'active' => request()->routeIs('settings.payments', 'settings.stripe.*'),
            'icon' => 'card',
        ],
        [
            'label' => 'Reminders',
            'route' => 'settings.edit',
            'active' => request()->routeIs('settings.edit', 'settings.update'),
            'icon' => 'bell',
        ],
    ];
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="ui-sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col transition-transform duration-300 ease-smooth lg:translate-x-0"
>
    <div class="flex h-16 shrink-0 items-center border-b border-white/[0.06] px-5">
        <x-brand.logo size="md" :show-tagline="true" />
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
        <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">
            Collection
        </p>

        @foreach ($navItems as $item)
            <x-ui.sidebar-link
                :href="route($item['route'])"
                :active="$item['active']"
                :icon="$item['icon']"
                :badge="($item['badge'] ?? 0) > 0 ? $item['badge'] : null"
            >
                {{ $item['label'] }}
            </x-ui.sidebar-link>
        @endforeach
    </nav>

    <div class="border-t border-white/[0.06] p-4">
        <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-white/[0.04] p-4">
            <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-brand-500/20 blur-2xl"></div>
            <p class="relative text-xs font-semibold text-white">Automated reminders</p>
            <p class="relative mt-1.5 text-[11px] leading-relaxed text-slate-400">
                Reduce missed rent with timely nudges and a clear collection health view.
            </p>
        </div>
    </div>
</aside>
