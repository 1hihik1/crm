<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'user_id', 'paid_at', 'amount', 'method'];

    protected $casts = ['paid_at' => 'datetime'];

    public function order() { return $this->belongsTo(Order::class); }
}
