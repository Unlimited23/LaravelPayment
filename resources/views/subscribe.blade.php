@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">@lang('dashboard.payments.subscribe')</div>

                <div class="card-body">
                  <form action="{{ route('subscribe.store') }}" method="post" id="paymentForm">
                      @csrf
                      <div class="row mt-3">
                        <div class="row">
                          <label>@lang('dashboard.payments.platform_select')</label>
                          <div class="form-group">
                            <div class="btn-group btn-group-toggle">
                                @foreach($plans as $plan)
                                    <label class="btn btn-outline-info rounded mt-2 p-3 m-2">
                                      <input type="radio" name="plan" value="{{ $plan->slug }}" required>
                                      <p class="h2 bold text-uppercase">{{ $plan->slug }}</p>
                                      <p class="display-4 text-uppercase">{{ $plan->visual_price }}</p>
                                    </label>
                                @endforeach
                            </div>
                          </div>
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

                      <div class="text-center mt-3">
                        <button id="btnSubscribe" class="btn btn-primary btn-lg" action="submit">@lang('dashboard.payments.subscribe')</button>
                      </div>
                  </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
