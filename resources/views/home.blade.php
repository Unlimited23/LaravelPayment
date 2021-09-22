@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('dashboard.payments.make') }}</div>

                <div class="card-body">
                  <form action="{{ route('pay') }}" method="post" id="paymentForm">
                      @csrf
                      <div class="row">
                        <div class="col-auto">
                          <label>@lang('dashboard.payments.amount'):</label>
                          <input type="number"
                            name="amount"
                            min="5"
                            step="0.01"
                            class="form-control"
                            value="{{ mt_rand(500, 100000) / 100 }}"
                            required
                          />
                          <small class="form-text text-muted">@lang('dashboard.payments.amount_info')</small>
                        </div>
                        <div class="col-auto">
                          <label>@lang('dashboard.payments.currency'):</label>
                          <select class="form-select" name="currency" required>
                              @foreach($currencies as $currency)
                                <option value="{{ $currency->iso }}">
                                  {{ Str::upper($currency->iso) }}
                                </option>
                              @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="row">
                          <label>@lang('dashboard.payments.platform_select')</label>
                          <div class="form-group" id="toggler">
                            <div class="btn-group btn-group-toggle"
                                data-toggle="buttons">
                                @foreach($paymentPlatforms as $paymentPlatform)
                                    <label class="btn btn-secondary rounded mt-2 p-1 m-2" 
                                        data-bs-target="#{{ $paymentPlatform->name }}Collapse"
                                        data-bs-toggle="collapse">
                                      <input type="radio" name="payment_platform" value="{{ $paymentPlatform->id }}" required>
                                      <img src="{{ asset($paymentPlatform->image) }}" class="img-thumbnail"> 
                                    </label>
                                @endforeach
                            </div>
                            @foreach($paymentPlatforms as $paymentPlatform)
                                <div id="{{ $paymentPlatform->name }}Collapse" class="collapse" data-bs-parent="#toggler">
                                  @includeIf('components.'. Str::lower($paymentPlatform->name) . '-collapse')
                                </div>
                            @endforeach
                          </div>
                        </div>
                      </div>
                        <div class="row">
                            <div class="col-auto">
                                <p class="border-bottom border-primary rounded">
                                    @if(!auth()->user()?->hasActiveSubscription())
                                        <span>Would you like a discount every time</span>
                                        <a class="btn btn-outline-primary" href="{{ route('subscribe.show') }}">Subscribe</a>
                                    @else
                                        You get a <strong>10% off</strong> as part of your subscription? (Applied on checkout.)
                                    @endif
                                </p>
                            </div>
                        </div>
                      <div class="text-center mt-3">
                        <button id="btnPay" class="btn btn-primary btn-lg" action="submit">@lang('dashboard.payments.pay')</button>
                      </div>
                  </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
