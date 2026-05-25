<?php

namespace Tests\Feature\Mail;

use App\Mail\Transactional\PaymentConfirmationSubmittedMail;
use App\Models\PaymentHistory;
use App\Models\PaymentProof;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentConfirmationSubmittedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_landlord_receives_email_when_tenant_submits_confirmation(): void
    {
        Mail::fake();
        Storage::fake('local');

        $landlord = User::factory()->create(['email' => 'landlord@example.com']);
        $tenant = Tenant::factory()->for($landlord)->create([
            'password' => Hash::make('password'),
            'portal_enabled_at' => now(),
        ]);
        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create();

        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $this->actingAs($tenant, 'tenant')
            ->post(route('portal.payment-proofs.store'), [
                'payment_id' => $payment->id,
                'proof' => $file,
                'note' => 'Paid today',
                'mark_as_paid' => '1',
                'claimed_paid_at' => now()->toDateString(),
            ])
            ->assertRedirect(route('portal.dashboard'));

        Mail::assertSent(PaymentConfirmationSubmittedMail::class, function (PaymentConfirmationSubmittedMail $mail) use ($landlord) {
            return $mail->hasTo($landlord->email);
        });
    }

    public function test_confirmation_submitted_mail_renders_review_link(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::factory()->for($landlord)->create(['name' => 'Sam Tenant']);
        $payment = PaymentHistory::factory()->dueSoon()->for($tenant)->create();
        $proof = PaymentProof::factory()->for($tenant)->for($payment)->create();

        $html = (new PaymentConfirmationSubmittedMail($proof->load(['tenant.landlord', 'paymentHistory'])))->render();

        $this->assertStringContainsString('Sam Tenant', $html);
        $this->assertStringContainsString(route('payment-proofs.show', $proof), $html);
    }
}
