<x-app-layout title="Add tenant" description="Set rent once — reminders, periods, and tracking run automatically.">
    <x-ui.card>
        <p class="mb-6 text-sm text-slate-400">
            Add who pays the rent and when it is due. We schedule rent periods, send reminders, and open their portal invite — no monthly admin.
        </p>
        <form method="POST" action="{{ route('tenants.store') }}" class="space-y-6">
            @csrf
            @include('tenants._form_create')
            <div class="flex gap-3 border-t border-white/[0.06] pt-6">
                <x-ui.button type="submit">Add tenant</x-ui.button>
                <x-ui.button variant="ghost" :href="route('tenants.index')">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-app-layout>
