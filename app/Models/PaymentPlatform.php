<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPlatform extends Model
{
    protected $fillable = [
        'name',
        'image',
        'subscriptions_enabled',
    ];
    
    public function scopeWithEnabledSubscriptions($query)
    {
        return $query->where('subscriptions_enabled', true);
    }
}
