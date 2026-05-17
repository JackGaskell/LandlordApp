<?php

namespace App\Providers;

use App\Contracts\Billing\SubscriptionBillingGateway;
use App\Contracts\Payments\RentPaymentGateway;
use App\Contracts\Webhooks\StripeWebhookVerifier;
use App\Mail\Auth\ResetPasswordMail;
use App\Mail\Auth\VerifyEmailMail;
use App\Models\LandlordSetting;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Services\Billing\NullSubscriptionBillingGateway;
use App\Services\Billing\StripeSubscriptionBillingGateway;
use App\Services\Payments\NullRentPaymentGateway;
use App\Services\Payments\StripeRentPaymentGateway;
use App\Services\Webhooks\Handlers\RentCheckoutCompletedHandler;
use App\Services\Webhooks\Handlers\SubscriptionCheckoutCompletedHandler;
use App\Services\Webhooks\Handlers\SubscriptionLifecycleHandler;
use App\Services\Webhooks\NullStripeWebhookVerifier;
use App\Services\Webhooks\StripeSignatureVerifier;
use App\Services\Webhooks\StripeWebhookDispatcher;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBillingGateways();
        $this->registerStripeWebhooks();
    }

    public function boot(): void
    {
        $this->registerScopedRouteBindings();
        $this->registerAuthMailTemplates();
    }

    protected function registerBillingGateways(): void
    {
        $this->app->bind(SubscriptionBillingGateway::class, function ($app) {
            if (! config('landlord.stripe.enabled')) {
                return $app->make(NullSubscriptionBillingGateway::class);
            }

            return $app->make(StripeSubscriptionBillingGateway::class);
        });

        $this->app->bind(RentPaymentGateway::class, function ($app) {
            if (! config('landlord.stripe.enabled')) {
                return $app->make(NullRentPaymentGateway::class);
            }

            return $app->make(StripeRentPaymentGateway::class);
        });
    }

    protected function registerStripeWebhooks(): void
    {
        $this->app->bind(StripeWebhookVerifier::class, function ($app) {
            if (filled(config('services.stripe.webhook_secret'))) {
                return $app->make(StripeSignatureVerifier::class);
            }

            return $app->make(NullStripeWebhookVerifier::class);
        });

        $this->app->tag([
            SubscriptionCheckoutCompletedHandler::class,
            RentCheckoutCompletedHandler::class,
            SubscriptionLifecycleHandler::class,
        ], 'stripe.webhook.handlers');

        $this->app->singleton(StripeWebhookDispatcher::class, function ($app) {
            return new StripeWebhookDispatcher(
                $app->tagged('stripe.webhook.handlers'),
            );
        });
    }

    protected function registerAuthMailTemplates(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new VerifyEmailMail($url, $notifiable->name))
                ->to($notifiable->getEmailForVerification());
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new ResetPasswordMail(
                resetUrl: $url,
                userName: $notifiable->name,
                expireMinutes: (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ))->to($notifiable->getEmailForPasswordReset());
        });
    }

    protected function registerScopedRouteBindings(): void
    {
        Route::bind('tenant', function (string $value) {
            return Tenant::query()
                ->whereKey($value)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        });

        Route::bind('payment', function (string $value) {
            return PaymentHistory::query()
                ->whereKey($value)
                ->forLandlord(auth()->id())
                ->firstOrFail();
        });

        Route::bind('setting', function (string $value) {
            return LandlordSetting::query()
                ->whereKey($value)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        });
    }
}
