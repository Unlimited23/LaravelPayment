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

    public function __construct()
    {
        $this->secret = config('services.stripe.secret');
        $this->stripeClient = new StripeClient($this->secret);
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

    protected function resolveFactor($currency)
    {
        $zeroDecimalCurrencies = ['jpy'];

        if (in_array($currency, $zeroDecimalCurrencies)) {
            return 1;
        }

        return 100;
    }
}
