<x-app-layout :title="'Edit '.$tenant->name">
    <x-ui.card>
        <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="space-y-6">
            @csrf
            @method('PATCH')
            @include('tenants._form', ['tenant' => $tenant])
            <div class="flex gap-3 border-t border-zinc-200 pt-6">
                <x-ui.button type="submit">Update tenant</x-ui.button>
                <x-ui.button variant="ghost" :href="route('tenants.show', $tenant)">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
