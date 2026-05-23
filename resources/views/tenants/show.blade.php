<x-app-layout :title="$tenant->name" description="Tenant profile and payment history">
    <x-slot name="actions">
        <x-ui.button variant="secondary" :href="route('tenants.edit', $tenant)">Edit</x-ui.button>
    </x-slot>

    <x-ui.flash />

    <x-ui.card title="Tenant rent portal" class="mb-6" description="Give your tenant secure access to their rent dashboard, streak, and payment proof uploads.">
        @if ($tenant->hasPortalAccess())
            <p class="text-sm text-slate-400">Portal is active. The tenant can sign in at <a href="{{ route('portal.login') }}" class="font-medium text-brand-300 hover:text-white">{{ route('portal.login') }}</a> using <span class="text-white">{{ $tenant->email }}</span>.</p>
        @elseif ($tenant->hasPendingInvite())
            <p class="text-sm text-slate-400">Invite sent — waiting for the tenant to set their password.</p>
            @if (session('portal_invite_url'))
                <div class="mt-4 rounded-xl border border-white/[0.08] bg-white/[0.03] p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Invite link (copy & send)</p>
                    <p class="mt-2 break-all text-sm text-brand-300">{{ session('portal_invite_url') }}</p>
                </div>
            @endif
        @else
            <p class="text-sm text-slate-400">Enable the portal to send a secure invite link so they can track rent and build reliability.</p>
            <form method="POST" action="{{ route('tenants.portal.store', $tenant) }}" class="mt-4">
                @csrf
                <x-ui.button type="submit">Enable tenant portal</x-ui.button>
            </form>
        @endif
    </x-ui.card>

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
            :value="$reliabilityProfile->scoreFormatted().'%'"
            :hint="$reliabilityProfile->badge->label().' · '.$reliabilityProfile->currentStreak.' mo streak'"
            :tone="$reliabilityProfile->score >= 75 ? 'success' : ($reliabilityProfile->score >= 50 ? 'warning' : 'danger')"
        />
        <x-ui.stat-card
            label="Consistency"
            :value="$reliabilityProfile->consistencyFormatted().'%'"
            :hint="$reliabilityProfile->consistencyWindowMonths.' month window · '.$reliabilityProfile->totalOnTime.' on time'"
            tone="brand"
        />
        <div class="ui-card flex items-center justify-center p-5">
            <div class="text-center">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tenant status</p>
                <div class="mt-2"><x-tenant-status-badge :status="$tenant->status" /></div>
            </div>
        </div>
    </div>

    <x-ui.card title="Payment history" class="mt-6" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="ui-table-head">
                    <tr>
                        <th class="px-6 py-3">Due date</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Paid at</th>
                        <th class="px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($tenant->paymentHistories as $payment)
                        <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30">
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
