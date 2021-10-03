<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentPlatformResolver;
use App\Models\{PaymentPlatform, Plan, Subscription};
use App\Http\Requests\Subscriptions\{ApprovalRequest, StoreRequest};

class SubscriptionController extends Controller
{
    public function __construct(protected PaymentPlatformResolver $resolver)
    {
        $this->middleware(['auth', 'unsubscribed']);
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

    public function approval(ApprovalRequest $request)
    {
        $validated = $request->validated();

        if (session()->has('subscriptionPlatformId')) {
            $paymentPlatform = $this->resolver->resolveService(session()->get('subscriptionPlatformId'));

            if ($paymentPlatform->validateSubscription($validated)) {
                $plan = Plan::where('slug', $validated['plan'])->firstOrFail();
                $user = $request->user();

                $subscription = Subscription::create([
                    'active_until' => now()->addDays($plan->duration_in_days),
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ]);

                return redirect()
                    ->route('home')
                    ->withSuccess(['payment' => "Thanks {$user->name}. You have have {$plan->slug} subscription. Start using it now."]);
            }
        }

        return redirect()
            ->route('subscribe.show')
            ->withErrors('We cannot check your subscription. Try again, please.');
    }

    public function cancel()
    {
        return redirect()
            ->route('subscribe.show')
            ->withErrors('You cancelled. Come back whenever you\'re ready');
    }
}
