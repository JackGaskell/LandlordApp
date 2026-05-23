@props(['reliability'])

<div class="ui-card-elevated flex h-full flex-col p-6">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Reliability score</p>

    <div class="mt-4 flex items-end gap-3">
        <p class="text-5xl font-semibold tracking-tight text-white">{{ $reliability->scoreFormatted() }}</p>
        <span class="mb-2 text-lg text-slate-400">/ 100</span>
    </div>

    <p class="mt-2 text-base font-medium text-brand-300">{{ $reliability->portalHeadline() }}</p>
    <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-400">{{ $reliability->portalMessage() }}</p>

    <div class="mt-6 grid grid-cols-3 gap-2 border-t border-white/[0.06] pt-4 text-center">
        <div>
            <p class="text-lg font-semibold text-emerald-400">{{ $reliability->paymentsOnTime }}</p>
            <p class="text-[10px] uppercase tracking-wide text-slate-500">On time</p>
        </div>
        <div>
            <p class="text-lg font-semibold text-amber-400">{{ $reliability->paymentsLate }}</p>
            <p class="text-[10px] uppercase tracking-wide text-slate-500">Late</p>
        </div>
        <div>
            <p class="text-lg font-semibold text-slate-300">{{ $reliability->paymentsMissed }}</p>
            <p class="text-[10px] uppercase tracking-wide text-slate-500">Missed</p>
        </div>
    </div>
</div>
