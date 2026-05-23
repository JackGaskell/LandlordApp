@props(['paymentId', 'payment'])

<form method="POST" action="{{ route('portal.payment-proofs.store') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    <input type="hidden" name="payment_id" value="{{ $paymentId }}">

    <div class="rounded-xl border border-white/[0.08] bg-white/[0.03] p-4">
        <label class="flex cursor-pointer items-start gap-3">
            <input
                type="checkbox"
                name="mark_as_paid"
                value="1"
                class="ui-checkbox mt-0.5"
                checked
            />
            <span>
                <span class="text-sm font-medium text-white">I've paid this rent</span>
                <span class="mt-0.5 block text-xs text-slate-500">Your landlord will confirm after reviewing your proof.</span>
            </span>
        </label>
    </div>

    <div>
        <label class="text-sm font-medium text-slate-300" for="claimed_paid_at">Date you paid</label>
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
        <p class="mt-1 text-xs text-slate-500">Screenshot, bank receipt, or PDF (max {{ (int) config('landlord.payment_proofs.max_kb') }}KB)</p>
        <input
            id="proof"
            name="proof"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png,.heic,.webp,image/*"
            required
            class="mt-2 block w-full text-sm text-slate-400 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500/20 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-200 hover:file:bg-brand-500/30"
        />
        <x-input-error :messages="$errors->get('proof')" class="mt-2" />
    </div>

    <div>
        <label class="text-sm font-medium text-slate-300" for="note">Note for your landlord (optional)</label>
        <textarea
            id="note"
            name="note"
            rows="3"
            class="ui-input mt-1.5 w-full"
            placeholder="e.g. Bank transfer ref 48291 — paid Tuesday morning"
        >{{ old('note') }}</textarea>
        <x-input-error :messages="$errors->get('note')" class="mt-2" />
    </div>

    @if ($payment ?? false)
        <p class="text-xs text-slate-500">
            Submitting for <span class="text-slate-300">{{ $payment->due_date->format('F Y') }}</span> · {{ '£'.number_format($payment->amount, 2) }}
        </p>
    @endif

    <x-ui.button type="submit" class="w-full justify-center">Submit for review</x-ui.button>
</form>
