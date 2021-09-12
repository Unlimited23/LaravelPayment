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

    protected $baseURI;
    protected $key;
    protected $secret;
    protected $stripeClient;

    public function __construct()
    {
        $this->baseURI = config('services.stripe.base_uri');
        $this->key = config('services.stripe.key');
        $this->secret = config('services.stripe.secret');
        $this->stripeClient = new StripeClient(config('services.stripe.secret'));
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

    protected function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['Authorization'] = $this->resolveAccessToken();
    }

    protected function decodeResponse($response)
    {
        return json_decode($response);
    }

    protected function resolveAccessToken()
    {
        return "Bearer {$this->secret}";
    }

    protected function createIntent($amount, $currency, $paymentMethod)
    {
        try {
            return $this->stripeClient->paymentIntents->create([
                'amount' => $amount * $this->resolveFactor($currency),
                'currency' => Str::lower($currency),
                'payment_method' => $paymentMethod,
                'confirmation_method' => 'manual',
                'payment_method_types' => ['card'],
            ]);
        } catch (ApiErrorException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function confirmPayment($paymentIntentId)
    {
        try {
            return $this->stripeClient->paymentIntents->confirm(
                $paymentIntentId,
                ['payment_method' => 'pm_card_visa']
            );
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
