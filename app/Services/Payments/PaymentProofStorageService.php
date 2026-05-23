<?php

namespace App\Services\Payments;

use App\Models\PaymentProof;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofStorageService
{
    public function disk(): string
    {
        return (string) config('landlord.payment_proofs.disk', 'local');
    }

    public function store(Tenant $tenant, UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?? 'bin');
        $allowed = config('landlord.payment_proofs.allowed_extensions', []);

        if (! in_array($extension, $allowed, true)) {
            abort(422, 'This file type is not allowed.');
        }

        $directory = trim(config('landlord.payment_proofs.directory', 'payment-proofs'), '/').'/'.$tenant->id;
        $filename = Str::uuid()->toString().'.'.$extension;

        $path = $file->storeAs($directory, $filename, $this->disk());

        return [
            'disk' => $this->disk(),
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => $file->getSize(),
        ];
    }

    public function delete(PaymentProof $proof): void
    {
        if ($proof->path && Storage::disk($proof->disk)->exists($proof->path)) {
            Storage::disk($proof->disk)->delete($proof->path);
        }
    }

    public function downloadResponse(PaymentProof $proof): StreamedResponse
    {
        abort_unless($proof->fileExists(), 404);

        return Storage::disk($proof->disk)->download(
            $proof->path,
            $proof->original_filename,
        );
    }
}
