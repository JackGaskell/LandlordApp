@props(['upcoming'])

<div @class([
    'relative overflow-hidden rounded-2xl border p-6 shadow-card-dark sm:p-8',
    'border-amber-500/20 bg-navy-900' => $upcoming->isOverdue,
    'border-white/[0.08] bg-navy-900' => ! $upcoming->isOverdue,
])>
    <div class="pointer-events-none absolute inset-0 opacity-30" aria-hidden="true">
        <div @class([
            'absolute -right-12 -top-12 h-40 w-40 rounded-full blur-3xl',
            'bg-amber-500/25' => $upcoming->isOverdue,
            'bg-brand-500/20' => ! $upcoming->isOverdue,
        ])></div>
    </div>
    <div class="relative">
        <p class="text-sm font-medium text-slate-400">{{ $upcoming->cardTitle() }}</p>
        <p class="mt-2 text-4xl font-semibold tracking-tight text-white sm:text-5xl">{{ $upcoming->amountFormatted() }}</p>
        <p @class([
            'mt-2 text-sm font-medium',
            'text-amber-300' => $upcoming->isOverdue,
            'text-brand-300' => ! $upcoming->isOverdue,
        ])>{{ $upcoming->dueLabel }}</p>
        <p class="mt-1 text-xs text-slate-500">Due {{ $upcoming->dueDate->format('l, j F Y') }}</p>
    </div>
</div>
