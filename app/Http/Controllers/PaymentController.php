<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentPlatformResolver;
use App\Http\Requests\PaymentStoreRequest;

class PaymentController extends Controller
{
    public function __construct(protected PaymentPlatformResolver $resolver)
    {
        $this->middleware('auth');
    }

    public function pay(PaymentStoreRequest $request)
    {
        $validated = $request->validated();

        session()->put('paymentPlatformId', $validated['payment_platform']);

        return $this->resolver->resolveService($validated['payment_platform'])->handlePayment($validated);
    }
    
    public function approval()
    {
        if (session()->has('paymentPlatformId')) {
            return $this->resolver->resolveService(session()->get('paymentPlatformId'))->handleApproval();
        }

        return redirect()
                ->route('home')
                ->withErrors('We cannot retrieve your payment platform. Try again, please!');
    }

    public function cancel()
    {
        return redirect()
                ->route('home')
                ->withErrors('You cancelled the payment');
    }
}
