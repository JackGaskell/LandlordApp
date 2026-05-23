@props(['paymentId', 'payment'])

<form method="POST" action="{{ route('portal.payment-proofs.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <input type="hidden" name="payment_id" value="{{ $paymentId }}">
    <input type="hidden" name="mark_as_paid" value="1">

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium text-slate-300" for="claimed_paid_at">Date paid</label>
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

        <div>
            <label class="text-sm font-medium text-slate-300" for="proof">Payment proof</label>
            <input
                id="proof"
                name="proof"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.heic,.webp,image/*"
                required
                class="mt-1.5 block w-full text-sm text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-500/20 file:px-3 file:py-2 file:text-xs file:font-medium file:text-brand-200 hover:file:bg-brand-500/30"
            />
            <x-input-error :messages="$errors->get('proof')" class="mt-2" />
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-300" for="note">Note <span class="font-normal text-slate-500">(optional)</span></label>
        <input
            id="note"
            name="note"
            type="text"
            value="{{ old('note') }}"
            placeholder="Bank transfer reference"
            class="ui-input mt-1.5 w-full"
        />
        <x-input-error :messages="$errors->get('note')" class="mt-2" />
    </div>

    <x-ui.button type="submit" class="w-full justify-center">Submit for review</x-ui.button>
</form>
