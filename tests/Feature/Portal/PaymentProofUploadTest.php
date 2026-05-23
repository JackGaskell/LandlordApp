<?php

namespace Tests\Feature\Portal;

use App\Enums\PaymentProofStatus;
use App\Models\PaymentHistory;
use App\Models\PaymentProof;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_upload_payment_proof(): void
    {
        Storage::fake('local');

        $tenant = Tenant::factory()->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);

        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create();

        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $this->actingAs($tenant, 'tenant')
            ->post(route('portal.payment-proofs.store'), [
                'payment_id' => $payment->id,
                'proof' => $file,
                'note' => 'Paid yesterday via bank transfer',
                'mark_as_paid' => '1',
                'claimed_paid_at' => now()->toDateString(),
            ])
            ->assertRedirect(route('portal.dashboard'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payment_proofs', [
            'tenant_id' => $tenant->id,
            'payment_history_id' => $payment->id,
            'status' => PaymentProofStatus::Pending->value,
        ]);

        $proof = PaymentProof::query()->first();
        Storage::disk('local')->assertExists($proof->path);
    }
}
