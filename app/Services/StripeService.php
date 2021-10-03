<?php

namespace App\Services;

use App\Traits\ConsumesExternalServices;
use App\Contracts\PaymentService;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Str;

class StripeService implements PaymentService
{
    use ConsumesExternalServices;

    protected $secret;
    protected $stripeClient;
    protected $plans;

    public function __construct()
    {
        $this->secret = config('services.stripe.secret');
        $this->stripeClient = new StripeClient($this->secret);
        $this->plans = config('services.stripe.plans');
    }

    public function handlePayment(array $validated)
    {
        $intent = $this->createIntent($validated['amount'], $validated['currency'], $validated['payment_method']);

        session()->put('paymentIntentId', $intent?->id);

        return redirect()->route('approval');
    }

    public function handleApproval()
    {
        if (session()->has('paymentIntentId')) {
            $confirmation = $this->confirmPayment(session()->get('paymentIntentId'));

            if ($confirmation->status == 'requires_action') {
                $clientSecret = $confirmation->client_secret;

                return view('stripe.3d-secure', compact('clientSecret'));
            }

            if ($confirmation->status === 'succeeded') {
                $name = $confirmation->charges->data[0]->billing_details->name;
                $currency = Str::upper($confirmation->currency);
                $amount = $confirmation->amount / $this->resolveFactor($currency);

                return redirect()
                    ->route('home')
                    ->withSuccess(['payment' =>
                        "Thanks {$name}.
                        We received your {$amount} {$currency} payment."
                    ]);
            }
        }

        return redirect()
                ->route('home')
                ->withErrors('We cannot proceed with payment approval. Try again please!');
    }

    public function handleSubscription(array $validated)
    {
        $user = auth()->user();
        $customer = $this->createCustomer($user->name, $user->email, $validated['payment_method']);
        $subscription = $this->createSubscription($customer->id, $validated['payment_method'], $this->plans[$validated['plan']]);

        if ($subscription->status == 'active') {
            session()->put('subscriptionId', $subscription->id);
            return redirect()->route('subscribe.approval', [
                'plan' => $validated['plan'],
                'subscription_id' => $subscription->id,
            ]);
        }

        return redirect()
            ->route('subscribe.show')
            ->withErrors('We were unable to active the subscription. Try again, please!');
    }

    protected function createIntent($amount, $currency, $paymentMethod)
    {
        try {
            return $this->stripeClient->paymentIntents->create([
                'amount' => $amount * $this->resolveFactor($currency),
                'currency' => Str::lower($currency),
                'payment_method' => $paymentMethod,
                'confirmation_method' => 'manual',
            ]);
        } catch (ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function confirmPayment($paymentIntentId)
    {
        try {
            return $this->stripeClient->paymentIntents->confirm($paymentIntentId);
        } catch (ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function createCustomer($name, $email, $paymentMethod)
    {
        try {
            return $this->stripeClient->customers->create([
                'name' => $name,
                'email' => $email,
                'payment_method' => $paymentMethod
            ]);
        } catch (ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function createSubscription($customerId, $paymentMethod, $priceId)
    {
        try {
            return $this->stripeClient->subscriptions->create([
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId],
                ],
                'default_payment_method' => $paymentMethod
            ]);
        } catch (ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function validateSubscription(array $validated)
    {
        if (session()->has('subscriptionId')) {
            $subscriptionId = session()->get('subscriptionId');

            session()->forget('subscriptionId');

            return $subscriptionId == $validated['subscription_id'];
        }

        return false;
    }

    protected function resolveFactor($currency)
    {
        $zeroDecimalCurrencies = ['jpy'];

        if (in_array($currency, $zeroDecimalCurrencies)) {
            return 1;
        }

        return 100;
    }
}
