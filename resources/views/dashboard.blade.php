<x-app-layout
    title="Dashboard"
    description="Your rent collection health at a glance."
>
    <x-slot name="actions">
        <x-ui.button variant="secondary" :href="route('settings.edit')">Reminders</x-ui.button>
        <x-ui.button :href="route('tenants.create')">Add tenant</x-ui.button>
    </x-slot>

    <div class="space-y-8">
        <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-navy-900 p-6 shadow-card-dark sm:p-8">
            <div class="pointer-events-none absolute inset-0 bg-brand-gradient-soft opacity-40"></div>
            <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-brand-500/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-400">Collection health this month</p>
                    <p class="mt-2 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                        {{ $health['collection_rate'] }}<span class="text-2xl text-slate-400">%</span>
                    </p>
                    <p class="mt-2 text-sm text-slate-400">
                        <span class="font-medium text-white">£{{ $health['collected_this_month'] }}</span>
                        collected of
                        <span class="font-medium text-white">£{{ $health['expected_this_month'] }}</span> expected
                    </p>
                </div>

                <div class="w-full sm:max-w-xs">
                    <div class="h-2.5 overflow-hidden rounded-full bg-white/10">
                        <div
                            class="h-full rounded-full bg-brand-gradient shadow-glow transition-all duration-700 ease-smooth"
                            style="width: {{ min($health['collection_rate'], 100) }}%"
                        ></div>
                    </div>
                    <p class="mt-2 text-right text-xs text-slate-500">Target: 100% on-time collection</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Total monthly rent" :value="'£'.$health['total_monthly_rent']" hint="Active tenants" tone="brand">
                <x-slot name="icon">
                    <x-ui.icon name="money" />
                </x-slot>
            </x-ui.stat-card>

            <x-ui.stat-card label="Paid" :value="'£'.$health['paid_amount']" :hint="$health['paid_this_month_count'].' tenants this month'" tone="success">
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot>
            </x-ui.stat-card>

            <x-ui.stat-card label="Due soon" :value="$health['due_soon_count']" :hint="'£'.$health['due_soon_amount'].' at risk'" tone="warning">
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot>
            </x-ui.stat-card>

            <x-ui.stat-card label="Overdue" :value="$health['overdue_count']" :hint="'£'.$health['overdue_amount'].' outstanding'" tone="danger">
                <x-slot name="icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </x-slot>
            </x-ui.stat-card>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.card title="Overdue" :description="$health['overdue_count'].' need attention'">
                @forelse ($health['overdue_tenants'] as $tenant)
                    <a href="{{ route('tenants.show', $tenant) }}" class="flex items-center justify-between gap-3 rounded-xl px-2 py-2.5 transition hover:bg-white/[0.04]">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-500">£{{ number_format($tenant->rent_amount, 2) }} / month</p>
                        </div>
                        <x-payment-status-badge status="overdue" />
                    </a>
                @empty
                    <x-ui.empty-state title="All caught up" description="No overdue rent right now. Your reminders are doing their job." />
                @endforelse
            </x-ui.card>

            <x-ui.card title="Due soon" description="Next 7 days">
                @forelse ($health['due_soon_tenants'] as $tenant)
                    <a href="{{ route('tenants.show', $tenant) }}" class="flex items-center justify-between gap-3 rounded-xl px-2 py-2.5 transition hover:bg-white/[0.04]">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-500">Due day {{ $tenant->rent_due_day }}</p>
                        </div>
                        <x-ui.badge tone="warning">Upcoming</x-ui.badge>
                    </a>
                @empty
                    <x-ui.empty-state title="Nothing due soon" description="No rent due in the next week." />
                @endforelse
            </x-ui.card>

            <x-ui.card title="Paid this month" :description="$health['paid_this_month_count'].' tenants'">
                @forelse ($health['paid_tenants'] as $tenant)
                    <a href="{{ route('tenants.show', $tenant) }}" class="flex items-center justify-between gap-3 rounded-xl px-2 py-2.5 transition hover:bg-white/[0.04]">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-500">
                                @if (isset($tenant->collected_this_month))
                                    £{{ number_format($tenant->collected_this_month, 2) }} collected
                                @else
                                    £{{ number_format($tenant->rent_amount, 2) }} / month
                                @endif
                            </p>
                        </div>
                        <x-payment-status-badge status="paid" />
                    </a>
                @empty
                    <x-ui.empty-state title="No payments yet" description="Paid rent for this month will show up here." />
                @endforelse
            </x-ui.card>

            <x-ui.card title="Recent activity" description="Latest payment updates" :padding="false">
                <div class="divide-y divide-white/[0.06]">
                    @forelse ($health['recent_activity'] as $payment)
                        <x-ui.activity-item :payment="$payment" />
                    @empty
                        <x-ui.empty-state title="No activity yet" description="Payment updates will appear here as you track rent." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
