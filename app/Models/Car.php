<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = ['user_id', 'brand', 'model', 'year', 'vin', 'license_plate'];

    public function owner() { return $this->belongsTo(User::class, 'user_id'); }
    public function orders() { return $this->hasMany(Order::class); }
}
