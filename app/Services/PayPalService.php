<?php

namespace App\Services;

use App\Traits\ConsumesExternalServices;
use App\Contracts\PaymentService;
use Str;

class PayPalService implements PaymentService
{
    use ConsumesExternalServices;

    protected $baseURI;
    protected $clientID;
    protected $clientSecret;
    protected $plans;

    public function __construct()
    {
        $this->baseURI = config('services.paypal.base_uri');
        $this->clientID = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->plans = config('services.paypal.plans');
    }

    public function handlePayment(array $validated)
    {
        $order = $this->createOrder($validated['amount'], $validated['currency']);

        session()->put('approvalId', $order->id);

        return redirect(collect($order->links)->where('rel', 'approve')->first()?->href);
    }

    public function handleApproval()
    {
        if (session()->has('approvalId')) {
            $payment = $this->capturePayment(session()->get('approvalId'));
            $amount = $payment->purchase_units[0]->payments->captures[0]->amount;
            return redirect()
                    ->route('home')
                    ->withSuccess(['payment' =>
                        "Thanks {$payment->payer->name->given_name}.
                        We received your {$amount->value} {$amount->currency_code} payment."
                    ]);
        }

        return redirect()
                ->route('home')
                ->withErrors('We cannot capture the payment. Please try again later!');
    }

    public function handleSubscription()
    {
        dd($this->plans);
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
        $credentials = base64_encode("{$this->clientID}:{$this->clientSecret}");

        return "Basic {$credentials}";
    }

    protected function createOrder($amount, $currency)
    {
        $factor = $this->resolveFactor($currency);

        return $this->makeRequest(
            method: 'POST',
            requestUrl: '/v2/checkout/orders',
            formParams: [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    0 => [
                        'amount' => [
                            'currency_code' => Str::upper($currency),
                            'value' => round($amount * $factor) / $factor,
                        ]
                    ]
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('approval'),
                    'cancel_url' => route('cancel'),
                ]
            ],
            isJsonRequest: true);
    }

    protected function capturePayment($approvalId)
    {
        return $this->makeRequest(
            'POST',
            "/v2/checkout/orders/{$approvalId}/capture",
            headers: [
                'Content-Type' => 'application/json',
            ],
        );
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
