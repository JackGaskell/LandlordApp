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
        <div x-data="appShell()" class="ui-shell">
            @include('layouts.partials.sidebar')

            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="closeSidebar()"
                class="fixed inset-0 z-40 bg-navy-950/70 backdrop-blur-sm lg:hidden"
                style="display: none;"
            ></div>

            <div class="lg:pl-64">
                @include('layouts.partials.topbar')

                <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    @if (isset($header))
                        <div class="mb-8">
                            {{ $header }}
                        </div>
                    @elseif ($title ?? false)
                        <x-ui.page-header :title="$title" :description="$description ?? null">
                            @isset($actions)
                                <x-slot name="actions">{{ $actions }}</x-slot>
                            @endisset
                        </x-ui.page-header>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
