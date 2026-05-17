<x-app-layout :title="$tenant->name" description="Tenant profile and payment history">
    <x-slot name="actions">
        <x-ui.button variant="secondary" :href="route('tenants.edit', $tenant)">Edit</x-ui.button>
    </x-slot>

    <x-ui.flash />

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card
            label="Monthly rent"
            :value="'£'.number_format($tenant->rent_amount, 2)"
            tone="brand"
        />
        <x-ui.stat-card
            label="Next due date"
            :value="$nextDueDate->format('j M Y')"
            :hint="'Due day '.$tenant->rent_due_day.' of each month'"
        />
        <x-ui.stat-card
            label="Reliability"
            :value="$reliability->scoreFormatted().'%'"
            :hint="$reliability->grade.' · '.$reliability->paymentsTracked.' payments tracked'"
            :tone="$reliability->score >= 75 ? 'success' : ($reliability->score >= 50 ? 'warning' : 'danger')"
        />
        <div class="ui-card flex items-center justify-center p-5">
            <div class="text-center">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Tenant status</p>
                <div class="mt-2"><x-tenant-status-badge :status="$tenant->status" /></div>
            </div>
        </div>
    </div>

    <x-ui.card title="Payment history" class="mt-6" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="ui-table-head">
                    <tr>
                        <th class="px-6 py-3">Due date</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Paid at</th>
                        <th class="px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($tenant->paymentHistories as $payment)
                        <tr class="hover:bg-zinc-50/80:bg-zinc-800/30">
                            <td class="ui-table-cell">{{ $payment->due_date->format('d M Y') }}</td>
                            <td class="ui-table-cell font-medium">£{{ number_format($payment->amount, 2) }}</td>
                            <td class="ui-table-cell"><x-payment-status-badge :status="$payment->status" /></td>
                            <td class="ui-table-cell text-zinc-500">{{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="ui-table-cell text-right">
                                @if (! $payment->status->isSettled())
                                    <form method="POST" action="{{ route('payments.mark-paid', $payment) }}" class="inline">
                                        @csrf
                                        <x-ui.button type="submit" variant="ghost">Mark paid</x-ui.button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state title="No payments recorded" description="Payment history will appear here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-app-layout>
