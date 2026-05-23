<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-navy-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0a1222">

        <title>Rent portal · {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans text-slate-100">
        <div class="relative flex min-h-full flex-col justify-center px-6 py-12 sm:px-10">
            <div class="pointer-events-none absolute inset-0 bg-brand-gradient-soft opacity-30"></div>
            <div class="pointer-events-none absolute -right-24 top-0 h-72 w-72 rounded-full bg-brand-500/10 blur-3xl"></div>

            <div class="relative mx-auto w-full max-w-md">
                <div class="mb-8 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wider text-brand-300">Tenant rent portal</p>
                    <h1 class="mt-2 text-2xl font-semibold text-white">{{ config('app.name') }}</h1>
                    <p class="mt-2 text-sm text-slate-400">Stay on top of rent, track your progress, and build reliability.</p>
                </div>

                {{ $slot }}

                <p class="mt-8 text-center text-xs text-slate-500">
                    Landlord? <a href="{{ route('login') }}" class="font-medium text-brand-300 hover:text-white">Sign in here</a>
                </p>
            </div>
        </div>
    </body>
</html>
