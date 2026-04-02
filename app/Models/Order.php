<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'car_id', 'workplace_id', 'employee_id', 'ordered_at', 'deadline', 'completed_at', 'status', 'total_amount'];

    protected $casts = [
        'ordered_at' => 'datetime',
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function client() { return $this->belongsTo(User::class, 'user_id'); }
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
    public function car() { return $this->belongsTo(Car::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
