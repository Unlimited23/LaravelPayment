<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentPlatformResolver;
use App\Models\{PaymentPlatform, Plan};
use App\Http\Requests\Subscriptions\StoreRequest;

class SubscriptionController extends Controller
{
    public function __construct(protected PaymentPlatformResolver $resolver)
    {
        $this->middleware('auth');
    }

    public function show()
    {
        return view('subscribe', [
            'plans' => Plan::all(),
            'paymentPlatforms' => PaymentPlatform::withEnabledSubscriptions()->get(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        session()->put('subscriptionPlatformId', $validated['payment_platform']);

        return $this->resolver->resolveService($validated['payment_platform'])->handleSubscription($validated);
    }

    public function approval()
    {
        dd('approval');
    }

    public function cancel()
    {
        dd('cancel');
    }
}
