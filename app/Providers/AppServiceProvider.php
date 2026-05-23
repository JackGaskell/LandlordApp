<?php

namespace App\Providers;

use App\Contracts\Billing\SubscriptionBillingGateway;
use App\Contracts\Notifications\PaymentProofNotifier;
use App\Contracts\Payments\RentPaymentGateway;
use App\Contracts\Webhooks\StripeWebhookVerifier;
use App\Events\PaymentProofs\PaymentProofApproved;
use App\Events\PaymentProofs\PaymentProofRejected;
use App\Events\PaymentProofs\PaymentProofSubmitted;
use App\Listeners\PaymentProofs\QueueLandlordPaymentProofNotification;
use App\Listeners\PaymentProofs\QueueTenantPaymentProofReviewNotification;
use App\Models\PaymentProof;
use App\Mail\Auth\ResetPasswordMail;
use App\Mail\Auth\VerifyEmailMail;
use App\Models\LandlordSetting;
use App\Models\PaymentHistory;
use App\Models\Tenant;
use App\Observers\PaymentHistoryObserver;
use App\Services\Billing\NullSubscriptionBillingGateway;
use App\Services\Billing\StripeSubscriptionBillingGateway;
use App\Services\Notifications\LogPaymentProofNotifier;
use App\Services\Payments\NullRentPaymentGateway;
use App\Services\Payments\PaymentProofQueryService;
use App\Services\Payments\StripeRentPaymentGateway;
use App\Services\Webhooks\Handlers\RentCheckoutCompletedHandler;
use App\Services\Webhooks\Handlers\SubscriptionCheckoutCompletedHandler;
use App\Services\Webhooks\Handlers\SubscriptionLifecycleHandler;
use App\Services\Webhooks\NullStripeWebhookVerifier;
use App\Services\Webhooks\StripeSignatureVerifier;
use App\Services\Reminders\Channels\EmailReminderChannel;
use App\Services\Reminders\ReminderChannelRegistry;
use App\Services\Webhooks\StripeWebhookDispatcher;
use App\Enums\ReminderChannel;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBillingGateways();
        $this->registerStripeWebhooks();

        $this->app->bind(PaymentProofNotifier::class, LogPaymentProofNotifier::class);

        $this->app->singleton(ReminderChannelRegistry::class, function ($app) {
            $registry = new ReminderChannelRegistry($app);
            $registry->register(ReminderChannel::Email, EmailReminderChannel::class);

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->registerScopedRouteBindings();
        $this->registerAuthMailTemplates();
        $this->registerPaymentProofEvents();
        $this->registerLandlordViewComposers();
        PaymentHistory::observe(PaymentHistoryObserver::class);
    }

    protected function registerPaymentProofEvents(): void
    {
        Event::listen(PaymentProofSubmitted::class, QueueLandlordPaymentProofNotification::class);
        Event::listen(PaymentProofApproved::class, QueueTenantPaymentProofReviewNotification::class);
        Event::listen(PaymentProofRejected::class, QueueTenantPaymentProofReviewNotification::class);
    }

    protected function registerLandlordViewComposers(): void
    {
        View::composer('layouts.partials.sidebar', function ($view) {
            if (auth()->check()) {
                $view->with(
                    'pendingProofCount',
                    app(PaymentProofQueryService::class)->pendingCountForLandlord(auth()->user()),
                );
            }
        });
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
            if (request()->is('portal/*')) {
                return Tenant::query()->whereKey($value)->firstOrFail();
            }

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

        Route::bind('payment_proof', function (string $value) {
            return PaymentProof::query()
                ->forLandlord(auth()->id())
                ->whereKey($value)
                ->firstOrFail();
        });
    }
}
