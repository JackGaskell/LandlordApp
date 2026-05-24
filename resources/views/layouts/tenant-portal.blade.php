<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-navy-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0a1222">

        <title>
            @isset($title)
                {{ $title }} · {{ config('app.name') }}
            @else
                {{ config('app.name') }}
            @endisset
        </title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans">
        <div class="ui-shell min-h-full" x-data="{ menuOpen: false }">
            <header class="sticky top-0 z-40 border-b border-white/[0.04] bg-navy-950/95 backdrop-blur-md">
                <div class="mx-auto flex h-14 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('portal.dashboard') }}" class="text-sm font-semibold text-white">
                            {{ config('app.name') }}
                            <span class="ml-1 rounded-md bg-brand-500/20 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-300">Home</span>
                        </a>
                    </div>

                    <div class="hidden items-center gap-2 sm:flex">
                        <span class="text-sm text-slate-400">{{ auth('tenant')->user()->name }}</span>
                        <form method="POST" action="{{ route('portal.logout') }}">
                            @csrf
                            <x-ui.button type="submit" variant="ghost" size="sm">Sign out</x-ui.button>
                        </form>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-400 hover:bg-white/[0.06] hover:text-white sm:hidden"
                        @click="menuOpen = ! menuOpen"
                        aria-label="Menu"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

                <div
                    x-show="menuOpen"
                    x-cloak
                    class="border-t border-white/[0.06] px-4 py-3 sm:hidden"
                >
                    <p class="text-sm font-medium text-white">{{ auth('tenant')->user()->name }}</p>
                    <form method="POST" action="{{ route('portal.logout') }}" class="mt-3">
                        @csrf
                        <x-ui.button type="submit" variant="secondary" class="w-full justify-center">Sign out</x-ui.button>
                    </form>
                </div>
            </header>

            <main class="mx-auto max-w-5xl px-4 py-5 sm:px-6 sm:py-7">
                @if ($title ?? false)
                    <div class="mb-4 sm:mb-5">
                        <h1 class="text-xl font-semibold tracking-tight text-white sm:text-2xl">{{ $title }}</h1>
                        @if ($description ?? false)
                            <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
                        @endif
                    </div>
                @endif

                <x-ui.flash />

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
