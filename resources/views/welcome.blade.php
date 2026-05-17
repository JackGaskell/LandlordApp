<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a1222">
    <title>{{ config('app.name') }} — Rent collection you can trust</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-navy-950 font-sans text-slate-300 antialiased">
    <div class="pointer-events-none fixed inset-0 bg-brand-gradient-glow"></div>

    <header class="relative z-10 border-b border-white/[0.06]">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 lg:px-8">
            <x-brand.logo href="{{ url('/') }}" size="md" :show-tagline="true" />
            <nav class="flex items-center gap-3">
                @auth
                    <x-ui.button variant="secondary" :href="route('dashboard')">Dashboard</x-ui.button>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-300 transition hover:text-white">Log in</a>
                    <x-ui.button :href="route('register')">Get started</x-ui.button>
                @endauth
            </nav>
        </div>
    </header>

    <main class="relative z-10">
        <section class="mx-auto max-w-6xl px-6 pb-20 pt-16 lg:px-8 lg:pt-24">
            <div class="mx-auto max-w-3xl text-center">
                <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.04] px-3 py-1 text-xs font-medium text-slate-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-teal"></span>
                    Built for UK landlords who hate chasing rent
                </p>
                <h1 class="text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Stop missed rent.<br>
                    <span class="text-gradient">Start collecting with confidence.</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-400">
                    Automated reminders, payment visibility, and tenant accountability—without spreadsheet chaos or generic property software.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <x-ui.button :href="route('register')" class="min-w-[200px] justify-center px-8 py-3">Start free</x-ui.button>
                    <x-ui.button variant="secondary" :href="route('login')" class="min-w-[200px] justify-center px-8 py-3 border-white/10 bg-white/[0.04] text-white hover:bg-white/[0.08]">Sign in</x-ui.button>
                </div>
            </div>

            <div class="relative mx-auto mt-16 max-w-5xl overflow-hidden rounded-2xl border border-white/[0.08] bg-navy-900/80 shadow-card-dark ring-1 ring-white/[0.06]">
                <div class="border-b border-white/[0.06] bg-white/[0.03] px-4 py-3">
                    <div class="flex gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500/80"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500/80"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500/80"></span>
                    </div>
                </div>
                <div class="grid gap-px bg-white/[0.06] p-6 sm:grid-cols-4">
                    @foreach ([['98%', 'Collection rate'], ['3', 'Due soon'], ['1', 'Overdue'], ['£4.2k', 'Collected']] as [$val, $label])
                        <div class="rounded-xl bg-navy-950/50 p-4 text-center">
                            <p class="text-2xl font-semibold text-white">{{ $val }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="border-t border-white/[0.06] bg-navy-900/40 py-20">
            <div class="mx-auto grid max-w-6xl gap-10 px-6 lg:grid-cols-3 lg:px-8">
                @foreach ([
                    ['Automated reminders', 'Email tenants before and after due dates so you are not the bad guy—they get a nudge, you get paid.'],
                    ['Collection health', 'See overdue, due soon, and paid tenants on one dashboard designed for cash flow—not CRM clutter.'],
                    ['Payment accountability', 'Track verification status and reliability scores so you know who pays on time.'],
                ] as [$title, $body])
                    <div class="rounded-2xl border border-white/[0.08] bg-white/[0.03] p-6 transition hover:border-white/15 hover:bg-white/[0.05]">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-brand-gradient">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <footer class="relative z-10 border-t border-white/[0.06] py-8 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Rent collection intelligence for modern landlords.
    </footer>
</body>
</html>
