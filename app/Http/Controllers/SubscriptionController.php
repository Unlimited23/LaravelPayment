<?php

namespace App\Http\Controllers;

use App\Models\{PaymentPlatform, Plan};
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function show()
    {
        return view('subscribe', [
            'plans' => Plan::all(),
            'paymentPlatforms' => PaymentPlatform::all(), //PaymentPlatform::withEnabledSubscriptions()->get(),
        ]);
    }

    public function store()
    {
        
    }

    public function approval()
    {
        
    }

    public function cancel()
    {
        
    }
}
