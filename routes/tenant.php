<?php

use App\Http\Controllers\Portal\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Portal\Auth\NewPasswordController;
use App\Http\Controllers\Portal\Auth\PasswordResetLinkController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\InviteController;
use App\Http\Controllers\Portal\PaymentProofController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('tenant.guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

        Route::get('welcome/{tenant}', [InviteController::class, 'show'])
            ->middleware('signed')
            ->name('invite.show');
        Route::post('welcome/{tenant}', [InviteController::class, 'store'])
            ->middleware('signed')
            ->name('invite.store');
    });

    Route::middleware(['auth:tenant', 'tenant.portal'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('payment-proofs', [PaymentProofController::class, 'index'])->name('payment-proofs.index');
        Route::post('payment-proofs', [PaymentProofController::class, 'store'])->name('payment-proofs.store');
        Route::get('payment-proofs/{paymentProof}/file', [\App\Http\Controllers\Portal\PaymentProofFileController::class, 'show'])
            ->name('payment-proofs.file');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
