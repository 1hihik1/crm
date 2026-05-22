<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workplace extends Model
{
    protected $fillable = ['room_id', 'name'];

    public function room() { return $this->belongsTo(Room::class); }
    public function orders() { return $this->hasMany(Order::class); }
}
