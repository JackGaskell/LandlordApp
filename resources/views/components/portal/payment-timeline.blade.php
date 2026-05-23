@props(['timeline'])

<x-ui.card title="Payment timeline" description="How each rent period went" :padding="false">
    <div class="px-5 py-4">
        @forelse ($timeline as $entry)
            <div class="relative flex gap-4 pb-8 last:pb-0">
                @if (! $loop->last)
                    <span class="absolute left-[11px] top-6 h-[calc(100%-12px)] w-px bg-white/[0.08]" aria-hidden="true"></span>
                @endif

                <span class="relative z-10 mt-1.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full ring-4 ring-navy-900 {{ $entry->dotClasses() }}">
                    <span class="h-2 w-2 rounded-full bg-white/90"></span>
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $entry->periodLabel }}</p>
                            <p class="text-xs text-slate-500">{{ $entry->amountFormatted() }}</p>
                        </div>
                        <x-ui.badge :tone="$entry->badgeTone()">
                            {{ $entry->outcome->label() }}
                        </x-ui.badge>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">{{ $entry->subtitle() }}</p>
                </div>
            </div>
        @empty
            <x-ui.empty-state
                title="No periods yet"
                description="Your rent timeline will appear as payments are recorded."
            />
        @endforelse
    </div>
</x-ui.card>
