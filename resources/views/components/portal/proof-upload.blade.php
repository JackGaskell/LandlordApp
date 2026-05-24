@props(['paymentId', 'payment', 'compact' => false])

<form
    method="POST"
    action="{{ route('portal.payment-proofs.store') }}"
    enctype="multipart/form-data"
    x-data="{
        fileName: '',
        showNote: @js((bool) old('note')),
        handleFile(event) {
            const file = event.target.files?.[0];
            this.fileName = file ? file.name : '';
        }
    }"
    class="space-y-4"
>
    @csrf
    <input type="hidden" name="payment_id" value="{{ $paymentId }}">
    <input type="hidden" name="mark_as_paid" value="1">

    <div>
        <label for="proof" class="ui-file-drop py-5" :class="{ 'ui-file-drop-has-file': fileName }">
            <input
                id="proof"
                name="proof"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.heic,.webp,image/*"
                required
                class="sr-only"
                @change="handleFile($event)"
            />
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500/15 text-brand-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
            </span>
            <span class="mt-2 block text-xs font-medium text-white" x-show="! fileName">Add receipt or screenshot</span>
            <span class="mt-2 block text-xs font-medium text-emerald-300" x-show="fileName" x-text="fileName" x-cloak></span>
        </label>
        <x-input-error :messages="$errors->get('proof')" class="mt-2" />
    </div>

    <div>
        <label for="claimed_paid_at" class="text-[10px] font-medium uppercase tracking-wider text-slate-500">Date paid</label>
        <input
            id="claimed_paid_at"
            name="claimed_paid_at"
            type="date"
            value="{{ old('claimed_paid_at', now()->toDateString()) }}"
            max="{{ now()->toDateString() }}"
            class="ui-input mt-1.5 w-full"
        />
        <x-input-error :messages="$errors->get('claimed_paid_at')" class="mt-2" />
    </div>

    @unless ($compact)
        <div>
            <button
                type="button"
                class="text-xs font-medium text-slate-400 transition-colors hover:text-white"
                @click="showNote = ! showNote"
                x-text="showNote ? 'Hide note' : 'Add note'"
            ></button>
            <div x-show="showNote" x-transition x-cloak class="mt-2">
                <input
                    id="note"
                    name="note"
                    type="text"
                    value="{{ old('note') }}"
                    placeholder="Bank transfer reference"
                    class="ui-input w-full"
                />
                <x-input-error :messages="$errors->get('note')" class="mt-2" />
            </div>
        </div>
    @endunless

    <x-ui.button type="submit" class="w-full justify-center">
        Send for review
    </x-ui.button>
</form>
