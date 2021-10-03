@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Complete the security steps!</div>

                <div class="card-body">
                    <p>You need to follow the additional steps from your bank in order to complete this payment!</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    stripe.confirmCardPayment(
        "{{ $clientSecret }}",
        {payment_method: "{{ $paymentMethod }}"},
    )
        .then(result => {
            result.error
                ? window.location.replace("{{ route('subscribe.cancel') }}")
                : window.location.replace("{!! route('subscribe.approval', ['plan' => $plan, 'subscription_id' => $subscriptionId]) !!}");
        });
</script>
@endpush

@endsection
