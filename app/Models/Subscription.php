<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $guarded = ['id'];
    
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
