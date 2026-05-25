@props(['items'])

<x-ui.card title="Recent activity" description="Payments and confirmations" :padding="false">
    <div class="divide-y divide-white/[0.06]">
        @forelse ($items as $item)
            <div class="flex gap-4 px-5 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $item->type === 'payment' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-brand-500/15 text-brand-300' }}">
                    @if ($item->type === 'payment')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5" />
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white">{{ $item->title }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $item->description }}</p>
                </div>
                <p class="shrink-0 text-xs text-slate-500">{{ $item->occurredAt->diffForHumans() }}</p>
            </div>
        @empty
            <x-ui.empty-state
                title="Nothing here yet"
                description="Activity will show when payments are recorded or you upload a receipt."
            />
        @endforelse
    </div>
</x-ui.card>
