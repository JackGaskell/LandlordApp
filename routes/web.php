<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandlordSettingController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('payment-proofs', [\App\Http\Controllers\Landlord\PaymentProofController::class, 'index'])
        ->name('payment-proofs.index');
    Route::get('payment-proofs/{payment_proof}', [\App\Http\Controllers\Landlord\PaymentProofController::class, 'show'])
        ->name('payment-proofs.show');
    Route::get('payment-proofs/{payment_proof}/file', [\App\Http\Controllers\Landlord\PaymentProofFileController::class, 'show'])
        ->name('payment-proofs.file');
    Route::post('payment-proofs/{payment_proof}/approve', [\App\Http\Controllers\Landlord\PaymentProofReviewController::class, 'approve'])
        ->name('payment-proofs.approve');
    Route::post('payment-proofs/{payment_proof}/reject', [\App\Http\Controllers\Landlord\PaymentProofReviewController::class, 'reject'])
        ->name('payment-proofs.reject');

    Route::resource('tenants', TenantController::class);

    Route::post('tenants/{tenant}/portal', [\App\Http\Controllers\TenantPortalController::class, 'store'])
        ->name('tenants.portal.store');

    Route::post('tenants/{tenant}/payments', [PaymentHistoryController::class, 'store'])
        ->name('tenants.payments.store');

    Route::patch('payments/{payment}', [PaymentHistoryController::class, 'update'])
        ->name('payments.update');

    Route::post('payments/{payment}/mark-paid', [PaymentHistoryController::class, 'markPaid'])
        ->name('payments.mark-paid');

    Route::delete('payments/{payment}', [PaymentHistoryController::class, 'destroy'])
        ->name('payments.destroy');

    Route::get('settings/reminders', [LandlordSettingController::class, 'edit'])
        ->name('settings.edit');

    Route::put('settings/{setting}', [LandlordSettingController::class, 'update'])
        ->name('settings.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/stripe/webhook', \App\Http\Controllers\StripeWebhookController::class)
    ->name('stripe.webhook');

/*
| Stripe Checkout (uncomment when UI is ready)
| Route::middleware(['auth', 'verified'])->group(function () {
|     Route::post('/billing/checkout', [\App\Http\Controllers\BillingController::class, 'checkout'])
|         ->name('billing.checkout');
|     Route::get('/billing/portal', [\App\Http\Controllers\BillingController::class, 'portal'])
|         ->name('billing.portal');
|     Route::post('/payments/{payment}/checkout', [\App\Http\Controllers\RentCheckoutController::class, 'store'])
|         ->name('payments.checkout');
| });
*/

require __DIR__.'/auth.php';
