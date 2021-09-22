<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = ['id'];
    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    public function getVisualPriceAttribute()
    {
        return '$' . number_format($this->price, 2, '.', ',');
    }
}
