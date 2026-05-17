<header class="ui-topbar">
    <button
        type="button"
        @click="sidebarOpen = true"
        class="inline-flex items-center justify-center rounded-xl p-2 text-slate-400 transition hover:bg-white/[0.06] hover:text-white lg:hidden"
    >
        <span class="sr-only">Open sidebar</span>
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <div class="flex flex-1 items-center justify-end gap-2 sm:gap-3">
        <x-dropdown align="right" width="56">
            <x-slot name="trigger">
                <button type="button" class="flex items-center gap-2.5 rounded-xl border border-white/10 bg-white/[0.04] py-1.5 pl-1.5 pr-3 text-sm shadow-sm transition hover:bg-white/[0.08]">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-gradient text-xs font-bold text-white shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden max-w-[140px] truncate font-medium text-slate-200 sm:block">
                        {{ Auth::user()->name }}
                    </span>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="border-b border-white/[0.06] px-4 py-3">
                    <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                </div>
                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
