<x-app-layout
    title="Tenants"
    description="Manage who rents from you and track their rent behaviour."
>
    <x-slot name="actions">
        <x-ui.button :href="route('tenants.create')">Add tenant</x-ui.button>
    </x-slot>

    <x-ui.flash />

    <x-ui.card :padding="false">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead class="ui-table-head">
                    <tr>
                        <th>Tenant</th>
                        <th>Rent</th>
                        <th>Due day</th>
                        <th>Latest payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.06] bg-transparent">
                    @forelse ($tenants as $tenant)
                        <tr class="ui-table-row">
                            <td class="ui-table-cell">
                                <a href="{{ route('tenants.show', $tenant) }}" class="font-medium ui-link">
                                    {{ $tenant->name }}
                                </a>
                                <p class="text-xs text-slate-500">{{ $tenant->email }}</p>
                            </td>
                            <td class="ui-table-cell font-semibold text-white">£{{ number_format($tenant->rent_amount, 2) }}</td>
                            <td class="ui-table-cell">{{ $tenant->rent_due_day }}</td>
                            <td class="ui-table-cell">
                                @if ($tenant->latestPayment)
                                    <x-payment-status-badge :status="$tenant->latestPayment->status" />
                                    <p class="mt-1 text-xs text-slate-500">{{ $tenant->latestPayment->due_date->format('d M Y') }}</p>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="ui-table-cell">
                                <x-tenant-status-badge :status="$tenant->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state
                                    title="No tenants yet"
                                    description="Add your first tenant to start tracking rent and sending reminders."
                                >
                                    <x-slot name="action">
                                        <x-ui.button :href="route('tenants.create')">Add tenant</x-ui.button>
                                    </x-slot>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tenants->hasPages())
            <div class="border-t border-white/[0.06] px-6 py-4">
                {{ $tenants->links() }}
            </div>
        @endif
    </x-ui.card>
</x-app-layout>
