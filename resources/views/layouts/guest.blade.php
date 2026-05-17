<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-navy-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0a1222">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans antialiased">
        <div class="flex min-h-screen flex-col lg:flex-row">
            <main class="order-1 flex min-h-screen flex-1 flex-col justify-center bg-navy-950 px-6 py-12 sm:px-10 lg:order-2 lg:px-16 xl:px-24">
                <div class="mb-8 lg:hidden">
                    <x-brand.logo-light href="{{ url('/') }}" size="md" />
                </div>

                <div class="mx-auto w-full max-w-md">
                    {{ $slot }}
                </div>
            </main>

            <aside class="relative order-2 hidden min-h-screen w-full shrink-0 flex-col overflow-hidden bg-navy-gradient p-10 lg:order-1 lg:flex lg:w-2/5 lg:max-w-xl lg:p-12 xl:p-16">
                <div class="pointer-events-none absolute inset-0 bg-brand-gradient-glow"></div>
                <div class="pointer-events-none absolute -left-20 top-1/3 h-72 w-72 rounded-full bg-brand-500/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -right-10 bottom-20 h-64 w-64 rounded-full bg-accent-teal/10 blur-3xl"></div>

                <div class="relative shrink-0">
                    <x-brand.logo href="{{ url('/') }}" size="lg" :show-tagline="true" />
                </div>

                <div class="relative flex flex-1 flex-col justify-center py-10">
                    <div class="max-w-md space-y-6">
                        <h1 class="text-3xl font-semibold leading-tight tracking-tight text-white xl:text-4xl">
                            Rent collection you can trust.
                        </h1>
                        <p class="text-base leading-relaxed text-slate-400">
                            Automated reminders, payment visibility, and tenant accountability—built for landlords who take cash flow seriously.
                        </p>
                        <ul class="space-y-3 text-sm text-slate-300">
                            <li class="flex items-center gap-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand-gradient text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5"/></svg>
                                </span>
                                Smart reminders before and after due dates
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand-gradient text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5"/></svg>
                                </span>
                                Collection health dashboard at a glance
                            </li>
                            <li class="flex items-center gap-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand-gradient text-white">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-10.5"/></svg>
                                </span>
                                Verified payment tracking & reliability scores
                            </li>
                        </ul>
                    </div>
                </div>

                <p class="relative shrink-0 text-xs text-slate-500">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            </aside>
        </div>
    </body>
</html>
