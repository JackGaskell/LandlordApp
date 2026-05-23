<?php

namespace Tests\Feature\PaymentProofs;

use App\Enums\PaymentProofStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVerificationStatus;
use App\Models\PaymentHistory;
use App\Models\PaymentProof;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_submit_proof_and_mark_rent_paid_pending_review(): void
    {
        Storage::fake('local');

        $tenant = Tenant::factory()->create([
            'password' => bcrypt('password'),
            'portal_enabled_at' => now(),
        ]);

        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create();

        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $this->actingAs($tenant, 'tenant')
            ->post(route('portal.payment-proofs.store'), [
                'payment_id' => $payment->id,
                'proof' => $file,
                'note' => 'Paid via bank transfer',
                'mark_as_paid' => '1',
                'claimed_paid_at' => now()->toDateString(),
            ])
            ->assertRedirect(route('portal.dashboard'));

        $payment->refresh();

        $this->assertDatabaseHas('payment_proofs', [
            'tenant_id' => $tenant->id,
            'payment_history_id' => $payment->id,
            'status' => PaymentProofStatus::Pending->value,
        ]);

        $this->assertNotNull($payment->paid_at);
        $this->assertSame(PaymentVerificationStatus::Pending, $payment->verification_status);
    }

    public function test_landlord_can_approve_proof_and_verify_payment(): void
    {
        Storage::fake('local');

        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create();
        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'verification_status' => PaymentVerificationStatus::Pending,
            'paid_at' => now(),
        ]);

        $proof = PaymentProof::factory()->for($tenant)->for($payment)->create([
            'status' => PaymentProofStatus::Pending,
            'tenant_marked_paid' => true,
            'claimed_paid_at' => now(),
        ]);

        $this->actingAs($landlord)
            ->post(route('payment-proofs.approve', $proof), [
                'landlord_note' => 'Received, thank you',
            ])
            ->assertRedirect(route('payment-proofs.show', $proof));

        $proof->refresh();
        $payment->refresh();

        $this->assertSame(PaymentProofStatus::Approved, $proof->status);
        $this->assertSame(PaymentVerificationStatus::Verified, $payment->verification_status);
        $this->assertSame(PaymentStatus::Paid, $payment->status);
    }

    public function test_landlord_can_reject_proof_and_reset_unverified_payment(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create();
        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create([
            'paid_at' => now(),
            'verification_status' => PaymentVerificationStatus::Pending,
        ]);

        $proof = PaymentProof::factory()->for($tenant)->for($payment)->create([
            'tenant_marked_paid' => true,
        ]);

        $this->actingAs($landlord)
            ->post(route('payment-proofs.reject', $proof), [
                'landlord_note' => 'Amount does not match',
            ])
            ->assertRedirect(route('payment-proofs.show', $proof));

        $proof->refresh();
        $payment->refresh();

        $this->assertSame(PaymentProofStatus::Rejected, $proof->status);
        $this->assertNull($payment->paid_at);
        $this->assertSame(PaymentVerificationStatus::Disputed, $payment->verification_status);
    }

    public function test_landlord_cannot_review_other_landlords_proof(): void
    {
        $proof = PaymentProof::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('payment-proofs.approve', $proof))
            ->assertNotFound();
    }

    public function test_duplicate_pending_proof_is_blocked(): void
    {
        Storage::fake('local');

        $tenant = Tenant::factory()->create([
            'password' => bcrypt('password'),
            'portal_enabled_at' => now(),
        ]);

        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create();

        PaymentProof::factory()->for($tenant)->for($payment)->create([
            'status' => PaymentProofStatus::Pending,
        ]);

        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $this->actingAs($tenant, 'tenant')
            ->post(route('portal.payment-proofs.store'), [
                'payment_id' => $payment->id,
                'proof' => $file,
                'mark_as_paid' => '1',
            ])
            ->assertSessionHasErrors('proof');
    }
}
