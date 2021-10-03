<?php

namespace App\Http\Requests\Subscriptions;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return array_merge([
            'plan' => 'required|exists:plans,slug',
            'payment_platform' => 'required|exists:payment_platforms,id',
        ], $this->payment_platform == 2 ? [ // stripe
            'payment_method' => 'required|string',
        ] : []);
    }
}
