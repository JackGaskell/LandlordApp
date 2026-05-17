<x-app-layout title="Add tenant" description="Create a tenant to track rent and reminders.">
    <x-ui.card>
        <form method="POST" action="{{ route('tenants.store') }}" class="space-y-6">
            @csrf
            @include('tenants._form')
            <div class="flex gap-3 border-t border-zinc-200 pt-6">
                <x-ui.button type="submit">Save tenant</x-ui.button>
                <x-ui.button variant="ghost" :href="route('tenants.index')">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
